<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Feedback Table Listing Class.
 *
 */
class  WPF_Feedbacks_Listing {

	/**
	 * Helpres Object.
	 *
	 * @var object
	 */
	private $helpers;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->helpers = WPF_Feedbacks_Helpers::init();
	}

	/**
	 * orderby Columns.
	 *
	 * @var array
	 */
	private $orderby_columns = array(
		'submitted_at',
	);

	/**
	 * Handle Feedbacks Listing Display.
	 *
	 * @return void
	 */
	public function display_feedbacks_table() {
		$feedback_table = new WPF_Feedback_List_Table();
		$order_by       = 'submitted_at';
		$order          = 'DESC';
		if ( ! empty( $_GET['orderby'] ) ) {
			$order_by_parameter = sanitize_text_field( $_GET['orderby'] );
			if ( in_array( $order_by_parameter, $this->orderby_columns, true ) ) {
				$order_by = $order_by_parameter;
			}
		}
		if ( ! empty( $_GET['order'] ) && ( 'asc' === $_GET['order'] ) ) {
			$order = 'ASC';
		}
		$feedbacks           = $this->helpers::get_all_feedbacks( $order_by, $order );
		$rows                = [];
		$feedback_view_nonce = wp_create_nonce( 'wpf_feedback_view_nonce' );
		foreach ( $feedbacks as $feedback ) {
			$content = ( strlen( $feedback->content ) < 50 ) ? $feedback->content : substr( $feedback->content, 0, 100 ) . '...';
			$rows[]  = array(
				'id'           => $feedback->id,
				'feedback'     => '<a href="'. admin_url( 'admin.php?page=wpf_feedbacks&action=view&feedback_id=' . $feedback->id . '&_wpnonce=' . $feedback_view_nonce ) . '" > ' . $content . '</a>',
				'post'         => '<a href="' . get_edit_post_link( $feedback->post_id ) . '" >' . get_the_title( $feedback->post_id ) . '</a>',
				'submitted_at' => $feedback->submitted_at,
				'username'     => $feedback->username,
				'email'        => $feedback->email,
			);
		}

		$feedback_table->table_data = $rows;
		$feedback_table->prepare_items();
		$feedback_table->display();
	}
}
