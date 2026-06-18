# CLAUDE.md — Gestion de Stock (Entrées / Sorties)

> **Réf. projet :** MRTECH-DEV-STOCK-2026-01
> **Client :** Khal (société à préciser)
> **Éditeur :** MRTECH — MEBODO Richard Aristide, Architecte Solutions Digitales
> **Statut :** spec verrouillée — prêt pour sprint Claude Code

Ce fichier est la référence unique pour le développement. Toute décision technique doit rester cohérente avec ce document. En cas d'ambiguïté, privilégier la simplicité et le périmètre verrouillé ci-dessous — **ne pas ajouter de fonctionnalité hors scope**.

---

## 1. Contexte & objectif

Application web interne de **gestion de stock** pour une activité de distribution. Le client suit ses **entrées** (réapprovisionnements) et ses **sorties** (livraisons) de matériel, avec calcul automatique du **stock restant** sur les articles concernés. Les sorties enregistrent le **nom du livreur** et la destination/client.

Usage **interne uniquement**, sur **PC**, par **4 utilisateurs** (1 admin + 3 opérateurs). Pas d'accès public, pas de client final sur l'appli.

---

## 2. Périmètre

### Inclus (à livrer)
- Authentification + gestion de 2 rôles (admin / opérateur)
- Référentiel **articles** avec drapeau « suivi de stock » par article + seuil d'alerte
- Saisie des **entrées** (réappro)
- Saisie des **sorties** (avec nom du livreur + destination/client)
- **Calcul automatique du stock restant** sur les articles suivis
- **Tableau de bord** : niveaux, alertes stock bas, derniers mouvements
- **Historique** des mouvements filtrable + recherche
- **Export Excel** (historique des mouvements + état du stock)
- Déploiement VPS + formation utilisateurs

### Explicitement HORS scope (ne pas développer)
- ❌ Temps réel / WebSocket / Reverb — le client accepte un rafraîchissement manuel
- ❌ Bon de sortie / décharge PDF signée — non retenu par le client
- ❌ Application mobile
- ❌ Géolocalisation du livreur / carte
- ❌ Multi-entrepôts (un seul emplacement de stock)
- ❌ Gestion fournisseurs avancée, commandes, facturation
- ❌ Codes-barres / scan (note : extension future possible, ne pas implémenter)

---

## 3. Stack technique

| Couche | Choix | Note |
|---|---|---|
| Backend | **Laravel 11** (PHP 8.2+) | API REST |
| Frontend | **Vue.js 3** (Composition API) + Vite | SPA |
| State | Pinia | |
| Routing | Vue Router | |
| HTTP | Axios | intercepteur token + gestion 401 |
| UI | Tailwind CSS | interface sobre, dense, orientée saisie |
| DB | **MySQL 8** | |
| Auth | Laravel Sanctum | SPA / token |
| Export | `maatwebsite/excel` | .xlsx |
| Serveur | VPS DigitalOcean, Nginx, PHP-FPM | self-hosted, coûts récurrents minimes |

**Contraintes locales (Gabon) :**
- Connectivité variable (2G/3G) → frontend léger, pagination systématique, payloads minimaux, pas de dépendances lourdes inutiles.
- Pas d'adressage postal fiable → la « destination » d'une sortie est un champ texte libre.
- Tout self-hosted, aucune dépendance SaaS payante.

---

## 4. Modèle de données

