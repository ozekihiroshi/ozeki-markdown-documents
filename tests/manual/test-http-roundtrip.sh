#!/usr/bin/env bash

set -euo pipefail

if [ "$#" -ne 4 ]; then
    echo "Usage: $0 BASE_URL ADMIN_USER ADMIN_PASSWORD FIXTURE.md" >&2
    exit 2
fi

base_url=$1
admin_user=$2
admin_password=$3
fixture=$4
test_root=$(mktemp -d /tmp/ozmd-http-roundtrip.XXXXXXXX)
cookie=$test_root/cookies.txt
login_response=$test_root/login.html
import_page=$test_root/import.html
list_page=$test_root/list.html
export_file=$test_root/exported.md

curl -fsS -c "$cookie" "$base_url/wp-login.php" > /dev/null

curl -fsS -L \
    -c "$cookie" \
    -b "$cookie" \
    --data-urlencode "log=$admin_user" \
    --data-urlencode "pwd=$admin_password" \
    --data-urlencode "wp-submit=Log In" \
    --data-urlencode "redirect_to=$base_url/wp-admin/" \
    --data-urlencode "testcookie=1" \
    "$base_url/wp-login.php" \
    -o "$login_response"

curl -fsS \
    -c "$cookie" \
    -b "$cookie" \
    "$base_url/wp-admin/edit.php?post_type=ozmd_document&page=ozmd-import" \
    -o "$import_page"

nonce=$(
    sed -n 's/.*name="_wpnonce" value="\([^"]*\)".*/\1/p' "$import_page" |
        head -1
)
test -n "$nonce"

curl -fsS -L \
    -c "$cookie" \
    -b "$cookie" \
    -F "action=ozmd_import_document" \
    -F "_wpnonce=$nonce" \
    -F "ozmd_file=@$fixture;type=text/markdown" \
    "$base_url/wp-admin/admin-post.php" \
    -o "$list_page"

grep -q "Markdown imported as a draft" "$list_page"

export_url=$(
    grep -o 'href="[^"]*action=ozmd_export_document[^"]*"' "$list_page" |
        head -1 |
        sed 's/^href="//;s/"$//;s/&#038;/\&/g;s/&amp;/\&/g'
)
test -n "$export_url"

curl -fsS -b "$cookie" "$export_url" -o "$export_file"
cmp "$fixture" "$export_file"

echo "import_http=success"
echo "export_http=exact_bytes"
sha256sum "$fixture" "$export_file"
echo "test_root=$test_root"
