<?php
	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();

	// If we have a title
	if ( ! empty( $baton_conductor->baton_conductor_theme_mod['title'] ) ) :
		do_action( 'baton_conductor_title_before', $baton_conductor_instance,  $baton_conductor );
?>
		<h3 class="widgettitle widget-title conductor-widget-title baton-baton-conductor-title">
			<?php echo $baton_conductor_instance['title']; ?>
		</h3>
<?php
		do_action( 'baton_conductor_title_after', $baton_conductor_instance, $baton_conductor );
	endif;