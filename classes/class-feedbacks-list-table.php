<?php
defined( 'ABSPATH' ) or die( 'You cannot access this page directly.' );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Feedback Listing Table Class.
 *
 */
class WPF_Feedback_List_Table extends WP_List_Table {

	/**
	 * Table Data.
	 *
	 * @var array
	 */
	public $table_data        = [];

	/**
	 * Posts per page.
	 *
	 * @var int
	 */
	protected $posts_per_page;

	/**
	 * Plugin Text domain.
	 *
	 * @var string
	 */
	private $text_domain;

	/**
	 * Helpers Object.
	 *
	 * @var object
	 */
	private $helpers;

	public function __construct() {
		parent::__construct();
		$this->posts_per_page = 5;
		$this->text_domain    = WPF_DOMAIN;
		$this->helpers        = WPF_Feedbacks_Helpers::init();
	}

	/**
	 * First Column Actions.
	 *
	 * @param Array $item Column Item.
	 * @return String
	 */
	public function column_feedback( $item ) {
		$title                 = '<strong>' . $item['feedback'] . '</strong>';
		$feedback_delete_nonce = wp_create_nonce( 'wpf_feedback_delete_nonce' );
		$actions               = array(
			'delete' => '<a class="trash" href="' . admin_url( 'admin.php?page=wpf_feedbacks&action=delete&feedback_id=' . $item['id'] . '&_wpnonce=' . $feedback_delete_nonce ) . '" >Delete</a>',
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Each Column Checkbox.
	 *
	 * @param Array $item Column Item.
	 * @return String
	 */
	public function column_cb( $item ) {
		$cb = '<input type="checkbox" id="cb-select-' . $item['id'] . '" name="feedbacks[]" value="' . $item['id'] . '" />';
		return $cb;
	}

	/**
	 * Table Columns.
	 *
	 * @param Array $item Column Item.
	 * @param String $column_name Column name.
	 * @return String
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'cb':
			case 'post':
			case 'username':
			case 'submitted_at':
			case 'feedback':
			case 'email':
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Return Page Number.
	 *
	 * @return int
	 */
	function get_pagenum() {
		$page_num = isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1;
		return $page_num;
	}

	/**
	 * Prepare Items to fill the table.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->process_bulk_actions();
		$data         = $this->table_data;
		$per_page     = $this->posts_per_page;
		$current_page = $this->get_pagenum();
		$total_items  = $this->feedbacks_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Count Feedbacks.
	 *
	 * @return int
	 */
	private function feedbacks_count() {
		return $this->helpers::count_feedbacks();
	}

	/**
	 * Hidden Columns
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array(
			'id' => __( 'ID', $this->text_domain ),
		);
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array(
			'submitted_at' => array(
				'submitted_at',
				false,
			),
		);
	}

	/**
	 * Bulk Actions.
	 *
	 * @return Array
	 */
	protected function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', $this->text_domain ),
		);
	}

	/**
	 * Return The Table Columns.
	 *
	 * @return Array
	 */
	function get_columns() {
		$columns = array(
			'cb'           => '<input id="cb-select-all-1" type="checkbox" />',
			'feedback'     => __( 'Feedback', $this->text_domain ),
			'post'         => __( 'Post Title', $this->text_domain ),
			'submitted_at' => __( 'Submitted date', $this->text_domain ),
			'username'     => __( 'Username', $this->text_domain ),
			'email'        => __( 'Email', $this->text_domain ),
		);
		return $columns;
	}

	/**
	 * Handle Bulk Actions.
	 *
	 * @return void
	 */
	public function process_bulk_actions() {
		if ( 'delete' === $this->current_action() ) {

			if ( ! empty( $_POST['feedbacks'] ) && is_array( $_POST['feedbacks'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpf_bulk_nonce'] ), 'wpf_bulk_actions_nonce' ) ) {
				$feedbacks_ids = wp_unslash( $_POST['feedbacks'] );
				$feedback_ids  = array_filter(
					$feedbacks_ids,
					function( $id ) {
						return is_numeric( $id );
					}
				);
				if ( ! empty( $feedback_ids ) ) :
					$this->helpers->bulk_delete_feedbacks( $feedback_ids );
					wp_redirect( admin_url( 'admin.php?page=wpf_feedbacks&delete=success' ) );
					die();
				endif;
			}
		}
	}
}
