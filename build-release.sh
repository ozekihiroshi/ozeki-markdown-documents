#!/usr/bin/env bash

set -euo pipefail

readonly PLUGIN_SLUG='ozeki-markdown-documents'
readonly SCOPER_VERSION='0.18.19'
readonly SCOPER_SHA256='170fb84bd3390defb30f99f7dc39c9a89d10c29973accc26f31c00abc5b25933'
readonly ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly BUILD_DIR="${ROOT_DIR}/build"
readonly PLUGIN_FILE="${ROOT_DIR}/${PLUGIN_SLUG}.php"
readonly SCOPER_PHAR="${BUILD_DIR}/tools/php-scoper-${SCOPER_VERSION}.phar"
readonly SCOPER_URL="https://github.com/humbug/php-scoper/releases/download/${SCOPER_VERSION}/php-scoper.phar"

for command in php composer curl sha256sum zip unzip mktemp; do
    command -v "${command}" >/dev/null 2>&1 || {
        echo "Required command not found: ${command}" >&2
        exit 1
    }
done

version="$({
    sed -n 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//p' "${PLUGIN_FILE}"
} | head -n 1 | tr -d '\r')"

[[ "${version}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || {
    echo "Invalid release version: ${version}" >&2
    exit 1
}

readonly ZIP_FILE="${BUILD_DIR}/${PLUGIN_SLUG}-${version}.zip"
work_dir="$(mktemp -d)"
readonly work_dir
readonly stage_dir="${work_dir}/stage/${PLUGIN_SLUG}"
readonly scoped_dir="${work_dir}/scoped/${PLUGIN_SLUG}"
readonly work_zip="${work_dir}/${PLUGIN_SLUG}-${version}.zip"

cleanup() {
    rm -rf "${work_dir}"
}
trap cleanup EXIT

required_files=(
    "${PLUGIN_FILE}"
    "${ROOT_DIR}/uninstall.php"
    "${ROOT_DIR}/readme.txt"
    "${ROOT_DIR}/LICENSE"
    "${ROOT_DIR}/third-party-notices.txt"
    "${ROOT_DIR}/composer.json"
    "${ROOT_DIR}/composer.lock"
    "${ROOT_DIR}/package.json"
    "${ROOT_DIR}/package-lock.json"
    "${ROOT_DIR}/tools/build-math.mjs"
    "${ROOT_DIR}/tools/build-mermaid.mjs"
    "${ROOT_DIR}/scoper.inc.php"
    "${ROOT_DIR}/languages/ozeki-markdown-documents.pot"
)

for file in "${required_files[@]}"; do
    [[ -f "${file}" ]] || {
        echo "Required release source is missing: ${file}" >&2
        exit 1
    }
done

composer validate --no-check-publish "${ROOT_DIR}/composer.json"

while IFS= read -r -d '' file; do
    php -l "${file}" >/dev/null
done < <(find "${ROOT_DIR}/src" -type f -name '*.php' -print0)
php -l "${PLUGIN_FILE}" >/dev/null
php -l "${ROOT_DIR}/uninstall.php" >/dev/null

mkdir -p "${stage_dir}" "$(dirname "${scoped_dir}")" "$(dirname "${SCOPER_PHAR}")"
rm -f "${ZIP_FILE}"

if ! printf '%s  %s\n' "${SCOPER_SHA256}" "${SCOPER_PHAR}" | sha256sum --status -c -; then
    download="${work_dir}/php-scoper.phar"
    curl -fL --retry 3 -o "${download}" "${SCOPER_URL}"
    printf '%s  %s\n' "${SCOPER_SHA256}" "${download}" | sha256sum -c -
    cp "${download}" "${SCOPER_PHAR}"
fi

cp "${PLUGIN_FILE}" "${stage_dir}/${PLUGIN_SLUG}.php"
cp "${ROOT_DIR}/uninstall.php" "${stage_dir}/uninstall.php"
cp "${ROOT_DIR}/readme.txt" "${stage_dir}/readme.txt"
cp "${ROOT_DIR}/LICENSE" "${stage_dir}/LICENSE"
cp "${ROOT_DIR}/third-party-notices.txt" "${stage_dir}/third-party-notices.txt"
cp "${ROOT_DIR}/composer.json" "${stage_dir}/composer.json"
cp "${ROOT_DIR}/composer.lock" "${stage_dir}/composer.lock"
cp "${ROOT_DIR}/package.json" "${stage_dir}/package.json"
cp "${ROOT_DIR}/package-lock.json" "${stage_dir}/package-lock.json"
mkdir -p "${stage_dir}/tools"
cp "${ROOT_DIR}/tools/build-math.mjs" "${stage_dir}/tools/build-math.mjs"
cp "${ROOT_DIR}/tools/build-mermaid.mjs" "${stage_dir}/tools/build-mermaid.mjs"
cp -R "${ROOT_DIR}/src" "${stage_dir}/src"
cp -R "${ROOT_DIR}/assets" "${stage_dir}/assets"
cp -R "${ROOT_DIR}/languages" "${stage_dir}/languages"

composer install \
    --working-dir="${stage_dir}" \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

php -d memory_limit=1G "${SCOPER_PHAR}" add-prefix \
    --config="${ROOT_DIR}/scoper.inc.php" \
    --working-dir="${stage_dir}" \
    --output-dir="${scoped_dir}" \
    --force \
    .

cp "${stage_dir}/${PLUGIN_SLUG}.php" "${scoped_dir}/${PLUGIN_SLUG}.php"
cp "${stage_dir}/uninstall.php" "${scoped_dir}/uninstall.php"
composer dump-autoload --working-dir="${scoped_dir}" --no-dev --optimize
rm -f "${scoped_dir}/composer.lock"

while IFS= read -r -d '' file; do
    php -l "${file}" >/dev/null
done < <(find "${scoped_dir}/src" -type f -name '*.php' -print0)
php -l "${scoped_dir}/${PLUGIN_SLUG}.php" >/dev/null
php -l "${scoped_dir}/uninstall.php" >/dev/null
php "${ROOT_DIR}/tests/manual/test-release-package.php" "${scoped_dir}"

(
    cd "$(dirname "${scoped_dir}")"
    zip -rq "${work_zip}" "${PLUGIN_SLUG}"
)

unzip -tq "${work_zip}" >/dev/null
cp "${work_zip}" "${ZIP_FILE}"

echo "Release package: ${ZIP_FILE}"
sha256sum "${ZIP_FILE}"
