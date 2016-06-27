<?php
/*
 * This template is used for displaying the Front Page (when selected in Settings > Reading).
 *
 * This template is used even when the option is selected, but a page is not. It contains fallback functionality
 * to ensure content is still displayed.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-page baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<?php
					// If the Front Page is active
					if ( baton_has_static_front_page() ) :
						// Front Page Sidebar
						if ( sds_is_front_page_sidebar_active() ) : // Front Page Sidebar
				?>
						<!-- Front Page Sidebar -->
						<aside class="front-page-widgets <?php echo ( sds_is_front_page_sidebar_active() ) ? 'widgets' : 'no-widgets'; ?>">
							<?php dynamic_sidebar( 'front-page-sidebar' ); ?>
						</aside>
						<!-- End Front Page Sidebar -->
				<?php
						endif;
				?>
						<?php // TODO: Baton conductor on the Front Page? ?>
						<div class="in front-page-in baton-flex baton-flex-front-page <?php echo ( sds_is_front_page_sidebar_active() ) ? 'has-front-page-sidebar' : 'no-front-page-sidebar'; ?>">
							<!-- Page Content -->
							<div class="baton-col baton-col-content">
								<section class="content-container content-page-container">
									<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

									<?php get_template_part( 'loop', 'page' ); // Loop - Page ?>

									<!-- Comments -->
									<?php comments_template(); // Comments ?>
									<!-- End Comments -->

									<div class="clear"></div>
								</section>
							</div>
							<!-- End Page Content -->

							<!-- Primary Sidebar -->
							<?php get_sidebar(); ?>
							<!-- End Primary Sidebar -->
						</div>
				<?php
					// Otherwise no Front Page is selected
					else:
						// If Baton Conductor is enabled
						if ( baton_is_baton_conductor_enabled() ) :
							get_template_part( 'loop', 'home-baton-conductor' ); // Loop - Home Baton Conductor
						// Otherwise, no Baton Conductor is not enabled, show posts
						else:
				?>
								<div class="in front-page-in baton-flex baton-flex-front-page">
									<!-- Home/Blog Content -->
									<div class="baton-col baton-col-content">
										<section class="content-container content-home-container content-blog-container">
											<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

											<?php get_template_part( 'loop', 'home' ); // Loop - Home ?>

											<?php get_template_part( 'loop', 'navigation' ); // Loop - Navigation ?>

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
					endif;
				?>

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>