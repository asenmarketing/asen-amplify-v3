# Amplify v3 Modernization Plan

Living document. Check items off as they ship and keep the "Roadmap status" lines in [AGENTS.md](AGENTS.md) in sync.

## Goals

1. Make the theme's design tokens (spacing, type scale, colors, widths, radius) the **default options inside Gutenberg** — core blocks, Kadence, and Asen blocks all read the same scale.
2. Make the editor **safe for clients**: palette/preset-only controls, curated inserter, content-only editing of layouts.
3. Stop generating CSS in PHP (`head-mods.php`, per-block `<style>` tags).
4. Give the theme an upgrade path between upstream and client forks.

## Repos

- `asen-marketing/amplify-lite` — v2, frozen. Existing client forks keep using it; only security fixes go there. Its `.gitignore` excludes `/index.php`, so a fresh clone is a theme WordPress refuses to activate — fix that there if anyone clones it again.
- `asenmarketing/asen-amplify-v3` — v3, this repo. Cloned from amplify-lite with history, theme directory `asen-amplify-v3`, `Theme Name: Asen Amplify`, version 3.0.0.

## Starting point (Oct 2026)

- Classic theme, no `theme.json`, no editor styles, no editor palette.
- 30 ACF blocks: 29 via `acf_register_block()`, 1 (`asen-intro-block`) via `block.json` using the "Reusable - Asen Tokens" clone group. 14 block templates emit per-instance `<style>` with pixel values and hand-rolled breakpoint multipliers.
- Tokens in `scss/_tokens.scss`; brand colors per client in `scss/_colors.scss`. The `site_colors` / `site_max_width` option fields are deprecated.
- No SCSS color functions and no arithmetic on `$spacing-*` / `$font-size-*` anywhere → tokens can be aliased to CSS custom properties without breakage. Only `$site-overall-max-width - 200px` (2 places) needs `calc()`.
- Client forks diverge heavily: the George Ewart build changed ~70 upstream files (header/off-canvas/banner templates, typography, buttons, utility mixins) on top of adding 6 `ewart-*` blocks, 2 CPTs, and 3 options pages. There is currently no upgrade path.

## Open decisions

- [x] **Token source of truth → Option A.** `theme.json` is the source; `_tokens.scss` / `_colors.scss` become `var(--wp--preset--…)` / `var(--wp--custom--…)` aliases. (Option B — PHP reading the SCSS files — rejected: a parser to maintain for no gain.)
- [x] **Option-page color pickers → keep as overrides.** Builders Only color fields (Links & Buttons, Page Banners, header/off-canvas, floating CTA) stay. They layer on top of the theme.json/SCSS defaults: `head-mods.php` prints each one as a `:root` custom prop only when set, and SCSS consumes it with a token fallback (`var(--button-bg, var(--wp--preset--color--primary))`). Button *variants* are not option fields — they are classes in `_buttons.scss` (`.button.red`, `.button-knockout`, `.button-small`…), whose colors reference palette slugs.
- [x] **Distribution → parent/child.** Client repo holds only the child theme; the parent is installed from a GitHub release and updates itself on every site (mechanics in Phase 5).
- [x] **Kadence → core only for new builds.** Group/Columns/Grid/Media & Text are the layout containers. Kadence stays installed on older sites and ages out. Its one remaining use (video popup) is covered by the Asen Video Popup block or a `<dialog>`-based replacement in Phase 4.
- [x] **Minimum versions → WP 6.7, PHP 8.1.** Goes in `style.css` in Phase 0.
- [x] **Retire overlap blocks → yes, per the table in the Oct 2026 discussion.** Button, Buttons Group, Anchor, WYSIWYG, CTA, Copy + Image retire behind patterns/core; Featured Blogs stays (reworked: 3 recent or 3 hand-picked); Link Wrapper and all CPT-driven blocks stay.
- [x] **Header/footer layout selector → removed (Oct 2026).** Keep header layout 1 and the standard footer as the only built-ins; mailing list becomes a toggle; layouts 2–4 and the simple footers come out of upstream. "Custom from scratch" becomes a child-theme override of the header/footer template parts. Drops `site_header_layout` / `site_footer_layout` and their conditionals in `head-mods.php`, `acf-customizations.php`, `off-canvas.php`, `footer.php`.

