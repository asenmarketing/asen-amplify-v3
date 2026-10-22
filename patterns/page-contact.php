<?php
/**
 * Title: Asen - Contact Page
 * Slug: amplify/page-contact
 * Categories: asen
 * Description: Banner, contact form, contact details from Site Options, and map.
 * Keywords: page, contact, form, map
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1400
 */
?>
<!-- wp:acf/asen-banner {"name":"acf/asen-banner","data":{"eyebrow":"","title":"","subtitle":"We usually reply within one business day.","background_type":"none","overlay":"","__container_width":"xl","__heading_size":"h1"},"mode":"preview","lock":{"move":true,"remove":true}} /-->

<!-- wp:acf/asen-form-container {"name":"acf/asen-form-container","data":{"heading":"Send us a message","subheading":"","gravity_forms_shortcode":"","button_alignment":"left","__container_width":"md"},"mode":"preview"} /-->

<!-- wp:group {"templateLock":"contentOnly","className":"is-style-section-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-section-light"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Call</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"asen/site-option","args":{"key":"phone"}}}}} -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Email</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"asen/site-option","args":{"key":"email"}}}}} -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Visit</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"asen/site-option","args":{"key":"address"}}}}} -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Hours</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"asen/site-option","args":{"key":"hours"}}}}} -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:acf/asen-location-map {"name":"acf/asen-location-map","data":{"heading":"","address":"","full_width":"0","__container_width":"xl"},"mode":"preview"} /-->
