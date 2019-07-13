<?php

// Notices
if ( isset( $_GET['delete'] ) && 'success' === $_GET['delete'] ) :
	unset( $_GET['delete'] );
	?>
	<div class="notice notice-success">
		<h4>Feedback is Deleted Successfully</h4>
	</div>
	<script type="text/javascript">
		window.history.pushState('object', document.title, location.href.split("&")[0]);
	</script>
	<?php
endif;

$feedbacks_listing = new WPF_Feedbacks_Listing();
?>

<form id="feedbacks-filter" method="post">
    <input type="hidden" name="_wpf_bulk_nonce" value="<?php echo wp_create_nonce( 'wpf_bulk_actions_nonce' ); ?>" />
	<?php $feedbacks_listing->display_feedbacks_table(); ?>
</form>

<script type="text/javascript">
	jQuery('.trash').on('click', function() {
		confirm('Are You sure You want to delete this feedback');
	});
</script>
