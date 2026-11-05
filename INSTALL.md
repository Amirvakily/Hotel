# Installing the Venus Hotel website

The site is plain PHP and runs on ordinary **cPanel** shared hosting with nothing else to install. The default database is **SQLite** (no database to create). **MySQL/MariaDB** is supported too.

## Requirements

| Item | Minimum |
|---|---|
| PHP | 8.0 or newer (8.2 / 8.3 recommended) |
| PHP extensions | `pdo_sqlite` (or `pdo_mysql`), `gd` (with WebP), `mbstring`, `fileinfo`, `zip`, `dom` |
| Disk | ~80 MB with demo content, plus your photos |
| Apache | `mod_rewrite` on (standard on cPanel) |

> In cPanel open **Select PHP Version** (or MultiPHP Manager), pick 8.2/8.3 and tick the extensions above.

---

## cPanel (recommended)

1. **Get the zip.** On your computer run `bash scripts/build-release.sh` → `dist/venus-hotel-1.0.0.zip` (or download it from GitHub Releases).
2. cPanel → **File Manager** → `public_html` (or a subdomain folder).
3. **Upload** the zip, right-click it → **Extract**. `public_html/index.php` must exist afterwards.
4. Make sure `storage` and `uploads` are writable (permissions `755`, or `775` if you get errors).
5. Open `https://your-domain/install/`.
6. The installer checks the server. Ask your host to enable anything shown in red.
7. Choose the database:
   - **SQLite** (recommended): nothing to do.
   - **MySQL**: cPanel → **MySQL Databases** → create a database and a user, add the user with **ALL PRIVILEGES**, then fill in the form.
8. Enter the admin username and password (8+ characters).
9. Tick **demo content** to start with sample rooms, menus, photos and a 360° tour (all editable/deletable later).
10. Press **Install** (up to a minute with demo content).
11. Delete the `install` folder in File Manager (the installer also locks itself).
12. Sign in at `https://your-domain/admin/`.

**HTTPS:** get a free certificate in cPanel (**SSL/TLS Status** or **Let's Encrypt**), then uncomment the two "force HTTPS" lines in `.htaccess`.

**Sub-folder:** a site in `public_html/hotel` works at `https://domain/hotel/` automatically. If links break, enable `RewriteBase /hotel/` in `.htaccess`.

## VPS (Ubuntu / Debian)

```bash
git clone https://github.com/amirvakily/hotel.git && cd hotel
sudo bash scripts/install-vps.sh --domain=venushotel.ir --user=admin --pass='a-strong-password' --demo --ssl --email=you@example.com
```
Installs Nginx + PHP-FPM, copies the site to `/var/www/venus`, configures Nginx (`scripts/nginx.conf`) and optionally gets a Let's Encrypt certificate.

## Docker

```bash
docker compose up -d      # then open http://localhost:8080/install/
```

## Local preview

```bash
php public/app/cli.php install --user=admin --pass=venus12345
bash scripts/dev-server.sh        # http://localhost:8000
```

## Command line (SSH / cPanel Terminal)

```bash
cd public_html
php app/cli.php migrate                       # after updating the files
php app/cli.php password admin 'new-password' # reset a password
php app/cli.php cache:clear
php app/cli.php backup                        # zip in storage/backups
```

## Updating

1. Admin → **Backup** → create and download a backup.
2. Extract the new zip over the old files. `storage` and `uploads` (your data and photos) are not touched.
3. Sign in once; database changes apply automatically (or run `php app/cli.php migrate`).

## Troubleshooting

| Problem | Fix |
|---|---|
| Error 500 | Use PHP 8.0+. Check `storage/logs/app.log`. |
| Every page but the home page is 404 | `mod_rewrite` is off. The site also works with `index.php?r=…` links, or ask your host to enable it. |
| Photo upload fails | Set `upload_max_filesize` to 32M (Select PHP Version → Options). Check `uploads` permissions. |
| "Cannot write storage/config.php" | Make `storage` writable (`755`/`775`). |
| No WebP images | GD needs WebP support; JPEG is used otherwise. |
| Map does not show | Tiles come from OpenStreetMap; another provider can be set in Settings → Map. |
| Forgot the admin password | `php app/cli.php password admin 'new-password'` |

## Security notes

- `app/` and `storage/` are blocked by `.htaccess`. On Nginx use `scripts/nginx.conf`.
- PHP cannot run inside `uploads/`.
- Six failed logins lock that IP for 15 minutes.
- All admin forms use CSRF tokens; uploaded images are re-encoded before saving.
