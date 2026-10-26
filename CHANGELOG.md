# Changelog

Versions follow semver. A **major** bump means a child theme may need changes: renamed or removed blocks, field names,
template parts, SCSS variables / mixins, CSS custom properties, or filters. Note the migration under the version.

## 3.3.2 — 2026-10-26

- Asen - Dynamic Color SVG: the uploaded file's own `width` and `height` on the root `<svg>` are stripped, so the theme's CSS and the block's max width size it instead of the export's fixed pixel size. A file with no `viewBox` gets one from those values first so it still scales.

## 3.3.1 — 2026-10-26

- Builders Only → Page Banners → Default Overlay Opacity is a percentage (0–100, empty = 50%) to match the banner's Overlay field. A value saved as a 0–1 decimal still works: it reads as the percentage it rendered at (so 1 is 100%, not 1%), in the option's field and on the front end, until it is re-saved.

## 3.3.0 — 2026-10-22

- Asen - Banner: Overlay is a percentage (0–100) instead of Site default / None / Light / Medium / Heavy, so the tint can be tuned per banner. Empty is still the site default (Builders Only → Page Banners → Default Overlay Opacity) and 0 is none. Saved radio values keep rendering at the strength they had (light 25%, medium 50%, heavy 75%) and load into the field as that number. The wrapper carries `has-overlay` plus `--banner-overlay-opacity` as an inline custom property instead of the `has-overlay-{default,light,medium,heavy}` classes; `.banner-overlay` always reads the variable.

## 3.2.3 — 2026-10-21

- Off-canvas: the `ul.sub-menu` rules (hidden until toggled, 25px / 20px links, no text-transform) apply only inside `.off-canvas-menu` instead of to every `.sub-menu` on the site, so a child theme's own menus with sub-menus are no longer hidden or restyled by the parent.

## 3.2.2 — 2026-10-19

- Block editor: the content canvas renders at 70% (`zoom` on `.editor-styles-wrapper`, factor in `--asen-editor-zoom`), so text and spacing read closer to their front-end proportions in the narrower editor column. The 3.2.1 image cap and the block heading label divide by the factor so they still land at 200×300px and 22px on screen. Front end unchanged.

## 3.2.1 — 2026-10-19

- Block editor: the `.asen-block-heading` label is 22px, and images inside the editor canvas are capped at 200px wide by 300px tall so large uploads don't blow out the layout while editing. Front end unchanged.

## 3.2.0 — 2026-10-19

- `responsive-heading` and `responsive-paragraph` step down at 1800px, 1500px, 1024px and 640px instead of two breakpoints, with multipliers tiered by the starting size so big type shrinks more than small type. Small sizes stop shrinking on narrow screens: headings of 30px and under keep their size at 640px; paragraphs of 16px and under at 1500px and 1024px, 18px and under at 640px. Mixin signatures are unchanged; everything built on them re-renders at the new sizes.

## 3.1.2 — 2026-10-15

- theme.json is the only color palette when Kadence Blocks is active. Kadence 3.8 saves a default palette, and Kadence then adds those colors plus WordPress's legacy default colors to the block editor, the theme.json palette and the ACF color picker swatches. The theme now reads Kadence's palette as empty. A child theme that wants Kadence's palette back: `add_filter( 'asen/allow_kadence_palette', '__return_true' )`.

## 3.1.1 — 2026-10-15

- Site Options color fields are back to the normal color picker (wheel and hex input), with the theme.json palette as its swatches, instead of 3.1.0's swatch-only palette. They save hex again; a color saved as a palette variable under 3.1.0 still prints.

## 3.1.0 — 2026-10-15

- Footer Options colors are palette-only swatches from theme.json and save the palette variable (`--wp--preset--color--{slug}`), so they follow palette changes. The hard-coded `#999999` / `#000000` / `#FFFFFF` defaults are gone; empty falls back to `_component-vars.scss`.
- Footer Options font sizes (footer, bottom bar, mailing-list title / subtitle, social icons) are theme.json font-size selects (title also offers the heading scale) instead of pixel inputs. Previously saved pixel values still print until the option is re-saved.
- The same for the rest of Site Options: Header, Off-Canvas (including the button repeater), Builders Only links / buttons / page banner (and the Text Properties clone behind the banner eyebrow / title / subtitle), the floating CTA and Page Options' header background are palette-only swatches; the `#000000` / `#FFFFFF` / `#999999` / `#495057` defaults are gone. Off-canvas menu font sizes are heading-scale + font-size selects; off-canvas copyright and button font sizes are font-size selects (the 40 / 30 / 18px field defaults are gone too). Heights, max width, paddings and the breakpoint stay pixel inputs. Previously saved hex and pixel values still print.

## 3.0.1 — 2026-10-14

- Logged-in toolbars render correctly again: the admin bar, plugins' admin-bar items (Yoast) and Query Monitor stay out of the `vendor` cascade layer, where the theme's element rules were beating their ID-scoped rules and pushing admin-bar links out of the bar.

## 3.0.0 — 2026-10-13

First release of v3, split from amplify-lite (which stays frozen for existing sites). Highlights:

- `theme.json` is the token source; SCSS variables alias its CSS custom properties. Cascade layers keep core and plugin CSS below the theme.
- All blocks are `block.json` with native spacing / color / alignment; structure-only SCSS; no inline styles or scripts. Overlapping and dead blocks removed; Asen - Banner replaces the page and front-page banner templates.
- Site Options are CSS variable overrides; one header and one footer layout.
- Client-safe editing: preset-only controls, inserter allowlist, locked page banner, section and button styles, page-starter patterns.
- Parent/child distribution with self-updates from GitHub releases; a child's compiled CSS rebuilds automatically when the parent's SCSS changes, one request at a time.
- Block bindings: `asen/site-option` puts Site Options contact info (phone, email, address, new Hours field) into core blocks; the Contact Page pattern uses it.
- No jQuery from the theme. Removed from amplify-lite: jquery-modal (`.modal`, `.blocker`, `rel="modal:open"`), the scroll-down CTA, the `asen-dom-loaded` / `asen-window-loaded` / `asen-time-to-relax` body classes, `data-useragent` on `<html>`, and the file-input wrapper markup (`.file-upload-wrapper`; native inputs are styled instead).
