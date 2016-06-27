<?php
	// Grab the Baton Conductor instance
	$baton_conductor = Baton_Conductor_Instance();

	// Grab the Baton Conductor instance (settings)
	$baton_conductor_instance = $baton_conductor->get_baton_conductor_instance();


	// Base CSS classes
	$css_classes = array(
		'in',
		'baton-baton-conductor-in',
		'cf',
		'conductor-widget',
		'conductor-widget-wrap',
		'conductor-row',
		'conductor-widget-row',
		'conductor-flex',
		'conductor-widget-flex'
	);

	// Add the specific column CSS classes
	$css_classes[] = 'conductor-row-' . $baton_conductor_instance['flexbox_columns'] . '-columns';
	$css_classes[] = 'conductor-widget-row-' . $baton_conductor_instance['flexbox_columns'] . '-columns';
	$css_classes[] = 'conductor-flex-' . $baton_conductor_instance['flexbox_columns'] . '-columns';
	$css_classes[] = 'conductor-widget-flex-' . $baton_conductor_instance['flexbox_columns'] . '-columns';
	$css_classes[] = 'conductor-' . $baton_conductor_instance['flexbox_columns'] . '-columns';
	$css_classes[] = 'conductor-widget-' . $baton_conductor_instance['flexbox_columns'] . '-columns';

	// Sanitize CSS classes
	$css_classes = array_filter( $css_classes, 'sanitize_html_class' );
?>

<div id="baton-baton-conductor" class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">