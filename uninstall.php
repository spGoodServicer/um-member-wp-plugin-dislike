<?php
/**
 * Uninstall Ultimate Member - Dislike
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'um_dislike_path' ) ) {
	define( 'um_dislike_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_dislike_url' ) ) {
	define( 'um_dislike_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_dislike_plugin' ) ) {
	define( 'um_dislike_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );
if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_dislike\core\Dislike_Setup' ) ) {
		require_once um_dislike_path . 'includes/core/class-dislike-setup.php';
	}

	$dislike_setup = new um_ext\um_dislike\core\Dislike_Setup();

	//remove settings
	foreach ( $dislike_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	update_option( 'um_options', $options );

	delete_option( 'um_dislike_last_version_upgrade' );
	delete_option( 'um_dislike_version' );
	delete_option( 'um_dislike_users_last_updated' );
	delete_option( 'widget_um_dislike_users' );
	delete_option( 'um_dislike_users' );
}