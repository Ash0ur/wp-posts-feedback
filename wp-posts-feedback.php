<?php

/**
 *
 * @package   WP posts Feedback
 * @author    Abdelrahman Ashour < abdelrahman.ashour38@gmail.com >
 * @license   GPL-2.0+
 * @copyright 2018 Ash0ur


 * Plugin Name: WP posts Feedback
 * Description: A plugin that Adds A Feedback option for posts with Thumbs Up / Down with option to add a feedback.
 * Version:      1.0.0
 * Author:       Abdelrahman Ashour
 * Author URI:   https://profiles.wordpress.org/ashour
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  WPF-domain
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

global $wpdb;

define( 'WPF_PREFIX', 'WPF' );
define( 'WPF_TABLE_NAME', $wpdb->prefix . WPF_PREFIX . '_feedbacks' );
define( 'WPF_FOLDER_NAME', 'wp-posts-feedback' );
define( 'WPF_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'WPF_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/dist' );
define( 'WPF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPF_DOMAIN', WPF_PREFIX . '-domain' );
define( 'WPF_VERSION', '1.0.0' );


if ( ! class_exists( 'WP_POSTS_FEEDBACK' ) ) :

	/**
	 * Main Class.
	 */
	class WP_POSTS_FEEDBACK {

		/**
		 * Thumbs Arr.
		 *
		 * @var array
		 */
		private $thumbs = [ 'up', 'down' ];

		/**
		 * Plugin Settings.
		 *
		 * @var array
		 */
		private static $settings;
		/**
		 * Get Class instance.
		 *
		 * @return void
		 */
		public static function init() {
			$theObj = new self();
		}

		/**
		 * Class Constructor.
		 */
		private function __construct() {
			$this->include_files();
			$this->setup_actions();
			self::$settings = self::get_settings();
		}

		/**
		 * Plugin Activated Hook Functions.
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::create_feedback_table();
			self::set_default_values();
		}

		/**
		 * Include files
		 *
		 * @return void
		 */
		public function include_files() {
			require_once WPF_PATH . '/classes/class-helper-functions.php';
			require_once WPF_PATH . '/classes/class-feedbacks-list-table.php';
			require_once WPF_PATH . '/classes/class-feedbacks-listing.php';
		}

		/**
		 * Create  Feedback DB Table.
		 *
		 * @return void
		 */
		public static function create_feedback_table() {
			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();

			$sql = 'CREATE TABLE IF NOT EXISTS ' . WPF_TABLE_NAME . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				`content` TEXT NOT NULL,
				`post_id` BIGINT(20) UNSIGNED NOT NULL,
				`submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
				`username` varchar(60),
				`email` varchar(100),
				PRIMARY KEY  (id),
				FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		/**
		 * Set Default Values for settings.
		 *
		 * @return void
		 */
		public static function set_default_values() {
			$feedback_settings = self::$settings;

			if ( ! empty( $feedback_settings ) ) {
				return;
			}

			$post_types = self::get_all_posts_types();

			$post_types_titles = array();
			foreach ( $post_types as $post_type_name => $post_type ) {
				$post_types_titles[ $post_type_name ] = 'Was this post helpful?';
			}
			$feedback_default_settings = array(
				'feedback_title'              => $post_types_titles,
				'feedback_form_title'         => 'Help us improve it, Give us a feedback',
				'after_feedback_submit_title' => 'Thank you for your feedback!',
				'feedback_form_option'        => 1,
				'feedback_thumb_up_color'     => '#F00',
				'feedback_thumb_down_color'   => '#000',
				'feedback_posts_types'        => $post_types,
				'recaptcha_status'            => 0,
				'recaptcha_key'               => '',
				'recaptcha_secret'            => '',
				'feedback_custom_css'         => '',
			);

			self::update_settings( $feedback_default_settings );
		}

		/**
		 * Enqueue Admin assets.
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			if ( isset( $_GET['page'] ) && ( 'wpf-feedback-settings' === wp_unslash( $_GET['page'] ) ) ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'wp-theme-plugin-editor' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
				wp_enqueue_script( WPF_PREFIX . '_actions', WPF_ASSETS_URL . '/dashboard/js/actions.js', array( 'jquery', 'wp-color-picker' ), WPF_VERSION, true );
			}
		}

		/**
		 * Enqueue Frontend assets.
		 *
		 * @return void
		 */
		public function frontend_enqueue_global() {
			wp_enqueue_style( WPF_PREFIX . '_frontend-styles', WPF_ASSETS_URL . '/frontend/css/frontend-styles.css', array(), WPF_VERSION, false );

			wp_enqueue_script( 'jquery' );

			if ( is_single() ) {
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_script( WPF_PREFIX . '_actions', WPF_ASSETS_URL . '/frontend/js/actions.js', array( 'jquery' ), WPF_VERSION, true );

				$settings     = self::$settings;
				$recaptcha    = $settings['recaptcha_status'];
				$user_details = $settings['feedback_form_option'];
				wp_localize_script(
					WPF_PREFIX . '_actions',
					WPF_PREFIX . '_ajax_data',
					array(
						'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
						'nonce'       => wp_create_nonce( WPF_PREFIX . '_nonce' ),
						'postID'      => get_the_ID(),
						'recaptcha'   => self::is_recaptcha_valid(),
						'userDetails' => ( $user_details ? true : false ),
					)
				);
			}
		}


		/**
		 * Setup Action Hooks.
		 */
		public function setup_actions() {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_global' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_filter( 'the_content', array( $this, 'post_feedback_html' ), PHP_INT_MAX, 1 );
			add_action( 'wp_ajax_nopriv_WPF_save_feedback', array( $this, 'feedback_save' ) );
			add_action( 'wp_ajax_WPF_save_feedback', array( $this, 'feedback_save' ) );
			add_action( 'wp_ajax_noprev_WPF_post_thumb', array( $this, 'add_post_thumb' ) );
			add_action( 'wp_ajax_WPF_post_thumb', array( $this, 'add_post_thumb' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_thumbs_metabox' ) );

			add_action(
				'admin_menu',
				function() {
					add_menu_page( 'Feedbacks_list', 'Feedbacks', 'manage_options', 'wpf_feedbacks', array( $this, 'feedbacks_listing_func' ), 'dashicons-feedback', 30 );
					add_submenu_page( 'wpf_feedbacks', 'Posts Feedback Settings', 'Settings', 'manage_options', 'wpf-feedback-settings', array( $this, 'feedback_settings' ) );
				}
			);

			add_action( 'admin_init', array( $this, 'posts_thumbs_columns' ), 100 );

			add_action( WPF_PREFIX . '_feedback_settings_submit', array( $this, 'handle_feedback_submit' ) );
		}


		/**
		 * Thumbs Post Metabox.
		 *
		 * @return void
		 */
		public function add_thumbs_metabox() {
			$post_types = self::$settings['feedback_posts_types'];
			add_meta_box( 'wpf-post-thumbs-metabox', __( 'Thumbs', WPF_DOMAIN ), array( $this, 'thumbs_metabox_content' ), array_keys( (array) $post_types ), 'side', 'high' );
		}

		/**
		 * Thumb Metabox Content Callback.
		 *
		 * @param Object $post Post Object.
		 * @return void
		 */
		public function thumbs_metabox_content( $post ) {
			$thumbs_up   = get_post_meta( $post->ID, WPF_PREFIX . '_thumbs_up', true );
			$thumbs_down = get_post_meta( $post->ID, WPF_PREFIX . '_thumbs_down', true );
			?>
			<style>
				.wpf-post-thumbs .feedback-thumbs {width: 80px;margin: auto;clear: both;overflow: hidden;}
				.wpf-post-thumbs .feedback-thumbs .thumbs {width: 50%;}
				.wpf-post-thumbs .feedback-thumbs .thumbs-up {float: left;}
				.wpf-post-thumbs .feedback-thumbs .thumbs-down {float: right;text-align: right;}
			</style>
			<div class="wpf-post-thumbs" >
				<div class="feedback-thumbs">
					<div class=" thumbs thumbs-up">
						<ul>
							<li><span class="dashicons wpf-up dashicons-thumbs-up" <?php echo ! empty( self::$settings['feedback_thumb_up_color'] ) ? "style=color:" . esc_attr( self::$settings['feedback_thumb_up_color'] ) : ''; ?>></span></li>
							<li><b><?php echo ! empty( $thumbs_up ) ? (int) esc_html( $thumbs_up ) : 0; ?></b></li>
						</ul>
					</div>
					<div class="thumbs thumbs-down">
						<ul>
							<li><span class="dashicons wpf-down dashicons-thumbs-down" <?php echo ! empty( self::$settings['feedback_thumb_down_color'] ) ? "style=color:" . esc_attr( self::$settings['feedback_thumb_down_color'] ) : ''; ?>></span></li>
							<li><b><?php echo ! empty( $thumbs_down ) ? (int) esc_html( $thumbs_down ) : 0; ?></b></li>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Check if recaptcha is valid by checking key , secret and status.
		 *
		 * @return boolean
		 */
		public function is_recaptcha_valid() {
			$catpcha_status = self::$settings['recaptcha_status'];
			$catpcha_key    = self::$settings['recaptcha_key'];
			$catpcha_secret = self::$settings['recaptcha_secret'];

			if ( $catpcha_status && ! empty( $catpcha_key ) && ! empty( $catpcha_secret ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Feedback Listing Template.
		 *
		 * @return void
		 */
		public function feedbacks_listing_func() {
			include_once WPF_PATH . '/views/feedbacks-template.php';
		}

		/**
		 * Feedback Settings Page.
		 *
		 * @return void
		 */
		public function feedback_settings() {
			include_once WPF_PATH . '/feedbacks-settings.php';
		}

		/**
		 * Posts list Table Thumbs Columns.
		 *
		 * @return void
		 */
		public function posts_thumbs_columns() {
			$post_types = self::get_all_posts_types();

			foreach ( $post_types as $post_type ) {

				add_action(
					'manage_' . $post_type . '_posts_custom_column',
					function( $column, $post_id ) {
						if ( WPF_PREFIX . '_thumbs_up' === $column ) {

							$thumbs = get_post_meta( $post_id, WPF_PREFIX . '_thumbs_up', true );
							echo '<h4>' . ( ! empty( $thumbs ) ? $thumbs : '0' ) . '</h4>';

						} elseif ( WPF_PREFIX . '_thumbs_down' === $column ) {

							$thumbs = get_post_meta( $post_id, WPF_PREFIX . '_thumbs_down', true );
							echo '<h4>' . ( ! empty( $thumbs ) ? $thumbs : '0' ) . '</h4>';

						}
					},
					10,
					2
				);

				add_action(
					'manage_' . $post_type . '_posts_columns',
					function( $columns ) {
						return array_merge(
							$columns,
							array( WPF_PREFIX . '_thumbs_up' => __( '<h4 class="dashicons-before dashicons-thumbs-up" ' . ( ! empty( self::$settings['feedback_thumb_up_color'] ) ? 'style="color:' . esc_attr( self::$settings['feedback_thumb_up_color'] ) . '"' : '' ) . ' ></h4>' ) ),
							array( WPF_PREFIX . '_thumbs_down' => __( '<h4 class="dashicons-before dashicons-thumbs-down" ' . ( ! empty( self::$settings['feedback_thumb_down_color'] ) ? 'style="color:' . esc_attr( self::$settings['feedback_thumb_down_color'] ) . '"' : '' ) . ' ></h4>' ) )
						);
					}
				);

				add_filter(
					'manage_edit-' . $post_type . '_sortable_columns',
					function( $sortable_columns ) {
						$sortable_columns[ WPF_PREFIX . '_thumbs_up' ]   = array( 'thumbs_up', 1 );
						$sortable_columns[ WPF_PREFIX . '_thumbs_down' ] = array( 'thumbs_down', 1 );
						return $sortable_columns;
					},
					1000,
					1
				);
			}

			add_action(
				'pre_get_posts',
				function( $query ) {
					$screen = get_current_screen();
					if ( is_admin() && $query->is_main_query() && ( ! empty( $screen ) ) && ( 'edit' === $screen->base ) ) {

						if ( isset( $_GET['orderby'] ) && ( ( 'thumbs_up' === wp_unslash( $_GET['orderby'] ) ) || ( 'thumbs_down' === wp_unslash( $_GET['orderby'] ) ) ) ) {

							$query->set( 'meta_key', WPF_PREFIX . '_' . wp_unslash( $_GET['orderby'] ) );
							$query->set( 'orderby', 'meta_value_num' );

							if ( isset( $_GET['order'] ) && ( ( 'desc' === wp_unslash( $_GET['order'] ) ) || ( 'asc' === wp_unslash( $_GET['order'] ) ) ) ) {
								$query->set( 'order', wp_unslash( $_GET['order'] ) );
							}

							add_filter( 'get_meta_sql', array( $this, 'filter_sql_meta_for_thumbs_sort' ), 1000, 1 );


						}
					}
				},
				100,
				1
			);
		}

		/**
		 * Filter the SQL meta when sorting by thumbs to inlcude all results.
		 *
		 * @param array $clauses The Query sql clause.
		 * @return array
		 */
		public function filter_sql_meta_for_thumbs_sort( $clauses ) {
			remove_filter( 'get_meta_sql', 'filter_sql_meta_for_thumbs_sort' );
			$clauses['join']  = str_replace( 'INNER JOIN', 'LEFT JOIN', $clauses['join'] ) . $clauses['where'];
			$clauses['where'] = '';
			return $clauses;
		}

		/**
		 * Get all Posts Types.
		 *
		 * @return array
		 */
		private static function get_all_posts_types() {
			$args = [
				'public'   => true,
				'_builtin' => false,
			];

			$output   = 'names';
			$operator = 'and';

			$post_types = get_post_types( $args, $output, $operator );
			$post_types = array_reverse( array_merge( $post_types, array( 'post' => 'post' ) ) );
			return $post_types;
		}

		/**
		 * Feedback HTML.
		 *
		 * @param String $content
		 * @return string
		 */
		public function post_feedback_html( $content ) {
			global $post;

			$settings           = self::$settings;
			$feedback_titles    = $settings['feedback_title'];
			$result             = '';
			$is_captcha         = ( 1 === $settings['recaptcha_status'] && ! empty( $settings['recaptcha_key'] ) && ! empty( $settings['recaptcha_secret'] ) ) ? true : false;
			$after_submit_title = $settings['after_feedback_submit_title'];

			if ( is_single() && in_array( get_post_type( $post ), array_keys( (array) $settings['feedback_posts_types'] ) ) ) {

				ob_start();

				if ( ! empty( $settings['feedback_custom_css'] ) ) {
					echo '<style type="text/css" >';
							$custom_css = wp_kses( json_decode( $settings['feedback_custom_css'] ), array( '\'', '\"' ) );
							$custom_css = str_replace( '&gt;', '>', $custom_css );
							echo $custom_css;
					echo '</style>';
				}

				if ( $is_captcha ) {
					echo '<script src="https://www.google.com/recaptcha/api.js" async defer ></script>';
				}
				?>
			<div class="feedback-container">
				<div class="feedback-choice-wrapper">
					<h3 class="feedback-title"><?php esc_html_e( $feedback_titles[ get_post_type( get_the_ID() ) ], WPF_DOMAIN ); ?></h3>
					<div class="feedback-choice">
						<span class="dashicons wpf-up dashicons-thumbs-up" style="color:<?php echo esc_attr( $settings['feedback_thumb_up_color'] ); ?>"></span>
						<span class="dashicons wpf-down dashicons-thumbs-down" style="color:<?php echo esc_attr( $settings['feedback_thumb_down_color'] ); ?>"></span>
					</div>
				</div>

				<div class="feedback-form-container">
					<form method="post" class="feedback-form" >

						<div class="feedback-content">
							<label for="wpf-feedback-content"><?php esc_html_e( $settings['feedback_form_title'] ); ?></label>
							<textarea class="feedback-input" name="wpf-feedback-content" id="wpf-feedback-content" cols="30" rows="10" required ></textarea>
						</div>

						<?php
						if ( $settings['feedback_form_option'] ) :
							?>
						<div class="feedback-user-details">
							<div class="feedback-name">
								<label for="wpf-feedback-name">
									<span>Your Name</span>
									<input class="feedback-input" type="text" id="wpf-feedback-name" name="wpf-feedback-name"  required />
								</label>
							</div>

							<div class="feedback-mail">
								<label for="wpf-feedback-email" >
									<span>Your Email</span>
									<input class="feedback-input" type="email" id="wpf-feedback-email" name="wpf-feedback-email" required />
								</label>
							</div>
						</div>

							<?php
						endif;
						if ( $is_captcha ) {
							echo '<div class="g-recaptcha" data-callback="wpfRecaptchaCallback" data-sitekey="' . esc_html( $settings['recaptcha_key'] ) . '" ' . ( ( $is_captcha && $settings['feedback_form_option'] ) ? 'style="text-align: center;"' : '' ) . ' ></div>';
						}
						?>
						<div class="submit">
							<button type="submit" class="btn btn-primary feedback-submit " <?php echo ( $is_captcha && $settings['feedback_form_option'] ) ? 'style="display: block;"' : ''; ?> >Submit</button>
						</div>
					</form>
				</div>


				<?php
				if ( ! empty( $after_submit_title ) ) :
					?>
				<div class="feedback-after-submit">
					<h4><?php echo esc_html( $after_submit_title ); ?></h4>
				</div>
					<?php
				endif;
				?>
			</div>

				<?php
				$result = ob_get_contents();
				ob_get_clean();
				$content .= $result;
			}

			return $content;
		}

		/**
		 * Feedback Submit.
		 *
		 * @return void
		 */
		public function handle_feedback_submit() {

			if ( ! empty( $_POST['submit-type'] ) && ( 'feedback-settings' === $_POST['submit-type'] ) && current_user_can( 'manage_options' ) && check_ajax_referer( WPF_PREFIX . '-feedback-settings-nonce', '_wpnonce' ) ) {
				$settings = self::$settings;
				if ( ! empty( $_POST['feedback-title'] ) && is_array( $_POST['feedback-title'] ) ) {
					$feedback_titles            = $_POST['feedback-title'];
					$settings['feedback_title'] = ( $settings['feedback_title'] );
					foreach ( $feedback_titles as $post_type_name => $title ) {
						$settings['feedback_title'][ sanitize_text_field( $post_type_name ) ] = sanitize_text_field( $title );
					}

					$settings['feedback_title'] = $settings['feedback_title'];
				}

				if ( ! empty( $_POST['feedback-form-title'] ) ) {
					$settings['feedback_form_title'] = sanitize_text_field( $_POST['feedback-form-title'] );
				}

				if ( ! empty( $_POST['after-feedback-submit-title'] ) ) {
					$settings['after_feedback_submit_title'] = sanitize_text_field( $_POST['after-feedback-submit-title'] );
				}

				if ( ! empty( $_POST['recaptcha-key'] ) ) {
					$settings['recaptcha_key'] = sanitize_text_field( $_POST['recaptcha-key'] );
				}

				if ( ! empty( $_POST['recaptcha-secret'] ) ) {
					$settings['recaptcha_secret'] = sanitize_text_field( $_POST['recaptcha-secret'] );
				}

				if ( ! empty( $_POST['feedback-thumb-up-color'] ) ) {
					$settings['feedback_thumb_up_color']  = sanitize_text_field( $_POST['feedback-thumb-up-color'] );
				}

				if ( ! empty( $_POST['feedback-thumb-down-color'] ) ) {
					$settings['feedback_thumb_down_color']  = sanitize_text_field( $_POST['feedback-thumb-down-color'] );
				}

				if ( empty( $_POST['feedback-post-type'] ) ) {
					$settings['feedback_posts_types'] = [];

				} elseif ( ! empty( $_POST['feedback-post-type'] ) && is_array( $_POST['feedback-post-type'] ) ) {
					$types      = $_POST['feedback-post-type'];
					$post_types = self::get_all_posts_types();

					$settings['feedback_posts_types'] = [];
					foreach ( $types as $type ) {
						if ( in_array( sanitize_text_field( $type ), array_keys( $post_types ), true ) ) {
							$settings['feedback_posts_types'][ $type ] = $type;
						}
					}
				}

				if ( ! empty( $_POST['feedback-custom-css'] ) ) {
					$custom_css                       = wp_kses( sanitize_textarea_field( $_POST['feedback-custom-css'] ), array( '\'', '\"' ) );
					$custom_css                       = str_replace( '&gt;', '>', $custom_css );
					$settings ['feedback_custom_css'] = json_encode( $custom_css );
				}

				if ( isset( $_POST['feedback-form-option'] ) ) {
					$settings['feedback_form_option'] = 1;
				} else {
					$settings['feedback_form_option'] = 0;
				}
				if ( isset( $_POST['recaptcha-status'] ) ) {
					$settings['recaptcha_status'] = 1;
				} else {
					$settings['recaptcha_status'] = 0;
				}

				self::update_settings( $settings );

			}
		}

		/**
		 * Update Settings.
		 *
		 * @param Array $settings Feedback Settings.
		 * @return void
		 */
		private static function update_settings( $settings ) {
			update_option( WPF_PREFIX . '_feedback_settings', json_encode( $settings ) );
		}

		/**
		 * Get Settings
		 *
		 * @param String $key
		 * @return Array
		 */
		private static function get_settings( $key = '' ) {
			$settings = (array) json_decode( get_option( WPF_PREFIX . '_feedback_settings' ) );
			$settings['feedback_title'] = ( isset( $settings['feedback_title'] ) ) ? (array) $settings['feedback_title'] : '';

			return ( ( ! empty( $key ) && isset( $settings[ $key ] ) ) ? $settings[ $key ] : $settings );
		}

		/**
		 * Save The Feedback.
		 *
		 * @return AJAX_Response
		 */
		public function feedback_save() {
			global $wpdb;

			if ( check_ajax_referer( WPF_PREFIX . '_nonce', 'security' ) ) {
				$name        = '';
				$email       = '';
				$form_option = self::$settings['feedback_form_option'];

				if ( $form_option ) {
					$name  = isset( $_POST['data']['name'] ) ? sanitize_user( wp_unslash( $_POST['data']['name'] ) ) : '';
					$email = isset( $_POST['data']['mail'] ) ? sanitize_email( wp_unslash( $_POST['data']['mail'] ) ) : '';
				}

				$content = isset( $_POST['data']['content'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['content'] ) ) : '';
				$post_ID = ( isset( $_POST['data']['postID'] ) && intval( $_POST['data']['postID'] ) ) ? (int) ( wp_unslash( $_POST['data']['postID'] ) ) : '';

				if ( self::is_recaptcha_valid() ) {
					if ( empty( $_POST['data']['g-recaptcha-response'] ) ) {
						$this->ajax_response( 'fail' );
					} else {
						$result = wp_remote_post(
							'https://www.google.com/recaptcha/api/siteverify',
							array(
								'body' => array(
									'secret'   => self::$settings['recaptcha_secret'],
									'response' => $_POST['data']['g-recaptcha-response'],
									'remoteip' => $_SERVER['REMOTE_ADDR'],
								),
							)
						);
						if ( ! json_decode( $result['body'] )->success ) {
							$this->ajax_response( 'fail' );
						}
					}
				}

				if ( ! empty( $content ) && ! empty( $post_ID ) ) {
					$result = $wpdb->insert(
						WPF_TABLE_NAME,
						array(
							'post_ID'  => $post_ID,
							'content'  => $content,
							'username' => $name,
							'email'    => $email,
						),
						array(
							'%d',
							'%s',
							'%s',
							'%s',
						)
					);

					if ( ! is_wp_error( $result ) ) {
						$this->ajax_response( 'save' );
					}
				}
			}

			$this->ajax_response( 'fail' );
		}


		/**
		 * Add Thumb to Post.
		 *
		 * @return void
		 */
		public function add_post_thumb() {

			if ( check_ajax_referer( WPF_PREFIX . '_nonce', 'security' ) ) {

				$post_ID = ( isset( $_POST['data']['postID'] ) && intval( $_POST['data']['postID'] ) ) ? (int) ( wp_unslash( $_POST['data']['postID'] ) ) : '';
				$thumb   = isset( $_POST['data']['thumb'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['thumb'] ) ) : '';

				$post = get_post( $post_ID );

				if ( ! empty( $post ) && ! empty( $post_ID ) && ( in_array( $thumb, $this->thumbs ) ) ) {

					$post_thumbs = (int) get_post_meta( $post_ID, WPF_PREFIX . '_thumbs_' . $thumb, true );

					if ( ! empty( $post_thumbs ) ) {

						$post_thumbs++;

					} else {

						$post_thumbs = 1;
					}

					update_post_meta( $post_ID, WPF_PREFIX . '_thumbs_' . $thumb, $post_thumbs );

					$this->ajax_response( $thumb );
				}
			}

			$this->ajax_response( 'fail' );

		}

		/**
		 * Ajax response.
		 *
		 * @param String $type Response Type.
		 * @param String $data
		 * @return void
		 */
		protected function ajax_response( $type, $data = '' ) {
			$responses = array(
				'up'   => array(
					'code'    => 200,
					'message' => __( 'Thumbs up is updated successfully', 'wp' ),
					'data'    => $data,
				),
				'down' => array(
					'code'    => 200,
					'message' => __( 'Thumbs down is updated successfully', 'wp' ),
					'data'    => $data,
				),
				'save' => array(
					'code'    => 200,
					'message' => __( 'Feedback is saved successfully', 'wp' ),
					'data'    => $data,
				),
				'fail' => array(
					'code'    => 404,
					'message' => __( 'Invalid request! Please try again!', 'wpas-chat' ),
					'data'    => $data,
				),
			);

			wp_send_json( $responses[ $type ] );
			die();
		}

	}

	add_action( 'plugins_loaded', array( 'WP_POSTS_FEEDBACK', 'init' ), 10 );
	register_activation_hook( __FILE__, array( 'WP_POSTS_FEEDBACK', 'plugin_activated' ) );

endif;
