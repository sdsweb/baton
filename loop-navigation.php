<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<footer class="pagination">
	<?php
		the_posts_pagination( array(
			'mid_size' => 2,
			'next_text' => _x( 'Next &#8594;', 'next post', 'baton' ),
			'prev_text' => _x( '&#8592; Previous', 'previous post', 'baton' ),
			'type' => 'list'
		) );
	?>
</footer>