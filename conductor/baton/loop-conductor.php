<?php
	global $post;

	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// Baton Conductor Query
	$baton_conductor_query_args = array(
		'instance' => $baton_conductor_instance,
		'display_content_args_count' => 4 // Current number of arguments on the display_content() function, used in sortable output
	);
	$baton_conductor_query_args = apply_filters( 'baton_conductor_query_args', $baton_conductor_query_args, $baton_conductor_instance, $baton_conductor );

	// Default to Baton Conductor Query
	if ( ! $baton_conductor->baton_conductor_query && ! ( $baton_conductor->baton_conductor_query = apply_filters( 'baton_conductor_query', false, $baton_conductor_query_args, $baton_conductor_instance, $baton_conductor ) ) )
		$baton_conductor->baton_conductor_query = new Baton_Conductor_Query( $baton_conductor_query_args );



	/*
	 * Display Content
	 */
	$baton_conductor->display_content( $post, $baton_conductor_instance );
?>
