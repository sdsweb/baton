<?php
/**
 * This class manages all Baton Conductor Customizer functionality with our Baton theme.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Conductor_Customizer' ) ) {
	class Baton_Conductor_Customizer {
		/**
		 * @var string
		 */
		public $version = '1.0.5';


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
			// Customizer
			add_action( 'customize_register', array( $this, 'customize_register' ), 25 ); // Add settings/sections/controls to Customizer (after SDS Core/Baton)
		}


		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/


		/**************
		 * Customizer *
		 **************/

		/**
		 * This function registers various Customizer options for this theme.
		 */
		public function customize_register( $wp_customize ) {
			// Load custom Customizer API assets
			include_once get_template_directory() . '/customizer/class-baton-customizer-conductor-control.php'; // Baton Customizer Conductor Control

			// Grab the Baton Conductor instance
			$baton_conductor = Baton_Conductor_Instance();

			/**
			 * Baton Conductor
			 */

			// Section
			$wp_customize->add_section( 'baton_conductor', array(
				'priority' => 40,
				'title' => __( 'Blog Display', 'baton' ),
				'description' => sprintf( __( 'This is a preview of our Conductor Widget. <a href="%1$s" target="_blank">Install our Conductor plugin</a> for even better ways to control your content.', 'baton' ), esc_url( 'https://conductorplugin.com/?utm_source=baton&utm_medium=link&utm_content=baton-conductor&utm_campaign=baton' ) ),
			) );

			/*
			 * Settings
			 */

			// Disabled
			$wp_customize->add_setting(
				'baton_conductor[disabled]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_disabled', $baton_conductor->defaults['disabled'] ),
					'sanitize_callback' => 'baton_boolval'
				)
			);

			// Enhanced Display Disabled
			$wp_customize->add_setting(
				'baton_conductor[enhanced_display_disabled]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_enhanced_display_disabled', $baton_conductor->defaults['enhanced_display_disabled'] ),
					'sanitize_callback' => 'baton_boolval'
				)
			);

			// Title
			$wp_customize->add_setting(
				'baton_conductor[title]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_title', $baton_conductor->defaults['title'] ),
					'sanitize_callback' => 'sanitize_text_field'
				)
			);

			// Posts Per Page
			$wp_customize->add_setting(
				'baton_conductor[posts_per_page]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_posts_per_page', $baton_conductor->defaults['posts_per_page'] ),
					'sanitize_callback' => 'absint'
				)
			);

			// Category
			$wp_customize->add_setting(
				'baton_conductor[category]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_category', $baton_conductor->defaults['category'] ),
					'sanitize_callback' => 'absint'
				)
			);

			// Flexbox
			$wp_customize->add_setting(
				'baton_conductor[flexbox_columns]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_flexbox_columns', $baton_conductor->defaults['flexbox_columns'] ),
					'sanitize_callback' => 'absint'
				)
			);

			// Output
			$wp_customize->add_setting(
				'baton_conductor[output]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_output', $baton_conductor->defaults['output'] ),
					'sanitize_callback' => array( $this, 'sanitize_output' ),
					'sanitize_js_callback' => array( $this, 'sanitize_js_output' )
				)
			);

			// Excerpt Length
			$wp_customize->add_setting(
				'baton_conductor[excerpt_length]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_excerpt_length', $baton_conductor->defaults['excerpt_length'] ),
					'sanitize_callback' => 'absint'
				)
			);

			// Featured Image Size
			$wp_customize->add_setting(
				'baton_conductor[post_thumbnails_size]',
				array(
					'default' => apply_filters( 'theme_mod_baton_conductor_post_thumbnails_size', $baton_conductor->defaults['post_thumbnails_size'] ),
					'sanitize_callback' => 'sanitize_text_field'
				)
			);

			/*
			 * Controls
			 */

			// Disabled
			$wp_customize->add_control(
				new SDS_Theme_Options_Customize_Checkbox_Control(
					$wp_customize,
					'baton_conductor[disabled]', // IDs can have nested array keys
					array(
						'label' => __( 'Blog Display', 'baton' ),
						'description' => __( 'Use this setting to enable or disable Blog Display <sup>by Conductor</sup>.', 'baton' ),
						'section'  => 'baton_enable_disable_features',
						'settings' => 'baton_conductor[disabled]',
						'priority' => 10,
						'active_callback' => 'baton_has_blog_front_page',
						'type' => 'checkbox', // Used in js controller
						'css_class' => 'baton-conductor-disabled',
						'css_id' => 'baton_conductor_disabled',
						'checked_label' => __( 'Yes', 'baton' ),
						'unchecked_label' => __( 'No', 'baton' ),
						'style' => array(
							'before' => 'width: 38%; text-align: center;',
							'after' => 'width: 38%; padding: 0 6px; text-align: center; right: 0;'
						)
					)
				)
			);

			// Enhanced Display Disabled
			$wp_customize->add_control(
				new SDS_Theme_Options_Customize_Checkbox_Control(
					$wp_customize,
					'baton_conductor[enhanced_display_disabled]', // IDs can have nested array keys
					array(
						'label' => __( 'Enhanced Blog Display', 'baton' ),
						'description' => __( 'Use this setting to enable or disable the enhanced Blog Display <sup>by Conductor</sup>.', 'baton' ),
						'section'  => 'baton_enable_disable_features',
						'settings' => 'baton_conductor[enhanced_display_disabled]',
						'priority' => 20,
						'active_callback' => 'baton_conductor_customizer_active_callback',
						'type' => 'checkbox', // Used in js controller
						'css_class' => 'baton-conductor-enhanced-display-disabled',
						'css_id' => 'baton_conductor_enhanced_display_disabled',
						'checked_label' => __( 'Yes', 'baton' ),
						'unchecked_label' => __( 'No', 'baton' ),
						'style' => array(
							'before' => 'width: 38%; text-align: center;',
							'after' => 'width: 38%; padding: 0 6px; text-align: center; right: 0;'
						)
					)
				)
			);

			// Conductor
			$wp_customize->add_control(
				new Baton_Customize_Conductor_Control(
					$wp_customize,
					'baton_conductor',
					array(
						'label' => __( 'Conductor', 'baton' ),
						'section' => 'baton_conductor',
						'settings' => array(
							'title' => 'baton_conductor[title]',
							'posts_per_page' => 'baton_conductor[posts_per_page]',
							'category' => 'baton_conductor[category]',
							'flexbox_columns' => 'baton_conductor[flexbox_columns]',
							'output' => 'baton_conductor[output]',
							'post_thumbnails_size' => 'baton_conductor[post_thumbnails_size]',
							'excerpt_length' => 'baton_conductor[excerpt_length]'
						),
						'priority' => 10,
						'active_callback' => 'baton_conductor_customizer_active_callback'
					)
				)
			);
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function sanitizes Baton Conductor data.
		 */
		public function sanitize_output( $input ) {
			$output_elements = ( ! empty( $input ) ) ? json_decode( $input, true ) : false;

			if ( ! empty( $output_elements ) ) {
				$callback_prefix = apply_filters( 'baton_conductor_query_callback_prefix', 'baton_conductor_', $input ); // Callback function prefix

				// Reset the output array first
				$input = array();

				// Loop through each output element
				foreach ( $output_elements as $priority => $element ) {
					$the_priority = ( int ) $priority;
					$id = sanitize_text_field( $element['id'] );

					// Create a sanitized array of data
					$input[$the_priority] = array(
						'id' => $id,
						'priority' => $the_priority,
						'label' => sanitize_text_field( $element['label'] ),
						'type' => sanitize_text_field( $element['type'] ),
						'visible' => ( $element['visible'] ),
						'callback' => $callback_prefix . $id
					);

					// Link
					if ( isset( $element['link'] ) )
						$input[$the_priority]['link'] = ( $element['link'] ) ? true: false;

					// Content
					if ( $element['type'] === 'content' )
						$input[$the_priority]['value'] = ( isset( $element['value'] ) && ! empty( $element['value']) ) ? sanitize_text_field( $element['value'] ) : 'content'; // Default to content
				}
			}

			return $input;
		}

		/**
		 * This function sanitizes Baton Conductor data on the front-end.
		 */
		public function sanitize_js_output( $input ) {
			return json_encode( $input );
		}
	}


	function Baton_Conductor_Customizer_Instance() {
		return Baton_Conductor_Customizer::instance();
	}

	// Starts Baton Customizer
	Baton_Conductor_Customizer_Instance();
}