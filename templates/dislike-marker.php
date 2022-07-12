<?php if ( ! defined( 'ABSPATH' ) ) exit;

$class = $is_dislike ? 'dislike' : 'like';
// $title = $is_dislike ? __( 'dislike', 'um-dislike' ) : __( 'like', 'um-dislike' ); ?>


	<a href="javascript:void(0);" class="um-dislike-btn <?php echo $class;?>" data-user-id="<?php echo $um_profile_id;?>">
		<?php 
			if($class=='dislike')
				echo '<i class="um-faicon-play-circle-o"></i>';
			else
				echo '<i class="um-faicon-ban"></i>';
		?>
		
	
	</a>

