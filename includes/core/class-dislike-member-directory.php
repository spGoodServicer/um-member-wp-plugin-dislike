<?php
namespace um_ext\um_dislike\core;


use um\core\Member_Directory_Meta;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Dislike_Member_Directory
 *
 * @package um_ext\um_dislike\core
 */
class Dislike_Member_Directory {


	/**
	 * Dislike_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'enqueue_scripts' ), 10, 1 );
		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'member_directory_options_profile' ), 10, 1 );

		add_filter( 'um_members_directory_filter_fields',  array( $this, 'directory_filter_dropdown_options' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types',  array( $this, 'directory_filter_types' ), 10, 1 );
		add_filter( 'um_search_fields',  array( $this, 'dislike_dropdown' ), 10, 1 );
		add_filter( 'um_query_args_dislike_status__filter',  array( $this, 'dislike_status_filter' ), 10, 4 );

		add_filter( 'um_prepare_user_query_args' ,array( $this, 'hide_disliked_member' ), 10, 2 );
		//UM metadata
		add_filter( 'um_query_args_dislike_status__filter_meta',  array( $this, 'dislike_status_filter_meta' ), 10, 6 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'get_members_data' ), 50, 2 );

		add_action( 'um_members_just_after_name', array( &$this, 'extend_js_template' ), 10, 1 );
		add_action( 'um_members_just_after_name', array( &$this, 'extend_js_template' ), 10, 1 );
	}

	


	/**
	 *
	 */
	function enqueue_scripts() {
		UM()->Dislike()->enqueue_scripts();
	}


	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	function member_directory_options_profile( $fields ) {
		$fields = array_merge( array_slice( $fields, 0, 3 ), array(
			array(
				'id'    => '_um_dislike_hide_stats',
				'type'  => 'checkbox',
				'label' => __( 'Hide dislike stats', 'um-dislike' ),
				'value' => UM()->query()->get_meta_value( '_um_dislike_hide_stats', null, 'na' ),
			),
		), array_slice( $fields, 3, count( $fields ) - 1 ) );

		return $fields;
	}


	/**
	 * Add Member Directory filter
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	function directory_filter_dropdown_options( $options ) {
		$options['dislike_status'] = __( 'Dislike Status', 'um-dislike' );
		return $options;
	}


	/**
	 * Set dislike_status filter type
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	function directory_filter_types( $types ) {
		$types['dislike_status'] = 'select';
		return $types;
	}


	/**
	 * Build Select box for Dislike Status filter
	 * @param array $attrs
	 *
	 * @return array
	 */
	function dislike_dropdown( $attrs ) {
		if ( isset( $attrs['metakey'] ) && 'dislike_status' == $attrs['metakey'] ) {
			$attrs['type'] = 'select';

			$attrs['options'] = array(
				0 => __( 'Like', 'um-dislike' ),
				1 => __( 'Dislike', 'um-dislike' ),
			);
		}
		return $attrs;
	}
	/**
	 * Build Select box for Dislike Status filter
	 * @param array $attrs
	 *
	 * @return array
	 */
	function dislike_hide( $attrs ) {
		$attrs['options'] = array(
			0 => __( 'Like', 'um-dislike' ),
			1 => __( 'Dislike', 'um-dislike' ),
		);
		return $attrs;
	}


	/**
	 * Filter users by Dislike status
	 *
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return bool
	 */
	function dislike_status_filter( $query, $field, $value, $filter_type ) {
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		if ( ! ( in_array( 1, $value ) && in_array( 0, $value ) ) ) {
			$dislike_users_array = UM()->Dislike()->common()->get_dislike_users();

			foreach ( $value as $val ) {
				if ( $val == '0' ) {
					if ( ! empty( $dislike_users_array ) ) {
						UM()->member_directory()->query_args['exclude'] = $dislike_users_array;
					}
				} elseif ( $val == '1' ) {
					if ( ! empty( $dislike_users_array ) ) {
						UM()->member_directory()->query_args['include'] = $dislike_users_array;
					}
				}
			}
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return true;
	}


	/**
	 * Filter users by Dislike status
	 *
	 * @param $skip
	 * @param Member_Directory_Meta $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 * @param bool $is_default
	 *
	 * @return bool
	 */
	function dislike_status_filter_meta( $skip, $query, $field, $value, $filter_type, $is_default ) {
		$skip = true;

		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		if ( ! ( in_array( 1, $value ) && in_array( 0, $value ) ) ) {

			$dislike_users_array = UM()->Dislike()->common()->get_dislike_users();

			foreach ( $value as $val ) {
				if ( $val == '0' ) {
					if ( ! empty( $dislike_users_array ) ) {
						$query->where_clauses[] = "u.ID NOT IN ('" . implode( "','", $dislike_users_array ) . "')";
					}
				} elseif ( $val == '1' ) {
					if ( ! empty( $dislike_users_array ) ) {
						$query->where_clauses[] = "u.ID IN ('" . implode( "','", $dislike_users_array ) . "')";
					}
				}
			}
		}

		if ( ! $is_default ) {
			$query->custom_filters_in_query[ $field ] = $value;
		}

		return $skip;
	}



	/**
	 * Expand AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function get_members_data( $data_array, $user_id ) {
		$data_array['is_dislike'] = false;
		if ( ! UM()->Dislike()->common()->is_hidden_status( $user_id ) ) {
			$data_array['is_dislike'] = UM()->Dislike()->is_dislike( $user_id );
		}
		return $data_array;
	}


	/**
	 * @param $args
	 */
	function extend_js_template( $args ) {
		$hide_dislike_show_stats = ! empty( $args['dislike_hide_stats'] ) ? $args['dislike_hide_stats'] : ! UM()->options()->get( 'dislike_show_stats' );
		if ( empty( $hide_dislike_show_stats ) && is_user_logged_in()) { ?>
		<div class="um-members-dislike">
			<a href="javascript:void(0);" class="um-dislike-btn<?php if(UM()->Dislike()->is_dislike($args)) echo " dislike"?>" data-user-id="<?php echo $args;?>">
				<?php 
					if(UM()->Dislike()->is_dislike($args))
						echo '<i class="um-faicon-play-circle-o"></i>';
					else
						echo '<i class="um-faicon-ban"></i>';
				?>
				
			
			</a>
			<!--span class="dislike_text">このユーザーへの表示を再開する。</span-->
			<!--span class="like_text">【このユーザーには表示させないようにする。】</span-->
		</div>
		
	<?php }
	}
	function hide_disliked_member( $query_args, $directory_data ) {
		extract($directory_data);
	    $dislike_users_array = UM()->Dislike()->get_dislike_users();
	    $exclude = array(get_current_user_id());
	    foreach($dislike_users_array as $user_id=>$blockList)
	    {
	    	if(in_array(get_current_user_id(), $blockList))
	    		$exclude[]=$user_id;
	    }
	    $query_args['exclude'] = $exclude;
	    return $query_args;
	}
}