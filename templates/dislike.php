<?php
/**
 * Template for the UM Dislike Users.
 * Used for "Ultimate Member - Dislike Users" widget.
 *
 * Caller: method Dislike_Shortcode->ultimatemember_dislike()
 * Shortcode: [ultimatemember_dislike]
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-dislike/dislike.php
 */

if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-dislike" data-max="<?php echo $max; ?>">

	<?php $previous_user_id = um_user( 'ID' );
	foreach ( $dislike as $user => $last_seen ) {

		um_fetch_user( $user );

		$user_meta = get_userdata( $user );
		$user_roles = $user_meta->roles;
		if ( $roles != 'all' && count( array_intersect( $user_roles, explode( ',', $roles ) ) ) <= 0 ) {
			continue;
		}

		$name = um_user( 'display_name' );
		if ( empty( $name ) ) {
			continue;
		} ?>

		<div class="um-dislike-user">
			<div class="um-dislike-pic">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-tip-n" title="<?php echo esc_attr( $name ); ?>">
					<?php echo get_avatar( um_user( 'ID' ), 40 ); ?>
				</a>
			</div>
		</div>

	<?php }

	if ( ! $previous_user_id ) {
		um_reset_user();
	} else {
		um_fetch_user( $previous_user_id );
	} ?>

	<div class="um-clear"></div>
</div>