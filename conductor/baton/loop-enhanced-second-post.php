<?php
	global $post;

	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// Grab the Baton Conductor Query instance
	$baton_conductor_query = Baton_Conductor_Query();
?>

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
</div>