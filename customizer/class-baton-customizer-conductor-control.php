<?php
/**
 * Baton Customizer Conductor Control
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

if ( ! class_exists( 'Baton_Customize_Conductor_Control' ) ) {
	final class Baton_Customize_Conductor_Control extends WP_Customize_Control {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/*
		 * @var array
		 */
		public $output_features = array();

		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		function __construct( $manager, $id, $args = array() ) {
			// Populate output features
			$this->output_features = array(
				// Featured Image
				'featured_image' => array(
					'link' => true // Enable/disable linking
				),
				// Post Title
				'post_title' => array(
					'link' => true // Enable/disable linking
				),
				// Post Content
				'post_content' => array(
					'edit_content_type' => true // Content type (special case)
				),
				// Read More
				'read_more' => array(
					'link' => true, // Enable/disable linking
					'edit_label' => array( 'default' => baton_more_link_label( true ) ) // Edit label (pass a default)
				)
			);

			// Call the parent constructor here
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * This function enqueues scripts and styles
		 */
		public function enqueue() {
			// Grab the Baton Conductor instance
			$baton_conductor = Baton_Conductor_Instance();

			// Stylesheets
			wp_enqueue_style( 'baton-conductor', get_template_directory_uri() . '/css/baton-conductor.css', array( 'dashicons' ) );

			// Scripts
			wp_enqueue_script( 'baton-conductor', get_template_directory_uri() . '/js/baton-conductor.js', array( 'backbone', 'jquery-ui-sortable' ), $this->version, true );
			wp_localize_script( 'baton-conductor', 'baton_conductor', array(
				'defaults' => $baton_conductor->defaults,
				'customizer' => array(
					'section_sup_label' => _x( 'by Conductor', 'label applied to <sup> tag within Customizer section title for Baton Conductor', 'baton' ),
					'control_enabled_label' => _x( 'Enabled', 'label applied to Customizer control title for Baton Conductor', 'baton' )
				),
				'output' => array(
					'priority_step_size' => 10
				)
			) );

			// Call the parent enqueue method here
			parent::enqueue();
		}

		/**
		 * This function renders the control's content.
		 */
		public function render_content() {
			global $_wp_additional_image_sizes;

			if ( ! empty( $this->description ) ) :
		?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php
			endif;
		?>

		<?php
			// Title
			if ( isset( $this->settings['title'] ) ) :
		?>
			<p class="baton-conductor-widget-setting baton-conductor-widget-title">
				<?php // Widget Title ?>
				<label for="baton_conductor_title"><strong><?php _e( 'Title', 'baton' ); ?></strong></label>
				<br />

				<div class="baton-conductor-widget-title-container">
					<input type="text" class="baton-conductor-input" id="baton_conductor_title" name="baton_conductor[title]" <?php echo esc_attr( $this->settings['title']->value() ); ?>" <?php $this->link( 'title' ); ?> />
				</div>
			</p>
			<br />
		<?php
			endif;
		?>

			<div class="baton-conductor-section baton-conductor-section-display baton-conductor-accordion-section" data-baton-conductor-section="baton-conductor-section-content">
				<div class="baton-conductor-section-title baton-conductor-accordion-section-title">
					<h3><?php _e( 'Content Settings', 'baton' ); ?></h3>
				</div>

				<div class="baton-conductor-section-inner">
					<div class="baton-conductor-accordion-section-content">
						<?php
							// Posts Per Page
							if ( isset( $this->settings['posts_per_page'] ) ) :
						?>
							<div class="baton-conductor-setting baton-conductor-posts-per-page">
								<label for="baton_conductor_posts_per_page">
									<strong>
										<?php _ex( 'Show', 'Beginning of number of posts per page to display label; before <input> element', 'baton' ); ?>
										<input type="text" class="baton-conductor-input baton-conductor-inline-input baton-conductor-number" id="baton_conductor_posts_per_page" name="baton_conductor[posts_per_page]" value="<?php echo esc_attr( $this->settings['posts_per_page']->value() ); ?>" <?php $this->link( 'posts_per_page' ); ?> placeholder="<?php _ex( '#', 'placeholder for number input elements', 'baton' ); ?>" />
										<?php _ex( 'posts per page.', 'End of number of posts per page to display label; after <input> element', 'baton' ); ?>
									</strong>
								</label>
							</div>
						<?php
							endif;

							// Category
							if ( isset( $this->settings['category'] ) ) :
						?>
							<div class="baton-conductor-setting baton-conductor-category">
								<label for="baton_conductor_category"><strong><?php _e( 'Category', 'baton' ); ?></strong></label>
								<br />
								<?php
									// Category dropdown
									$category_dropdown = wp_dropdown_categories( array(
										'name' => 'baton_conductor[category]',
										'selected' => $this->settings['category']->value(),
										'orderby' => 'NAME',
										'hierarchical' => true,
										'show_option_all' => __( 'All Categories', 'baton' ),
										'hide_empty' => false,
										'id' => 'baton_conductor_category',
										'class' => 'baton-conductor-select',
										'echo' => false
									) );

									// Add the Customizer "link" for this field (only replacing once to ensure only the outer most wrapper element gets the adjustment)
									$category_dropdown = preg_replace( '/<select/', '<select ' . $this->get_link( 'category' ) . ' ', $category_dropdown, 1 );

									// Output list of categories
									echo $category_dropdown;
								?>
							</div>
						<?php
							endif;
						?>
					</div>
				</div>
			</div>

			<div class="baton-conductor-section baton-conductor-section-display baton-conductor-accordion-section" data-baton-conductor-section="baton-conductor-section-display">
				<div class="baton-conductor-section-title baton-conductor-accordion-section-title">
					<h3><?php _e( 'Display Settings', 'baton' ); ?></h3>
				</div>

				<div class="baton-conductor-section-inner">
					<div class="baton-conductor-accordion-section-content">
						<?php
							// Flexbox Columns
							if ( isset( $this->settings['flexbox_columns'] ) ) :
						?>
							<p class="baton-conductor-columns baton-conductor-flexbox-columns baton-conductor-customize-columns">
								<label for="baton_conductor_flexbox_columns"><strong><?php _e( 'Number of Columns', 'baton' ); ?></strong></label>
								<br />
								<input type="range" min="1" max="6" class="baton-conductor-input baton-conductor-flexbox-columns-range" id="baton_conductor_flexbox_columns" name="baton_conductor[flexbox_columns]" value="<?php echo esc_attr( $this->settings['flexbox_columns']->value() ); ?>" <?php $this->link( 'flexbox_columns' ); ?> />
								<span class="baton-conductor-flexbox-columns-value"><?php echo $this->settings['flexbox_columns']->value(); ?></span>
								<br />
								<small class="description baton-conductor-description"><?php _e( 'Specify the number of columns used when outputting widget content. Note: When the Enhanced Demo Display <sup>by Conductor</sup> is enabled, this value applies to posts which are output after the enhanced displays.', 'baton' ); ?></small>
							</p>
						<?php
							endif;

							// Output
							if ( isset( $this->settings['output'] ) ) :
								// Grab the value
								$baton_conductor_output = $this->settings['output']->value();
						?>
							<div class="baton-conductor-setting baton-conductor-output">
								<label for="baton_conductor_output"><strong><?php _e( 'Adjust Output Elements', 'baton' ); ?></strong></label>
								<br />
								<ul class="baton-conductor-output-list">
									<?php
										// Will be in order based on priority
										if ( ! empty( $baton_conductor_output ) )
											foreach ( $baton_conductor_output as $priority => $element ) {
												$id = $element['id'];
												$type = $element['type'];

												// Determine the features this element supports (first by id)
												$supports = ( isset( $this->output_features[$id] ) ) ? $this->output_features[$id] : array();

												// Find support by type if not found by id
												if ( empty( $supports ) )
													$supports = ( isset( $this->output_features[$type] ) ) ? $this->output_features[$type] : array();

												// Generate CSS Classes
												$css_classes = array(
													'ui-state-default',
													'baton-conductor-output-element',
													'baton-conductor-output-element-' . $element['id']
												);

												// Visible CSS Class
												if ( isset( $element['visible'] ) && $element['visible'] )
													$css_classes[] = 'visible';

												// Link CSS Class
												if ( $supports && array_key_exists( 'link', $supports ) && isset( $element['link'] ) && $element['link'] )
													$css_classes[] = 'link';

												$output = '<li class="' . implode( ' ', $css_classes ) . '"'; // Start the element
													$output .= ' data-priority="' . esc_attr( $priority ) . '"'; // Priority
													$output .= ' data-id="' . esc_attr( $element['id'] ) . '"'; // ID
													$output .= ' data-label="' . esc_attr( $element['label'] ) . '"'; // Label
													$output .= ' data-type="' . esc_attr( $element['type'] ) . '"'; // Type
													$output .= ( isset( $element['visible'] ) && $element['visible'] ) ? ' data-visible="true"' : ' data-visible="false"'; // Visible
													$output .= ( $supports && array_key_exists( 'link', $supports ) && isset( $element['link'] ) && $element['link'] ) ? ' data-link="true"' : ' data-link="false"'; // Link
													$output .= ( $supports && array_key_exists( 'edit_content_type', $supports ) ) ? ' data-value="' . $element['value'] .'"' : false; // Edit Content Type (special case; content/excerpt only)
												$output .= '>'; // End the element
												$output .= ' <div class="dashicons dashicons-sort"></div>'; // Sort handle

												// Label Editing
												if ( $supports && array_key_exists( 'edit_label', $supports ) ) {
													$output .= ' <span class="baton-conductor-output-element-label baton-conductor-output-element-label-editable editable-input">';
														$output .= '<span class="label">' . $element['label'] . '</span>';
														$output .= ' <div class="dashicons dashicons-edit"></div>';
														$output .= '<div class="baton-conductor-output-element-label-input">';
															$output .= '<input value="';
																$output .= ( ( ! empty( $supports['edit_label']['default'] ) && $element['label'] !== $supports['edit_label']['default'] ) || ( empty( $supports['edit_label']['default'] ) && $element['label'] !== $element['id'] ) ) ? $element['label'] .'"' : '"';
																$output .= 'placeholder="' . __( 'Enter custom label...', 'baton' ) .'"';
																$output .= ( ! empty( $supports['edit_label']['default'] ) ) ? 'data-original="' . $supports['edit_label']['default'] . '"' : 'data-original="' . $element['id'] . '"';
																$output .= ' data-current="" />';
															$output .= '<div class="dashicons dashicons-no-alt baton-conductor-discard"></div>';
															$output .= '<div class="dashicons dashicons-yes baton-conductor-save"></div>';
														$output .= '</div>';
													$output .= '</span>';
												}
												// Content Type Editing (special case)
												else if ( $supports && array_key_exists( 'edit_content_type', $supports ) ) {
													$output .= ' <span class="baton-conductor-output-element-label baton-conductor-output-element-label-editable editable-select">';
														$output .= '<span class="label">' . $element['label'] . '</span>';
														$output .= ' <div class="dashicons dashicons-edit"></div>';
														$output .= '<div class="baton-conductor-output-element-label-input baton-conductor-output-element-label-select">';
															$output .= '<select data-current="';
															$output .= ( isset( $element['value'] ) && ! empty( $element['value'] ) ) ? $element['value'] .'">' : '">';
																$output .= '<option value="content" data-label="' .__( 'Content', 'baton' ) . '"' . selected( $element['value'], 'content', false ) . '>' . __( 'Content', 'baton' ) . '</option>';
																$output .= '<option value="excerpt" data-label="' .__( 'Excerpt', 'baton' ) . '"' . selected( $element['value'], 'excerpt', false ) . '>' . __( 'Excerpt', 'baton' ) . '</option>';
															$output .= '</select>';
															$output .= '<div class="dashicons dashicons-no-alt baton-conductor-discard"></div>';
															$output .= '<div class="dashicons dashicons-yes baton-conductor-save"></div>';
														$output .= '</div>';
													$output .= '</span>';
												}
												// Regular Label
												else
													$output .= ' <span class="baton-conductor-output-element-label">' . $element['label'] . '</span>';


												// Controls
												$output .= ' <span class="baton-conductor-output-element-controls">';
													// Link
													if ( $supports && array_key_exists( 'link', $supports ) )
														$output .= ' <div class="dashicons dashicons-admin-links baton-conductor-link"></div>';

													// Removal
													if ( $supports && array_key_exists( 'remove', $supports ) )
														$output .= ' <div class="dashicons dashicons-no-alt baton-conductor-remove"></div>';

													// Visibility
													$output .= ' <div class="dashicons dashicons-visibility baton-conductor-visibility"></div>';
												$output .= '</span>';
												$output .= '</li>';

												// Output
												echo $output;
											}
									?>
								</ul>

								<?php
									// Remove the callback parameter from the instance (for serialized data in hidden input element)
									$output_without_callbacks = array();

									if ( ! empty( $baton_conductor_output) ) {
										$output_without_callbacks = $baton_conductor_output;

										// Remove the callback parameter
										foreach ( $output_without_callbacks as &$element )
											unset( $element['callback'] );
									}
								?>

								<input type="hidden" class="baton-conductor-input baton-conductor-output-data" id="baton_conductor_output" name="baton_conductor[output]" value="<?php echo esc_attr( json_encode( $output_without_callbacks ) ); ?>" <?php $this->link( 'output' ); ?> />
								<small class="description baton-conductor-description"><?php _e( 'Adjust the order of elements output on the front-end display.', 'baton' ); ?></small>
							</div>
						<?php
							endif;
						?>
					</div>
				</div>
			</div>

			<div class="baton-conductor-section baton-conductor-section-display baton-conductor-accordion-section" data-baton-conductor-section="baton-conductor-section-advanced">
				<div class="baton-conductor-section-title baton-conductor-accordion-section-title">
					<h3><?php _e( 'Advanced Settings', 'baton' ); ?></h3>
				</div>

				<div class="baton-conductor-section-inner">
					<div class="baton-conductor-accordion-section-content">
						<?php
							// Featured Image Size
							if ( isset( $this->settings['post_thumbnails_size'] ) ) :
						?>
							<p class="baton-conductor-post-thumbnails-size">
								<?php // Featured Image Size ?>
								<label for="baton_conductor_post_thumbnails_size"><strong><?php _e( 'Featured Image Size', 'baton' ); ?></strong></label>
								<br />
								<select name="baton_conductor[post_thumbnails_size]" id="baton_conductor_post_thumbnails_size" class="baton-conductor-select" <?php $this->link( 'post_thumbnails_size' ); ?>>
									<option value="" <?php selected( $this->settings['post_thumbnails_size']->value(), '' ); ?> ><?php _e( '&mdash; Select &mdash;', 'baton' ); ?></option>
									<?php
										// Get all of the available image sizes
										$avail_image_sizes = array();

										foreach ( get_intermediate_image_sizes() as $size ) {
											$avail_image_sizes[$size] = array(
												'label' => '',
												'width' => 0,
												'height' => 0
											);
	
											// Built-in Image Sizes
											if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
												$avail_image_sizes[$size]['label'] = $size;
												$avail_image_sizes[$size]['width'] = get_option( $size . '_size_w' );
												$avail_image_sizes[$size]['height'] = get_option( $size . '_size_h' );
											}
											// Additional Image Sizes
											else if ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[$size] ) ) {
												$avail_image_sizes[$size]['label'] = $size;
												$avail_image_sizes[$size]['width'] = $_wp_additional_image_sizes[$size]['width'];
												$avail_image_sizes[$size]['height'] = $_wp_additional_image_sizes[$size]['height'];
											}

											// If width is 0, adjust the value
											if ( $avail_image_sizes[$size]['width'] == 0 )
												$avail_image_sizes[$size]['width'] = 9999;

											// If height is 0, adjust the value
											if ( $avail_image_sizes[$size]['height'] == 0 )
												$avail_image_sizes[$size]['height'] = 9999;
										}
	
										foreach ( $avail_image_sizes as $size => $atts ) :
											$dimensions = array( $atts['width'], $atts['height'] );
									?>
										<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $this->settings['post_thumbnails_size']->value(), $size ); ?>><?php echo $atts['label'] . ' (' . implode( ' x ', $dimensions ) . ')'; ?></option>
									<?php
										endforeach;
									?>
								</select>
								<small class="description baton-conductor-description"><?php _e( 'Adjust the displayed featured image size on the front-end.', 'baton' ); ?></small>
							</p>	
						<?php
							endif;
						?>

						<?php
							// Excerpt Length
							if ( isset( $this->settings['excerpt_length'] ) ) :
						?>
							<div class="baton-conductor-setting baton-conductor-excerpt-length">
								<label for="baton_conductor_excerpt_length">
									<strong>
										<?php _ex( 'Limit excerpt to', 'Beginning of content limit label; before <input> element', 'baton' ); ?>
										<input type="text" class="baton-conductor-input baton-conductor-inline-input baton-conductor-number" id="baton_conductor_excerpt_length" name="baton_conductor[excerpt_length]" value="<?php echo esc_attr( $this->settings['excerpt_length']->value() ); ?>" <?php $this->link( 'excerpt_length' ); ?> placeholder="<?php _ex( '#', 'placeholder for number input elements', 'baton' ); ?>" />
										<?php _ex( 'words.', 'End of content limit label; after <input> element', 'baton' ); ?>
									</strong>
								</label>
								<br />
								<small class="description baton-conductor-description"><?php _e( 'This setting only applies when "Excerpt" is selected for the content display output element.', 'baton' ); ?></small>
							</div>
						<?php
							endif;
						?>
					</div>
				</div>
			</div>
		<?php
		}
	}
}