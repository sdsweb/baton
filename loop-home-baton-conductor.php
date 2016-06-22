<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;

	global $wp_query;
?>

<?php
	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			// Grab the current post index
			$post_index = ( $wp_query->current_post + 1 );

			// If Baton Conductor display is enhanced
			if ( baton_is_baton_conductor_display_enhanced() ) :

				// Switch based on current post index
				switch ( $post_index ) :
					// First Post
					case 1:
						get_template_part( 'conductor/baton/loop', 'enhanced-first-post-before' ); // Baton Conductor - Loop - Enhanced First Post Before
						get_template_part( 'conductor/baton/loop', 'enhanced-first-post' ); // Baton Conductor - Loop - Enhanced First Post
					break;

					// Second Post
					case 2:
						get_template_part( 'conductor/baton/loop', 'enhanced-second-post-before' ); // Baton Conductor - Loop - Enhanced Second Post Before
						get_template_part( 'conductor/baton/loop', 'enhanced-second-post' ); // Baton Conductor - Loop - Enhanced Second Post
					break;

					// Third Post
					case 3:
						get_template_part( 'conductor/baton/loop', 'enhanced-third-post' ); // Baton Conductor - Loop - Enhanced Third Post
						get_template_part( 'conductor/baton/loop', 'enhanced-third-post-after' ); // Baton Conductor - Loop - Enhanced Third Post After
					break;

					// Fourth Post
					case 4:
						get_template_part( 'conductor/baton/loop', 'enhanced-fourth-post' ); // Baton Conductor - Loop - Enhanced Fourth Post
						get_template_part( 'conductor/baton/loop', 'enhanced-fourth-post-after' ); // Baton Conductor - Loop - Enhanced Fourth Post After
					break;

					// Default (all other posts)
					default:
						// Fifth Post
						if ( $post_index === 5 ) :
							get_template_part( 'conductor/baton/loop', 'conductor-before' ); // Baton Conductor - Loop - Conductor Before
							get_template_part( 'conductor/baton/loop', 'conductor-title' ); // Baton Conductor - Loop - Conductor Title
						endif;

						get_template_part( 'conductor/baton/loop', 'conductor' ); // Baton Conductor - Loop - Conductor

						// Last Post
						if ( $post_index === $wp_query->post_count ) :
							get_template_part( 'conductor/baton/loop', 'conductor-pagination' ); // Baton Conductor - Loop - Conductor Pagination
							get_template_part( 'conductor/baton/loop', 'conductor-after' ); // Baton Conductor - Loop - Conductor After
						endif;
					break;
				endswitch;
			// Otherwise use the normal Baton Conductor display
			else:
				// First Post
				if ( $post_index === 1 ) :
					get_template_part( 'conductor/baton/loop', 'conductor-before' ); // Baton Conductor - Loop - Conductor Before
					get_template_part( 'conductor/baton/loop', 'conductor-title' ); // Baton Conductor - Loop - Conductor Title
				endif;

				get_template_part( 'conductor/baton/loop', 'conductor' ); // Baton Conductor - Loop - Conductor

				// Last Post
				if ( $post_index === $wp_query->post_count ) :
					get_template_part( 'conductor/baton/loop', 'conductor-pagination' ); // Baton Conductor - Loop - Conductor Pagination
					get_template_part( 'conductor/baton/loop', 'conductor-after' ); // Baton Conductor - Loop - Conductor After
				endif;
			endif;
		endwhile;
	else: // No Posts
?>
	<div class="in front-page-in baton-flex baton-flex-front-page">
		<!-- Home/Blog Content -->
		<div class="baton-col baton-col-content">
			<section class="content-container content-home-container content-blog-container">
				<!-- Article (No Posts) -->
				<article class="content no-posts no-home-posts content-home cf">
					<!-- Article Content -->
					<div class="article-content cf">
						<?php sds_no_posts(); ?>

						<div class="clear"></div>
					</div>
					<!-- End Article Content -->
				</article>
				<!-- End Article (No Posts) -->

				<div class="clear"></div>
			</section>
		</div>
		<!-- End Home/Blog Content -->

		<!-- Primary Sidebar -->
		<?php get_sidebar(); ?>
		<!-- End Primary Sidebar -->
	</div>
<?php
	endif;
?>

