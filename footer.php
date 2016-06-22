<?php

	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;


global $sds_theme_options, $sidebars_widgets;
?>
		</div>
		<!-- End Content Wrapper -->

		<div class="clear"></div>

		<!-- Footer -->
		<footer id="footer" class="cf">
			<div class="in footer-widgets-in">
				<!-- Footer Sidebar -->
				<aside class="footer-widgets baton-flex baton-flex-3-columns baton-flex-footer-widgets <?php echo ( is_active_sidebar( 'footer-sidebar' ) && isset( $sidebars_widgets['footer-sidebar'] ) ) ? 'widgets-' . count( $sidebars_widgets['footer-sidebar'] ) : false; ?> <?php echo ( is_active_sidebar( 'footer-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
					<?php sds_footer_sidebar(); ?>
				</aside>
				<!-- End Footer Sidebar -->
			</div>

			<div class="clear"></div>

			<div class="in copyright-area-widgets-in">
				<!-- Copyright Area Sidebar -->
				<aside class="copyright-area copyright-area-widgets cf <?php echo ( is_active_sidebar( 'copyright-area-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
					<?php sds_copyright_area_sidebar(); ?>
				</aside>
				<!-- End Copyright Area Sidebar -->

				<!-- Copyright Wrap -->
				<div class="copyright-wrap baton-flex baton-flex-2-columns baton-flex-copyright">
					<!-- Copyright -->
					<div class="copyright baton-col baton-col-copyright">
						<!-- Copyright Message -->
						<p class="copyright-message">
							<?php sds_copyright( __( 'Baton', 'baton') ); ?>
						</p>
						<!-- End Copyright Message -->

						<!-- Footer Navigation -->
						<nav class="footer-nav">
							<?php
								// Footer Navigation Area
								if ( has_nav_menu( 'footer_nav' ) )
									wp_nav_menu( array(
										'theme_location' => 'footer_nav',
										'container' => false,
										'menu_class' => 'footer-nav menu',
										'menu_id' => 'footer-nav',
									) );
							?>
						</nav>
						<!-- End Footer Navigation -->
					</div>
					<!-- End Copyright -->

					<!-- Social Media -->
					<div class="social-media-footer baton-col baton-col-social-media-footer">
						<?php sds_social_media(); ?>
					</div>
					<!-- End Social Media -->
				</div>
				<!-- End Copyright Wrap -->
			</div>

			<div class="clear"></div>
		</footer>
		<!-- End Footer -->

		<div class="clear"></div>

		<?php wp_footer(); ?>
	</body>
</html>