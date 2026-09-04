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
edit_page=$test_root/edit.html
preview_response=$test_root/preview.json

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

edit_url=$(
    grep -o 'href="[^"]*post.php?post=[^"]*action=edit[^"]*"' "$list_page" |
        head -1 |
        sed 's/^href="//;s/"$//;s/&#038;/\&/g;s/&amp;/\&/g'
)
test -n "$edit_url"

curl -fsS -b "$cookie" "$edit_url" -o "$edit_page"
grep -q 'id="ozmd-source"' "$edit_page"
grep -q 'id="ozmd-preview"' "$edit_page"
grep -q 'Markdown Export' "$edit_page"
grep -q 'Export .md' "$edit_page"

preview_nonce=$(
    grep 'var ozmdPreview = ' "$edit_page" |
        sed -n 's/.*"nonce":"\([^"]*\)".*/\1/p' |
        head -1
)
post_id=$(
    grep 'var ozmdPreview = ' "$edit_page" |
        sed -n 's/.*"postId":"\([0-9][0-9]*\)".*/\1/p' |
        head -1
)
test -n "$preview_nonce"
test -n "$post_id"

curl -fsS \
    -b "$cookie" \
    --data-urlencode "action=ozmd_preview_markdown" \
    --data-urlencode "nonce=$preview_nonce" \
    --data-urlencode "post_id=$post_id" \
    --data-urlencode "source=# Live preview

<script>must not execute</script>" \
    "$base_url/wp-admin/admin-ajax.php" \
    -o "$preview_response"

grep -Fq '"success":true' "$preview_response"
grep -Fq '<h1>Live preview<\/h1>' "$preview_response"
grep -Fq '&lt;script&gt;must not execute&lt;\/script&gt;' "$preview_response"

echo "import_http=success"
echo "export_http=exact_bytes"
echo "editor_split_preview=present"
echo "preview_ajax=safe_server_render"
echo "editor_export_button=present"
sha256sum "$fixture" "$export_file"
echo "test_root=$test_root"
