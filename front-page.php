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
				<?php if ( is_active_sidebar( 'front-page-sidebar' ) ) : // Front Page Sidebar ?>
					<!-- Front Page Sidebar -->
					<aside class="front-page-widgets <?php echo ( is_active_sidebar( 'front-page-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
						<?php dynamic_sidebar( 'front-page-sidebar' ); ?>
					</aside>
					<!-- End Front Page Sidebar -->
				<?php else: ?>
					<!-- Front Page Sidebar (Default Widgets) -->
					<aside class="front-page-widgets no-widgets default-widgets">
						<?php baton_default_widgets(); ?>
						<?php do_action( 'dynamic_sidebar_after', 'front-page-sidebar', true ); ?>
					</aside>
					<!-- End Front Page Sidebar (Default Widgets) -->
				<?php endif; ?>

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>