<?php

// Make sure the Customize Image Control class exists
if ( ! class_exists( 'WP_Customize_Image_Control' ) )
	return false;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Image_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Logo_Control extends WP_Customize_Image_Control {
	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Just calling the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function enqueues scripts and styles
	 */
	public function enqueue() {
		wp_enqueue_media(); // Enqueue media scripts
		wp_enqueue_script( 'sds-theme-options-customizer-logo', SDS_Theme_Options::sds_core_url() . '/js/customizer-sds-theme-options-logo.js', array( 'customize-base', 'customize-controls' ), SDS_Theme_Options::get_version() );

		// Call the parent enqueue method here
		parent::enqueue();
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
		global $sds_theme_options;
	?>
		<div class="customize-image-picker customize-sds-theme-options-logo-upload">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<p>
				<?php
					$sds_logo_dimensions = apply_filters( 'sds_theme_options_logo_dimensions', '300x100' );
					printf( __( 'Upload a logo to to replace the site title. Recommended dimensions: %1$s.', 'baton' ), $sds_logo_dimensions );
				?>
			</p>

			<strong><?php _e( 'Current Logo:', 'baton' ); ?></strong>
			<div class="sds-theme-options-preview sds-theme-options-logo-preview">
				<?php
					if ( isset( $sds_theme_options['logo_attachment_id'] ) && $sds_theme_options['logo_attachment_id'] ) :
						echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' );
					else :
				?>
						<div class="description"><?php _e( 'No logo selected.', 'baton' ); ?></div>
				<?php endif; ?>
			</div>

			<input type="hidden" id="sds_theme_options_logo" class="sds-theme-options-upload-value" name="sds_theme_options[logo_attachment_id]"  value="<?php echo ( isset( $sds_theme_options['logo_attachment_id'] ) && ! empty( $sds_theme_options['logo_attachment_id'] ) ) ? esc_attr( $sds_theme_options['logo_attachment_id'] ) : false; ?>" />
			<input type="submit" id="sds_theme_options_logo_attach" class="button-primary sds-theme-options-upload" name="sds_theme_options_logo_attach"  value="<?php esc_attr_e( 'Choose Logo', 'baton' ); ?>" data-media-title="Choose A Logo" data-media-button-text="Use As Logo" />
			<?php submit_button( __( 'Remove Logo', 'baton' ), array( 'secondary', 'button-remove-logo' ), 'sds_theme_options[remove-logo]', false, ( ! isset( $sds_theme_options['logo_attachment_id'] ) || empty( $sds_theme_options['logo_attachment_id'] ) ) ? array( 'disabled' => 'disabled', 'data-init-empty' => 'true' ) : false ); ?>
		</div>
	<?php
	}
}