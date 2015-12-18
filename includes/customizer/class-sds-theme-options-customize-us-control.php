<?php

if ( ! class_exists( 'WP_Customize_Control' ) )
	return;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
if ( ! class_exists( 'WP_Customize_US_Control' ) ) {
	class SDS_Theme_Options_Customize_US_Control extends WP_Customize_Control {
		public $content = '';

		/**
		 * Constructor
		 */
		function __construct( $manager, $id, $args ) {
			// Just calling the parent constructor here
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * This function renders the control's content.
		 */
		public function render_content() {
			echo $this->content;
		}
	}
}