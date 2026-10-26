<?php function asen_top_head() {
    ?>

<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<?php

    if ( class_exists( 'ACF' ) ):

        if ( get_field( 'site_font_snippets', 'option' ) ):
            echo get_field( 'site_font_snippets', 'option' );
        endif;
    endif;
    }

    add_action( 'wp_head', 'asen_top_head', 1 );

    function asen_head_code() {

    if ( class_exists( 'ACF' ) ):

        if ( get_field( 'site_head_script_snippets', 'option' ) ):
            echo get_field( 'site_head_script_snippets', 'option' );
        endif;
    endif;

    }

    add_action( 'wp_head', 'asen_head_code' );

    function asen_body_open_code() {

    if ( class_exists( 'ACF' ) ):

        if ( get_field( 'site_after_opening_body_tag_script_snippets', 'option' ) ):
            echo get_field( 'site_after_opening_body_tag_script_snippets', 'option' );
        endif;
    endif;
    }

    add_action( 'wp_body_open', 'asen_body_open_code' );

    function asen_body_close_code() {
    if ( class_exists( 'ACF' ) ):

        if ( get_field( 'site_before_closing_body_tag_script_snippets', 'option' ) ):
            echo get_field( 'site_before_closing_body_tag_script_snippets', 'option' );
        endif;
    endif;
    }

    add_action( 'wp_footer', 'asen_body_close_code' );

