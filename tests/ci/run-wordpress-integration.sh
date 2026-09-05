#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 3 ]]; then
    echo "Usage: $0 PHP_VERSION WORDPRESS_VERSION RELEASE_ZIP" >&2
    exit 2
fi

readonly php_version="$1"
readonly wordpress_version="$2"
readonly release_zip="$(cd "$(dirname "$3")" && pwd)/$(basename "$3")"
readonly test_file="$(cd "$(dirname "${BASH_SOURCE[0]}")/../manual" && pwd)/test-mvp.php"
readonly suffix="${GITHUB_RUN_ID:-local}-${GITHUB_RUN_ATTEMPT:-0}-$$-${php_version//./}-${wordpress_version//./}"
readonly safe_suffix="$(printf '%s' "$suffix" | tr -cd 'a-zA-Z0-9_.-')"
readonly network="ozmd-ci-network-${safe_suffix}"
readonly volume="ozmd-ci-html-${safe_suffix}"
readonly database="ozmd-ci-db-${safe_suffix}"
readonly download_dir="$(mktemp -d /tmp/ozmd-wp-core.XXXXXXXX)"
readonly wordpress_archive="${download_dir}/wordpress-${wordpress_version}.tar.gz"

cleanup() {
    docker rm -f "$database" >/dev/null 2>&1 || true
    docker volume rm "$volume" >/dev/null 2>&1 || true
    docker network rm "$network" >/dev/null 2>&1 || true
    rm -rf "$download_dir"
}
trap cleanup EXIT

test -f "$release_zip"
test -f "$test_file"

docker network create "$network" >/dev/null
docker volume create "$volume" >/dev/null

docker run -d \
    --name "$database" \
    --network "$network" \
    -e MARIADB_DATABASE=wordpress \
    -e MARIADB_USER=wordpress \
    -e MARIADB_PASSWORD=wordpress-ci-only \
    -e MARIADB_ROOT_PASSWORD=root-ci-only \
    mariadb:11 >/dev/null

for attempt in $(seq 1 60); do
    if docker exec "$database" healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; then
        break
    fi
    if [[ "$attempt" -eq 60 ]]; then
        echo "MariaDB did not become ready." >&2
        exit 1
    fi
    sleep 2
done

curl -fL --retry 3 \
    "https://downloads.wordpress.org/release/wordpress-${wordpress_version}.tar.gz" \
    -o "$wordpress_archive"

docker run --rm --user 0:0 \
    -v "$volume:/var/www/html" \
    -v "$wordpress_archive:/tmp/wordpress.tar.gz:ro" \
    alpine:3.23 \
    sh -lc 'tar -xzf /tmp/wordpress.tar.gz --strip-components=1 -C /var/www/html && chown -R 33:33 /var/www/html'

wp() {
    docker run --rm \
        --user 33:33 \
        --network "$network" \
        -e HOME=/tmp \
        -v "$volume:/var/www/html" \
        -v "$release_zip:/tmp/ozeki-markdown-documents.zip:ro" \
        -v "$test_file:/tmp/test-mvp.php:ro" \
        "wordpress:cli-php${php_version}" \
        wp "$@"
}

wp config create \
    --dbname=wordpress \
    --dbuser=wordpress \
    --dbpass=wordpress-ci-only \
    --dbhost="$database:3306" \
    --skip-check
wp core install \
    --url=http://ozmd-ci.invalid \
    --title='OZMD integration test' \
    --admin_user=ozmd_admin \
    --admin_password=ozmd-ci-only \
    --admin_email=ozmd@example.invalid \
    --skip-email
wp plugin install /tmp/ozeki-markdown-documents.zip --activate

test "$(wp core version)" = "$wordpress_version"
wp plugin is-active ozeki-markdown-documents
wp eval-file /tmp/test-mvp.php

echo "php_version=$php_version"
echo "wordpress_version=$wordpress_version"
echo "result=success"
