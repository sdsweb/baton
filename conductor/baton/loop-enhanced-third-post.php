<?php
	global $post;

	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// Grab the Baton Conductor Query instance
	$baton_conductor_query = Baton_Conductor_Query();
?>

<div class="note-row note-row-1-columns note-flex note-flex-1-columns note-1-columns note-row-1 note-row-odd" data-note-row="1">
	<div class="note-col note-col-1 note-col-odd" data-note-column="1" data-note-editor-id="1">
		<div class="note-content note-content-wrap">
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

			<?php
				/*
				 * Read More
				 */

				// Grab the read more priority
				$read_more_priority = $baton_conductor_instance['output_elements']['read_more'];

				// Grab the read more output element
				$read_more_output_element = $baton_conductor_instance['output'][$read_more_priority];

				// Adjust the callback function
				$read_more_output_element['callback'] = 'baton_conductor_read_more';

				// Display the read more
				$baton_conductor->display_output_element( $read_more_output_element, $baton_conductor_query, array( $post, $baton_conductor_instance, $baton_conductor, $baton_conductor_query ) );
			?>
		</div>
	</div>
</div>