## Phase 0 — Groundwork & hygiene (1–2 days)

- [x] `block_categories` → `block_categories_all`; intro block moved to the `asen-blocks` category.
- [x] `site_colors` + `site_max_width` removed from Site Options, `head-mods.php`, the front-page banner (now `var(--wp--style--global--wide-size)`), and `_custom.scss`.
- [x] `_block-globals.scss` spacing classes are `sm/md/lg`; duplicates removed from the intro block.
- [x] CPT template filenames: `single-product.php`, `single-service.php`, `archive-product.php`, `archive-service.php` (archives kept for forks that enable `has_archive`); `head-mods.php` checks fixed.
- [x] Leaflet and the YouTube API enqueue only when `has_block()` finds their block (`asen_enqueue_block_dependencies()` in `theme.php`, Leaflet with SRI). Phase 2 follow-up: move the map block's inline script to a `viewScript` so Leaflet can load in the footer.
- [x] Admin `<style>` echoes moved into `includes/css/backend-styles.css`.
- [x] Delete `css/index_safe_save_*`; untrack `css/`; theme-scoped `.gitignore`; `README.md`; `style.css` headers.
- [x] `style.css`: `Requires at least: 6.7`, `Requires PHP: 8.1`.

- [x] Header/footer: one `templates/header/header.php` (was layout 1) and one `templates/footer/footer.php` (was standard, mailing-list column behind `site_footer_mailing_list_enabled`). Layout selects, layout-2 logo injection in `menus.php`, dead `site_cta_button` filter, `.layout-*` / `.header-layout-*` / `.simple` CSS all removed.

## Phase 1 — `theme.json` as token layer (2–3 days)

- [x] `theme.json` (v3) written. Deviations from the list below: `useRootPaddingAwareAlignments` is **off** (the classic header/footer markup handles its own gutters), and `styles.elements` is deferred — `_typography.scss` consumes the heading/font tokens instead, so there is still one source. Spec as planned:
  - `settings.color.palette` — fixed slugs: `primary`, `secondary`, `accent`, `dark`, `light`, `white`, `black`, plus `gray-100…900`. Per-client values are edited here (or in the child theme).
  - `settings.spacing.spacingSizes` — `xs/sm/md/lg/xl` from `_tokens.scss`, as `clamp()` so the 0.8/0.7/0.55 breakpoint mixins become unnecessary. `defaultSpacingSizes: false`, `customSpacingSize: false`, `padding/margin/blockGap: true`, `units: ["px","rem","%","vw"]`.
  - `settings.typography.fontSizes` (fluid) from `$font-size-*`; `fontFamilies` for the site font; `customFontSize: false`.
  - `settings.layout.contentSize` / `wideSize` from container widths; `useRootPaddingAwareAlignments: true`.
  - `settings.custom.*` for heading sizes, line heights, gaps, border radii, container widths → `--wp--custom--…`.
  - `styles.elements` for `h1–h6`, `link`, and the *base* `button` only. Variant classes (`.button.red` etc.) stay in `_buttons.scss` and reference palette vars so a client fork changes only `theme.json`.
  - `settings.appearanceTools: true`.
