<?php
/**
 * Title: Asen - Standard Page
 * Slug: amplify/page-standard
 * Categories: asen
 * Description: Banner, intro, two image-and-copy rows and a call to action.
 * Keywords: page, about, standard
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1400
 */
?>
<!-- wp:acf/asen-banner {"name":"acf/asen-banner","data":{"eyebrow":"","title":"","subtitle":"A short line under the page title.","background_type":"none","overlay":"","__container_width":"xl","__heading_size":"h1"},"mode":"preview","lock":{"move":true,"remove":true}} /-->

<!-- wp:acf/asen-intro-block {"name":"acf/asen-intro-block","data":{"heading":"Section heading","subheading":"A supporting subheading","blurb":"Two or three sentences that introduce the page.","cta_options":"0","__container_width":"lg","__font_size":"md"},"mode":"preview","style":{"typography":{"textAlign":"center"},"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}}} /-->

<!-- wp:group {"templateLock":"contentOnly","className":"asen-copy-image"} -->
<div class="wp-block-group asen-copy-image"><!-- wp:media-text {"mediaPosition":"left","mediaWidth":55,"verticalAlignment":"center"} -->
<div class="wp-block-media-text is-stacked-on-mobile is-vertically-aligned-center" style="grid-template-columns:55% auto"><figure class="wp-block-media-text__media"></figure><div class="wp-block-media-text__content"><!-- wp:heading -->
<h2 class="wp-block-heading">What we do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A few sentences about this. Swap the image on the left for something relevant.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:media-text -->

<!-- wp:media-text {"mediaPosition":"right","mediaWidth":55,"verticalAlignment":"center"} -->
<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile is-vertically-aligned-center" style="grid-template-columns:auto 55%"><div class="wp-block-media-text__content"><!-- wp:heading -->
<h2 class="wp-block-heading">How we do it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A few sentences about this. Swap the image on the right for something relevant.</p>
<!-- /wp:paragraph --></div><figure class="wp-block-media-text__media"></figure></div>
<!-- /wp:media-text --></div>
<!-- /wp:group -->

<!-- wp:group {"templateLock":"contentOnly","className":"is-style-section-brand","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-section-brand"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Ready to talk?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">One line that tells visitors what happens next.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Get in touch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
