#!/usr/bin/env bash
# One-shot installer for a fresh Ubuntu 22.04/24.04 or Debian 12 server (run as root):
#   sudo bash scripts/install-vps.sh --domain=venushotel.ir --user=admin --pass='a-strong-password' [--demo] [--ssl --email=you@example.com]
set -euo pipefail
DOMAIN=""; ADMIN="admin"; PASS=""; DEMO=""; SSL=""; EMAIL=""; TARGET="/var/www/venus"
for a in "$@"; do case $a in
  --domain=*) DOMAIN="${a#*=}";; --user=*) ADMIN="${a#*=}";; --pass=*) PASS="${a#*=}";;
  --demo) DEMO="1";; --ssl) SSL="1";; --email=*) EMAIL="${a#*=}";; --target=*) TARGET="${a#*=}";;
  *) echo "Unknown option $a"; exit 1;; esac; done
[ -z "$DOMAIN" ] && { echo "--domain is required"; exit 1; }
[ ${#PASS} -lt 8 ] && { echo "--pass is required (8+ characters)"; exit 1; }
SRC="$(cd "$(dirname "$0")/.." && pwd)"

echo "==> Installing Nginx and PHP"
apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y nginx php-fpm php-sqlite3 php-gd php-mbstring php-xml php-zip php-curl unzip
PHPV="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"

echo "==> Copying files to $TARGET"
mkdir -p "$TARGET"
cp -a "$SRC/public" "$TARGET/"
chown -R www-data:www-data "$TARGET/public/storage" "$TARGET/public/uploads"
find "$TARGET/public" -type d -exec chmod 755 {} \; ; find "$TARGET/public" -type f -exec chmod 644 {} \;

echo "==> Configuring Nginx"
sed -e "s/example.com www.example.com/$DOMAIN www.$DOMAIN/" -e "s#/var/www/venus/public#$TARGET/public#" -e "s#php8.3-fpm#php$PHPV-fpm#" "$SRC/scripts/nginx.conf" > /etc/nginx/sites-available/venus
ln -sf /etc/nginx/sites-available/venus /etc/nginx/sites-enabled/venus
rm -f /etc/nginx/sites-enabled/default
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 32M/; s/^post_max_size.*/post_max_size = 64M/; s/^memory_limit.*/memory_limit = 256M/' /etc/php/$PHPV/fpm/php.ini
systemctl restart "php$PHPV-fpm" && nginx -t && systemctl reload nginx

echo "==> Creating the site"
cd "$TARGET/public"
if [ ! -f storage/config.php ]; then
  sudo -u www-data php app/cli.php install --user="$ADMIN" --pass="$PASS" --site-url="https://$DOMAIN" $( [ -z "$DEMO" ] && echo --no-demo )
fi
rm -rf "$TARGET/public/install"

if [ -n "$SSL" ]; then
  echo "==> HTTPS with Let's Encrypt"
  apt-get install -y certbot python3-certbot-nginx
  certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos -m "${EMAIL:-admin@$DOMAIN}" --redirect
fi
echo "Done. Open http://$DOMAIN/admin/ and sign in as $ADMIN."
