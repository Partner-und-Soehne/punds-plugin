# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

`punds-plugin` ("Partner & Söhne – Core") is the agency's proprietary WordPress plugin, installed on **every** client site plus Partner & Söhne's own site. It bundles agency-wide functionality (branding, tracking, security tweaks, compliance features) so individual client sites don't need custom `functions.php` code for these concerns.

Deployment: WP Umbrella rolls out updates; the bundled GitHub Plugin Update Checker (`lib/plugin-update-checker/`) additionally surfaces new releases directly in each site's WordPress admin.

## Architecture

- `punds-core-loader.php` is the single entry point. It:
  - Defines `PUNDS_CORE_PATH`, `PUNDS_CORE_URL`, `PUNDS_CORE_VERSION`
  - Wires up the GitHub Plugin Update Checker
  - Auto-`require_once`s every `*.php` file directly under `punds-core/` via `glob()` — **no manual registration needed**. Dropping a new file into `punds-core/` is enough to load it as a module.
- Each file in `punds-core/` is a self-contained module (own hooks, own concerns). Shared assets live in `punds-core/assets/`.

## Module map (`punds-core/`)

- `admin-footer-branding.php` — black admin menu + custom footer branding (agency corporate design)
- `ai-generated-image-label.php` — "KI-generiert" checkbox/data model + media library UI (Kennzeichnungspflicht)
- `ai-generated-image-label-frontend.php` — frontend badge for AI-labeled images; filters `punds_ai_label_text`, `punds_ai_label_css`, `punds_ai_label_wrapper_html`; shortcode `[punds_ai_label]`; kill switch `PUNDS_AI_LABEL_DISABLED`
- `custom-login-logo.php` — agency logo on wp-login screen
- `disable-comments.php` — fully disables comments/pingbacks/trackbacks across the site
- `duplicate-posts.php` — "Duplicate" action for posts/pages
- `e-recht24-fix.php` — suppresses noisy eRecht24 admin notices (low priority hook, 999)
- `enable-svg-upload.php` — safe SVG/SVGZ upload support with validation + thumbnail fallback
- `google-sso.php` — "Login with Google" for agency staff, domain-restricted; gated by `PUNDS_GOOGLE_CLIENT_ID` / `PUNDS_SSO_ALLOWED_DOMAIN`
- `manage-tracking-scripts.php` — admin UI for head/footer tracking scripts (e.g. GTM)
- `ps-utm-tracking.php` — UTM + click-ID cookie persistence, Contact Form 7 hidden-field autofill

## Conventions

- Every module guards against direct access with an `ABSPATH` check at the top.
- Nonce verification on any state-changing admin action; sanitize all input.
- Optional/config-gated features are toggled via constants defined in the **site's** `wp-config.php` (e.g. `PUNDS_AI_LABEL_DISABLED`, `PUNDS_GOOGLE_CLIENT_ID`, `PUNDS_SSO_ALLOWED_DOMAIN`), not via an admin settings screen — follow this pattern for new opt-in/opt-out features rather than adding UI toggles.
- Requirements: WordPress 5.9+, PHP 8.0+. Contact Form 7 is an optional integration (UTM autofill only).

## Constraints

- This plugin runs unmodified across many unrelated client sites — **no client-specific logic belongs here**. Anything specific to one client should be its own separate plugin on that site.
- Changes must stay backward-compatible: a change here affects every site once it updates, and only takes effect after that site pulls the new plugin version.
- License is proprietary — this is not an open-source project despite living on GitHub for the update checker's benefit.
