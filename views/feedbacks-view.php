<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$feedback_id = get_query_var( 'wpf_feedback_id' );

if ( empty( $feedback_id ) ) {
	return;
}

$feedback = WPF_Feedbacks_Helpers::get_feedback( $feedback_id );

if ( $feedback ) {

	$feedback_message  = $feedback->content;
	$feedback_username = $feedback->username;
	$feedback_usermail = $feedback->email;
	?>

<div class="wrap wp-clearfix">
	<div id="howto" class="wp-clearfix" >
		<div class="alignright wp-clearfix">
			<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=wpf_feedbacks' ) ); ?>" class="button button-primary"> Back </a>
		</div>
	</div>
	<div class="plugin-information wp-clearfix review">
		<div class="postbox-container alignleft">
			<div class="card">
				<h3 class="title">Feedback Message</h3>
				<p>
					<?php echo esc_textarea( $feedback_message ); ?>
				</p>
			</div>
		</div>
		<?php if ( ! empty( $feedback_username ) && ! empty( $feedback_usermail ) ) : ?>
		<div class="alignright" >
			<div class="card">
				<h3 class="title"> Feedback User Details </h3>
				<ul>
					<li class="feedback-username">
						<span><b>Username:</b> </span>
						<span><?php echo esc_html( $feedback_username ); ?></span>
					</li>
					<li class="feedback-username">
						<span><b>Usermail:</b> </span>
						<span><?php echo esc_html( $feedback_usermail ); ?></span>
					</li>
				</ul>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
	<?php
}

?>
