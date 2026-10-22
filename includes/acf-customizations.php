<?php

// ACF JSON saves to the active theme (the child on client sites) and loads from the parent, then the child, so a child's
// copy of a group overrides the parent's. Edit parent groups only in the parent repo, or the child copy shadows every update.
function asen_acf_json_save_point($path) {
    return get_stylesheet_directory() . '/acf-groups';
}

add_filter('acf/settings/save_json', 'asen_acf_json_save_point');

function asen_acf_json_load_point($paths) {
    unset($paths[0]);
    return array_merge($paths, array_unique([get_template_directory() . '/acf-groups', get_stylesheet_directory() . '/acf-groups']));
}

add_filter('acf/settings/load_json', 'asen_acf_json_load_point');

// ACF Extended deletes a field group's JSON file whenever the group is saved or synced without its "Auto Sync: JSON"
// setting, and groups imported via Sync don't get that setting by default. Force it on for every group so acf-groups/ stays the source of truth.
function asen_acf_force_json_autosync($field_group) {
    if (function_exists('acfe_has_json_sync') && function_exists('acfe_set') && ! acfe_has_json_sync($field_group)) {
        $sync   = (array) acfe_get($field_group, 'acfe.autosync', []);
        $sync[] = 'json';
        acfe_set($field_group, 'acfe.autosync', array_values(array_unique($sync)));
    }
    return $field_group;
}

add_filter('acf/validate_field_group', 'asen_acf_force_json_autosync', 30);

// Option Pages (Editors and up; edit_posts would let Contributors save them)
function asen_setup_acf_option_pages() {

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Site Options',
            'menu_title' => 'Site Options',
            'menu_slug'  => 'site-options',
            'capability' => 'edit_others_posts',
            'redirect'   => true,
            'icon_url'   => get_template_directory_uri() . '/includes/images/site-icon-small.png',
        ]);
        acf_add_options_page([
            'page_title'  => 'Site Options',
            'menu_title'  => 'Site Options',
            'menu_slug'   => 'custom-options',
            'parent_slug' => 'site-options',
            'capability'  => 'edit_others_posts',
            'redirect'    => false,
        ]);
        acf_add_options_page([
            'page_title'  => 'Header Options',
            'menu_title'  => 'Header Options',
            'menu_slug'   => 'header-options',
            'parent_slug' => 'site-options',
            'capability'  => 'edit_others_posts',
            'redirect'    => false,
        ]);
        acf_add_options_page([
            'page_title'  => 'Off-Canvas Options',
            'menu_title'  => 'Off-Canvas Options',
            'menu_slug'   => 'off-canvas-options',
            'parent_slug' => 'site-options',
            'capability'  => 'edit_others_posts',
            'redirect'    => false,
        ]);
        acf_add_options_page([
            'page_title'  => 'Footer Options',
            'menu_title'  => 'Footer Options',
            'menu_slug'   => 'footer-options',
            'parent_slug' => 'site-options',
            'capability'  => 'edit_others_posts',
            'redirect'    => false,
        ]);
        acf_add_options_page([
            'page_title'  => 'Blog Archive Options',
            'menu_title'  => 'Archive Options',
            'menu_slug'   => 'blog-archive-options',
            'parent_slug' => 'edit.php',
            'capability'  => 'edit_others_posts',
        ]);

// if ( get_field('site_services_enabled', 'option') ) {

//     acf_add_options_page(array(

//         'page_title' 	=> 'Services Archive Options',

//         'menu_title'	=> 'Archive Options',

//         'menu_slug'     => 'services-archive-options',

//         'parent_slug'	=> 'edit.php?post_type=services',

//     ));

// }

// if ( get_field('site_products_enabled', 'option') ) {

//     acf_add_options_page(array(

//         'page_title' 	=> 'Products Archive Options',

//         'menu_title'	=> 'Archive Options',

//         'menu_slug'     => 'products-archive-options',

//         'parent_slug'	=> 'edit.php?post_type=products',

//     ));

// }

// if ( get_field('site_team_members_enabled', 'option') ) {

//     acf_add_options_page(array(

//         'page_title' 	=> 'Team Archive Options',

//         'menu_title'	=> 'Archive Options',

//         'menu_slug'     => 'team-archive-options',

