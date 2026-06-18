# Gestion de Stock (Entrées / Sorties) — MRTECH

Application web interne de gestion de stock (Laravel 11 + Vue 3 SPA + MySQL).
Référence projet : **MRTECH-DEV-STOCK-2026-01**. Spécification complète : voir [CLAUDE.md](CLAUDE.md).

## Stack
- **Backend** : Laravel 11 (PHP 8.2+), API REST, Sanctum (token), Policies, FormRequests, Resources.
- **Frontend** : Vue 3 (`<script setup>`), Vue Router, Pinia, Axios, Tailwind CSS, build Vite.
- **DB** : MySQL 8 (MySQL 5.7 supporté en local MAMP).
- **Export** : `maatwebsite/excel` (.xlsx).

## Installation locale (MAMP)

```bash
# 1. Dépendances
composer install
npm install

# 2. Environnement (.env déjà configuré pour MAMP : MySQL 127.0.0.1:8889 root/root)
php artisan key:generate   # si nécessaire

# 3. Base de données + données de démo
php artisan migrate:fresh --seed

# 4. Build du frontend
npm run build              # ou `npm run dev` en développement (hot reload)

# 5. Serveur
php artisan serve --port=8000
```

Ouvrir http://127.0.0.1:8000

### Comptes de démonstration (seeder)
| Rôle | Email | Mot de passe |
|---|---|---|
| Admin | `khal@gabaostore.ga` | `password` |
| Opérateur | `operateur@gabaostore.ga` | `password` |

## Commandes utiles
- `php artisan stock:recompute` — recalcule `stock_actuel` de tous les articles suivis depuis `stock_initial` + mouvements (filet de sécurité).
- `npm run dev` — frontend en mode développement (HMR).

## Architecture
- **Source de vérité du stock** : table `mouvements`. `articles.stock_actuel` est un cache dénormalisé maintenu **transactionnellement** par `App\Services\MouvementService`.
- **Règles métier** (blocage stock négatif, articles non suivis, atomicité, immutabilité opérateur) : voir `MouvementService` et les Policies dans `app/Policies`.
- **API** : préfixe `/api`, toutes les routes (hors `/login`) protégées par `auth:sanctum`. Voir `routes/api.php`.
- **SPA** : servie par la route catch-all dans `routes/web.php` ; sources sous `resources/js`.

## Déploiement (VPS)
Voir la section 11 de [CLAUDE.md](CLAUDE.md) : Nginx + PHP-FPM 8.2, MySQL 8, `APP_ENV=production`, `APP_DEBUG=false`, HTTPS Let's Encrypt, `npm run build`, sauvegarde `mysqldump` quotidienne.

---
*MRTECH — MEBODO Richard Aristide, Architecte Solutions Digitales*
