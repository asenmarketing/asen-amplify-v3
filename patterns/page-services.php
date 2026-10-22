<?php
/**
 * Title: Asen - Services Page
 * Slug: amplify/page-services
 * Categories: asen
 * Description: Banner, intro, services grid, testimonials and a call to action.
 * Keywords: page, services
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1400
 */
?>
<!-- wp:acf/asen-banner {"name":"acf/asen-banner","data":{"eyebrow":"","title":"","subtitle":"What we offer, in one sentence.","background_type":"none","overlay":"","__container_width":"xl","__heading_size":"h1"},"mode":"preview","lock":{"move":true,"remove":true}} /-->

<!-- wp:acf/asen-intro-block {"name":"acf/asen-intro-block","data":{"heading":"What we do","subheading":"","blurb":"A short overview of the services below.","cta_options":"0","__container_width":"lg","__font_size":"md"},"mode":"preview","style":{"typography":{"textAlign":"center"},"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}}} /-->

<!-- wp:acf/asen-services-hub {"name":"acf/asen-services-hub","data":{"use_carousel":"0","display_options":"image","number_of_columns":"3","link_style":"text","link_text":"Learn more","__container_width":"xl","__heading_size":"h6"},"mode":"preview"} /-->

<!-- wp:acf/asen-testimonials {"name":"acf/asen-testimonials","data":{"use_carousel":"1","number_of_columns":"1","nav_options":"selectors","__container_width":"md","__font_size":"md"},"mode":"preview","style":{"typography":{"textAlign":"center"},"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg"}}},"className":"is-style-section-light"} /-->

<!-- wp:group {"templateLock":"contentOnly","className":"is-style-section-dark","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-section-dark"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Not sure where to start?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Tell us what you need and we'll point you the right way.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Contact us</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
