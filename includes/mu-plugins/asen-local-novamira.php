<?php
/**
 * Plugin Name: Asen - Local Novamira
 * Description: On local sites, keeps Novamira's active state in a marker file instead of the database, so a pull can't deactivate it and a push can't activate it on the server. Installed into wp-content/mu-plugins by the Asen Amplify theme (includes/mu-plugins/); edit the theme copy, not this one.
 * Author: Asen Marketing
 */

define( 'ASEN_LOCAL_NOVAMIRA_PLUGIN', 'novamira/novamira.php' );

// Marker lives outside the web root (Local: app/.asen-novamira-active) so a pull never touches it
function asen_local_novamira_marker() {
    static $marker = null;
    if ( $marker === null ) {
        $dir    = wp_is_writable( dirname( ABSPATH ) ) ? dirname( ABSPATH ) : WPMU_PLUGIN_DIR;
        $marker = $dir . '/.asen-novamira-active';
    }
    return $marker;
}

function asen_local_novamira_enabled() {
    return wp_get_environment_type() === 'local' && file_exists( WP_PLUGIN_DIR . '/' . ASEN_LOCAL_NOVAMIRA_PLUGIN );
}

// READS: active when the marker exists
function asen_local_novamira_inject( $plugins ) {
    if ( is_array( $plugins ) && asen_local_novamira_enabled() && file_exists( asen_local_novamira_marker() ) && ! in_array( ASEN_LOCAL_NOVAMIRA_PLUGIN, $plugins, true ) ) {
        $plugins[] = ASEN_LOCAL_NOVAMIRA_PLUGIN;
    }
    return $plugins;
}
add_filter( 'option_active_plugins', 'asen_local_novamira_inject' );

// WRITES: never stored in the database, so a pushed database leaves it inactive on the server
add_filter( 'pre_update_option_active_plugins', function ( $plugins ) {
    if ( is_array( $plugins ) && asen_local_novamira_enabled() ) {
        $plugins = array_values( array_diff( $plugins, array( ASEN_LOCAL_NOVAMIRA_PLUGIN ) ) );
    }
    return $plugins;
} );

// A normal activate / deactivate sets or clears the marker
add_action( 'activated_plugin', function ( $plugin ) {
    if ( $plugin === ASEN_LOCAL_NOVAMIRA_PLUGIN && asen_local_novamira_enabled() ) {
        touch( asen_local_novamira_marker() );
    }
} );
add_action( 'deactivated_plugin', function ( $plugin ) {
    if ( $plugin === ASEN_LOCAL_NOVAMIRA_PLUGIN && file_exists( asen_local_novamira_marker() ) ) {
        unlink( asen_local_novamira_marker() );
    }
} );

// MIGRATE: a database that still lists Novamira (activated before this file) moves to the marker
add_action( 'init', function () {
    if ( ! asen_local_novamira_enabled() ) {
        return;
    }
    remove_filter( 'option_active_plugins', 'asen_local_novamira_inject' );
    $stored = get_option( 'active_plugins', array() );
    add_filter( 'option_active_plugins', 'asen_local_novamira_inject' );
    if ( is_array( $stored ) && in_array( ASEN_LOCAL_NOVAMIRA_PLUGIN, $stored, true ) ) {
        touch( asen_local_novamira_marker() );
        update_option( 'active_plugins', $stored );
    }
} );
