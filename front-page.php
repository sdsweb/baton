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
				<?php if ( sds_is_front_page_sidebar_active() ) : // Front Page Sidebar ?>
					<!-- Front Page Sidebar -->
					<aside class="front-page-widgets <?php echo ( sds_is_front_page_sidebar_active() ) ? 'widgets' : 'no-widgets'; ?>">
						<?php dynamic_sidebar( 'front-page-sidebar' ); ?>
					</aside>
					<!-- End Front Page Sidebar -->
				<?php else: // Default Widgets ?>
					<!-- Front Page Sidebar (Default Widgets) -->
					<aside class="front-page-widgets no-widgets default-widgets">
						<?php
							// Default Widgets
							if ( baton_is_demo_content_enabled() )
								baton_default_widgets();
						?>
						<?php do_action( 'dynamic_sidebar_after', 'front-page-sidebar', true ); ?>
					</aside>
					<!-- End Front Page Sidebar (Default Widgets) -->
				<?php endif; ?>

				<?php
					// If Baton Conductor is disabled
					if ( ! baton_is_baton_conductor_enabled() ) :
				?>
					<div class="in front-page-in baton-flex baton-flex-front-page <?php echo ( sds_is_front_page_sidebar_active() || baton_is_demo_content_enabled() ) ? 'has-front-page-sidebar' : 'no-front-page-sidebar'; ?>">
						<?php
							// If the Front Page is active
							if ( baton_has_static_front_page() ) :
						?>
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
						<?php
							// Otherwise, no Front Page is selected, show posts
							else:
						?>
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
						<?php
							endif;
						?>

						<!-- Primary Sidebar -->
						<?php get_sidebar(); ?>
						<!-- End Primary Sidebar -->
					</div>
				<?php
					endif;
				?>

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>