// Site Options as CSS variables. Only fields that are set are printed, as an unlayered :root rule right after the theme
// stylesheet, so they override the defaults in scss/_component-vars.scss. Toggles are body classes (body-class-mods.php).
function asen_option_css() {
    if ( ! class_exists( 'ACF' ) ) {
        return;
    }

    $opt     = fn( $name ) => get_field( $name, 'option' );
    $px      = fn( $value ) => is_numeric( $value ) ? (float) $value . 'px' : null;
    $size    = fn( $value ) => is_numeric( $value ) ? $px( $value ) : $value; // token selects; numbers are pre-token pixel values
    $row     = fn( $name ) => is_array( $opt( $name ) ) ? ( $opt( $name )[0] ?? array() ) : array();
    $icon    = fn( $code ) => is_string( $code ) && preg_match( '/^[0-9a-f]{2,6}$/i', $code ) ? '"\\' . $code . '"' : null;
    $page_id = is_singular() ? get_queried_object_id() : 0;

    $off_canvas_button = $row( 'off_canvas_button_options' );
    $floating_cta      = $row( 'cta_styling' );
    $overlay_opacity   = asen_banner_overlay_opacity_legacy_value( $opt( 'page_banner_background_overlay_opacity' ) );

    $vars = array(
        // Header
        '--header-height'           => $px( $opt( 'site_header_height' ) ),
        '--header-logo-height'      => $px( $opt( 'site_header_logo_height' ) ),
        '--header-max-width'        => $opt( 'full_width_header' ) ? '100%' : $px( $opt( 'header_max_width' ) ),
        '--header-menu-color'       => $opt( 'header_menu_color' ),
        '--header-menu-hover-color' => $opt( 'header_menu_hover_color' ),
        '--header-cta-bg'           => $opt( 'site_header_cta_color' ),
        '--header-cta-color'        => $opt( 'site_header_cta_text_color' ),
        '--header-bg'               => $page_id && get_field( 'disable_overlapping_header', $page_id ) ? get_field( 'single_header_background_color', $page_id ) : null,

        // Links & buttons
        '--link-color'              => $opt( 'link_color' ),
        '--link-hover-color'        => $opt( 'link_hover_color' ),
        '--button-bg'               => $opt( 'button_background_color' ),
        '--button-bg-hover'         => $opt( 'button_background_hover_color' ),
        '--button-color'            => $opt( 'button_text_color' ),
        '--button-color-hover'      => $opt( 'button_text_hover_color' ),
        '--button-font-size'        => $size( $opt( 'regular_button_font_size' ) ),
        '--button-padding-y'        => $px( $opt( 'regular_button_vertical_padding' ) ),
        '--button-padding-x'        => $px( $opt( 'regular_button_horizontal_padding' ) ),
        '--button-small-font-size'  => $size( $opt( 'small_button_font_size' ) ),
        '--button-small-padding-y'  => $px( $opt( 'small_button_vertical_padding' ) ),
        '--button-small-padding-x'  => $px( $opt( 'small_button_horizontal_padding' ) ),

        // Off-canvas menu
        '--off-canvas-bg'                 => $opt( 'off_canvas_background_color' ),
        '--off-canvas-color'              => $opt( 'off_canvas_text_color' ),
        '--off-canvas-hover-color'        => $opt( 'off_canvas_hover_color' ),
        '--off-canvas-font-size'          => $size( $opt( 'off_canvas_font_size' ) ),
        '--off-canvas-font-size-mobile'   => $size( $opt( 'off_canvas_mobile_font_size' ) ),
        '--off-canvas-text-transform'     => $opt( 'off_canvas_uppercase' ) ? 'uppercase' : null,
        '--off-canvas-indicator-closed'   => $icon( $opt( 'sub-menu_indicator_closed' ) ),
        '--off-canvas-indicator-open'     => $icon( $opt( 'sub-menu_indicator_open' ) ),
        '--off-canvas-indicator-open-transform' => $icon( $opt( 'sub-menu_indicator_open' ) ) ? 'none' : null,
        '--off-canvas-copyright-color'    => $opt( 'off_canvas_copyright_color' ),
        '--off-canvas-copyright-font-size' => $size( $opt( 'off_canvas_copyright_font_size' ) ),
        '--off-canvas-button-bg'          => $off_canvas_button['button_background_color'] ?? null,
        '--off-canvas-button-color'       => $off_canvas_button['button_text_color'] ?? null,
        '--off-canvas-button-bg-hover'    => $off_canvas_button['button_background_hover_color'] ?? null,
        '--off-canvas-button-color-hover' => $off_canvas_button['button_text_hover_color'] ?? null,

        // Footer
        '--footer-bg'                     => $opt( 'footer_background_color' ),
        '--footer-color'                  => $opt( 'footer_text_color' ),
        '--footer-hover-color'            => $opt( 'footer_text_hover_color' ),
        '--footer-font-size'              => $size( $opt( 'footer_font_size' ) ),
        '--footer-logo-height'            => $px( $opt( 'footer_logo_height' ) ),
        '--footer-bottom-bg'              => $opt( 'footer_bottom_background_color' ),
        '--footer-bottom-color'           => $opt( 'footer_bottom_text_color' ),
        '--footer-bottom-hover-color'     => $opt( 'footer_bottom_text_hover_color' ),
        '--footer-bottom-font-size'       => $size( $opt( 'footer_bottom_font_size' ) ),
        '--footer-social-size'            => $size( $opt( 'footer_social_links_size' ) ),
        '--footer-mailing-title-color'    => $opt( 'footer_mailing_list_title_color' ),
        '--footer-mailing-title-size'     => $size( $opt( 'footer_mailing_list_title_size' ) ),
        '--footer-mailing-subtitle-color' => $opt( 'footer_mailing_list_subtitle_color' ),
        '--footer-mailing-subtitle-size'  => $size( $opt( 'footer_mailing_list_subtitle_size' ) ),

        // Asen - Banner defaults
        '--banner-bg'                  => $opt( 'page_banner_background_color' ),
        '--banner-image-blend'         => $opt( 'page_banner_background_blend_mode' ),
        '--banner-overlay-bg'          => $opt( 'page_banner_background_overlay_color' ),
        '--banner-overlay-opacity'     => is_numeric( $overlay_opacity ) ? max( 0, min( 100, (float) $overlay_opacity ) ) / 100 : null,
        '--banner-eyebrow-color'       => $opt( 'page_banner_eyebrow_color' ),
        '--banner-eyebrow-transform'   => $opt( 'page_banner_eyebrow_transform' ),
        '--banner-title-color'         => $opt( 'page_banner_title_color' ),
        '--banner-title-transform'     => $opt( 'page_banner_title_transform' ),
        '--banner-subtitle-color'      => $opt( 'page_banner_subtitle_color' ),
        '--banner-subtitle-transform'  => $opt( 'page_banner_subtitle_transform' ),
        '--banner-button-bg'           => $opt( 'banner_button_background_color' ),
        '--banner-button-color'        => $opt( 'banner_button_text_color' ),
        '--banner-button-bg-hover'     => $opt( 'banner_button_background_hover_color' ),
        '--banner-button-color-hover'  => $opt( 'banner_button_text_hover_color' ),

        // Floating contact CTA
        '--floating-cta-bg'    => $floating_cta['background_color'] ?? null,
        '--floating-cta-color' => $floating_cta['text_color'] ?? null,

        // This page (Page Options)
        '--page-padding-top'    => $page_id && is_numeric( get_field( 'page_padding_top', $page_id ) ) ? get_field( 'page_padding_top', $page_id ) . ( get_field( 'page_padding_top_unit_type', $page_id ) ?: '%' ) : null,
        '--page-padding-bottom' => $page_id && is_numeric( get_field( 'page_padding_bottom', $page_id ) ) ? get_field( 'page_padding_bottom', $page_id ) . ( get_field( 'page_padding_bottom_unit_type', $page_id ) ?: '%' ) : null,
    );

    $css = '';
    foreach ( $vars as $name => $value ) {
        // Radios store "0" for "none"; anything that could break out of the declaration is stripped.
        if ( null === $value || false === $value || '' === $value || '0' === $value || is_array( $value ) ) {
            continue;
        }
        $value = trim( str_replace( array( ';', '{', '}', '<', '>' ), '', (string) $value ) );
        // Size options store a theme.json variable name (--wp--preset--…), as did 3.1.0's palette colors
        if ( preg_match( '/^--[\w-]+$/', $value ) ) {
            $value = 'var(' . $value . ')';
        }
        $css .= $name . ':' . $value . ';';
    }
    if ( $css ) {
        wp_add_inline_style( 'main', ':root{' . $css . '}' );
    }

    // Media queries can't read CSS variables, so the off-canvas breakpoint is the one rule set still generated here.
    $breakpoint = (int) $opt( 'off_canvas_breakpoint' ) ?: 1024;
    wp_add_inline_style( 'main', '@layer components{@media (max-width:' . $breakpoint . 'px){'
        . 'header.site-header .inside.align-left{justify-content:space-between}'
        . 'header.site-header .inside.align-left .navigation,header.site-header .inside.align-left .header-menu{width:fit-content}'
        . '.off-canvas-menu-backing-overlay,.off-canvas-menu-container{display:flex}'
        . '.off-canvas-menu-hamburger{display:block}'
        . 'header.site-header nav.header-menu{display:none}'
        . '.has-sticky-hamburger .navigation .button{margin-right:3rem}'
        . '}}' );
}
add_action( 'wp_enqueue_scripts', 'asen_option_css', 20 );
