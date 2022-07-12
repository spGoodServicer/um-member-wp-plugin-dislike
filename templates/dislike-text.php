<?php if ( ! defined( 'ABSPATH' ) ) exit;

$class = $is_dislike ? 'dislike' : 'offline';
$title = $is_dislike ? __( 'dislike', 'um-dislike' ) : __( 'offline', 'um-dislike' ); ?>

<span class="um-dislike-status <?php echo esc_attr( $class ) ?>">
	<?php echo $title ?>
</span>