<?php
namespace um_ext\um_dislike\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Dislike_Common
 * @package um_ext\um_dislike\core
 */
class Dislike_Common {

	/**
	 * Dislike_Frontend constructor.
	 */
	function __construct() {
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 9999 );

		add_action( 'um_after_profile_name_inline', array( &$this, 'um_dislike_show_user_status' ) );

		add_filter( 'um_predefined_fields_hook', array( &$this, 'um_dislike_add_fields' ), 100, 1 );
		add_filter( 'um_account_tab_privacy_fields', array( &$this, 'um_activity_account_dislike_fields' ), 10, 2 );
		add_filter( 'um_profile_field_filter_hook__dislike_status', array( &$this, 'um_dislike_show_status' ), 99, 2 );

		add_action( 'um_messaging_conversation_list_name', array( &$this, 'messaging_show_dislike_dot' ) );
		add_action( 'um_messaging_conversation_list_name_js', array( &$this, 'messaging_show_dislike_dot_js' ) );
		add_filter( 'um_messaging_conversation_json_data', array( &$this, 'messaging_dislike_status' ), 10, 1 );

		add_action( 'um_delete_user',  array( $this, 'clear_dislike_user' ), 10, 1 );

		add_action( 'clear_auth_cookie', array( $this, 'clear_auth_cookie_clear_dislike_user' ), 10 );

		add_filter( 'um_rest_api_get_stats', array( &$this, 'rest_api_get_stats' ), 10, 1 );

		// Friends
		add_filter( 'um_friends_dislike_users', array( $this, 'get_dislike_users' ) );

