<?php
/**
 * Plugin Name: Asen - Local Novamira
 * Description: On local sites, re-activates Novamira after a database pull if it was active here before. Installed into wp-content/mu-plugins by the Asen Amplify theme (includes/mu-plugins/); edit the theme copy, not this one.
 * Author: Asen Marketing
 */

define( 'ASEN_LOCAL_NOVAMIRA_PLUGIN', 'novamira/novamira.php' );

// Marker lives outside the web root (Local: app/.asen-novamira-active) so a pull never touches it
function asen_local_novamira_marker() {
    $dir = wp_is_writable( dirname( ABSPATH ) ) ? dirname( ABSPATH ) : WPMU_PLUGIN_DIR;
    return $dir . '/.asen-novamira-active';
}

function asen_local_novamira_enabled() {
    return wp_get_environment_type() === 'local' && file_exists( WP_PLUGIN_DIR . '/' . ASEN_LOCAL_NOVAMIRA_PLUGIN );
}

// REMEMBER: a normal activate / deactivate sets or clears the marker
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

// RESTORE: a pulled database no longer lists Novamira; activate it again if the marker says it was active here
add_action( 'init', function () {
    if ( ! asen_local_novamira_enabled() ) {
        return;
    }
    $marker = asen_local_novamira_marker();
    if ( in_array( ASEN_LOCAL_NOVAMIRA_PLUGIN, (array) get_option( 'active_plugins', array() ), true ) ) {
        if ( ! file_exists( $marker ) ) {
            touch( $marker ); // activated before this file existed
        }
        return;
    }
    if ( file_exists( $marker ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        activate_plugin( ASEN_LOCAL_NOVAMIRA_PLUGIN );
    }
} );
