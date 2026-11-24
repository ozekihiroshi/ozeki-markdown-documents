# 0.1.3 rc.1 verification — 2026-11-24

Status: published to WordPress.org as 0.1.3 on November 24, 2026, SVN r3820592.
The user completed the authenticated SVN commit at 13:19:26 UTC (22:19:26 JST).
The plugin directory reports 0.1.3. All 517 files in its distributed ZIP match
the reviewed candidate by path and SHA256; see `distribution-verification.json`.
Production site installations were not changed by this publication task.

The fix checks the referenced document before reading rendered HTML or its cache.
Direct views and embeds share visibility, password and basic WP-Members checks.
An embedding page cannot grant access to its restricted document. This corrects
Markdown Documents itself and does not require Resilience to be active.

The original Markdown is not transformed. Paid membership/expiration extensions
are not added to the supported contract. Recognized conditions that cannot be
evaluated deny display. Site-wide third-party page/CDN caches are outside this test.

## Exact candidate ZIP

- File: `ozeki-markdown-documents-0.1.3.zip`
- SHA256: `fd1806ff93e3e60f91ae23c7fcc3640d075458172a8c88e9b2f94fb67551c8a7`
- Built with `build-release.sh`, including dependency isolation and package checks.
- No source overlay was used for the final install/upgrade tests.

Disposable Docker instances used synthetic data, WordPress 7.1.4, PHP 8.3 and
WP-Members 3.5.8.3. Mail, scheduled work and external WordPress HTTP were disabled.
The containers, volumes and internal networks were removed after the tests.

| Check | Result / evidence |
| --- | --- |
| Fresh installation of exact ZIP | Passed; `fresh-install.json` |
| Upgrade from 0.1.2 | Passed; `upgrade.json` |
| Existing public/member/password document sources and conditions | All three preserved during upgrade |
| Direct/embed, guest/member/admin, cold/warm cache, password and provider denials | 85 checks passed in each final installation |
| Existing MVP import/render/revision/assets/Japanese language-pack check | Passed in both installations |
| Plugin Check | Exit 0, no errors on both installations; `plugin-check.txt` |

The Japanese catalog was separately staged as a language-pack fixture. This does
not mean a WordPress.org Japanese language pack has been approved or published.

## Earlier source-level confirmation

`source-roundtrip-summary.json` records the preceding isolated source tests:
Resilience enabled/disabled, provider unavailable, and preservation → recovery →
normal editor save → recapture → second recovery. Guest denial, member access,
password access, cache behavior and the edited Markdown were retained. Anonymous
HTTP requests were checked; this was not a full interactive browser UI test.
S3 was simulated with a file-backed transport. This evidence is source-level,
not an additional claim of exact-ZIP real-S3 roundtrip verification.

## Packaging regression found and corrected

The first unpublished build prefixed the names inside `function_exists()` for
WP-Members. That disabled membership-provider recognition even though source
tests passed. `scoper.inc.php` now excludes the two external provider functions.
`tests/manual/test-release-package.php` fails the build if those references change.
The same access matrix then passed using the final ZIP. The failed internal build
was retained outside the repository for diagnosis, not released.

The initial MVP runs also stopped because the isolated site's language was not
set at bootstrap. The fixture was corrected; no product workaround was added.

The first Actions compatibility run exposed an invalid zero hash-table offset
in the newly compiled Japanese MO catalog. WordPress 6.4's POMO reader requires
that offset to mark the end of the translation index even when the hash table
has zero entries. The catalog header was corrected without changing translations.
The catalog is a separate language-pack fixture, so the tested plugin ZIP is
unchanged. The existing 6.4 compatibility job detects this regression.

## Release boundary

Existing production sites, Resilience source, stored originals/history, previously
published ZIPs and tags were not changed. The new SVN tag is `0.1.3`.
CI, WordPress Compatibility and Plugin Check succeeded on source commit
`03ad73d4a83e3b0bfe1cebcb474ab70a13c568c8`, including after its main merge.
Resilience release preparation will use the published 0.1.3 dependency.
