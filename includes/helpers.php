<?php

function asen_background_image_inline( $wp_thumb = null, $post_id = null ) {
    if ($wp_thumb == null) {
		$wp_thumb = 'full';
	}
    if ($post_id == null) {
		$post_id = get_the_ID();
	}
    $featured_image_src = get_the_post_thumbnail_url($post_id, $wp_thumb);
    if (isset($featured_image_src)) {
		$result = 'style="background-image: url(\'';
		$result .= $featured_image_src;
		$result .= '\');"';
	}
    return ! empty( $result ) ? $result : false;
}

// GET ACF IMAGE
// Gets image URL through ACF on the current page
// ACF must provide attachment ID
function asen_acf_image_url($acf_image, $wp_thumb, $inline_style = false, $post_id = null) {
	if ($post_id == null) {
		$post_id = get_the_ID();
	}

	// Could be normal field or sub_field
	if (get_sub_field($acf_image, $post_id)) {
		$acf_field = get_sub_field($acf_image, $post_id);
	} elseif (get_field($acf_image, $post_id)) {
		$acf_field = get_field($acf_image, $post_id);
	}
	if (isset($acf_field)) {
		$image = wp_get_attachment_image_src($acf_field['ID'], $wp_thumb);
		if ($inline_style) {
			$result = 'style="background-image: url(\'';
			$result .= $image[0];
			$result .= '\');"';
		} else {
			$result = $image[0];
		}
	}
	return ! empty( $result ) ? $result : false;
}

// Remove dumb phone number stuff to return just the part we want for <a href="tel:...
function asen_clean_phone_number( $number ) {
	$typical_phone_junk = array(' ', '-', '.', '(', ')');
	return str_replace($typical_phone_junk, '', $number);
}

// Some hold overs from the old site related to Blog Categories.
function asen_get_primary_category_meta( $post_id = null ) {
    if ($post_id == null) {
        $post_id = get_the_ID();
    }
	// SHOW YOAST PRIMARY CATEGORY, OR FIRST CATEGORY
	$category = get_the_category( $post_id );
	$primary_category = array();
	// If post has a category assigned.
	if ( $category ) {
        $category_id = 0;
		$category_display = '';
		$category_slug = '';
		$category_link = '';
        $category_image = false;
		if ( class_exists('WPSEO_Primary_Term') )
		{
			// Show the post's 'Primary' category, if this Yoast feature is available, & one is set
			$wpseo_primary_term = new WPSEO_Primary_Term( 'category', $post_id );
			$wpseo_primary_term = $wpseo_primary_term->get_primary_term();
			$term = get_term( $wpseo_primary_term );
			if (is_wp_error($term)) {
				// Default to first category (not Yoast) if an error is returned
                $category_id = $category[0]->term_id;
				$category_display = $category[0]->name;
				$category_slug = $category[0]->slug;
				$category_link = get_category_link( $category[0]->term_id );
                $category_image = get_field( 'icon', 'category_' . $category[0]->term_id );
			} else {
				// Yoast Primary category
                $category_id = $term->term_id;
				$category_display = $term->name;
				$category_slug = $term->slug;
				$category_link = get_category_link( $term->term_id );
                $category_image = get_field( 'icon', 'category_' . $term->term_id );
			}
		}
		else {
			// Default, display the first category in WP's list of assigned categories
            $category_id = $category[0]->term_id;
			$category_display = $category[0]->name;
			$category_slug = $category[0]->slug;
			$category_link = get_category_link( $category[0]->term_id );
            $category_image = get_field( 'icon', 'category_' . $category[0]->term_id );
		}
        $primary_category['id'] = $category_id;
		$primary_category['url'] = $category_link;
		$primary_category['slug'] = $category_slug;
		$primary_category['title'] = $category_display;
        $primary_category['icon'] = wp_get_attachment_image($category_image, 'thumbnail');
	}
	return $primary_category;
}

function asen_get_primary_category( $post_id = null, $linked = true ) {
    if ($post_id == null) {
        $post_id = get_the_ID();
    }
	$primary_category = asen_get_primary_category_meta( $post_id );
    if ($linked) {
        $result = '<a href="' . $primary_category['url'] . '" class="primary-category" title="See all ' . $primary_category['title'] . ' blog posts">';
        // TODO: Control with params later.
        // $result .= $primary_category['icon'];
        $result .= $primary_category['title'] . '</a>';
    } else {
        $result = '<h4 class="blog-primary-category">' . $primary_category['title'] . '</h4>';
    }

	return !empty($result) ? $result : '';
}