> **Principe stock :** la table `mouvements` est la **source de vérité** (journal d'audit immuable côté opérateur). La colonne `articles.stock_actuel` est un **cache dénormalisé** mis à jour **dans une transaction** à chaque mouvement, pour un dashboard rapide. Une commande Artisan permet de recalculer entièrement le stock depuis les mouvements.

### `users`
| Champ | Type | Note |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | bcrypt |
| role | enum('admin','operateur') | |
| actif | boolean | défaut true |
| timestamps | | |

### `categories` (optionnel, léger — pour organiser les articles)
| Champ | Type |
|---|---|
| id | bigint PK |
| nom | string |
| timestamps | |

### `articles`
| Champ | Type | Note |
|---|---|---|
| id | bigint PK | |
| reference | string unique | code/SKU saisi par l'admin |
| designation | string | |
| categorie_id | bigint FK nullable | |
| unite | string | ex : pièce, carton, sac (défaut « pièce ») |
| suivi_stock | boolean | si false : mouvements tracés sans niveau de stock |
| seuil_alerte | integer nullable | pertinent uniquement si suivi_stock = true |
| stock_initial | integer | défaut 0 |
| stock_actuel | integer | **cache** — maintenu transactionnellement |
| actif | boolean | défaut true (soft désactivation au lieu de suppression) |
| timestamps | | |

### `mouvements`
| Champ | Type | Note |
|---|---|---|
| id | bigint PK | |
| article_id | bigint FK | |
| type | enum('entree','sortie') | |
| quantite | integer | > 0 |
| date_mouvement | date | défaut aujourd'hui |
| livreur | string nullable | **obligatoire si type = sortie** |
| destination | string nullable | client/lieu — texte libre (sortie) |
| source | string nullable | fournisseur/origine (entrée) |
| note | text nullable | |
| user_id | bigint FK | auteur de la saisie (audit) |
| timestamps | | |

Index : `(article_id, date_mouvement)`, `(type)`, `(livreur)`, `(date_mouvement)`.

---

## 5. Règles métier

1. **Quantité** toujours strictement positive. Le `type` détermine le sens.
2. **Entrée** sur article `suivi_stock` → `stock_actuel += quantite`.
3. **Sortie** sur article `suivi_stock` → `stock_actuel -= quantite`.
4. **Blocage stock négatif** : une sortie est refusée (422 + message clair) si `quantite > stock_actuel` sur un article suivi.
5. **Articles non suivis** (`suivi_stock = false`) : le mouvement est enregistré mais **n'affecte pas** `stock_actuel` et n'est jamais bloqué.
6. **Alerte stock bas** : un article suivi est « en alerte » si `stock_actuel <= seuil_alerte` (seuil défini, non null).
7. **Atomicité** : création/modification/suppression d'un mouvement + mise à jour de `stock_actuel` se font dans une **transaction DB unique**.
8. **Audit** : tout mouvement enregistre `user_id` et l'horodatage.
9. **Immutabilité côté opérateur** : un opérateur ne peut que **créer** des mouvements. Seul l'admin peut éditer/supprimer un mouvement, ce qui **déclenche un recalcul** du `stock_actuel` de l'article concerné (toujours dans une transaction).
10. **Commande de réconciliation** : `php artisan stock:recompute` recalcule `stock_actuel` de tous les articles à partir de `stock_initial` + somme des mouvements. Filet de sécurité.

---

## 6. Rôles & permissions (Laravel Policies)

| Action | Admin | Opérateur |
|---|:---:|:---:|
| Se connecter | ✓ | ✓ |
| Voir dashboard / stock / historique | ✓ | ✓ |
| Créer entrée / sortie | ✓ | ✓ |
| Éditer / supprimer un mouvement | ✓ | ✗ |
| Gérer le référentiel articles | ✓ | ✗ |
| Gérer les catégories | ✓ | ✗ |
| Gérer les utilisateurs | ✓ | ✗ |
| Exporter (Excel) | ✓ | ✓ |

---

## 7. Écrans (Vue 3)

1. **Login** — email + mot de passe.
2. **Dashboard** — cartes synthèse (nb articles suivis, nb en alerte, mouvements du jour), liste des articles **en alerte stock bas**, table des **derniers mouvements**.
3. **Saisie mouvement** — formulaire unique avec bascule Entrée / Sortie :
   - Entrée : article (recherche), quantité, date, source (fournisseur), note.
   - Sortie : article (recherche), quantité, date, **livreur (obligatoire)**, destination/client, note. Affiche le stock disponible de l'article sélectionné et bloque si insuffisant.
4. **Stock** — état des niveaux actuels des articles suivis, indicateur d'alerte, tri/filtre par catégorie.
5. **Historique** — table paginée des mouvements, filtres : type, article, livreur, plage de dates ; recherche plein texte ; bouton **Export Excel**.
6. **Articles** (admin) — CRUD référentiel, flag suivi + seuil, catégorie, activation/désactivation.
7. **Utilisateurs** (admin) — CRUD comptes + rôle + activation.

UI : sobre, dense, pensée pour la saisie rapide au clavier. Validation inline, messages d'erreur explicites en français.

---

## 8. API REST (préfixe `/api`)

```
POST   /login                 # Sanctum
POST   /logout
GET    /me

# Articles (lecture: tous | écriture: admin)
GET    /articles              # ?search=&categorie_id=&actif=
POST   /articles              # admin
GET    /articles/{id}
PUT    /articles/{id}         # admin
DELETE /articles/{id}         # admin (soft -> actif=false)

# Catégories
GET    /categories
POST   /categories            # admin
PUT    /categories/{id}       # admin
DELETE /categories/{id}       # admin

# Mouvements
GET    /mouvements            # ?type=&article_id=&livreur=&date_from=&date_to=&search=&page=
POST   /mouvements/entree
POST   /mouvements/sortie
PUT    /mouvements/{id}       # admin (recalcul stock)
DELETE /mouvements/{id}       # admin (recalcul stock)

# Stock & dashboard
GET    /stock                 # niveaux actuels (articles suivis)
GET    /stock/alertes         # articles en alerte
GET    /dashboard             # agrégats

# Export
GET    /export/mouvements     # .xlsx (mêmes filtres que GET /mouvements)
GET    /export/stock          # .xlsx

# Utilisateurs (admin)
GET    /users
POST   /users
PUT    /users/{id}
DELETE /users/{id}
```

Toutes les routes (hors `/login`) protégées par Sanctum. Autorisations fines via Policies. Validation via FormRequest dédiés.

---

## 9. Conventions de code

- **Laravel** : Eloquent partout (aucune requête SQL brute), FormRequest pour la validation, Policies pour l'autorisation, Resource classes pour les réponses JSON, Services pour la logique stock (`MouvementService` encapsule transaction + maj `stock_actuel`).
- **Vue 3** : Composition API (`<script setup>`), composants réutilisables (table paginée, modal, champ recherche article), Pinia par domaine (auth, articles, mouvements).
- **Nommage** : code et tables en français là où côté métier (designation, livreur…), conventions Laravel respectées par ailleurs.
- **Quantités** : entiers (pièces/cartons). Si un article au poids/volume apparaît plus tard, prévoir `decimal(12,2)` — **pas maintenant**.
- **Dates** : stockage `Y-m-d`, affichage `d/m/Y`.
- **Langue UI** : français intégral.

---

## 10. Sécurité

- Mots de passe hashés (bcrypt), HTTPS obligatoire en prod.
- Autorisation systématique par Policy (jamais de confiance au front).
- Validation stricte des entrées (FormRequest) → pas d'injection (Eloquent/requêtes préparées).
- Rate limiting sur `/login`.
- Secrets en `.env` uniquement, jamais commités.
- `stock_actuel` jamais modifiable directement via l'API — uniquement via le `MouvementService`.

---

## 11. Déploiement

- VPS DigitalOcean (droplet minimal suffisant : 4 utilisateurs internes), Ubuntu, Nginx + PHP-FPM 8.2, MySQL 8.
- Build Vue (`npm run build`) servi par Laravel ; routes API sous `/api`.
- `.env` de prod : `APP_ENV=production`, `APP_DEBUG=false`, DB, Sanctum stateful domains.
- Migrations + seeder (compte admin initial Khal + catégories de base).
- Sauvegarde MySQL quotidienne (cron `mysqldump`).
- Certificat HTTPS (Let's Encrypt).

---

## 12. Plan de sprint suggéré

| Sprint | Contenu |
|---|---|
| **S1 — Fondations** | Setup Laravel + Vue + Tailwind, Sanctum, modèle de données + migrations, auth, rôles & Policies, seeder admin |
| **S2 — Référentiel & mouvements** | CRUD articles + catégories, `MouvementService` (transaction + stock), saisie entrée/sortie, blocage stock négatif |
| **S3 — Consultation** | Dashboard, vue stock + alertes, historique filtrable + recherche + pagination, export Excel |
| **S4 — Finition & livraison** | Polish UI, gestion utilisateurs, commande `stock:recompute`, déploiement VPS, formation |

---

## 13. Definition of Done

- [ ] Un opérateur peut enregistrer une entrée et une sortie ; le stock se met à jour correctement.
- [ ] Une sortie supérieure au stock disponible (article suivi) est bloquée avec message clair.
- [ ] Les articles non suivis acceptent des mouvements sans impacter de niveau de stock.
- [ ] Le dashboard liste les articles en alerte (stock ≤ seuil).
- [ ] L'historique se filtre par type, article, livreur et dates ; l'export Excel reprend les filtres.
- [ ] Les permissions par rôle sont respectées (opérateur ne peut ni éditer un mouvement, ni gérer le référentiel/les utilisateurs).
- [ ] `php artisan stock:recompute` reconstruit fidèlement les niveaux.
- [ ] Application déployée en HTTPS sur le VPS, compte admin créé, sauvegarde DB active.

---

*— MRTECH — MEBODO Richard Aristide, Architecte Solutions Digitales*