- [ ] Override layering (defaults → variants → options): `theme.json` sets the token; `_buttons.scss` / `_typography.scss` consume `var(--button-bg, var(--wp--preset--color--primary))`-style fallbacks; `head-mods.php` prints `--button-bg`, `--link-color`, `--banner-title-color`… **only when the Builders Only field is set**. Kills every `color: ;` from empty fields and keeps the option pickers as true overrides.
- [ ] Optional: register the button variant classes as block styles on `core/button` (`is-style-red`) so clients pick from a dropdown instead of typing class names; SCSS targets both selectors.
- [x] `includes/theme-json.php`: `asen_user_is_builder()` (filterable via `asen/is_builder`) + `wp_theme_json_data_theme` filter that turns off `color.custom`/`customGradient`/`customDuotone`, `spacing.customSpacingSize`, `typography.customFontSize`, `layout.allowCustomContentAndWideSize` for non-builders.
- [x] `_tokens.scss` / `_colors.scss` are aliases; `$site-overall-max-width` is `var(--wp--style--global--wide-size)` (kept in `_site-option-styles.scss` as the single alias); the two subtraction sites use `calc()`; `_typography.scss` h1–h6 and `p` use the tokens; brand hex in `_off-canvas-menu.scss` replaced with `$color-primary`.
- [x] Editor parity: `scss/editor.scss` → `css/editor.css`, loaded via `add_editor_style`.
- [x] `head-mods.php` → CSS variables (Oct 2026). Defaults in `scss/_component-vars.scss` (`:root`, base layer, from tokens); `asen_option_css()` prints only set options as an unlayered `:root` override via `wp_add_inline_style('main')`; toggles are body classes; `footer-mods.php` deleted; the off-canvas template's and floating CTA's inline styles removed (CTA's jQuery scroll watcher → `js/floating-cta.js`, IntersectionObserver, enqueued only when enabled). The off-canvas breakpoint is the one generated `@media` block (media queries can't read variables), emitted inside `@layer components`. Header/footer/off-canvas/CTA hex colors mapped to palette tokens; default link color is now the palette primary (was Bootstrap reboot blue). Remaining CSS-printing PHP: the two page banners.
- [x] ~~Verify Kadence color pickers show the theme palette.~~ Kadence retired for new builds.

## Phase 1.5 — CSS cascade layers (done, Oct 2026)

- [x] `scss/index.scss` declares `@layer vendor, reset, base, components, blocks, utilities` and imports each partial group inside its layer; `_custom.scss` stays unlayered so client overrides win.
- [x] `includes/css-layers.php`: `style_loader_tag` turns third-party `<link>`s into `@import url() layer(vendor)`; inline styles (global-styles presets, per-block core CSS, plugin variables) get wrapped in `@layer vendor{}` right before printing. `main` and `core-block-supports` are excluded (`asen/unlayered_style_handles`).
- [x] Verified in a browser: core button resolves to the theme's `_buttons.scss`; theme sets `border-radius: 0` explicitly since core's pill radius now lives in `vendor` with nothing above it.
- [ ] Editor parity for layers: `css/editor.css` is loaded unlayered into the editor iframe, where core's editor CSS is also unlayered — specificity decides there. Revisit if editor previews diverge.
- Known cosmetic: WP prints the inlined block-library CSS across two `<style>` tags, so the vendor wrap's closing brace lands in a stray second tag (parse error, ignored). Harmless.

## Phase 2 — ACF blocks → `block.json` + native supports (3–5 days, one block at a time)

**Conversion recipe (settled with the first batch):** `block.json` with `apiVersion` 3, `supports.spacing` (top/bottom padding + margin) + `supports.color`, default `lg` padding in `attributes.style.default`, `example` data; template moves to `templates/block/` and uses `get_block_wrapper_attributes()`, escapes output, drops every `<style>`; field group loses max-width, color pickers, font sizes/weights/transforms, the Margin & Padding clone, gains `block_width` (clone of `__container_width`) and, where there's a text scale, `text_size` (clone of `__font_size`); group title becomes `Core - …`; SCSS moves to `scss/blocks/` as structure only; `acf_register_block()` call removed. Keep JS-facing class names.

- [x] Batch 1: special copy blurb, icon grid, form container, mailing list (mailing list now has its own form select, falling back to the Footer Options form; its background color is native).
- [x] Batch 2: icon + copy grid, FAQs, tabbed content, location map. Inline `<script>`s became `viewScript`s (vanilla JS, `view.asset.php` deps). Decisions made along the way: the map's Google Maps branch was a placeholder (rendered Uluru) and is gone — OpenStreetMap/Leaflet only, geocoded server-side via `asen_geocode_address()` with a 30-day transient, Leaflet registered in `theme.php` and pulled in as the view script's dependency + `viewStyle`; CARTO's basemap CDN now demands an API key (the old block was already showing "API KEY REQUIRED" tiles), so the default is OpenStreetMap's standard tiles with an `asen/map_tiles` filter for a keyed provider; the tabbed block no longer renders every panel twice for a mobile accordion (duplicate IDs) — buttons stack on small screens and `?tab=` deep links stay; the icon + copy grid's card options (background/border/radius) and all per-element color pickers are gone — card styling is a child-theme concern until section styles land in Phase 3; FAQs use a real `<button aria-expanded>` accordion with a grid-rows transition.