/**
 * Get unique ID.
 *
 * This is a PHP implementation of Underscore's uniqueId method. A static variable
 * contains an integer that is incremented with each call. This number is returned
 * with the optional prefix. As such the returned value is not universally unique,
 * but it is unique across the life of the PHP process.
 *
 * @see wp_unique_id() Themes requiring WordPress 5.0.3 and greater should use this instead.
 *
 * @staticvar int $id_counter
 *
 * @param string $prefix Prefix for the returned ID.
 * @return string Unique ID.
 */
function twentytwenty_unique_id( $prefix = '' ) {
	static $id_counter = 0;
	if ( function_exists( 'wp_unique_id' ) ) {
		return wp_unique_id( $prefix );
	}
	return $prefix . (string) ++$id_counter;
}

// Geocode an address with Nominatim once and cache it (30 days; a miss is cached for a day).
function asen_geocode_address( $address ) {
    $key    = 'asen_geocode_' . md5( $address );
    $cached = get_transient( $key );
    if ( false !== $cached ) {
        return $cached ?: null;
    }
    $response = wp_remote_get( 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . rawurlencode( $address ), array(
        'timeout'    => 8,
        'user-agent' => 'AsenAmplify/3 (' . home_url() . ')',
    ) );
    $results = 200 === (int) wp_remote_retrieve_response_code( $response ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : null;

    // Network error, rate limit or garbage: retry in a few minutes and keep showing the last good result meanwhile.
    if ( ! is_array( $results ) ) {
        $known = get_option( $key . '_last' );
        set_transient( $key, $known ?: 0, 10 * MINUTE_IN_SECONDS );
        return $known ?: null;
    }
    $hit = $results[0] ?? null;
    if ( empty( $hit['lat'] ) ) {
        set_transient( $key, 0, DAY_IN_SECONDS ); // Nominatim answered: the address isn't found.
        return null;
    }
    $coords = array( 'lat' => (float) $hit['lat'], 'lon' => (float) $hit['lon'] );
    set_transient( $key, $coords, 30 * DAY_IN_SECONDS );
    update_option( $key . '_last', $coords, false );
    return $coords;
}

// An uploaded SVG's markup, sanitized for inline output (no scripts, handlers or external references).
// $recolor drops the file's own <style> rules and style attributes, which would otherwise beat the theme's layered fill: currentColor.
function asen_inline_svg( $attachment_id, $recolor = false ) {
    $file = $attachment_id ? get_attached_file( $attachment_id ) : '';
    if ( ! $file || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) || ! is_readable( $file ) ) {
        return '';
    }
    $svg = preg_replace( array( '/<\?xml.*?\?>/s', '/<!DOCTYPE.*?>/si', '/<!--.*?-->/s', '#<script\b.*?</script>#si' ), '', file_get_contents( $file ) );
    if ( $recolor ) {
        $svg = preg_replace( array( '#<style\b.*?</style>#si', '/\sstyle=("[^"]*"|\'[^\']*\')/i' ), '', $svg );
    }

    $common = array_fill_keys( array( 'id', 'class', 'style', 'fill', 'fill-rule', 'fill-opacity', 'clip-rule', 'clip-path', 'mask', 'opacity', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-opacity', 'transform', 'd', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'width', 'height', 'points', 'offset', 'stop-color', 'stop-opacity', 'gradientunits', 'gradienttransform', 'viewbox', 'preserveaspectratio', 'xmlns', 'xmlns:xlink', 'version', 'role', 'aria-hidden', 'aria-label', 'focusable', 'xml:space' ), true );
    $allowed = array_fill_keys( array( 'svg', 'g', 'path', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'rect', 'defs', 'title', 'desc', 'lineargradient', 'radialgradient', 'stop', 'clippath', 'mask', 'symbol' ), $common );
    $allowed['use']   = $common + array( 'href' => true, 'xlink:href' => true );
    $allowed['style'] = array( 'type' => true ); // Illustrator exports color through classes; note the rules apply page-wide.

    // SVG paint properties in style="" (core's safe CSS list only gained fill/stroke in WP 7.1).
    $svg_css = fn( $props ) => array_merge( $props, array( 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-opacity', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stop-color', 'stop-opacity', 'clip-rule', 'opacity' ) );
    add_filter( 'safe_style_css', $svg_css );
    $clean = wp_kses( $svg, $allowed );
    remove_filter( 'safe_style_css', $svg_css );

    // kses lowercases attribute names; SVG's camelCase ones are restored so the markup is valid XML too.
    $camel = array( 'viewbox' => 'viewBox', 'preserveaspectratio' => 'preserveAspectRatio', 'gradientunits' => 'gradientUnits', 'gradienttransform' => 'gradientTransform', 'lineargradient' => 'linearGradient', 'radialgradient' => 'radialGradient', 'clippath' => 'clipPath' );
    return trim( preg_replace_callback( '/(?<=[\s<\/])(' . implode( '|', array_keys( $camel ) ) . ')(?=[\s=>\/])/', fn( $m ) => $camel[ $m[1] ], $clean ) );
}

// YouTube video ID from a bare ID or any youtube.com / youtu.be URL.
function asen_youtube_id( $value ) {
    $value = trim( $value );
    if ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~', $value, $m ) ) {
        return $m[1];
    }
    return preg_match( '/^[A-Za-z0-9_-]{11}$/', $value ) ? $value : '';
}

// Banner overlay strength: 'default' (empty: Builders Only → Page Banners opacity), 'none', or a whole percentage 1–100.
function asen_banner_overlay( $value ) {
    $value = asen_banner_overlay_legacy_value( $value );
    if ( is_numeric( $value ) ) {
        $value = (int) round( max( 0, min( 100, (float) $value ) ) );
        return $value ? $value : 'none';
    }
    return 'none' === $value ? 'none' : 'default';
}

// The Overlay field was a radio before 3.3.0; its saved values become the percentage they rendered at. Block data without
// field-key references loads through a keyless dummy field, so the render path maps here and the editor via acf/load_value.
function asen_banner_overlay_legacy_value( $value ) {
    $legacy = array( 'default' => '', 'none' => 0, 'light' => 25, 'medium' => 50, 'heavy' => 75 );
    return is_string( $value ) && isset( $legacy[ $value ] ) ? $legacy[ $value ] : $value;
}

// Banner args with defaults filled in, including the classes and the wrapper's inline custom properties. Site Options
// (Builders Only → Page Banners) supply the default title size, text alignment and overlay opacity; a block's own settings win.
function asen_banner_defaults( $args ) {
    $args = wp_parse_args( array_filter( $args, fn( $v ) => null !== $v && '' !== $v ), array(
        'eyebrow' => '', 'title' => '', 'subtitle' => '', 'links' => array(), 'background' => 'none', 'image' => null, 'slides' => array(),
        'video_desktop' => '', 'video_mobile' => '', 'overlay' => 'none', 'container_width' => 'xl', 'title_size' => '', 'has_text_align' => false,
    ) );
    // Text Properties sizes are h1–h6 (the "-big" variants never had CSS, so they map to the base size) or "default".
    $option_size  = fn( $name ) => function_exists( 'get_field' ) && preg_match( '/^(h[1-6])(-big)?$/', (string) get_field( $name, 'option' ), $m ) ? $m[1] : '';
    $option_align = function_exists( 'get_field' ) ? get_field( 'page_banner_text_align', 'option' ) : '';
    $title_size   = $args['title_size'] ?: ( $option_size( 'page_banner_title_size' ) ?: 'h1' );
    $eyebrow_size = $option_size( 'page_banner_eyebrow_size' );
    $sub_size     = $option_size( 'page_banner_subtitle_size' );
    $overlay      = 'none' !== $args['background'] ? asen_banner_overlay( $args['overlay'] ) : 'none';

    $args['overlay'] = $overlay;
    $args['style']   = is_int( $overlay ) ? '--banner-overlay-opacity:' . ( $overlay / 100 ) . ';' : '';
    $args['classes'] = array_filter( array(
        'asen-banner',
        'block-width-' . $args['container_width'],
        'has-title-' . $title_size,
        $eyebrow_size ? 'has-eyebrow-' . $eyebrow_size : '',
        $sub_size ? 'has-subtitle-' . $sub_size : '',
        'none' !== $args['background'] ? 'has-media' : '',
        'none' !== $overlay ? 'has-overlay' : '',
        ! $args['has_text_align'] && in_array( $option_align, array( 'left', 'center', 'right' ), true ) ? 'has-text-align-' . $option_align : '',
    ) );
    return $args;
}