		add_filter( 'um_settings_structure', array( $this, 'admin_settings' ), 10, 1 );
	}


	/**
	 * @param $settings
	 *
	 * @return mixed
	 */
	function admin_settings( $settings ) {
		$settings['extensions']['sections']['dislike'] = array(
			'title'     => __( 'Dislike', 'um-dislike' ),
			'fields'    => array(
				array(
					'id'    => 'dislike_show_stats',
					'type'  => 'checkbox',
					'label' => __( 'Show dislike stats in member directory', 'um-dislike' ),
				),
			),
		);

		return $settings;
	}


	

	/**
	 * Register custom scripts
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um-dislike', um_dislike_url . 'assets/js/um-dislike' . $suffix . '.js', array( 'jquery' ), um_dislike_version, true );
		wp_register_style( 'um-dislike', um_dislike_url . 'assets/css/um-dislike' . $suffix . '.css', array( 'um_styles' ), um_dislike_version );
	}


	/**
	 * Show user dislike status beside name
	 *
	 * @param $args
	 */
	function um_dislike_show_user_status( $args ) {
		if ( $this->is_hidden_status( um_profile_id() ) ) {
			return;
		}

		UM()->Dislike()->enqueue_scripts();
		$args['um_profile_id'] = um_profile_id();
		$args['is_dislike'] = UM()->Dislike()->is_dislike( um_profile_id() );

		ob_start();
			
		UM()->get_template( 'dislike-marker.php', um_dislike_plugin, $args, true );

		ob_end_flush();
	}


	/**
	 * Extends core fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	function um_dislike_add_fields( $fields ) {

		$fields['_hide_dislike_status'] = array(
			'title'         => __( 'Show my dislike status?', 'um-dislike' ),
			'metakey'       => '_hide_dislike_status',
			'type'          => 'radio',
			'label'         => __( 'Show my dislike status?', 'um-dislike' ),
			'help'          => __( 'Do you want other people to see that you are dislike?', 'um-dislike' ),
			'required'      => 0,
			'public'        => 1,
			'editable'      => 1,
			'default'       => 'yes',
			'options'       => array( 'yes' => __( 'Yes', 'um-dislike' ), 'no' => __( 'No', 'um-dislike' ) ),
			'account_only'  => true,
		);

		UM()->account()->add_displayed_field( '_hide_dislike_status', 'privacy' );

		$fields['dislike_status'] = array(
			'title'             => __( 'Dislike Status', 'um-dislike' ),
			'metakey'           => 'dislike_status',
			'type'              => 'text',
			'label'             => __( 'Dislike Status', 'um-dislike' ),
			'edit_forbidden'    => 1,
			'show_anyway'       => true,
			'custom'            => true,
		);

		return $fields;
	}


	/**
	 * Shows the dislike field in account page
	 *
	 * @param string $args
	 * @param array $shortcode_args
	 *
	 * @return string
	 */
	function um_activity_account_dislike_fields( $args, $shortcode_args ) {
		return $args . ',_hide_dislike_status';
	}


	/**
	 * Shows the dislike status
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return string
	 */
	function um_dislike_show_status( $value, $data ) {
		if ( $this->is_hidden_status( um_user('ID') ) ) {
			return $value;
		}

		UM()->Dislike()->enqueue_scripts();

		$args['is_dislike'] = UM()->Dislike()->is_dislike( um_user('ID') );

		ob_start();

		UM()->get_template( 'dislike-text.php', um_dislike_plugin, $args, true );

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Show dislike dot in messaging extension
	 */
	function messaging_show_dislike_dot() {
		if ( $this->is_hidden_status( um_user('ID') ) ) {
			return;
		}

		UM()->Dislike()->enqueue_scripts();

		$args['is_dislike'] = UM()->Dislike()->is_dislike( um_user('ID') );

		ob_start();

		UM()->get_template( 'dislike-marker.php', um_dislike_plugin, $args, true );

		ob_end_flush();
	}


	/**
	 * Private Messages dislike status integration
	 * JS template for conversations list
	 *
	 */
	function messaging_show_dislike_dot_js() {
		ob_start(); ?>

		<span class="um-dislike-status <# if ( conversation.dislike ) { #>dislike<# } else { #>offline<# } #>"><i class="um-faicon-circle"></i></span>

		<?php ob_end_flush();
	}


	/**
	 * Private Messages dislike status integration
	 *
	 * @param array $conversation
	 *
	 * @return array $conversation
	 */
	function messaging_dislike_status( $conversation ) {
		$conversation['dislike'] = UM()->Dislike()->is_dislike( um_user('ID') );
		return $conversation;
	}


	/**
	 * Make the user offline
	 *
	 * @param $user_id
	 */
	function clear_dislike_user( $user_id ) {
		$dislike_users = UM()->Dislike()->get_dislike_users();
		if ( ! empty( $dislike_users[ $user_id ] ) ) {
			unset( $dislike_users[ $user_id ] );
			update_option( 'um_dislike_users', $dislike_users );

			update_option( 'um_dislike_users_last_updated', time() );
		}
	}


	/**
	 * Remove dislike user on logout process
	 */
	function clear_auth_cookie_clear_dislike_user() {
		$userinfo = wp_get_current_user();

		if ( ! empty( $userinfo->ID ) ) {
			$this->clear_dislike_user( $userinfo->ID );
		}
	}


	/**
	 * Get dislike users count via REST API
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	function rest_api_get_stats( $response ) {
		$users = UM()->Dislike()->get_dislike_users();
		$response['stats']['total_dislike'] = $users ? count( $users ) : 0;
		return $response;
	}


	/**
	 * If user set hidden dislike status
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	function is_hidden_status( $user_id ) {
		$_hide_dislike_status = get_user_meta( $user_id, '_hide_dislike_status', true );
		if ( $_hide_dislike_status == 1 || ( isset( $_hide_dislike_status[0] ) && $_hide_dislike_status[0] == 'no' ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Return an array of dislike users ID
	 *
	 * @param array $dislike_user_ids
	 *
	 * @return array
	 */
	public function get_dislike_users( $dislike_user_ids = array() ) {
		$dislike = UM()->Dislike()->get_dislike_users();
		if ( is_array( $dislike ) ) {
			$dislike_user_ids = array_keys( $dislike );
		}

		return $dislike_user_ids;
	}
	public function get_disliked_users() {
		$dislike = UM()->Dislike()->get_dislike_users();
		if ( is_array( $dislike ) ) {
			$dislike_user_ids = array_keys( $dislike );
		}

		return $dislike_user_ids;
	}
}