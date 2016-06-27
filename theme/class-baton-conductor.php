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
		public $version = '1.0.5';

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
		 * @var int
		 */
		public $enhanced_display_offset = 4;


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
				// Enhanced Display Disabled
				'enhanced_display_disabled' => false,
				// Title
				'title' => __( 'Recent News', 'baton' ),
				// Posts per page
				'posts_per_page' => 13,
				// Posts per page
				'enhanced_display_posts_per_page_offset' => $this->enhanced_display_offset,
				// Category
				'category' => 0, // All
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
				'post_thumbnails_size' => '',
				// Excerpt Length
				'excerpt_length' => apply_filters( 'excerpt_length', 55 )
			);

			// Load required assets
			$this->includes();


			// Setup the theme mod
			$this->setup_theme_mod();


			// Hooks
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); // Pre Get Posts
			add_action( 'wp', array( $this, 'wp' ), 20 ); // Populate theme mod value (after WordPress)
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // Enqueue scripts/styles
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
		 * This function adjusts the main query arguments on the home (blog) page if Baton Conductor
		 * is enabled.
		 */
		public function pre_get_posts( $query ) {
			// Bail if Baton Conductor is not enabled, we're in the admin, this isn't the main query, or we're not on the home (blog) page
			if ( $this->is_baton_conductor_disabled() || is_admin() || ! $query->is_main_query() || ! $query->is_home() )
				return;

			// Grab the Baton Conductor Query instance
			$baton_conductor_query = Baton_Conductor_Query();

			// If we're in the Customizer, setup the theme mod
			if ( is_customize_preview() )
				$this->setup_theme_mod();

			// Adjust the posts per page
			$query->set( 'posts_per_page', ( ! empty( $this->baton_conductor_theme_mod['posts_per_page'] ) && $this->baton_conductor_theme_mod['posts_per_page'] !== 0 ) ? $this->baton_conductor_theme_mod['posts_per_page'] : $baton_conductor_query->post_count->publish );

			// Adjust the category
			$query->set( 'cat', $this->baton_conductor_theme_mod['category'] );

			// Ignore sticky posts
			$query->set( 'ignore_sticky_posts', true );
		}

		/**
		 * This function populates the theme_mod.
		 */
		public function wp() {
			// Setup the theme mod
			$this->setup_theme_mod();
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
			return ( $this->baton_conductor_theme_mod['disabled'] === true );
		}

		/**
		 * This function determines if Baton Conductor enhanced display is disabled.
		 */
		public function is_baton_conductor_enhanced_display_disabled() {
			return ( $this->baton_conductor_theme_mod['enhanced_display_disabled'] === true );
		}

		/**
		 * This function determines if Baton Conductor enhanced display is enabled.
		 */
		public function is_baton_conductor_enhanced_display_enabled() {
			return ( $this->is_baton_conductor_enhanced_display_disabled() === false );
		}

		/**
		 * This function outputs the content for this Baton Conductor instance.
		 */
		public function display_content( $post, $instance ) {
			// TODO: Eventually change order of arguments (if possible) in a future release (Baton Conductor query should come before $this; have to think about backwards/forwards compatibility)
			do_action( 'baton_conductor_display_content', $post, $instance, $this, $this->baton_conductor_query );
		}

		/**
		 * This function displays a specific output element.
		 */
		public function display_output_element( $output_element, $baton_conductor_query, $args ) {
				// Array callback
				// Only display this element if the callback exists, it's callable, and the element is visible
				if ( is_array( $output_element['callback'] ) && method_exists( $output_element['callback'][0], $output_element['callback'][1] ) && $output_element['visible'] )
					call_user_func_array( $output_element['callback'], $args );
				// String/other callbacks within the Baton Conductor Query class
				// Only display this element if the callback exists, it's callable, and the element is visible
				else if ( ! is_array( $output_element['callback'] ) && method_exists( $baton_conductor_query, $output_element['callback'] ) && is_callable( array( $baton_conductor_query, $output_element['callback'] ) ) && $output_element['visible'] )
					call_user_func_array( array( $baton_conductor_query, $output_element['callback'] ), $args );
				// String/other callbacks outside of this class
				// Only display this element if the callback exists, it's callable, and the element is visible
				else if ( ! is_array( $output_element['callback'] ) && function_exists( $output_element['callback'] ) && is_callable( $output_element['callback'] ) && $output_element['visible'] )
					call_user_func_array( $output_element['callback'], $args );
		}

		/**
		 * This function generates CSS classes for widget output.
		 */
		public function get_css_classes( $instance ) {
			global $wp_query;

			// Grab the current post index
			$post_index = ( baton_is_baton_conductor_display_enhanced() ) ? ( ( $wp_query->current_post + 1 ) - $this->enhanced_display_offset ) : ( $wp_query->current_post + 1 );

			// Base CSS classes
			$css_classes = array(
				'conductor-widget',
				'flexbox',
				'conductor-widget-flexbox',
				'conductor-col'
			);

			// Even
			if ( $post_index % 2 === 0 ) {
				$css_classes[] = 'conductor-widget-even';
				$css_classes[] = 'conductor-widget-flexbox-even';
			}
			// Odd
			else {
				$css_classes[] = 'conductor-widget-odd';
				$css_classes[] = 'conductor-widget-flexbox-odd';
			}

			$css_classes[] = 'conductor-col-' . $post_index; // WP_Query returns posts in a zero-index array

			$css_classes = apply_filters( 'baton_conductor_css_classes', $css_classes, $instance, $this );

			return implode( ' ', $css_classes );
		}

		/**
		 * This function sets up the theme mod data.
		 */
		public function setup_theme_mod() {
			// Grab the Baton Conductor theme mod
			$this->baton_conductor_theme_mod = get_theme_mod( 'baton_conductor', $this->defaults );

			// Parse any saved arguments into defaults
			$this->baton_conductor_theme_mod = wp_parse_args( $this->baton_conductor_theme_mod, $this->defaults );
		}

		/**
		 * This function returns a Baton Conductor instance (settings).
		 */
		public function get_baton_conductor_instance() {
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
			return apply_filters( 'baton_conductor_instance', $instance, array(), $this );
		}
	}


	/**
	 * Create an instance of the Baton_Conductor_Query class.
	 */
	function Baton_Conductor_Instance() {
		return Baton_Conductor::instance();
	}

	// Starts Baton Conductor
	Baton_Conductor_Instance();
}