- [ ] Per block: create `includes/core-blocks/{slug}/block.json` with `apiVersion: 3`, same `name` (`acf/{slug}`), `acf.renderTemplate`, `supports: { color: {background,text}, spacing: {padding,margin}, typography: {fontSize}, align: false, anchor: true }`, `example` for inserter preview, `viewScript`/`style` where the block has assets.
- [ ] Per template: `get_block_wrapper_attributes()` on the `<section>`; delete the per-instance `<style>`; delete padding/margin/font-size/color fields from the field group. Keep `__container_width`, `__gap`, `__heading_size`, `__border_radius` as ACF selects (no native equivalent).
- [ ] Keep legacy field keys registered-but-hidden for one release so existing values still render through a fallback; optional one-shot migration mapping old px → nearest spacing preset into the block's `style` attribute.
- [ ] Remove the matching `acf_register_block()` call from `gutenberg.php` once each block is on `block.json`.
- [x] `asen-intro-block` converted: `apiVersion` 3, `supports.spacing` (top/bottom padding + margin) and `supports.color`, default `lg` padding in `attributes.style.default`, `get_block_wrapper_attributes()`, token font sizes without the responsive mixins, padding clones removed from its field group, `__spacing` removed from the shared token group.
- [x] Batch 3: team hub, services hub, products showcase, testimonials. One shared partial (`templates/block/partials/collection.php`) renders grid / alternating rows / Splide carousel for all four, with per-CPT item templates and one layout partial (`_collection.scss`). Glide is gone (library, SCSS, `asen_enqueue_glide_js()`); Splide, Leaflet and `asen-carousel` are registered on `init` in `theme.php`. Testimonial Carousel deleted: it was a client-specific design (purple palette, star row, only rendered with 6–9 testimonials, undefined `$extra_classes`) and Testimonials' carousel now covers it — 1 per view crossfades, N per view slides. Alignment uses native `typography.textAlign`; item title size is a `__heading_size` select; column count drives both grid columns and carousel slides per view. Removed per-block: nav icon/colour fields, rotating colour repeaters, "full width colour" backgrounds (native background colour instead), font sizes/weights/transforms. Services/Products block heading + subheading are no longer gated behind the background option.
- Found while doing batch 3: batch 2's regex removal of the FAQs registration also removed Team Hub's (a whitespace-only line after FAQs' `));`), so Team Hub was unregistered between `4575dc9` and this batch. Removal now asserts exactly one block per match.
- [x] Batch 4. Deleted: button, buttons group, anchor, WYSIWYG, CTA (core equivalents), the never-registered blockquote ×3 and fancy list templates, Split Fancy Copy (had fields but zero CSS — its gradient classes never existed in this theme), and Product/Service Tiers (registered with no field group, so it could never hold content) — replaced by the `asen-pricing-tiers` core-block pattern. Also deleted the now-unused Reusable - Margin & Padding and Reusable - Spacing clone groups. Converted:
  - Video Popup: native `<dialog>`, YouTube via a privacy-enhanced iframe (`youtube-nocookie.com`, no IFrame API, no jquery-modal), uploaded files via `<video>`; accepts an ID or any YouTube URL (`asen_youtube_id()`); closing removes the player so playback stops. This is the Kadence video-popup replacement.
  - Dropdown Links: `<details>`/`<summary>` with real links (respects each link's new-tab setting) instead of a div-based fake select; small viewScript closes it on outside click / Escape.
  - Dynamic Color SVG: inlined + sanitized (`asen_inline_svg()`: kses allowlist, scripts removed, camelCase restored) instead of an `<object>` recolored by JS; color comes from the block's native text color.
  - Featured Blogs: on the collection partial (new `lead` arg renders the CTA tile first), post cards from `templates/block/partials/post-item.php`.
  - Link Wrapper: `block.json` with jsx InnerBlocks; ACF's front-end inner container disabled for it (`acf/blocks/wrap_frontend_innerblocks`).
- [x] Native `typography.textAlign` retrofitted onto intro block and icon + copy grid (their alignment fields are gone).
- [x] Special Copy Blurb deleted (Oct 2026, Eric): Intro Block covers it.
- Verified in the live editor: both patterns parse with no invalid blocks; the editor offers only the theme palette and the Asen spacing presets.
- Root-caused (Oct 2026): ACF Extended Pro's AutoSync (`autosync-json.php`, `pre_update_field_group`) deletes a group's JSON on every save/sync unless the group has `acfe.autosync` containing `json`, and groups imported via Sync never get it. That removed Icon Grid's (`a5dad98`) and Link Wrapper's JSON. Fixed with `asen_acf_force_json_autosync()` (forces the setting for all groups on `acf/validate_field_group`) plus the setting written into the 7 files that lacked it; verified with a throwaway group that survives both import and save.
- [ ] Move the theme's admin `<style>` hacks into editor styles so the iframed (apiVersion 3) editor still looks right.

## Phase 3 — Client-safe editing (2–3 days)

- [x] `allowed_block_types_all` for clients: Asen blocks + core paragraph, heading, list, image, gallery, buttons, quote, separator, spacer, group, columns, media & text, cover, video, embed, table, synced patterns (`asen/client_allowed_blocks`). Clients also can't unlock blocks or use the code editor (`block_editor_settings_all`).
- [x] Page banners → `Asen - Banner` block (one block covers interior banner and front-page hero: image / slideshow / video backgrounds, overlay, native min height). Both banner templates, their SCSS and the Page - Banner / Front Page - Banner field groups deleted; `page.php` renders only content; new pages start with the banner locked; Contact template and CPT archives use the shared partial. Builders Only → Page Banners all remain site-wide defaults: eyebrow/title/subtitle color, size, transform; alignment; background color + blend; overlay tint + opacity (an empty banner Overlay, the default for new banners); button colors.
- [x] Patterns: `asen-hero`, page starters `page-standard`, `page-services`, `page-contact` (banner locked, CTA/copy sections `contentOnly`). All validated in the live editor.
- [ ] (was) `patterns/` directory: section patterns and page starters (`Block Types: core/post-content`, `Post Types: page`) so new pages open with a pattern picker. Core blocks only — never Kadence. Wrap sections in `templateLock: "contentOnly"`; `lock` structural blocks. (`patterns/asen-copy-image.php` and the `asen` category exist as the first example.)
- [x] Section styles on Group (Light / Dark / Brand) via `register_block_style()` + `_section-styles.scss` — deliberately *not* `style_data`: that CSS would land in the vendor layer, below the theme. They reset the component variables instead. Button styles Knockout / Small / Wide on core/button; core's Outline styled to match.
- [x] Block bindings (Oct 2026): `asen/site-option` source for phone/email/address/hours (new Hours field in Site Options → Contact Information), resolved per attribute (linked text / bare URL / plain), with an editor script so bound blocks show real values and the keys are pickable in the Attributes panel; the Contact Page pattern uses it; clients can't change bindings. ACF Pro already ships the `acf/field` source with editor UI — no theme code needed, and our CPTs don't use the block editor, so it's documented in AGENTS.md rather than used.
- [x] ~~`inserter: false` on builder-only blocks~~ — covered by the client allowlist.

## Phase 4 — Rationalize the block library (ongoing)

- [x] Copy + Image: block, template, and field group deleted; replaced by `patterns/asen-copy-image.php` (Group + two Media & Text rows), styled in `scss/blocks/_asen-copy-image.scss`.
- [x] Featured Blogs: reworked to 3 posts, "most recent" or "hand-picked" (relationship field, max 3, falls back to recent), optional CTA tile (default on; makes the row 4-up via `.has-cta-tile`).
- [x] Deleted Button, Buttons Group, Anchor, WYSIWYG, CTA. Still to do: register the `_buttons.scss` variants as block styles on `core/button` so clients pick them from a menu (Phase 3).
- [x] Video popup rebuilt on `<dialog>` (covers the Kadence video-popup use).
- [x] Glide dropped. [x] jquery-modal and jQuery dropped (Oct 2026): `main.js` rewritten vanilla (off-canvas menu, now with `inert`, `aria-expanded`, Escape); the custom file-input wrapper replaced by `::file-selector-button` styling (the wrapper set `tabIndex -1` on the input, so keyboard users couldn't upload); dead scroll-down CTA, `.modal`/`.blocker` CSS, `asen-*-loaded` body classes and `data-useragent` removed.

## Phase 5 — Distribution: parent/child with a self-updating parent (2–3 days)

Client repo contains **only the child theme**. The parent is installed once from a GitHub release and updates itself through WordPress's normal theme-update path, so WP Engine sites pick up releases without per-site deploys.

- [x] Done Oct 2026 (parent side): plugin-update-checker v5.7 vendored in `includes/vendor/plugin-update-checker` (MIT); `includes/updates.php` requires the `asen-amplify-v3.zip` release asset, takes an optional `ASEN_AMPLIFY_GITHUB_TOKEN`, auto-installs via `auto_update_theme` (opt out with `ASEN_AMPLIFY_DISABLE_AUTO_UPDATE`), and never runs on a git checkout or in theme development mode; `Update URI` header added; release + WP Engine workflows; child-aware CSS / ACF JSON / block loading; CHANGELOG.md. Original plan items:
- [ ] Parent self-update: vendor `yahnis-elsts/plugin-update-checker` into `includes/vendor/`, point it at the GitHub repo with release assets enabled, add `Update URI:` to `style.css`, and `add_filter( 'auto_update_theme', … )` → true for `asen-amplify-v3` only. Private repo → GitHub token read from a `wp-config.php` constant set per site (never committed).
- [ ] Release workflow (GitHub Action on tag): zip the theme with `asen-amplify-v3/` as the top-level folder, attach to the release, write the CHANGELOG entry. Sites see "Update available" in Appearance → Themes within ~12h via WP cron and auto-apply; "Update now" for immediate.
- [ ] Staging soak (GitHub Action on push to `main`): `wpengine/github-action-wpe-site-deploy` rsyncs the theme to one WPE staging environment (`REMOTE_PATH: wp-content/themes/asen-amplify-v3/`). Tag only after it's been checked there.
- [ ] Emergency push-to-all: the same deploy Action with a `matrix` of WPE environment names, run manually. Needs the agency SSH Gateway key as a repo secret.
- [ ] Parent code changes: enqueue CSS/JS from `get_stylesheet_directory_uri()`; ACF `load_json` includes parent **and** child `acf-groups/`; block.json loader scans both `core-blocks/` dirs; WP-SCSS compiles from the child (`@import "../../asen-amplify-v3/scss/index"` then client partials).
- [x] Child scaffold repo (`asenmarketing/asen-amplify-v3-starter`): `style.css` (`Template: asen-amplify-v3`), `theme.json`, `functions.php`, `includes/`, `scss/index.scss` + `_custom.scss`, `acf-groups/`, `patterns/`, `templates/` for overrides.
- [ ] `CHANGELOG.md` + semver tags. Renamed mixins/classes/field names/template parts = major bump + migration note.
- [ ] Verify on one WPE site: theme auto-update runs under WPE's cron; ACF renders from the updated local JSON without a manual Sync; the child's compiled CSS survives a parent update.

## Risks

- Aliasing tokens to `var()` breaks any future SCSS math/color functions on them — use `calc()` / `color-mix()` instead and say so in AGENTS.md.
- Role-gated `theme.json` settings must only touch `settings`, never `styles`, so the generated CSS is identical for every user.
- apiVersion 3 iframes the editor only when every registered block is v3; the theme's admin CSS must not depend on the non-iframed layout.
- Never rename a shipped block slug; client databases reference `acf/{slug}`.
- Removing ACF fields does not delete stored values, but templates must stop reading them or fall back gracefully.
