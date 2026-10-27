<?php

// DEBUG PRINT_R
function asen_debug_print($var) {

	$type = get_debug_type($var);

	$result = '<div class="asen-debug">';
	$result .= '<h5><span>Variable type:</span> ' . $type . '</h5>';

	if (
		isset($var) == false
	) {
		$result .= '<pre>isset() tests false.</pre>';
	} else if (
		$type == 'null'
	) {
		$result .= '<pre>Variable is null!</pre>';
	} else if (
		empty($var) && 
		$var == 0
	) {
		$result .= '<pre>empty() tests true, but is a <strong>' . $type . '</strong> zero.</pre>';
	} else if (
		empty($var)
	) {
		$result .= '<pre>empty() tests true.</pre>';
	} else if (
		$type == 'string'
	) {
		$var = print_r($var, true);
		$result .= '<pre><strong>"</strong>' . esc_html($var) . '<strong>"</strong></pre>';
	} else {
		$var = print_r($var, true);
		$result .= '<pre>' . esc_html($var) . '</pre>';
	}

	$result .= '</div>';

	echo $result;
}

// LOCAL-ONLY MU-PLUGINS
// Copies includes/mu-plugins/*.php into wp-content/mu-plugins on local sites (see AGENTS.md → Dev environment).
function asen_install_local_mu_plugins() {
	if ( wp_get_environment_type() !== 'local' ) {
		return;
	}
	foreach ( glob( get_template_directory() . '/includes/mu-plugins/*.php' ) ?: array() as $source ) {
		$target = WPMU_PLUGIN_DIR . '/' . basename( $source );
		if ( file_exists( $target ) && filemtime( $target ) >= filemtime( $source ) ) {
			continue;
		}
		if ( wp_mkdir_p( WPMU_PLUGIN_DIR ) && wp_is_writable( WPMU_PLUGIN_DIR ) ) {
			copy( $source, $target );
		}
	}
}
asen_install_local_mu_plugins();
