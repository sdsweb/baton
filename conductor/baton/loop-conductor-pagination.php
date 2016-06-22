<?php
	global $wp_query;

	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// Pagination
	if ( $baton_conductor->baton_conductor_query->has_pagination() ) :
		do_action( 'baton_conductor_pagination_before', $baton_conductor_instance, $wp_query, $baton_conductor );

		get_template_part( 'loop', 'navigation' ); // Loop - Navigation

		do_action( 'baton_conductor_pagination_after', $baton_conductor_instance, $wp_query, $baton_conductor );
	endif;
?>