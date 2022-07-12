<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class UM_Dislike
 */
class UM_Dislike {


	/**
	 * @var array
	 */
	var $dislike_users;

	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Dislike
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Dislike constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		add_filter( 'um_call_object_Dislike', array( &$this, 'get_this' ) );

		$this->init();

		$this->common();
		if ( UM()->is_request( 'frontend' ) ) {
			$this->shortcode();
		}

		$this->member_directory();

		require_once um_dislike_path . 'includes/core/um-dislike-widget.php';
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );
	}


	/**
	 * For using UM()->Dislike() function in plugin
	 *
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * Init variables
	 */
	function init() {
		//$this->users = get_option( 'um_dislike_users' );
		$this->dislike_users = get_option( 'um_dislike_users' );
		//$this->schedule_update();
	}


	/**
	 * @return um_ext\um_dislike\core\DisLike_Common()
	 */
	function common() {
		if ( empty( UM()->classes['um_dislike_common'] ) ) {
			UM()->classes['um_dislike_common'] = new um_ext\um_dislike\core\Dislike_Common();
		}
		return UM()->classes['um_dislike_common'];
	}

	/**
	 * @return um_ext\um_online\core\Online_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_dislike_shortcode'] ) ) {
			UM()->classes['um_dislike_shortcode'] = new um_ext\um_dislike\core\Dislike_Shortcode();
		}
		return UM()->classes['um_dislike_shortcode'];
	}

	


	/**
	 * @return um_ext\um_dislike\core\Dislike_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_dislike_member_directory'] ) ) {
			UM()->classes['um_dislike_member_directory'] = new um_ext\um_dislike\core\Dislike_Member_Directory();
		}
		return UM()->classes['um_dislike_member_directory'];
	}


	/**
	 * Init Dislike users widget
	 */
	function widgets_init() {
		register_widget( 'um_dislike_users' );
	}


	/**
	 * Gets users dislike
	 *
	 * @return bool|array
	 */
	function get_dislike_users() {
		if ( ! empty( $this->dislike_users ) && is_array( $this->dislike_users ) ) {
			arsort( $this->dislike_users ); // this will get us the last active user first
			return $this->dislike_users;
		}
		return false;
	}


	/**
	 * Checks if user is dislike
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	
	function is_dislike( $user_id ) {
		$dislikeUsers = $this->dislike_users;
		if(!isset($dislikeUsers[get_current_user_id()]))
			return false;
		if(in_array($user_id, $dislikeUsers[get_current_user_id()]))
	  	{
	  		return true;	
	  	}else{
	  		return false;
	  	}
	}
	


	/**
	 * Enqueue necessary scripts
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'um-dislike' );
		wp_enqueue_style( 'um-dislike' );
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_dislike', -10, 1 );
function um_init_dislike() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Dislike', true );
	}
}
//Ajax Dislike
function um_dislike() {
  // Make sure we have got the data we are expecting.
  $nonce = isset( $_POST["nonce"] ) ? $_POST["nonce"] : "";
  $user_id = isset( $_POST["user_id"] ) ? $_POST["user_id"] : null;
  $dislike="dislike";
  $dislikeUsers = UM()->Dislike()->get_dislike_users();
  if(!$dislikeUsers) $dislikeUsers=array();

  if(isset($dislikeUsers[get_current_user_id()])){
  	
  	if(in_array($user_id, $dislikeUsers[get_current_user_id()]))
  	{
  		$dislike="";
  		for($i=0;$i<count($dislikeUsers[get_current_user_id()]);$i++){
	  		if($dislikeUsers[get_current_user_id()][$i]==$user_id)	
		  			unset($dislikeUsers[get_current_user_id()][$i]);
	  	}	
  	}else{
  		$dislikeUsers[get_current_user_id()][]=$user_id;
  	}
  }else{
  	$dislikeUsers[get_current_user_id()]=array();
  	$dislikeUsers[get_current_user_id()][]=$user_id;
  }
  UM()->Dislike()->dislike_users=$dislikeUsers;
  update_option('um_dislike_users',UM()->Dislike()->dislike_users);
  
  if($dislike=="dislike")
  	$button = '<a href="javascript:void(0);" class="um-dislike-btn '.$dislike.'" data-user-id="'.$user_id.'"><i class="um-faicon-play-circle-o"></i></a>';
  else
  	$button = '<a href="javascript:void(0);" class="um-dislike-btn" data-user-id="'.$user_id.'"><i class="um-faicon-ban"></i></a>';



  wp_send_json_success( ['code'=>1,'btn'=>$button] );
}
// add our callback to both ajax actions.
add_action( "wp_ajax_um_dislike", "um_dislike" );
add_action( "wp_ajax_nopriv_um_dislike", "um_dislike" );
