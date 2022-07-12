<?php
namespace um_ext\um_dislike\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Dislike_Setup
 * @package um_ext\um_dislike\core
 */
class Dislike_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Friends_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'dislike_show_stats' => 0,
		);
	}


	/**
	 *
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}

		}

		update_option( 'um_options', $options );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}