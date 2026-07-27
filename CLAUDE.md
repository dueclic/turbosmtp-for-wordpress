# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Official turboSMTP WordPress plugin (slug: `turbosmtp`). It reroutes all `wp_mail()` traffic through turboSMTP — either via the HTTP API or via SMTP — and provides an admin UI for setup, delivery stats, and test emails. Structured on the WordPress Plugin Boilerplate (WPPB): a `Turbosmtp_Loader` collects hooks, `Turbosmtp` (includes/class-turbosmtp.php) wires admin and public classes together in `define_admin_hooks()` / `define_public_hooks()`.

## Commands

Asset build only — there are no tests and no PHP linting configured. PHP files are loaded directly by WordPress; nothing compiles them.

Toolchain: Node 20 (`.nvmrc`) + pnpm 9 (pinned via `packageManager`, use through corepack: `corepack enable` once, then plain `pnpm` works). SCSS compiles with dart-sass (`sass` + gulp-sass 5).

```bash
pnpm install        # uses node version in .nvmrc
pnpm run build      # gulp default: clean → copy vendor bundles → SCSS → JS
pnpm run build:css  # SCSS only (admin/scss → admin/css/*.min.css)
pnpm run build:js   # JS only (admin/js → admin/bundle/turbosmtp/*.min.js via babel+uglify)
pnpm run watch      # watch both SCSS and JS
```

## Local dev environment (wp-env)

`@wordpress/env` (needs Docker) spins up WordPress with the plugin mounted and activated; config in `.wp-env.json` (PHP 8.2, `WP_DEBUG` on).

```bash
pnpm run env:start    # http://localhost:8888 — admin: admin/password
pnpm run env:stop
pnpm run env:destroy  # remove containers + volumes
pnpm run env:cli ...  # WP-CLI inside the container, e.g. pnpm run env:cli option get ts_auth_options
```

Compiled/copy targets are committed: never hand-edit `admin/css/*.min.css`, `admin/bundle/**`. Edit sources in `admin/scss/` and `admin/js/`, then rebuild. `admin/bundle/chart.js`, `admin/bundle/daterangepicker`, and `turbosmtp-summarizer.min.js` are copied/transpiled from node_modules by the `plugins` gulp task.

## Release / versioning

Releases are driven by **conventional commits** (`feat:`, `fix:`, …) via `commit-and-tag-version`:

```bash
pnpm run release:dry   # preview: next version + changelog
pnpm run release       # bump versions, update CHANGELOG.md, commit, create tag
git push --follow-tags origin master   # push manually — this triggers the WP.org deploy
```

`pnpm run release` computes the next semver from commits since the last tag and updates every version location in one commit: `turbosmtp.php` (header `Version:` + `TURBOSMTP_VERSION` constant), `Stable tag:` in `.wordpress-org/readme/README.md`, `package.json`, and `CHANGELOG.md`. Config is in `.versionrc.js`; the custom updaters live in `scripts/version-updaters/`. Tags are plain versions with no `v` prefix (e.g. `4.9.8`). Never bump versions by hand.

The user-facing changelog inside `.wordpress-org/readme/README.md` (`== Changelog ==` section) is NOT generated — curate it manually when the release is worth announcing to WP.org users.

Deployment to the WordPress.org SVN repo happens automatically via GitHub Actions (`.github/workflows/deploy.yml`) when a git tag is pushed. `.distignore` controls what is excluded from the deployed zip (dev files like `CLAUDE.md`, `CHANGELOG.md`, `.versionrc.js`, `scripts/` are excluded).

## Architecture

### Mail interception (public/class-turbosmtp-public.php)

Two mutually exclusive send paths, chosen by the `is_smtp` flag in the `ts_send_options` option:

- **HTTP API** (`is_smtp` falsy): `pre_wp_mail` filter → `maybe_send_via_http()` short-circuits `wp_mail()` entirely, parses headers with `turbosmtp_get_headers_data()` (common-api.php) and posts to the turboSMTP `/mail/send` API. Fires the `turbosmtp_api_response` action afterwards.
- **SMTP** (`is_smtp` truthy): `phpmailer_init` action → `maybe_send_via_phpmailer()` reconfigures PHPMailer with the stored host/port/credentials. The SMTP password can be overridden with the `TURBOSMTP_SMTP_PASSWORD` constant (wp-config.php) instead of storing it in the DB.

Both paths are registered only when `turbosmtp_validapi()` is true.

### API client (includes/)

`Turbosmtp_Api_Base` (abstract) wraps `wp_remote_request` against `https://pro.api.serversmtp.com/api/v2` and throws `Turbosmtp_Exception` on errors; `Turbosmtp_Api` adds concrete calls: `send`, `get_user_config`, `create_api_keys`, `authorize`, `deauthorize`, `get_analytics`. Auth is consumer key/secret stored in the `ts_auth_options` option and injected into both admin and public classes from `Turbosmtp::__construct`.

### Options

- `ts_auth_options` — `consumer_key`, `consumer_secret`, `valid_api` flag; legacy pre-4.9 keys (`op_ts_email`, `op_ts_validapi`) live here too.
- `ts_send_options` — sender defaults and SMTP settings (`is_smtp`, `from`, `fromname`, `host`, `port`, `smtpsecure`, `email`, `password`).
- `ts_migration_done` — tracks the legacy→API-keys migration.

### Migration state machine

`turbosmtp_migration_has_done()` (common-api.php) gates the whole admin: if a legacy username/password install hasn't migrated to API keys, the plugin shows a migration notice/page (`admin/partials/migration.php`, `wp_ajax_turbosmtp_generate_api_keys`) instead of the normal stats/config hooks. Legacy submenu pages (`ts-dash`, `TSStats`, `TSLogout`) still exist in the admin class for that path.

### Admin (admin/class-turbosmtp-admin.php)

Single large class holding menu registration, the settings/config forms (partials in `admin/partials/`), AJAX endpoints (stats chart, stats history, test email, disconnect, API key generation) and `admin_post` handlers. Stats pages render with Chart.js + daterangepicker + the `ts-aggs-chartjs` summarizer bundle; email log table uses `Turbosmtp_Messages_List_Table` (extends `WP_List_Table`).

### Shared helpers (common-api.php)

Loaded before everything else. Notable: `turbosmtp_analytics_filter_options()` maps UI filter names to API event statuses (e.g. `delivered` = SUCCESS+OPEN+CLICK+UNSUB+REPORT); `turbosmtp_valid_hosts()` lists the EU/non-EU SMTP hosts; `turbosmtp_is_admin_page()` whitelists the plugin's screen IDs for conditional asset loading.

## Translations

- All user-facing strings go through `__()`/`_e()` with the `turbosmtp` text domain.
- The catalog `languages/turbosmtp.pot` is generated with WP-CLI: `pnpm run makepot` (wraps `wp i18n make-pot`; requires the `wp` binary — not installed by default). Regenerate it when translatable strings change.
- There is deliberately no `load_plugin_textdomain()` call: translations are delivered as automatic language packs from translate.wordpress.org (GlotPress), keyed on the `Text Domain` plugin header. No `.po`/`.mo` files live in the repo — translated strings are managed on translate.wordpress.org, not here.

## Conventions

- Code style is WordPress-flavored PHP (tabs, snake_case, Yoda-ish spacing as in existing files); match the surrounding file.
- Everything written to the repo or GitHub is in **English**: PR titles and bodies, commit messages, code comments, and docs — regardless of the language used in conversation.