//         'parent_slug'	=> 'edit.php?post_type=team',

//     ));
        // }
    }
}

add_action('init', 'asen_setup_acf_option_pages');

// Limit field groups to Asen Team members with our emails.
function asen_acf_permit_team_type($choices) {
    $choices['User']['asen_domain'] = 'User Email Domain';
    return $choices;
}

add_filter('acf/location/rule_types', 'asen_acf_permit_team_type');

function asen_acf_permit_team_value($choices) {
    $choices[0] = 'asenmarketing.com';
    return $choices;
}

add_filter('acf/location/rule_values/asen_domain', 'asen_acf_permit_team_value');

function asen_acf_permit_team_match($match, $rule, $options, $field_group) {
    $current_user              = wp_get_current_user();
    $current_user_email_domain = explode('@', $current_user->user_email);
    $domain                    = 'asenmarketing.com';

    if ("==" == $rule['operator']) {
        $match = ($current_user_email_domain[1] == $domain);
    } elseif ("!=" == $rule['operator']) {
        $match = ($current_user_email_domain[1] != $domain);
    }

    return $match;
}

add_filter('acf/location/rule_match/asen_domain', 'asen_acf_permit_team_match', 10, 4);


function asen_populate_gform_dropdown($field) {

// if GFAPI doesn't exist, GravityForms is not running so bail early
    if (! class_exists('GFAPI')) {
        return $field;
    }

    $forms = \GFAPI::get_forms();

    // Set the initial 'Select a Form' drop down value
    $choices = [];

    foreach ($forms as $form) {
        $choices[$form['id']] = $form['title'];
    }

    $field['choices'] = $choices;

    return $field;
}

add_filter('acf/load_field/name=site_footer_mailing_list_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=team_archive_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=products_archive_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=services_archive_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=page_footer_gravity_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=single_product_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=single_service_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=single_team_member_cta_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=contact_form', 'asen_populate_gform_dropdown');
add_filter('acf/load_field/name=mailing_list_form', 'asen_populate_gform_dropdown');

// Size options list the theme.json tokens and store the token's CSS variable, so a child theme's presets show up and
// asen_option_css() prints var(). Older options saved a pixel number, which still prints as px.
function asen_populate_font_size_choices($field) {
    $choices = [];
    foreach ((array) wp_get_global_settings(['typography', 'fontSizes', 'theme']) as $size) {
        $choices['--wp--preset--font-size--' . _wp_to_kebab_case($size['slug'])] = $size['name'] . ' (' . $size['size'] . ')';
    }
    $field['choices'] = $choices ?: $field['choices'];
    return $field;
}

function asen_populate_heading_size_choices($field) {
    $choices = [];
    foreach (array_keys((array) wp_get_global_settings(['custom', 'heading'])) as $heading) {
        $choices['--wp--custom--heading--' . _wp_to_kebab_case($heading)] = strtoupper($heading) . ' heading';
    }
    $field = asen_populate_font_size_choices($field);
    $field['choices'] = $choices + $field['choices'];
    return $field;
}

add_filter('acf/load_field/name=footer_font_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=footer_bottom_font_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=footer_mailing_list_subtitle_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=footer_social_links_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=footer_mailing_list_title_size', 'asen_populate_heading_size_choices');
add_filter('acf/load_field/name=off_canvas_copyright_font_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=small_button_font_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=regular_button_font_size', 'asen_populate_font_size_choices');
add_filter('acf/load_field/name=off_canvas_font_size', 'asen_populate_heading_size_choices');
add_filter('acf/load_field/name=off_canvas_mobile_font_size', 'asen_populate_heading_size_choices');

// Asen - Banner's pre-3.3.0 Overlay radio values load into the percentage field as numbers (helpers.php).
add_filter('acf/load_value/key=field_69eb9b01b0025', 'asen_banner_overlay_legacy_value');

function asen_acf_admin_panel_permission($show) {
    $current_user              = wp_get_current_user();
    $current_user_email_domain = explode('@', $current_user->user_email);
    $domain                    = 'asenmarketing.com';
    if (! array_key_exists(1, $current_user_email_domain)) {
        return;
    }
    $match = ($current_user_email_domain[1] == $domain);
    return $match;
}

add_filter('acf/settings/show_admin', 'asen_acf_admin_panel_permission');