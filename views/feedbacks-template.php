<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( isset( $_GET['page'] ) && 'wpf_feedbacks' === $_GET['page'] ) {

	if ( ! isset( $_GET['action'] ) ) {

		include_once WPF_PATH . '/views/feedbacks-listing.php';

	} elseif ( isset( $_GET['feedback_id'] ) && intval( $_GET['feedback_id'] ) && isset( $_GET['action'] ) && 'view' === $_GET['action'] ) {

		set_query_var( 'wpf_feedback_id', (int) $_GET['feedback_id'] );
		load_template( WPF_PATH . '/views/feedbacks-view.php', true );

	} elseif ( isset( $_GET['feedback_id'] ) && intval( $_GET['feedback_id'] ) && isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) {

		// delete the feedback
		$feedback_id = (int) wp_unslash( $_GET['feedback_id'] );

		if ( ! empty( $feedback_id) ) {

			WPF_Feedbacks_Helpers::delete_feedback( $feedback_id );
			wp_redirect( admin_url( 'admin.php?page=wpf_feedbacks&delete=success' ) );
			die();

		}
	}
}
