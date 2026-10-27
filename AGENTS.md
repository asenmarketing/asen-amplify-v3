# Asen Amplify (v3) — Agent Guide

Asen Marketing's WordPress theme for client sites, v3. Successor to `amplify-lite` (github.com/asen-marketing/amplify-lite), which is frozen for the sites already built on it. This repo is the **parent theme**; each client site is a child theme in its own repo (Phase 5 builds the scaffold and the self-update path). Clients get editor access after launch for minor text/image changes, so anything that reaches the editor must be safe for a non-developer to touch.

Keep this file current. When you add a convention, fix a known issue, or ship a roadmap phase, update the relevant section here. The detailed modernization plan lives in [MODERNIZATION.md](MODERNIZATION.md).

## Stack

- **Classic PHP theme with `theme.json`** (hybrid). `theme.json` holds every token — palette, spacing presets, font-size presets, heading scale, line heights, gaps, radii, container widths — and `_tokens.scss` / `_colors.scss` alias them as CSS custom properties. Edit `theme.json`, never the aliases. No block templates, no Site Editor.
- **ACF Pro + ACF Extended** define every field group, CPT, taxonomy, and options page. All of it is synced to `acf-groups/*.json` — commit the JSON, never hand-write `register_post_type` / `register_taxonomy` / `acf_add_local_field_group`. Edit groups in the ACF UI, or edit the JSON and use Custom Fields → Field Groups → Sync. **ACF Extended deletes a group's JSON file whenever that group is saved or synced without its "Auto Sync: JSON" setting** (ACFE only adds the setting to groups created in its UI, not ones arriving via Sync). `asen_acf_force_json_autosync()` in `acf-customizations.php` forces the setting on every group, and every file in `acf-groups/` carries `"acfe_autosync": ["json"]`; keep it when hand-writing JSON. Before committing after a sync, check that no `acf-groups/*.json` shows as deleted unless you trashed that group. A group that exists only as JSON (no DB copy) still works at runtime but is listed under Field Groups → "Sync available", not the main list. Block field groups carry `"acfe_categories": {"block-fields": "Asen Core Blocks"}` so they show in that ACF Extended category view; when hand-writing JSON, also keep `modified` at or before the current time — a future timestamp keeps the group in "Sync available" after it has been synced.
- **Gutenberg + ACF blocks** for page content; **core Group / Columns / Grid / Media & Text** are the layout containers (decided Oct 2026). **Kadence Blocks** is installed on older builds only — don't add Kadence dependencies or style against `.kt-*` selectors in upstream. Where it is active, its saved palette (plus the WP legacy colors it registers) is read as empty, so theme.json stays the only palette in the editor and ACF swatches (`asen_empty_kadence_palette()` in `theme-json.php`); a child opts back in with `add_filter( 'asen/allow_kadence_palette', '__return_true' )`.
- **Gravity Forms**, **Yoast**, **Relevanssi**, **SVG Support**, **WP-PageNavi**, **Broken Link Checker** are assumed present.
- **CSS cascade layers.** `scss/index.scss` declares `@layer vendor, reset, base, components, blocks, utilities` and wraps each partial group; `includes/css-layers.php` pushes every WordPress and plugin stylesheet (links and inline) into `vendor` (a link with SRI `integrity` stays a link — `@import` can't verify it), so the theme always sits above core/plugin defaults. Unlayered CSS — `_custom.scss`, child-theme CSS, editor-set inline block styles, `core-block-supports` — beats all layers, which is the whole override story: no `!important`, no specificity wars. Handles that must stay unlayered go through the `asen/unlayered_style_handles` filter.
- **WP-SCSS** compiles every non-partial in `scss/` on page load: `index.scss` → `css/index.css` (front end) and `editor.scss` → `css/editor.css` (block editor, via `add_editor_style`). Compressed, inline sourcemap. `css/` is gitignored. Never edit or commit compiled CSS, never run a compiler yourself. On production, `always_recompile` must be off.
- **JS:** vanilla, no jQuery anywhere in the theme (WordPress only loads it where a plugin such as Gravity Forms asks for it; child code that wants it declares `'jquery'` as a dependency). `js/main.js` (off-canvas menu: toggles, submenu slide, `inert` / `aria-expanded`, Escape), `js/carousel.js` (mounts Splide carousels from the collection partial), `js/floating-cta.js`, block `view.js` files, `js/vendor/splide` (+ auto-scroll extension). Splide is the only carousel library; modals are `<dialog>`. File inputs are native, styled through `::file-selector-button` in `_forms.scss`. No bundler; `node_modules/` is vestigial.
- **Fonts/icons:** Adobe Fonts + Font Awesome are injected via the "Font Snippets" field in Site Options → Builders Only.

## Directory map

```
theme.json                 the token source of truth (settings only so far; styles.elements still live in SCSS)
functions.php              requires includes/*.php in order; tiny WP fixes only
includes/
  theme.php                enqueues, theme supports, image sizes, asen_enqueue_splide()/glide(), block.json loader
  gutenberg.php            the "Asen Blocks" inserter category + ACF block filters
  patterns.php             registers the "Asen" pattern category
  updates.php              self-update from GitHub releases (plugin-update-checker in includes/vendor); off on git checkouts
  development.php          asen_debug_print() + asen_install_local_mu_plugins() (copies includes/mu-plugins/ into wp-content/mu-plugins on local sites only)
  mu-plugins/              local-only mu-plugins the theme installs on Local sites: asen-local-novamira.php re-activates Novamira after a prod pull where it was already active
  client-editing.php       client inserter allowlist, no block unlocking / code editor / binding changes for clients, locked banner in new pages' block template
  block-bindings.php       asen/site-option bindings source (Site Options contact info) + editor data for js/editor-bindings.js
  css-layers.php           moves core/plugin stylesheets into the `vendor` cascade layer
  theme-json.php           editor styles + preset-only editor settings for non-Asen users (asen_user_is_builder())
  core-blocks/{slug}/      block.json (+ view.js / view.asset.php) for every block, auto-loaded by asen_register_acf_blocks()
  acf-customizations.php   ACF JSON paths, options pages, "asen_domain" location rule, GF form dropdown populator
  head-mods.php            <head> output: font/script snippets, and asen_option_css() — Site Options as :root CSS variables + the off-canvas @media block
  backend-mods.php         admin menu curation, editor hiding for custom templates, admin CSS
  helpers.php              asen_acf_image_url(), asen_clean_phone_number(), asen_geocode_address() (cached Nominatim), primary-category helpers
  body-class-mods.php      Site Options toggles as body classes (has-static-header, has-overlapping-header, has-sticky-hamburger, …)
  menus.php, woocommerce.php, yoast.php, cmp-gravity-patch.php
  css/backend-styles.css   admin-only styles
templates/
  block/{slug}.php         block render templates (one per includes/core-blocks/{slug})
  block/partials/          collection.php (shared grid / alternating / Splide carousel for the CPT hubs) + one {type}-item.php per CPT;
                           banner.php (Asen - Banner markup, also used by the Contact template and CPT archives via asen_banner_defaults())
  header/header.php, footer/footer.php, menu/   the one header and footer; a child theme overrides these files for a custom build
  front-page-banner.php, interior-banner.php, blog/, archive/, social/
patterns/{slug}.php        block patterns, auto-registered from file headers (category: asen); page-*.php are new-page starters
scss/
  index.scss               import order matters: vendor → variables/mixins → semantic partials → blocks → custom
  _tokens.scss             aliases of theme.json spacing/font-size presets and custom vars (do not edit values here)
  _colors.scss             aliases of the theme.json palette ($color-primary, $gray-700…)
  _component-vars.scss     :root defaults for every variable Site Options can override (--header-height, --button-bg, --footer-bg…)
  _section-styles.scss     Group block styles (Light / Dark / Brand Section) that reset the component variables
  editor.scss              block-editor stylesheet entry (tokens, type, buttons, blocks)
  _measurements.scss       breakpoints, gutters, responsive mixins
  _utility.scss            helper classes + shared mixins
  _block-globals.scss      .block-width-* and .spacing-top/bottom-* utility classes for block wrappers
  blocks/_{slug}.scss      one partial per block (+ _collection.scss, pattern partials), imported in blocks/index.scss
  _custom.scss             client-specific overrides (last import)
acf-groups/                ACF JSON sync folder (field groups, post types, taxonomies, options pages)
page.php, single-*.php, archive-*.php, custom-*.php   template hierarchy + named page templates
```

## Naming conventions

| Thing | Upstream | Client fork (e.g. George Ewart) |
|---|---|---|
| Block slug | `asen-intro-block` | `ewart-footer-cta` |
| Block title | `Asen - Intro Block` (new) / `Asen Intro Block` (existing) | `Ewart - Footer CTA` |
| Render template | `templates/block/asen-intro-block.php` | `templates/block/ewart-footer-cta.php` |
| SCSS partial | `scss/blocks/_asen-intro-block.scss` | `scss/blocks/_ewart-footer-cta.scss` |
| ACF group title | `Core - Asen Intro Block` / `Gutenberg - Asen CTA` | `Gutenberg - Ewart Footer CTA` |
| CPT field group | `Single - Team` | `Ewart Single - Project` |
| Options group | `Asen Header Options` | `Ewart Options - Projects Hub` |
| Template-part folder | `templates/blog/` | `templates/ewart-projects/` |
| Page template | `custom-contact.php` → `Template Name: Contact` | `custom-project-hub.php` → `Ewart Project Hub` |
| PHP functions | `asen_*` / `site_*` | `asen_*` is fine; avoid generic names |
| SCSS variables | `$spacing-md`, `$gray-700` | `$ewart-red`, `$ewart-charcoal` |

Pick the client prefix once (short, lowercase, one word) and use it everywhere. Block titles use `{Prefix} - {Name}` for new blocks; older blocks without the dash are left alone.

## Block conventions

Every ACF block render template follows this shape:

```php
<?php
/**
 * Block Name: Asen - Example
 */
if ( is_admin() ) {
    echo '<h3 class="asen-block-heading">' . esc_html( $block['title'] ) . '</h3>';
}
$classes = [ 'asen-example', 'asen-example-' . $block['id'], $block['className'] ?? '' ];
?>
<section class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>"
    id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
    <div class="inside">
        …
    </div>
</section>
```

- `<section>` wrapper with the slug as the base class; `.inside` is the max-width container. Horizontal gutters come from `$site-overall-gutter` + `@include site-overall-gutter-small`; the width comes from `block-width-{sm|md|lg|xl|xxl|full}` (`_block-globals.scss`, driven by the `__container_width` select) — the template always emits one, with a sensible fallback (`?: 'lg'`).
- **Block partials are structure only.** Layout, gaps, widths, alignment, positioning, token-driven modifiers (text scale, container width). No colors, backgrounds, font weights/styles/families, text transforms, line heights, borders, shadows, hover decoration, or hard-coded pixel sizes by default — type comes from base typography and the tokens, color from native supports or modifier classes, everything else from the child theme. Flat selectors (`.asen-x .item`, never `section.asen-x .inside .items .item a`). If a block needs an opinion to function (an overlay's position, a carousel slide width, an icon at `2em`), that's structure; if it's taste, it's out.
- **Padding, margin, background and text color are native block supports** (`block.json` `supports.spacing` / `supports.color`), rendered by `get_block_wrapper_attributes()` on the `<section>`; the editor offers the theme.json presets. Set sensible defaults in `block.json` `attributes.style.default`. The **"Reusable - Asen Tokens"** clone group covers what core has no preset control for: `__container_width`, `__gap`, `__heading_size`, `__line_height`, `__border_radius`, and `__font_size` (a text *scale* for multi-element blocks). Clone seamlessly; read as `get_field('text_spacing')['__gap']`. Never add pixel inputs or per-instance `<style>` blocks. `asen-intro-block` is the reference implementation.
- **Every block is registered with `block.json`** in `includes/core-blocks/{slug}/` (auto-loaded); there are no `acf_register_block()` calls left. Set `acf.renderTemplate`, `category: asen-blocks`, `supports.anchor: true`, `supports.align: false`, and an `example`. Blocks with InnerBlocks (see Link Wrapper) use `acf.mode: preview` plus `supports.jsx: true, mode: false`, and check ACF's `$is_preview` rather than `is_admin()` when the editor markup must differ.
- **Block JS is a `viewScript`:** `includes/core-blocks/{slug}/view.js` (vanilla, no jQuery, scoped with `document.querySelectorAll('.{slug}')`) plus `view.asset.php` for dependencies and version. WordPress enqueues it only when the block renders, front end only. Third-party libraries are *registered* in `theme.php` (`asen_register_block_dependencies()`) and referenced as dependencies / `viewStyle` handles — never enqueued globally. No inline `<script>` in templates; pass data to JS through `data-` attributes. Anything that needs the network at render time (geocoding) happens in PHP with a transient, not in every visitor's browser.
- Block names are `acf/{slug}`; the render callback resolves `templates/block/{slug}.php`. Never rename a block slug that has shipped — content in client databases references it.
- Adding a field to a shipped block: ACF does **not** apply `default_value` to keys missing from a block's saved data, so existing instances read the new field as `null`. Make the absent state the old behavior — name toggles `hide_x` (default off) rather than `show_x` (default on), and `?:` a sensible default for selects.
- If a block's layout is reused by a page template, keep the block template thin and put the markup in a shared template part called with `get_template_part( 'templates/…', null, $args )`. See the Ewart footer CTA pattern.
- Blocks that render a CPT (Team Hub, Services Hub, Testimonials, FAQs, Products Showcase) are gated by the content-type toggles in Site Options → Builders Only.
- **CPT hubs share one layout partial:** `get_template_part( 'templates/block/partials/collection', null, [ items, item_template, layout => grid|alternating|carousel, columns, label, splide, item_args ] )`. Item templates use the shared anatomy `.item-media` / `.item-copy` / `.item-title` / `.item-meta` / `.item-link`, so `scss/blocks/_collection.scss` lays out every hub; per-block partials only add the block shell and genuinely different bits (testimonials' masonry). Carousel layout enqueues `asen-carousel` + `splide` itself; Splide options go in `data-splide`. A new CPT hub is a block template + an item template, no new layout CSS.
- **Page banners are the `Asen - Banner` block** (eyebrow, title that falls back to the page title, subtitle, two buttons; background none / image / slideshow / video with a mobile file and a poster image; overlay as a percentage (empty = site default, 0 = none) tinted by the block's background color; native color, spacing, min height and alignment). `page.php` renders only the content. New pages get it locked at the top via the page post type's block template; `supports.multiple: false`. Templates without the block editor (Contact, CPT archives) render the same markup with `get_template_part( 'templates/block/partials/banner', null, asen_banner_defaults( [ 'title' => … ] ) )`. Builders Only → Page Banners supply site-wide defaults — eyebrow / title / subtitle color, size and transform, text alignment, background color and blend mode, overlay tint and opacity (used by banners whose Overlay is left empty, the default), and button colors; a banner's own settings win.
- **Alignment is native:** `supports.typography.textAlign` gives the toolbar alignment control and a `has-text-align-*` class. Use it instead of an ACF alignment radio. 
- **Patterns** live in `patterns/{prefix}-{slug}.php` with headers `Title: {Prefix} - {Name}`, `Slug: amplify/{prefix}-{slug}`, `Categories: asen`. Core blocks only. Give the outer Group the retired block's class (e.g. `asen-copy-image`) and keep the pattern's rules in that block's SCSS partial, scoped to `.{slug}.wp-block-group`.
- **Retiring blocks.** v3 has no shipped client sites yet, so a block retired before the first v3 release is **deleted outright** — registration, template, field group JSON, legacy SCSS. Once the first v3 client ships, retiring a shipped block means `supports.inserter: false` and keeping everything so existing content renders. Deleted so far: Copy + Image (→ `asen-copy-image` pattern), Product/Service Tiers (→ `asen-pricing-tiers` pattern), Special Copy Blurb (→ Intro Block, which has icon / heading / subheading / blurb / CTAs), Testimonial Carousel (→ Testimonials carousel), Button, Buttons Group, Anchor, WYSIWYG, CTA, Split Fancy Copy (→ core blocks), and the never-registered Blockquote ×3 / Fancy List templates.

## Client editing

Clients (anyone `asen_user_is_builder()` says isn't Asen staff) get: preset-only color/spacing/font-size controls (`theme-json.php`); an inserter allowlist of Asen blocks plus core paragraph, heading, list, image, gallery, buttons, quote, separator, spacer, group, columns, media & text, cover, video, embed, table and synced patterns (`asen/client_allowed_blocks` to change it per site); no block unlocking, no code editor, and no connecting/disconnecting block bindings (`client-editing.php`). Builders see everything and can unlock. Layout safety comes from locking: the page banner is locked in place, and pattern sections use `templateLock: contentOnly` so clients edit text and images only. Styles panel options: Button → Knockout / Small / Wide (plus core's Outline, styled to match), Group → Light / Dark / Brand Section. Section styles work by resetting the component variables (`--link-color`, `--button-bg`, `--heading-color`…), so anything inside adapts; add new contexts the same way rather than with descendant color rules.

## Block bindings

Core Paragraph, Heading, Button, Image (and List Item from WP 7.1) can pull values from a source instead of storing them, and bound blocks can't be edited in place.

- **`asen/site-option`** (`includes/block-bindings.php`): Site Options → Contact Information. Keys `phone`, `email`, `address`, `hours`. One key adapts to the attribute it's bound to: text content (paragraph/heading/list item) gets the linked value (`tel:`, `mailto:`, Google Maps; hours unlinked, one line per row), a button or image `url` gets the bare link, button text / captions get the plain value. Markup: `<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"asen/site-option","args":{"key":"phone"}}}}} --><p></p><!-- /wp:paragraph -->`. The Contact Page pattern uses all four. Add a key in `asen_contact_info()`; the editor picks it up automatically (`js/editor-bindings.js` gets the resolved values, so bound blocks preview real data and the keys appear in the block's Attributes panel). Use it anywhere contact info appears in content, so a client changes a phone number once.
- **`acf/field`** (ACF Pro): fields of the current post, or of each post inside a Query Loop. The field needs "Allow Access to Value in Editor UI" (`allow_in_bindings`) on. Our CPTs don't use the block editor (their singles are PHP templates), so this matters only for a client CPT or page field built for block content.
- Builders connect fields in the block's Attributes panel; clients can't change bindings (`canUpdateBlockBindings` off).

## Adding a block (checklist)

1. Create the field group in ACF UI: title `Gutenberg - {Prefix} - {Name}`, location Block = `acf/{prefix}-{slug}`, clone "Reusable - Asen Tokens" fields for spacing/width/size. Confirm `acf-groups/group_*.json` was written.
2. `includes/core-blocks/{prefix}-{slug}/block.json` (copy `asen-intro-block` as the starting point).
3. `templates/block/{prefix}-{slug}.php` following the shape above. Escape everything that came from a field.
4. `scss/blocks/_{prefix}-{slug}.scss`, imported from `scss/blocks/_index.scss`. Use tokens and the responsive mixins; no raw breakpoint pixel values when a mixin exists.
5. If it needs JS: Splide, not Glide. Enqueue only for that block.
6. Load a page with the block, confirm WP-SCSS compiled without errors (errors print for logged-in users), check the editor preview too.

## Adding a CPT

1. ACF → Post Types (and Taxonomies) in the UI. Confirm `acf-groups/post_type_*.json` exists. `show_in_rest` on if it needs the block editor.
2. Field group `{Prefix} Single - {CPT}` located on the post type.
3. `single-{post_type}.php` using the **registered slug** (singular — e.g. `single-project.php`). Only add `archive-{post_type}.php` if `has_archive` is on.
4. If the CPT is optional per site, add a toggle in "Asen Builders Only" and gate the admin menu in `backend-mods.php` like the existing ones.

## SCSS rules

- 2-space indent. One partial per concern; import it from the right index file in the right order.
- Values come from `_tokens.scss` / `_colors.scss` (theme.json aliases) and `_measurements.scss`. Token variables are `var()`s: no SCSS math or color functions on them (`calc()` / `color-mix()` instead), and never pass them to `responsive-heading()` / `responsive-paragraph()` — the presets are already fluid, so `font-size: $heading-h3;` is the whole job. The responsive mixins stay for one-off pixel values; `site-overall-gutter-small` is still the horizontal gutter.
- Client-specific: brand colors go in `_colors.scss` under a prefixed name; page/section overrides go in `_custom.scss` or new `_{prefix}-*.scss` partials. **Do not bake client values into shared partials or mixin defaults** (a mixin whose default color is `$ewart-charcoal` is the anti-pattern — pass it as an argument).
- Button variants are **classes in `_buttons.scss`** (`.button.red`, `.button-knockout`, `.button-small`, `.button-wide`), applied via the block's class field or "Additional CSS class". Don't add option fields or ACF color pickers for button variants. Variant colors reference palette slugs, not raw hex, once Phase 1 lands.
- scssphp (WP-SCSS's compiler) warns "Array to string conversion" — shown on the front end to logged-in users only — on `:where()` / `:is()` wrapping an attribute selector, because of how it matches `@extend`s. Use a plain selector list instead. Check compiles with warnings captured, not just via an anonymous page load.
- `!important` is a smell; it usually means a specificity fight with Kadence or `head-mods.php` inline CSS. Fix the selector or move the rule.
- `$site-overall-max-width` is `var(--wp--style--global--wide-size)` — theme.json `settings.layout.wideSize`.

## PHP rules

- Match the indentation of the file you're in (upstream is mostly 4 spaces). Never submit reformat-only diffs.
- Escape output from fields: `esc_html`, `esc_url`, `esc_attr`. Clients type into these fields.
- Prefer hooks and `get_template_part( …, $args )` over editing `header.php` / `footer.php` / `page.php`.
- Client-specific PHP (query mods, search tweaks, taxonomy UI) goes in a dedicated `includes/{prefix}-*.php` required from `functions.php`, not inline in `functions.php`.
- No runtime filesystem writes (the exceptions are `asen_install_local_mu_plugins()` and the marker file `asen-local-novamira.php` keeps, both gated on `wp_get_environment_type()` being `local`), no `error_log()` debugging left in, no hard-coded CDN tags in `header.php` — enqueue per block.
- `asen_debug_print()` in `includes/development.php` is the sanctioned dump helper; remove calls before commit.

## Site Options (ACF option pages)

`Site Options` (slug `custom-options`), `Header Options`, `Off-Canvas Options`, `Footer Options`, `Blog → Archive Options` — Editors and up (`edit_others_posts`). There is **one header layout and one footer layout** (the v2 layout selectors were removed in v3); the footer mailing-list column is a toggle in Footer Options. A design that needs something else overrides `templates/header/header.php` / `templates/footer/footer.php` in the child theme. The **"Asen Builders Only"** group is only shown to users with an `@asenmarketing.com` email (custom `asen_domain` location rule in `acf-customizations.php`); same rule hides the ACF admin and the Kadence menu from clients. Put anything a client shouldn't touch there. Location rules only hide fields: ACF saves whatever field keys are posted, which is why the option pages themselves are limited to Editors and up and why every option value is escaped on output.

**Site Options are overrides, not the source.** Every option-driven value (header, off-canvas, footer, links & buttons, page banner, floating CTA, per-page padding) is a CSS variable whose default lives in `scss/_component-vars.scss` (built from theme tokens). `asen_option_css()` prints only the options that are set, as one unlayered `:root{…}` after the theme stylesheet, so they win without specificity. On/off options are body classes (`body-class-mods.php`). To add an option-driven value: default in `_component-vars.scss`, `var(--name)` in the partial, one line in `asen_option_css()`. Media queries can't read variables, so the off-canvas breakpoint is the only generated rule set. No other PHP in the theme prints CSS.

**Option fields use tokens, not raw values.** Colors are the normal ACFE color picker (Advanced on, Display "Default", Color Picker on) with Use Theme Colors on, so the picker's swatches are the theme.json palette; the option stores hex. Sizes are selects populated from theme.json (`asen_populate_font_size_choices()` / `asen_populate_heading_size_choices()`) that store the preset variable. `asen_option_css()` wraps any `--…` value in `var()`. No default values on these fields — empty means the `_component-vars.scss` default. Layout dimensions (heights, max width, paddings, the off-canvas breakpoint) stay pixel numbers through `$px()`; token selects go through `$size()`, which still prints a legacy numeric value as px.

**Removed in v3:** the `site_colors` repeater and `site_max_width` fields. Colors are the theme.json palette (`_colors.scss` aliases it); max width is theme.json `layout.wideSize`. The `includes/scss-mods.php` + `_global-variables.scss` approach seen in older client forks (options → rewrite an SCSS file on `wp_head`) is dead and must not be copied forward.

## Releases and child themes

This repo is the **parent** theme. Client sites run a child theme (`Template: asen-amplify-v3`) from their own repo, started from `asenmarketing/asen-amplify-v3-starter` (private template repo); the child's AGENTS.md covers client work, including new-site setup from a copied WP Engine environment. The child folder is `asen-amplify-child` on every site.

- **Release:** bump `Version:` in `style.css`, add a CHANGELOG entry, commit, push a tag `vX.Y.Z` matching the Version. `.github/workflows/release.yml` builds `asen-amplify-v3.zip` (top-level folder `asen-amplify-v3/`, `.gitattributes` export-ignore applies) and publishes the GitHub release. Every site finds it within ~12 hours and WP-Cron installs it (`includes/updates.php`); "Check again" under Dashboard → Updates is immediate. Auto-install stays within the installed major (3.x → 3.y); a new major appears as an available update and waits for "Update now" per site, after the child is checked against the migration notes. A suffixed tag (`v3.1.0-beta.1`) publishes as a pre-release. Sites only ever install a **published release's zip** — the updater's tag/branch fallbacks are filtered out, so commits on `main` (even a bumped Version) never reach a site until tagged. Draft and pre-releases are ignored.
- **Semver matters:** anything a child theme may reference (block slugs, field names, template-part paths, SCSS variables/mixins, CSS custom properties, filters, function names) is API. Renaming or removing one is a major version with a migration note in CHANGELOG.md.
- **Never auto-updated:** a checkout with `.git/` or `WP_DEVELOPMENT_MODE` `theme` (local dev). Per site: `ASEN_AMPLIFY_GITHUB_TOKEN` (optional — the repo is public; a token only lifts GitHub's 60/hour anonymous API limit, e.g. if a shared host IP hits it), `ASEN_AMPLIFY_DISABLE_AUTO_UPDATE`.
- **Staging / emergency deploys:** `.github/workflows/deploy-wpengine.yml` rsyncs the repo to WP Engine — every push to `main` goes to the `WPE_STAGING_ENV` repo variable; run it manually with a JSON list of environments to push a fix everywhere. Needs the `WPE_SSHG_KEY_PRIVATE` secret.
- **Child-aware parent code:** CSS loads with `get_theme_file_uri()` (child's compiled file first); ACF JSON loads parent then child (child copy of a group shadows the parent's — so edit parent groups only here); the block loader scans both themes' `includes/core-blocks/` (same folder name in the child replaces the parent block); `locate_template` / `get_template_part` / patterns / theme.json already resolve child-first. WP-SCSS on a child site needs Base Location **Child Theme**, and only watches the child's `scss/`; `asen_scss_watch_parent()` (`includes/theme.php`) forces a recompile when any parent `.scss` is newer than the child's `css/index.css`, so parent updates reach production with Always Recompile off. `asen_scss_compile_lock()` takes a MySQL `GET_LOCK` so only one request compiles at a time — WP-SCSS's shared cache folder otherwise lets concurrent compiles leave an empty `css/index.css`.

## Working in an amplify-lite-era client fork

Older client sites are forks of amplify-lite and stay that way. Rules for maintaining them:

- Change the client prefix once; add to, don't rewrite, shared files. Shared partials (`_header.scss`, `_typography.scss`, `front-page-banner.php`, `off-canvas.php`…) do get client edits in practice, but every edit there is one more thing that can't be pulled from upstream. Prefer an override in `_custom.scss` or a new partial.
- Keep the upstream ACF group JSON files; add client groups alongside with the prefixed titles.
- Remove upstream blocks a client will never use by unregistering, not deleting, so an upstream merge stays clean.

## Dev environment

- Sites run in **Local** (`*.local`). Theme path: `app/public/wp-content/themes/asen-amplify-v3/`. The dev site runs it the way client sites do: `themes/asen-amplify-child/` is a clone of the starter repo and is the active theme, with WP-SCSS Base Location set to Child Theme. Parent SCSS changes therefore compile into the **child's** `css/index.css`; the parent's own `css/` is stale and unused.
- WP-SCSS recompiles on each page load while `always_recompile` is on; compile errors show for logged-in users. Check the child's `css/index.css` mtime if styles look stale.
- Local's push to WP Engine never sends `.git` folders or `wp-config.php`, plus whatever `app/public/.wpe-push-ignore` lists (on this site: Novamira and Query Monitor, which must not reach a server).
- Pulling from WP Engine brings down a database where Novamira is inactive. `includes/mu-plugins/asen-local-novamira.php` (copied into `wp-content/mu-plugins/` by the theme on the first local request, and re-copied whenever the theme's file is newer) remembers that Novamira was active on this local in a marker file outside the web root (`app/.asen-novamira-active`, next to `public/`, so a pull never touches it) and calls `activate_plugin()` on the first request after a pull that lacks it. Nothing is forced: a site where Novamira was never activated stays that way, and a normal Deactivate clears the marker. Edit the theme copy, never the installed one.
- Some locals expose a **Novamira MCP** server (REST-first WP control). It can run PHP/WP-CLI, read/write theme files, and inspect options — use it for "what is this option set to" questions instead of guessing. Native Gutenberg content writes through it require the Block Editor Queue admin page to be open in a browser.
- Query Monitor is active locally; keep an eye on it after adding queries.
- Keep a page at `/asen-v3-test/` on the dev site with one instance of every converted block plus core paragraph/button and the patterns; it's the visual regression check (not in the repo — content lives in that site's DB).
- WP caches the theme's `patterns/` file list (and `theme.json`). A new pattern file won't register until the cache clears — set `define( 'WP_DEVELOPMENT_MODE', 'theme' );` in the local `wp-config.php`, or run `wp_get_theme()->delete_pattern_cache()` once. Production clears it on theme update.

## Known issues (fix opportunistically, track in MODERNIZATION.md)

- The Dynamic Color SVG block inlines the file's markup. When no text color is set it keeps the file's own `<style>` rules (Illustrator exports colors that way), and those rules apply page-wide — two SVGs sharing `.cls-1` will fight. Setting a text color strips them.
- Block templates, partials, header/footer/menu templates and the contact template escape their output; the root archive/single/search templates predate that pass — escape as you touch them.
- `site_remove_admin_login_header()` (`theme.php`) removes `_admin_bar_bump_cb`, a no-op since WP 6.4, so logged-in users get core's 32px `html` margin. The real switch is `add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) )`, but check the header/off-canvas offsets while logged in before flipping it.
- `cmp-gravity-patch.php`'s reCAPTCHA interceptor polls every 50ms until a real `grecaptcha` appears, i.e. for the whole page view where there's no reCAPTCHA. Cheap, but could be limited to pages with a form.

## Roadmap status

See [MODERNIZATION.md](MODERNIZATION.md). Update this line as phases land.

- Phase 0 (hygiene + deprecated field removal): done
- Phase 1 (`theme.json` tokens, editor styles, head-mods → custom props): done
- Phase 2 (ACF blocks → `block.json` + native supports, structure-only SCSS): done — 17 blocks, all `block.json`, no inline `<style>` or `<script>` in any template, no jQuery in block JS
- Phase 3 (client-safe editing: role-gated inserter, patterns, section styles, bindings): done — allowlist, editor locks, banner block + locked page template, button/section styles, hero + 3 page-starter patterns, `asen/site-option` bindings for contact info
- Phase 4 (block library rationalization): mostly done — overlap and dead blocks deleted, two replacement patterns (copy + image, pricing tiers), Glide gone, Video Popup rebuilt on `<dialog>` (the Kadence video-popup replacement). jQuery and jquery-modal removed (`main.js` is vanilla)
- Phase 5 (distribution): parent side done — self-updater, release + WP Engine workflows, child-aware loading, CHANGELOG. Child starter lives at `asenmarketing/asen-amplify-v3-starter`. v3.0.0 released 2026-10-13. Left: the first client site on it — confirm the auto-update runs under WP Engine's cron and the child's CSS recompiles after a parent update.
