<?php
/*
Plugin Name: Ultimate Member - Dislike
Plugin URI: 
Description: Display dislike users and show the user dislike status on your site.
Version: 1.0.0
Author: 
Author URI: 
Text Domain: um-dislike
Domain Path: /languages
UM version: 2.4.1
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_dislike_url', plugin_dir_url( __FILE__  ) );
define( 'um_dislike_path', plugin_dir_path( __FILE__ ) );
define( 'um_dislike_plugin', plugin_basename( __FILE__ ) );
define( 'um_dislike_extension', $plugin_data['Name'] );
define( 'um_dislike_version', $plugin_data['Version'] );
define( 'um_dislike_textdomain', 'um-dislike' );
define( 'um_dislike_requires', '1.0.0' );


if ( ! function_exists( 'um_dislike_plugins_loaded' ) ) {
	/**
	 * Text-domain loading
	 */
	function um_dislike_plugins_loaded() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_dislike_textdomain, WP_LANG_DIR . '/plugins/' . um_dislike_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_dislike_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'um_dislike_plugins_loaded', 0 );
}


if ( ! function_exists( 'um_dislike_check_dependencies' ) ) {
	/**
	 * Check dependencies in core
	 */
	function um_dislike_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_dislike_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-dislike' ), um_dislike_extension ) . '</p></div>';
			}
			exit;
			add_action( 'admin_notices', 'um_dislike_dependencies' );
		} else {
			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			
			if ( ! $is_um_active ) {
				//UM is not active
				function um_dislike_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-dislike' ), um_dislike_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_dislike_dependencies' );

			// } elseif ( true !== UM()->dependencies()->compare_versions( um_dislike_requires, um_dislike_version, 'dislike', um_dislike_extension ) ) {
			// 	//UM old version is active
			// 	function um_dislike_dependencies() {
			// 		echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_dislike_requires, um_dislike_version, 'dislike', um_dislike_extension ) . '</p></div>';
			// 	}

			// 	add_action( 'admin_notices', 'um_dislike_dependencies' );

			} else {
				require_once um_dislike_path . 'includes/core/um-dislike-init.php';
			}
		}
	}
	add_action( 'plugins_loaded', 'um_dislike_check_dependencies', -20 );
}


if ( ! function_exists( 'um_dislike_activation_hook' ) ) {
	/**
	 * Plugin Activation
	 */
	function um_dislike_activation_hook() {
		//first install
		$version = get_option( 'um_dislike_version' );
		if ( ! $version ) {
			update_option( 'um_dislike_last_version_upgrade', um_dislike_version );
		}

		if ( $version != um_dislike_version ) {
			update_option( 'um_dislike_version', um_dislike_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_dislike\core\Dislike_Setup' ) ) {
			require_once um_dislike_path . 'includes/core/class-dislike-setup.php';
		}

		$dislike_setup = new um_ext\um_dislike\core\Dislike_Setup();
		$dislike_setup->run_setup();
	}
	register_activation_hook( um_dislike_plugin, 'um_dislike_activation_hook' );
}