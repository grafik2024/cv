#!/usr/bin/env bash
# Reproducible local WordPress for development and QA of the Eurowet 2026 build.
# Installs WordPress + the free plugins the production site already runs (WooCommerce, Yoast SEO,
# Polylang, Elementor, Wordfence) and links the theme/plugin from this repository.
# Usage: tools/local-env/setup-wp.sh [wp_dir] [port]
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
WP_DIR="${1:-/opt/eurowet-wp}"
PORT="${2:-8080}"
URL="http://localhost:${PORT}"
DB=eurowet_local; DBUSER=eurowet; DBPASS=eurowet_local_pw

command -v wp >/dev/null || { curl -sSLo /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; chmod +x /usr/local/bin/wp; }
WP="wp --allow-root --path=${WP_DIR}"

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS ${DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci; CREATE USER IF NOT EXISTS '${DBUSER}'@'localhost' IDENTIFIED BY '${DBPASS}'; GRANT ALL ON ${DB}.* TO '${DBUSER}'@'localhost'; FLUSH PRIVILEGES;"

mkdir -p "${WP_DIR}"
if [ ! -f "${WP_DIR}/wp-load.php" ]; then
  $WP core download --locale=pl_PL
fi
if [ ! -f "${WP_DIR}/wp-config.php" ]; then
  $WP config create --dbname=${DB} --dbuser=${DBUSER} --dbpass=${DBPASS} --dbhost=localhost --locale=pl_PL --extra-php <<'PHP'
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_MEMORY_LIMIT', '512M' );
PHP
fi
if ! $WP core is-installed 2>/dev/null; then
  $WP core install --url="${URL}" --title="EUROWET" --admin_user=admin --admin_password=admin_local_only --admin_email=admin@localhost.invalid --skip-email
fi
$WP option update home "${URL}"; $WP option update siteurl "${URL}"
$WP language core install pl_PL --activate || true
$WP rewrite structure '/%postname%/' --hard
# Direct zip downloads (api.wordpress.org may be unreachable from sandboxed environments).
ZIPS="${TMPDIR:-/tmp}/eurowet-wpzips"; mkdir -p "${ZIPS}"
for p in woocommerce wordpress-seo polylang elementor wordfence; do
  if ! $WP plugin is-installed $p; then
    [ -s "${ZIPS}/$p.zip" ] || curl -sSL -o "${ZIPS}/$p.zip" "https://downloads.wordpress.org/plugin/$p.latest-stable.zip"
    $WP plugin install "${ZIPS}/$p.zip"
  fi
done
if ! $WP theme is-installed hello-elementor; then
  [ -s "${ZIPS}/hello-elementor.zip" ] || curl -sSL -o "${ZIPS}/hello-elementor.zip" "https://downloads.wordpress.org/theme/hello-elementor.zip"
  $WP theme install "${ZIPS}/hello-elementor.zip"
fi
ln -sfn "${REPO}/wordpress/wp-content/themes/eurowet-2026" "${WP_DIR}/wp-content/themes/eurowet-2026"
ln -sfn "${REPO}/wordpress/wp-content/plugins/eurowet-core" "${WP_DIR}/wp-content/plugins/eurowet-core"
echo "WordPress ready at ${URL} (dir ${WP_DIR})"
