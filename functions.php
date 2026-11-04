<?php

require_once get_template_directory() . '/includes/development.php';
require_once get_template_directory() . '/includes/theme.php';
require_once get_template_directory() . '/includes/helpers.php';
require_once get_template_directory() . '/includes/head-mods.php';
require_once get_template_directory() . '/includes/body-class-mods.php';
require_once get_template_directory() . '/includes/backend-mods.php';
require_once get_template_directory() . '/includes/custom-post-types.php';
require_once get_template_directory() . '/includes/menus.php';
require_once get_template_directory() . '/includes/acf-customizations.php';
require_once get_template_directory() . '/includes/gutenberg.php';
require_once get_template_directory() . '/includes/embeds.php';
require_once get_template_directory() . '/includes/patterns.php';
require_once get_template_directory() . '/includes/block-bindings.php';
require_once get_template_directory() . '/includes/theme-json.php';
require_once get_template_directory() . '/includes/css-layers.php';
require_once get_template_directory() . '/includes/client-editing.php';
require_once get_template_directory() . '/includes/updates.php';
require_once get_template_directory() . '/includes/woocommerce.php';
require_once get_template_directory() . '/includes/yoast.php';
require_once get_template_directory() . '/includes/cmp-gravity-patch.php';

// Fix for CSS auto-sizing issue caused by WP 6.7.1
function remove_auto_sizes_css_fix($add_auto_sizes) {return false;}
add_filter('wp_img_tag_add_auto_sizes', 'remove_auto_sizes_css_fix');

// Only display GF admin styles in backend
add_action('wp_enqueue_scripts', function () {
    $admin_only_gf_styles = [
        'gaddon_form_settings_css', // GF add-on settings-page CSS
        'gform_chosen',             // legacy Chosen dropdown CSS (admin)
    ];
    foreach ($admin_only_gf_styles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}, 100);