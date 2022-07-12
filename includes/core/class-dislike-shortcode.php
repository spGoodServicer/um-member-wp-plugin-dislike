<?php
namespace um_ext\um_dislike\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Dislike_Shortcode
 * @package um_ext\um_dislike\core
 */
class Dislike_Shortcode {


	/**
	 * Dislike_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_dislike', array( &$this, 'ultimatemember_dislike' ) );
	}


	/**
	 * Dislike users list shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_dislike( $args = array() ) {
		UM()->Dislike()->enqueue_scripts();

		$defaults = array(
			'max'   => 11,
			'roles' => 'all'
		);
		$args = wp_parse_args( $args, $defaults );

		$args['dislike'] = UM()->Dislike()->get_dislike_users();
		$template = ( $args['dislike'] && count( $args['dislike'] ) > 0 ) ? 'dislike' : 'nobody';

		ob_start();

		UM()->get_template( "{$template}.php", um_dislike_plugin, $args, true );

		$output = ob_get_clean();
		return $output;
	}
}