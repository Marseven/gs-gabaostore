# Déploiement sur Hostinger (hébergement mutualisé)

Guide pas-à-pas pour déployer **Gestion de Stock** (Laravel 11 + SPA Vue 3) sur Hostinger.

> L'application est une SPA servie par Laravel : le **document root doit pointer sur le dossier `public/`** (jamais sur la racine du projet, qui contient `.env`, `vendor/`, etc.).

---

## 0. Pré-requis Hostinger
- Plan avec **PHP 8.2** (réglable dans hPanel → Avancé → Configuration PHP).
- Une **base MySQL** (hPanel → Bases de données MySQL).
- **SSH** recommandé (plans Business/Cloud) pour lancer les commandes Artisan. À défaut, voir §6 (fallback sans SSH).

---

## 1. Build local des assets (sur votre machine)
Hostinger mutualisé n'exécute pas Node : on build **avant** d'envoyer.
Le dossier `public/build/` est **versionné dans Git** — un simple `git pull` (ou un export du dépôt) apporte donc les assets déjà compilés, sans Node sur le serveur.

```bash
npm ci
npm run build          # génère public/build/ (à committer)
composer install --no-dev --optimize-autoloader
git add public/build && git commit -m "chore: build assets" && git push
```

> Règle : rebuild + commit de `public/build/` à chaque modification du frontend, avant déploiement.

## 2. Envoi des fichiers
Uploadez **tout le projet** (y compris `vendor/` et `public/build/`) via Gestionnaire de fichiers ou SFTP, par ex. dans :

```
/home/uXXXX/domains/votre-domaine.tld/laravel-app/
```

> Ne placez PAS le projet directement dans `public_html`. On fait pointer le domaine vers son sous-dossier `public/`.

## 3. Faire pointer le domaine sur `public/`
Dans **hPanel → Sites web → (votre domaine) → Document Root**, réglez :

```
.../laravel-app/public
```

Si votre plan ne permet pas de changer le document root :
- **Le plus simple** : placez tout le projet dans `public_html/`. Le fichier [`.htaccess`](../.htaccess) **à la racine** (fourni dans le dépôt) réécrit automatiquement toutes les requêtes vers `public/` — rien d'autre à faire.
- Ou utilisez un **sous-domaine** (ex. `stock.votre-domaine.tld`) dont on règle la racine sur `laravel-app/public`.

Deux fichiers `.htaccess` interviennent :
- [`.htaccess`](../.htaccess) **racine** : aiguille vers `public/` (utile si le root reste sur la racine du projet) + bloque l'accès aux fichiers sensibles (`.env`, `composer.json`, `.git`…).
- [`public/.htaccess`](../public/.htaccess) **Laravel** : gère le contrôleur frontal (réécriture vers `index.php`).

> Si vous réglez le document root sur `.../public`, le `.htaccess` racine est au-dessus du root et reste sans effet (inoffensif).

## 4. Configuration `.env` de production
Copiez `.env.example` en `.env` et renseignez :

```env
APP_NAME="Gestion de Stock"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.tld
APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=uXXXX_gabaostore
DB_USERNAME=uXXXX_gabao
DB_PASSWORD=********

# Drivers fichier recommandés en mutualisé (pas de table sessions/cache requise)
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

SANCTUM_STATEFUL_DOMAINS=votre-domaine.tld
```

> ⚠️ Avec `SESSION_DRIVER=database`/`CACHE_STORE=database`, les tables `sessions`/`cache` doivent exister (créées par `php artisan migrate`). Les drivers `file` évitent cette dépendance.

Générez la clé applicative (en SSH) :
```bash
php artisan key:generate
```
> Sans SSH : générez `APP_KEY` en local (`php artisan key:generate --show`) et collez la valeur dans le `.env` du serveur.

## 5. Base de données + caches (SSH)
```bash
php artisan migrate --force --seed     # crée les tables + compte admin + données de base
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Le seeder crée le compte admin initial **`khal@gabaostore.ga` / `password`** → **changez ce mot de passe immédiatement** après la première connexion.

## 6. Fallback sans SSH
1. En local, configurez `.env` sur la base **distante** (host = adresse MySQL distante Hostinger si l'accès externe est activé) et lancez `php artisan migrate --force --seed`.
2. Sinon, exportez votre base locale seedée (`mysqldump`) puis importez le `.sql` via **phpMyAdmin** dans la base Hostinger.
3. Pensez à supprimer le cache de config si vous modifiez `.env` ensuite (`bootstrap/cache/config.php`).

## 7. HTTPS
hPanel → SSL → activer le certificat **Let's Encrypt** gratuit, puis forcer HTTPS. Vérifiez que `APP_URL` est en `https://`.

## 8. Sauvegarde
Activez les sauvegardes Hostinger, ou planifiez un cron (hPanel → Tâches Cron) :
```bash
mysqldump -uUSER -pPASS uXXXX_gabaostore > ~/backups/stock_$(date +\%F).sql
```

---

## Mises à jour ultérieures
```bash
# en local
npm run build && composer install --no-dev --optimize-autoloader
# envoyer les fichiers modifiés (dont public/build), puis en SSH :
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Vérifications post-déploiement
- `https://votre-domaine.tld/up` → page de santé Laravel (HTTP 200).
- `https://votre-domaine.tld/` → écran de connexion.
- Connexion admin → tableau de bord.
- Créer une entrée puis une sortie → le stock se met à jour.

## Dépannage

**`SQLSTATE[42S02] ... Table '..._gabao.sessions' doesn't exist`**
La base est vide : les migrations n'ont pas été exécutées. Lancez :
```bash
php artisan migrate --force --seed
php artisan config:clear && php artisan config:cache
```
Si vous n'avez pas le SSH, passez `SESSION_DRIVER=file` et `CACHE_STORE=file` dans le `.env` (supprime la dépendance à la table `sessions`/`cache`), puis importez le schéma via phpMyAdmin. Les tables métier (`users`, `articles`…) restent obligatoires : l'app ne fonctionne pas sans migrations.

**Page blanche / 500** : vérifiez `storage/` et `bootstrap/cache/` accessibles en écriture (`chmod -R 775`), et `APP_KEY` renseignée.

**Après modification du `.env`** : toujours `php artisan config:clear` (un `config:cache` obsolète garde les anciennes valeurs).
