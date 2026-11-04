<?php
/**
 * @file Theme functions.
 */

function site_enqueue_theme_scripts() {
    // WP-SCSS compiles the active theme's scss/, so on a client site the stylesheet is the child's css/index.css
    // (which imports the parent's SCSS); get_theme_file_*() picks the child's file and falls back to the parent's.
    $css_path         = get_theme_file_path( 'css/index.css' );
    $cache_buster_css = file_exists( $css_path ) ? filemtime( $css_path ) : null;
    $cache_buster_js  = date( "YmdHi", filemtime( get_template_directory() . '/js/main.js' ) );
    wp_enqueue_style( 'main', get_theme_file_uri( 'css/index.css' ), array(), $cache_buster_css );
    wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array(), $cache_buster_js, true );

    if ( function_exists( 'get_field' ) && get_field( 'enable_floating_cta', 'option' ) ) {
        wp_enqueue_script( 'asen-floating-cta', get_template_directory_uri() . '/js/floating-cta.js', array(), filemtime( get_template_directory() . '/js/floating-cta.js' ), true );
    }
}

add_action( 'wp_enqueue_scripts', 'site_enqueue_theme_scripts' );

// WP-SCSS only watches the active theme's scss/, so a child's compiled CSS would stay stale after a parent update.
function asen_scss_watch_parent( $needs_compiling ) {
    if ( $needs_compiling || ! is_child_theme() ) {
        return $needs_compiling;
    }
    $css = get_stylesheet_directory() . '/css/index.css';
    if ( ! file_exists( $css ) ) {
        return true;
    }
    $built = filemtime( $css );
    foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( get_template_directory() . '/scss', FilesystemIterator::SKIP_DOTS ) ) as $file ) {
        if ( 'scss' === $file->getExtension() && $file->getMTime() > $built ) {
            return true;
        }
    }
    return false;
}
add_filter( 'wp_scss_needs_compiling', 'asen_scss_watch_parent' );

// Concurrent WP-SCSS compiles share one cache folder and can leave an empty css file, so one request compiles at a time.
function asen_scss_compile_lock( $needs_compiling ) {
    global $wpdb;
    if ( ! $needs_compiling ) {
        return false;
    }
    $lock     = 'asen_scss_' . md5( get_stylesheet_directory() );
    $acquired = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) );
    if ( '1' === $acquired ) {
        add_action( 'shutdown', function () use ( $wpdb, $lock ) {
            $wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
        } );
    }
    return null === $acquired || '1' === $acquired;
}
add_filter( 'wp_scss_needs_compiling', 'asen_scss_compile_lock', 99 );

// Libraries blocks depend on are registered, never enqueued globally: block viewScripts list them as dependencies,
// and the collection partial enqueues the carousel only when a block renders in carousel layout.
function asen_register_block_dependencies() {
    $uri = get_template_directory_uri();
    wp_register_style( 'leaflet', $uri . '/js/vendor/leaflet/leaflet.css', array(), '1.9.4' );
    wp_register_script( 'leaflet', $uri . '/js/vendor/leaflet/leaflet.js', array(), '1.9.4', true );
    wp_register_style( 'splide', $uri . '/js/vendor/splide/splide.min.css', array(), '4.1.4' );
    wp_register_script( 'splide', $uri . '/js/vendor/splide/splide.min.js', array(), '4.1.4', true );
    wp_register_script( 'splide-auto-scroll', $uri . '/js/vendor/splide/splide-extension-auto-scroll.min.js', array( 'splide' ), '0.5.3', true );
    wp_register_script( 'asen-carousel', $uri . '/js/carousel.js', array( 'splide' ), filemtime( get_template_directory() . '/js/carousel.js' ), true );
}
add_action( 'init', 'asen_register_block_dependencies' );

// Enable native WordPress Featured Image
// Typically shouldn't be enabled unless importing old posts with Featured Images (ACF better option)
add_theme_support( 'post-thumbnails' );

// Add default posts and comments RSS feed links to head.

// add_theme_support( 'automatic-feed-links' );

// Let WordPress manage the document title.

// By adding theme support, we declare that this theme does not use a

// hard-coded <title> tag in the document head, and expect WordPress to
// provide it for us.
add_theme_support( 'title-tag' );

// Switch default core markup for search form, comment form, and comments
// to output valid HTML5.
add_theme_support(
    'html5',
    array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    )
);

function site_widgets_init() {
    register_sidebar( array(
        'name'          => 'Blog Sidebar',
        'id'            => 'blog_sidebar',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5>',
        'after_title'   => '</h5>',
    ) );
}

add_action( 'widgets_init', 'site_widgets_init' );

function site_remove_admin_login_header() {
    remove_action( 'wp_head', '_admin_bar_bump_cb' );
}

add_action( 'get_header', 'site_remove_admin_login_header' );

// Splide plus the auto-scroll extension, for client blocks that build their own carousels (e.g. a logo marquee).
function asen_enqueue_splide() {
    wp_enqueue_style( 'splide' );
    wp_enqueue_script( 'splide-auto-scroll' );
}

// Custom Image Sizes
add_image_size( 'asen-fullscreen', 1920, 1920, false );
add_image_size( 'asen-banner-general', 1920, 0, false );

add_filter( 'jpeg_quality', function () {
    return 75;
} );

// JPEG uploads are stored as WebP, full size and sub-sizes (WP keeps the uploaded JPEG as the original_image). WP falls
// back to JPEG where the server's image editor can't write WebP. Applies to new uploads, or existing ones once their
// sizes are regenerated. PNGs are left alone (logos, flat art).
add_filter( 'image_editor_output_format', function ( $formats ) {
    $formats['image/jpeg'] = 'image/webp';
    return $formats;
} );

// WP's WebP default is 86, which makes files about as big as the JPEGs they replace.
add_filter( 'wp_editor_set_quality', function ( $quality, $mime_type ) {
    return 'image/webp' === $mime_type ? 75 : $quality;
}, 10, 2 );

if ( ! isset( $content_width ) ) {
    $content_width = 1480;
}

// Every includes/core-blocks/{slug}/block.json in the parent, then the child; a child folder with the same name replaces the parent's block.
function asen_register_acf_blocks() {
    $folders = array();
    foreach ( array_unique( array( get_template_directory(), get_stylesheet_directory() ) ) as $theme_dir ) {
        foreach ( glob( $theme_dir . '/includes/core-blocks/*/block.json' ) ?: array() as $block_json ) {
            $folders[ basename( dirname( $block_json ) ) ] = dirname( $block_json );
        }
    }
    foreach ( $folders as $folder ) {
        register_block_type( $folder );
    }
}

function site_init() {
    // Disable for security.
    add_filter( 'xmlrpc_enabled', '__return_false' );
    // Enable infinite scrolling because it's our party.
    add_filter( 'media_library_infinite_scrolling', '__return_true' );
    // Disable new sitemap feature.
    add_filter( 'wp_sitemaps_enabled', '__return_false' );
    // Disable WordPress' automatic image scaling feature.
    add_filter( 'big_image_size_threshold', '__return_false' );
    asen_register_acf_blocks();
}

add_action( 'init', 'site_init' );