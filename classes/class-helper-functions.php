<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helpers Functions Class.
 */
class WPF_Feedbacks_Helpers {

	/**
	 * class Single instance.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * wpdb Object.
	 *
	 * @var object
	 */
	private static $wpdb;

	/**
	 * Feedbacks Table name.
	 *
	 * @var string
	 */
	private static $table_name;

	/**
	 * initialize single instance.
	 *
	 * @return object
	 */
	public static function init() {

		if ( is_null( self::$instance ) ) {
			global $wpdb;

			self::$wpdb       = $wpdb;
			self::$table_name = WPF_TABLE_NAME;

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * return all feedbacks
	 *
	 * @param string $order_by order feedbacks by column.
	 * @param string $order order ASC / DESC.
	 * @return void
	 */
	public static function get_all_feedbacks( $order_by = 'submitted_at', $order = 'DESC' ) {
		$table_name = self::$table_name;
		$feedbacks  = self::$wpdb->get_results( "SELECT * FROM $table_name ORDER BY $order_by $order" );
		return $feedbacks;
	}

	/**
	 * Count feedbacks.
	 *
	 * @return int
	 */
	public static function count_feedbacks() {
		$table_name = self::$table_name;
		$count      = self::$wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
		return $count;
	}

	/**
	 * Get feedback by ID.
	 *
	 * @param int $feedback_id Feedback ID.
	 * @return object
	 */
	public static function get_feedback( $feedback_id ) {
		$table_name = self::$table_name;
		$feedback   = self::$wpdb->get_row( self::$wpdb->prepare( "SELECT * FROM $table_name WHERE ID = %d", $feedback_id ) );
		return $feedback;
	}

	public static function get_feedback_by_post_id( $post_id ) {

	}

	/**
	 * Delete feedback by ID.
	 *
	 * @param int $feedback_id Feedback ID.
	 * @return int
	 */
	public static function delete_feedback( $feedback_id ) {
		$table_name = self::$table_name;
		$result     = self::$wpdb->delete( $table_name, array( 'ID' => $feedback_id ) );
		return $result;
	}

	/**
	 * Delete Feedbacks by IDs.
	 *
	 * @param array $ids Feedbacks IDs.
	 * @return void
	 */
	public static function bulk_delete_feedbacks( $ids ) {
		$table_name = self::$table_name;
		$ids = implode( "','", $ids );
		self::$wpdb->query( "DELETE FROM $table_name where id IN ('" . $ids . "' )" );
	}

	public static function sort_feedbacks_by_thumbs( $thumbs = 'up', $post_type = 'post' ) {
		global $wpdb;
		$table_name = self::$table_name;
		$result     = $wpdb->get_results( "SELECT p.id FROM {$wpdb->prefix}posts p LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id WHERE p.post_type = '{$post_type}' AND pm.meta_key = 'WPF_thumbs_{$thumbs}' ORDER BY pm.meta_value DESC" );
		return $result;
	}
}


WPF_Feedbacks_Helpers::init();
