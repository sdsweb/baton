<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Content_Layout_Control extends WP_Customize_Control {
	/*
	 * @var string
	 */
	public $content_layout_id = '';

	/*
	 * @var string
	 */
	public $custom_field_type = false;

	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Call the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
		global $sds_theme_options;
	?>
		<div class="customize-sds-theme-options-content-layout-wrap customize-sds-theme-options-content-layout-<?php echo esc_attr( $this->content_layout_id ); ?>-wrap">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php
				$content_layouts = ( function_exists( 'sds_content_layouts' ) ) ? sds_content_layouts() : false;

				// If we have content layouts
				if ( ! empty( $content_layouts ) ) :
			?>
				<div class="sds-theme-options-content-layout-wrap">
					<?php foreach( $content_layouts as $name => $atts ) : ?>
						<div class="sds-theme-options-content-layout sds-theme-options-content-layout-<?php echo $name; ?>">
							<label>
								<?php if ( ( ! isset( $sds_theme_options['content_layouts']['global'] ) || empty( $sds_theme_options['content_layouts'][$this->content_layout_id] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No content layout selected, use default ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $this->content_layout_id; ?>]" <?php checked( true ); ?> value="<?php echo $name; ?>" <?php $this->link(); ?> />
								<?php else: ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $this->content_layout_id; ?>]" <?php ( isset( $sds_theme_options['content_layouts'][$this->content_layout_id] ) ) ? checked( $sds_theme_options['content_layouts'][$this->content_layout_id], $name ) : checked( false ); ?> value="<?php echo $name; ?>" <?php $this->link(); ?> />
								<?php endif; ?>

								<div class="sds-theme-options-content-layout-preview">
									<?php
									if ( isset( $atts['preview_values'] ) )
										vprintf( $atts['preview'], $atts['preview_values'] );
									else
										echo $atts['preview'];
									?>
								</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
				<span class="description"><?php  printf( _x( '%1$s', 'Content layout description; describes where the content layout will be applied', 'baton' ), $this->description ); ?></span>
			<?php
				endif;
			?>
		</div>
	<?php
	}
}