<?php
/**
 * This class manages all Baton Conductor functionality with our Baton theme.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Conductor' ) ) {
	class Baton_Conductor {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var array
		 */
		public $defaults = array();

		/**
		 * @var array
		 */
		public $baton_conductor_theme_mod = array();

		/**
		 * @var Baton_Conductor_Query
		 */
		public $baton_conductor_query = false;

		/**
		 * @var array
		 */
		public $baton_conductor_query_hooks = array();


		private static $instance; // Keep track of the instance

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self();

			return self::$instance;
		}


		/*
		 * This function sets up all of the actions and filters on instance
		 */
		public function __construct() {
			$callback_prefix = apply_filters( 'baton_conductor_query_callback_prefix', 'baton_conductor_' ); // Callback function prefix
			
			// Populate defaults
			$this->defaults = array(
				// Disabled
				'disabled' => false,
				// Title
				'title' => __( 'Recent News', 'baton' ),
				// Posts per page
				'posts_per_page' => 9,
				// Category
				'category' => '',
				// Flexbox Columns
				'flexbox_columns' => 3,
				// Output
				'output' => array(
					// Featured Image
					10 => array(
						'id' => 'featured_image',
						'label' =>  __( 'Featured Image', 'baton' ),
						'type' => 'built-in',
						'visible' => true,
						'link' => true,
						'callback' => $callback_prefix . 'featured_image'
					),
					// Post Title
					20 => array(
						'id' => 'post_title',
						'label' =>  __( 'Title', 'baton' ),
						'type' => 'post_title',
						'visible' => true,
						'link' => true,
						'callback' => $callback_prefix . 'post_title'
					),
					// Post Content
					30 => array(
						'id' => 'post_content',
						'label' =>  __( 'Excerpt', 'baton' ),
						'type' => 'content',
						'visible' => true,
						'value' => 'excerpt',
						'callback' => $callback_prefix . 'post_content'
					),
					// Read More
					40 => array(
						'id' => 'read_more',
						'label' => baton_more_link_label(),
						'type' => 'read_more',
						'visible' => true,
						'link' =>  true,
						'callback' => $callback_prefix . 'read_more'
					),
					// Author Byline
					50 => array(
						'id' => 'author_byline',
						'label' =>  __( 'Author Byline', 'baton' ),
						'type' => 'built-in',
						'visible' => true,
						'callback' => $callback_prefix . 'author_byline'
					),
				),
				// Featured Image Size
				'post_thumbnails_size' => false,
				// Excerpt Length
				'excerpt_length' => apply_filters( 'excerpt_length', 55 )
			);

			// Load required assets
			$this->includes();


			// Hooks
			add_action( 'wp', array( $this, 'wp' ), 20 ); // Populate theme mod value (after WordPress)
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // Enqueue scripts/styles
			add_action( 'dynamic_sidebar_after', array( $this, 'dynamic_sidebar_after' ), 10, 2 ); // After Front Page Sidebar

		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			// Front-end
			if ( ! is_admin() )
				include_once get_template_directory() . '/theme/class-baton-conductor-query.php'; // Baton Conductor Query Class
		}

		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/

		/**
		 * This function populates the theme_mod
		 */
		public function wp() {
			// Grab the Baton Conductor theme mod
			$this->baton_conductor_theme_mod = get_theme_mod( 'baton_conductor', $this->defaults );

			// Parse any saved arguments into defaults
			$this->baton_conductor_theme_mod = wp_parse_args( $this->baton_conductor_theme_mod, $this->defaults );

		}

		/**
		 * This function enqueues all styles and scripts.
		 */
		public function wp_enqueue_scripts() {
			// Bail if Baton Conductor isn't active
			if ( $this->is_baton_conductor_disabled() )
				return;

			// Grab the Conductor Widget instance (if it exists)
			$conductor_widget = ( class_exists( 'Conductor' ) && function_exists( 'Conduct_Widget' ) ) ? Conduct_Widget() : false;

			// If Conductor isn't active or at least one Conductor Widget isn't active
			if ( ! class_exists( 'Conductor' ) || ( $conductor_widget && is_a( $conductor_widget, 'Conductor_Widget' ) && ! is_active_widget( false, false, $conductor_widget->id_base, true ) ) )
				// Conductor Flexbox Shim
				wp_enqueue_style( 'baton-conductor-flexbox', get_template_directory_uri() . '/css/conductor-flexbox.css', false, $this->version );
		}

		/**
		 * This function outputs Baton Conductor after the Front Page Sidebar.
		 */
		public function dynamic_sidebar_after( $sidebar_id, $has_widgets ) {
			// If Baton Conductor is enabled, we have widgets, and this is the Front Page Sidebar
			if ( $this->is_baton_conductor_enabled() && $has_widgets && $sidebar_id === 'front-page-sidebar' ) :
				// Create an "instance"
				$instance = array(
					// Title
					'title' => $this->baton_conductor_theme_mod['title'],
					// Posts Per page
					'posts_per_page' => $this->baton_conductor_theme_mod['posts_per_page'],
					// Category
					'category' => $this->baton_conductor_theme_mod['category'],
					// Flexbox Columns
					'flexbox_columns' => $this->baton_conductor_theme_mod['flexbox_columns'],
					'flexbox'=> array(
						'columns' => $this->baton_conductor_theme_mod['flexbox_columns']
					),
					// Output
					'output' => $this->baton_conductor_theme_mod['output'],
					// Output elements
					'output_elements' => array(),
					// Content Display Type
					'content_display_type' => 'excerpt',
					// Widget Size
					'widget_size' => 'flexbox',
					// Featured Image Size
					'post_thumbnails_size' => $this->baton_conductor_theme_mod['post_thumbnails_size'],
					// Excerpt Length
					'excerpt_length' => ( $this->baton_conductor_theme_mod['excerpt_length'] !== 0 ) ? $this->baton_conductor_theme_mod['excerpt_length'] : $this->defaults['excerpt_length']
				);

				// Loop through output elements to populate other data
				foreach ( $instance['output'] as $priority => $output ) {
					// Add this element to the list of output elements (Conductor expects this to exist)
					$instance['output_elements'][$output['id']] = $priority;

					// Post content
					if ( $output['id'] === 'post_content' )
						$instance['content_display_type'] = $output['value'];
				}

				// Query arguments
				$instance['query_args'] = array(
					'posts_per_page' => $instance['posts_per_page'],
					'max_num_posts' => '',
					'offset' => 0,
					'post__in' => false,
					'post__not_in' => false
				);

				// Conductor instance filter (second parameter would be widget args in Conductor)
				$instance = apply_filters( 'baton_conductor_instance', $instance, array(), $this );

				// Base CSS classes
				$css_classes = array(
					'in',
					'front-page-widget-in',
					'cf',
					'widget',
					'front-page',
					'front-page-sidebar',
					'conductor-widget',
					'conductor-widget-wrap',
					'conductor-row',
					'conductor-widget-row',
					'conductor-flex',
					'conductor-widget-flex'
				);

				// Add the specific column CSS classes
				$css_classes[] = 'conductor-row-' . $instance['flexbox_columns'] . '-columns';
				$css_classes[] = 'conductor-widget-row-' . $instance['flexbox_columns'] . '-columns';
				$css_classes[] = 'conductor-flex-' . $instance['flexbox_columns'] . '-columns';
				$css_classes[] = 'conductor-widget-flex-' . $instance['flexbox_columns'] . '-columns';
				$css_classes[] = 'conductor-' . $instance['flexbox_columns'] . '-columns';
				$css_classes[] = 'conductor-widget-' . $instance['flexbox_columns'] . '-columns';

				$css_classes = implode( ' ', $css_classes );
			?>
				<div id="front-page-baton-conductor" class="<?php echo esc_attr( $css_classes ); ?>">
					<?php
						// If we have a title
						if ( ! empty( $this->baton_conductor_theme_mod['title'] ) ) :
							do_action( 'baton_conductor_title_before', $instance,  $this );
					?>
							<h3 class="widgettitle widget-title conductor-widget-title"><?php echo $instance['title']; ?></h3>
					<?php
							do_action( 'baton_conductor_title_after', $instance, $this );
						endif;

						// Conductor Query
						$baton_conductor_query_args = array(
							'instance' => $instance,
							'display_content_args_count' => 4 // Current number of arguments on the display_content() function, used in sortable output
						);
						$baton_conductor_query_args = apply_filters( 'baton_conductor_query_args', $baton_conductor_query_args, $instance, $this );

						// Default to Baton Conductor Query
						if ( ! ( $this->baton_conductor_query = apply_filters( 'baton_conductor_query', false, $baton_conductor_query_args, $instance, $this ) ) )
							$this->baton_conductor_query = new Baton_Conductor_Query( $baton_conductor_query_args );

						// Return the query (should contain results)
						$baton_conductor_query_results = $this->baton_conductor_query->get_query();

						if ( $this->baton_conductor_query->have_posts() ) {
							while ( $this->baton_conductor_query->have_posts() ) : $this->baton_conductor_query->the_post();
								$this->display_content( $this->baton_conductor_query->get_current_post(), $instance );
							endwhile;

							// Pagination
							if ( $this->baton_conductor_query->has_pagination() ) {
								do_action( 'baton_conductor_pagination_before', $instance, $baton_conductor_query_results, $this );
								$this->baton_conductor_query->get_pagination_links();
								do_action( 'baton_conductor_pagination_after', $instance, $baton_conductor_query_results, $this );
							}

							// Reset global $post
							wp_reset_postdata();
						}
					?>
				</div>
			<?php
			endif;
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function determines if Baton Conductor is enabled.
		 */
		public function is_baton_conductor_enabled() {
			return ( $this->is_baton_conductor_disabled() === false );
		}

		/**
		 * This function determines if Baton Conductor is disabled.
		 */
		public function is_baton_conductor_disabled() {
			return $this->baton_conductor_theme_mod['disabled'] === true;
		}

		/**
		 * This function outputs the content for this Baton Conductor instance.
		 */
		public function display_content( $post, $instance ) {
			// TODO: Eventually change order of arguments (if possible) in a future release (Baton Conductor query should come before $this; have to think about backwards/forwards compatibility)
			do_action( 'baton_conductor_display_content', $post, $instance, $this, $this->baton_conductor_query );
		}

		/**
		 * This function generates CSS classes for widget output.
		 */
		public function get_css_classes( $instance ) {
			$baton_conductor_query = $this->baton_conductor_query->get_query();

			// Base CSS classes
			$css_classes = array(
				'conductor-widget',
				'flexbox',
				'conductor-widget-flexbox',
				'conductor-col'
			);

			// Even or odd
			if ( property_exists( $baton_conductor_query, 'current_post' ) ) {
				// Even
				if ( ( $baton_conductor_query->current_post + 1 ) % 2 === 0 ) {
					$css_classes[] = 'conductor-widget-even';
					$css_classes[] = 'conductor-widget-flexbox-even';
				}
				// Odd
				else {
					$css_classes[] = 'conductor-widget-odd';
					$css_classes[] = 'conductor-widget-flexbox-odd';
				}

				$css_classes[] = 'conductor-col-' . ( $baton_conductor_query->current_post + 1 ); // WP_Query returns posts in a zero-index array
			}

			$css_classes = apply_filters( 'baton_conductor_css_classes', $css_classes, $instance, $this );

			return implode( ' ', $css_classes );
		}
	}


	function Baton_Conductor_Instance() {
		return Baton_Conductor::instance();
	}

	// Starts Baton Customizer
	Baton_Conductor_Instance();
}