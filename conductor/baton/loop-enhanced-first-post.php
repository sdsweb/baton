<?php
	global $post;

	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// Grab the Baton Conductor Query instance
	$baton_conductor_query = Baton_Conductor_Query();


	/*
	 * Featured Image
	 */
	$featured_image_style = '';

	// Grab the featured image priority
	$featured_image_priority = $baton_conductor_instance['output_elements']['featured_image'];

	// Grab the featured image output element
	$featured_image_output_element = $baton_conductor_instance['output'][$featured_image_priority];

	// If the featured image is visible
	if ( $featured_image_output_element['visible'] && has_post_thumbnail() ) {
		$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

		// Add the background image CSS
		$featured_image_style .= 'background-image: url(' . esc_url( $featured_image[0] ) . ');';
	}
?>

<div id="baton-baton-conductor-enhanced-hero-1" class="baton-hero-widget widget baton-baton-conductor-enhanced" style="<?php echo esc_attr( $featured_image_style ); ?>">
	<div class="in baton-baton-conductor-enhanced-in cf">
		<div class="baton-baton-conductor-enhanced-wrapper baton-hero-1 baton-baton-conductor-enhanced-baton-hero-1 baton-hero baton-baton-conductor-enhanced-baton-hero">
			<div class="in baton-baton-conductor-enhanced-wrapper-in cf">
				<div class="note-content note-content-wrap baton-baton-conductor-enhanced-content baton-baton-conductor-enhanced-content-wrap">
					<?php
						/*
						 * Post Title
						 */

						// Grab the post title priority
						$post_title_priority = $baton_conductor_instance['output_elements']['post_title'];

						// Grab the post title output element
						$post_title_output_element = $baton_conductor_instance['output'][$post_title_priority];

						// Display the post title
						$baton_conductor->display_output_element( $post_title_output_element, $baton_conductor_query, array( $post, $baton_conductor_instance, $baton_conductor, $baton_conductor_query ) );
					?>

					<?php
						/*
						 * Post Content
						 */

						// Grab the post content priority
						$post_content_priority = $baton_conductor_instance['output_elements']['post_content'];

						// Grab the post content output element
						$post_content_output_element = $baton_conductor_instance['output'][$post_content_priority];

						// Display the post content
						$baton_conductor->display_output_element( $post_content_output_element, $baton_conductor_query, array( $post, $baton_conductor_instance, $baton_conductor, $baton_conductor_query ) );
					?>

					<br />

					<?php
						/*
						 * Read More
						 */

						// Grab the read more priority
						$read_more_priority = $baton_conductor_instance['output_elements']['read_more'];

						// Grab the read more output element
						$read_more_output_element = $baton_conductor_instance['output'][$read_more_priority];

						// Display the read more
						$baton_conductor->display_output_element( $read_more_output_element, $baton_conductor_query, array( $post, $baton_conductor_instance, $baton_conductor, $baton_conductor_query ) );
					?>
				</div>
			</div>
		</div>
	</div>
</div>