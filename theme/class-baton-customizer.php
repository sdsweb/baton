<?php
/**
 * This class manages all Customizer functionality with our Baton theme.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Customizer' ) ) {
	class Baton_Customizer {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var string, Transient name
		 */
		public $transient_name = 'baton_customizer_';

		/**
		 * @var array, Transient data
		 */
		public $transient_data = array();

		/**
		 * @var array, selected Baton color scheme properties
		 */
		public $sds_color_scheme = array();

		/**
		 * @var array, color scheme control IDs
		 */
		public $sds_color_scheme_controls = array();


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
			global $sds_theme_options;

			// Set the current Baton color scheme
			$sds_color_schemes = sds_color_schemes();
			$this->sds_color_scheme = ( ! empty( $sds_theme_options['color_scheme'] ) && array_key_exists( $sds_theme_options['color_scheme'], $sds_color_schemes ) ) ? $sds_color_schemes[$sds_theme_options['color_scheme']] : $this->sds_color_scheme;

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 9999 ); // After Setup Theme (late; load assets based on theme support)
			add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 20 ); // After core Customizer preview filters have been added

			// Customizer
			add_filter( 'sds_color_scheme_customizer_color_controls', array( $this, 'sds_color_scheme_customizer_color_controls' ) ); // Adjust color controls in SDS Core
			add_action( 'customize_register', array( $this, 'customize_register' ), 25 ); // Add settings/sections/controls to Customizer
			add_action( 'customize_save_after', array( $this, 'reset_transient' ) ); // Customize Save (reset transients)

			// More Link
			add_filter( 'theme_mod_baton_more_link_label', array( $this, 'theme_mod_baton_more_link_label' ) ); // Set the default more link button label

			// Front End
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) );
		}


		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/

		/**
		 * This function runs after the theme has been setup and determines which assets to load based on theme support.
		 */
		public function after_setup_theme() {
			$sds_theme_options_instance = SDS_Theme_Options_Instance(); // Grab the SDS_Theme_Options instance

			// Setup transient data
			$this->transient_name .= $sds_theme_options_instance->theme->get_template(); // Append theme name to transient name
			$this->transient_data = $this->get_transient();

			// If the theme has updated, let's update the transient data
			if ( ! isset( $this->transient_data['version'] ) || $this->transient_data['version'] !== $sds_theme_options_instance->theme->get( 'Version' ) )
				$this->reset_transient();
		}

		/**
		 * This function sets/resets the current color scheme for use in the Customizer.
		 */
		public function wp_loaded() {
			global $sds_theme_options;

			// Set the current Baton color scheme
			$sds_color_schemes = sds_color_schemes();
			$this->sds_color_scheme = ( ! empty( $sds_theme_options['color_scheme'] ) && array_key_exists( $sds_theme_options['color_scheme'], $sds_color_schemes ) ) ? $sds_color_schemes[$sds_theme_options['color_scheme']] : $this->sds_color_scheme;
		}


		/**************
		 * Customizer *
		 **************/

		/**
		 * This function adjusts the default color controls used in SDS Core to determine color
		 * defaults within a Customizer session (when a color scheme is adjusted).
		 */
		public function sds_color_scheme_customizer_color_controls( $color_controls ) {
			$color_controls = array_merge( $color_controls, array(
				'background_color', // Default background color,
			) );

			return $color_controls;
		}

		/**
		 * This function registers various Customizer options for this theme.
		 */
		public function customize_register( $wp_customize ) {
			// Load custom Customizer API assets
			include_once get_template_directory() . '/customizer/class-baton-customizer-font-size-control.php'; // Baton Customizer Font Size Control

			/**
			 * Demo Content
			 */

			/*
			 * Demo Content Section
			 */
			$wp_customize->add_section( 'baton_enable_disable_features', array(
				'priority' => 5, // Top
				'title' => __( 'Baton Features', 'baton' )
			) );


			/**
			 * General Settings
			 */

			/*
			 * General Settings Panel
			 */
			$wp_customize->add_panel( 'baton_general_settings', array(
				'priority' => 10, // After Baton Features
				'title' => __( 'General Settings', 'baton' )
			) );


			/**
			 * Logo/Site Title & Tagline Section
			 */
			if ( $title_tagline_section = $wp_customize->get_section( 'title_tagline' ) ) { // Get Section
				$title_tagline_section->panel = 'baton_general_settings'; // Add panel
				$title_tagline_section->priority = 10; // Adjust Priority
			}


			/**
			 * Static Front Page Section
			 */
			if ( $static_front_page_section = $wp_customize->get_section( 'static_front_page' ) ) { // Get Section
				$static_front_page_section->panel = 'baton_general_settings'; // Add panel
				$static_front_page_section->priority = 20; // Adjust Priority
			}


			/**
			 * Nav Section
			 */
			if ( $nav_section = $wp_customize->get_section( 'nav' ) ) { // Get Section
				$nav_section->panel = 'baton_general_settings'; // Add panel
				$nav_section->priority = 30; // Adjust Priority
			}
			else if ( $nav_menus_panel = $wp_customize->get_panel( 'nav_menus' ) ) { // Get Panel (WordPress 4.3+)
				$nav_menus_panel->panel = 'baton_general_settings'; // Add panel
				$nav_menus_panel->priority = 30; // Adjust Priority
			}


			/**
			 * Site Layout Section
			 */
			$wp_customize->add_section( 'baton_design_site_layout', array(
				'priority' => 40, // After Static Front Page
				'title' => __( 'Site Layout', 'baton' ),
				'panel' => 'baton_general_settings'
			) );


			/**
			 * Max Width
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_max_width',
				array(
					'default' => apply_filters( 'baton_max_width', 1272, 1272 ), // Pass the default value as second parameter
					'sanitize_callback' => 'absint',
					'sanitize_js_callback' => 'absint'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Font_Size_Control(
					$wp_customize,
					'baton_max_width',
					array(
						'label' => __( 'Maximum Width', 'baton' ),
						'description' => __( 'The default width is 1272px.', 'baton' ),
						'section' => 'baton_design_site_layout',
						'settings' => 'baton_max_width',
						'priority' => 10, // Top
						'type' => 'number',
						'input_attrs' => array(
							'min' => apply_filters( 'theme_mod_baton_max_width_min', 800, 800 ), // Pass the default value as second parameter
							'max' => apply_filters( 'theme_mod_baton_max_width_max', 1272, 1272 ), // Pass the default value as second parameter
							'placeholder' => apply_filters( 'theme_mod_baton_max_width', 1272, 1272 ), // Pass the default value as second parameter
							'style' => 'width: 70px',
							'step' => '10'
						),
						'units' => array(
							'title' => _x( 'pixels', 'title attribute for max width Customizer control', 'baton' )
						)
					)
				)
			);


			/**
			 * Content Layouts (SDS Core)
			 */
			if ( $sds_theme_options_content_layouts_section = $wp_customize->get_section( 'sds_theme_options_content_layouts' ) ) { // Get Section
				$sds_theme_options_content_layouts_section->panel = 'baton_general_settings'; // Adjust panel
				$sds_theme_options_content_layouts_section->priority = 50; // Adjust priority
			}

			/**
			 * Show/Hide Elements Section
			 */
			if ( $sds_theme_options_show_hide_section = $wp_customize->get_section( 'sds_theme_options_show_hide' ) ) { // Get Section
				$sds_theme_options_show_hide_section->panel = 'baton_general_settings'; // Adjust panel
				$sds_theme_options_show_hide_section->priority = 60; // After Images
			}

			/**
			 * Background Image Section
			 */
			if ( $background_image_section = $wp_customize->get_section( 'background_image' ) ) { // Get Section
				$background_image_section->title = __( 'Background Color &amp; Image', 'baton' ); // Adjust Label
				$background_image_section->priority = 20; // After General Settings
			}

			/*
			 * Background Color
			 */
			if ( $background_image_section = $wp_customize->get_control( 'background_color' ) ) { // Get Control
				$background_image_section->section = 'background_image'; // Adjust Section
				$background_image_section->priority = 10; // Before Background Image
			}


			/**
			 * Color Scheme
			 */
			if ( $sds_theme_options_color_scheme_control = $wp_customize->get_control( 'sds_theme_options[color_scheme]' ) ) // Get Control
				// Store a reference of the color controls
				$this->sds_color_scheme_controls = $sds_theme_options_color_scheme_control->color_controls;

			/**
			 * Colors Section
			 */
			if ( $colors_section = $wp_customize->get_section( 'colors' ) ) // Get Section
				$colors_section->title = __( 'Color Scheme', 'baton' ); // Adjust Label

			// Remove Content Color
			$wp_customize->remove_control( 'content_color' );
			$wp_customize->remove_setting( 'content_color' );


			/**
			 * More Link Section
			 */
			$wp_customize->add_section( 'baton_content_more_link', array(
				'priority' => 40, // After Body (Content)
				'title' => __( 'More Link', 'baton' ),
				'panel' => 'baton_content'
			) );

			/**
			 * More Link Button Label
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_more_link_label',
				array(
					'default' => apply_filters( 'theme_mod_baton_more_link_label', '' ),
					'sanitize_callback' => 'sanitize_text_field'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_more_link_label',
					array(
						'label' => __( 'Button Label', 'baton' ),
						'section' => 'baton_content_more_link',
						'settings' => 'baton_more_link_label',
						'priority' => 10
					)
				)
			);
		}

		/**
		 * This function sets the default more link label in the Customizer.
		 */
		public function theme_mod_baton_more_link_label( $label = false ) {
			// Return the current color if set
			if ( $label )
				return $label;

			// Return the default
			return baton_more_link_label( true ) ;
		}

		/**
		 * This function adjusts the body classes based on theme mods.
		 */
		public function body_class( $classes ) {
			// Max Width
			if ( ( $theme_mod_baton_max_width = $this->get_theme_mod( 'baton_max_width', 1272 ) ) )
				$classes['baton_max_width'] = 'custom-max-width custom-max-width-' . $theme_mod_baton_max_width . ' max-width-' . $theme_mod_baton_max_width;

			return $classes;
		}

		/**
		 * This function returns a CSS <style> block for Customizer theme mods.
		 */
		// TODO: Add individual filters that allow adjustment of each selector, and CSS properties
		public function get_customizer_css() {
			// Check transient first (not in the Customizer)
			if ( ! is_customize_preview() && ! empty( $this->transient_data ) && isset( $this->transient_data['customizer_css' ] ) )
				return $this->transient_data['customizer_css'];
			// Otherwise return data
			else {
				// Make sure Customizer functions are available
				if ( is_admin() && ! class_exists( 'WP_Customize_Manager' ) )
					include_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

				// Grab the SDS Theme Options Instance
				$sds_theme_options_instance = SDS_Theme_Options_Instance();

				// Open <style>
				$r = '<style type="text/css" id="' . $sds_theme_options_instance->theme->get_template() . '-customizer">';

				// If we have a max width set by the user
				if ( ( $theme_mod_baton_max_width = $this->get_theme_mod( 'baton_max_width', 1272 ) ) ) {
					$r .= '/* Maximum Width */' . "\n";
					$r .= '.in,' . "\n";
					$r .= '.front-page-widgets .conductor-widget-title, .front-page-widgets .conductor-widget,' . "\n";
					$r .= '.front-page-widgets .widget.conductor-widget, .conductor-widget .front-page-widget-in,' . "\n";
					$r .= '.widget.conductor-widget .front-page-widget-in, .conductor-slider-testimonials-slider {' . "\n";
						$r .= 'max-width: ' . $theme_mod_baton_max_width . 'px;' . "\n";
					$r .= '}' . "\n\n";
				}

				// Close </style>
				$r .= '</style>';

				return $r;
			}
		}

		/**
		 * This function outputs CSS for Customizer settings.
		 */
		public function wp_head() {
			// Get Customizer CSS
			echo $this->get_customizer_css();
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns a theme mod but first checks to see if it is the default, and if so
		 * no value is returned. This is to prevent unnecessary CSS output in wp_head().
		 */
		public function get_theme_mod( $theme_mod_name, $default = false, $format_function = false, $default_format_function = false ) {
			$theme_mod = get_theme_mod( $theme_mod_name );

			// Should we format the value
			if ( $format_function )
				// Switch based on format function
				switch ( $format_function ) {
					// ltrim_hash (remove the hash symbol)
					case 'ltrim_hash':
						$theme_mod = ltrim( $theme_mod, '#' );
					break;
				}

			// Should we format the default value
			if ( $default_format_function )
				// Switch based on format function
				switch ( $default_format_function ) {
					// ltrim_hash (remove the hash symbol)
					case 'ltrim_hash':
						$default = ltrim( $default, '#' );
					break;
				}

			// Check this theme mod against the default
			if ( $theme_mod === $default )
				$theme_mod = false;

			return $theme_mod;
		}

		/**
		 * This function returns the current color scheme default and returns the $fallback as a fallback.
		 */
		public function get_current_color_scheme_default( $property, $fallback = false ) {
			// Set the default value to the fallback initially
			$default = $fallback;

			// Grab the color scheme default value if it exists
			if ( ! empty( $this->sds_color_scheme ) && isset( $this->sds_color_scheme[$property] ) && ! empty( $this->sds_color_scheme[$property] ) )
				$default = $this->sds_color_scheme[$property];

			return $default;
		}

		/**
		 * This function resets transient data to ensure front-end matches Customizer preview.
		 */
		public function reset_transient() {
			// Reset transient data on this class
			$this->transient_data = array();

			// Delete the transient data
			$this->delete_transient();

			// Set the transient data
			$this->set_transient();
		}


		/**
		 * This function gets our transient data. Additionally it calls the set_transient()
		 * method on this class to set and return transient data if the transient data doesn't
		 * currently exist.
		 */
		public function get_transient() {
			// Check for transient data first
			if ( ! $transient_data = get_transient( $this->transient_name ) )
				// Create and return the transient data if it doesn't exist
				$transient_data = $this->set_transient();

			return $transient_data;
		}

		/**
		 * This function stores data in our transient and returns the data.
		 */
		public function set_transient() {
			$sds_theme_options_instance = SDS_Theme_Options_Instance(); // Grab the SDS_Theme_Options instance

			$data = array(); // Default

			// Always add the Customizer CSS
			$data['customizer_css'] = $this->get_customizer_css();

			// Always add the theme's version
			$data['version'] = $sds_theme_options_instance->theme->get( 'Version' );

			// Set the transient
			set_transient( $this->transient_name, $data );

			return $data;
		}

		/**
		 * This function deletes our transient data.
		 */
		public function delete_transient() {
			// Delete the transient
			delete_transient( $this->transient_name );
		}
	}


	function Baton_Customizer_Instance() {
		return Baton_Customizer::instance();
	}

	// Starts Baton Customizer
	Baton_Customizer_Instance();
}