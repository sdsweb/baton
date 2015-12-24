<?php

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;


/**
 * SDS Theme Options
 *
 * Description: This Class instantiates SDS Options, providing themes with various options to use.
 *
 * @version 1.4.2
 */
if ( ! class_exists( 'SDS_Theme_Options' ) ) {
	global $sds_theme_options;

	class SDS_Theme_Options {
		/**
		 * @var string, Constant, Version of the class
		 */
		const VERSION = '1.4.2';


		// Private Variables

		/**
		 * @var SDS_Theme_Options, Instance of the class
		 */
		private static $instance; // Keep track of the instance


		// Public Variables

		/**
		 * @var string, Option name
		 */
		public static $option_name = 'sds_theme_options';

		/**
		 * @var array, Array of option defaults
		 */
		public $option_defaults = array();

		/**
		 * @var WP_Theme, Current theme object
		 */
		public $theme;

		/*
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new SDS_Theme_Options;

			return self::$instance;
		}

		/**
		 * This function sets up all of the actions and filters on instance as well as properties/data.
		 */
		function __construct() {
			// Setup properties
			$this->option_defaults = $this->get_sds_theme_option_defaults();
			$this->theme = $this->get_parent_theme();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); // Enqueue CSS/JS
			add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // Register Menu Item
		}

		/**
		 * This function enqueues our theme options stylesheet, WordPress media upload scripts, and our custom upload script only on our options page in admin.
		 */
		function admin_enqueue_scripts( $hook ) {
			// SDS Theme Options CSS
			wp_enqueue_style( 'sds-theme-options', SDS_Theme_Options::sds_core_url() . '/css/sds-theme-options.css', false, self::VERSION );

			// SDS Theme Options JS
			wp_enqueue_script( 'sds-theme-options', get_template_directory_uri() . '/includes/js/sds-theme-options.js', array( 'jquery' ), self::VERSION );

			// About Page
			if ( $hook === 'appearance_page_about-baton' )
				// Font Awesome
				wp_enqueue_style( 'font-awesome-css-min', SDS_Theme_Options::sds_core_url() . '/css/font-awesome.min.css' );
		}

		/**
		 * This function adds a menu item under "Appearance" in the Dashboard.
		 */
		function admin_menu() {
			// About
			add_theme_page( sprintf( __( 'About %1$s', 'baton' ), $this->theme->get( 'Name' ) ), sprintf( __( 'About %1$s', 'baton' ), $this->theme->get( 'Name' ) ), 'edit_theme_options', 'about-baton', array( $this, 'sds_about_theme_page' ) );
		}

		/**
		 * This function handles the rendering of the about page.
		 */
		function sds_about_theme_page() {
		?>
			<div class="wrap about-wrap">
				<h1><?php printf( __( 'Welcome to %1$s', 'baton' ), $this->theme->get( 'Name' ) ); ?></h1>
				<div class="about-text sds-about-text"><?php printf( __( 'Learn more about %1$s on this page.', 'baton' ), $this->theme->get( 'Name' ) ); ?></div>

				<h3 class="nav-tab-wrapper sds-theme-options-nav-tab-wrapper sds-theme-options-tab-wrap">
					<a href="#getting-started" id="getting-started-tab" class="nav-tab sds-theme-options-tab nav-tab-active"><?php _e( 'Getting Started', 'baton' ); ?></a>
					<a href="#free-vs-pro" id="free-vs-pro-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Free vs. Pro', 'baton' ); ?></a>
					<?php do_action( 'sds_about_page_navigation_tabs' ); // Hook for extending tabs ?>
				</h3>

				<div id="sds-about-page">
					<?php
					/*
					 * Getting Started
					 */
					?>
					<div id="getting-started-tab-content" class="sds-theme-options-tab-content sds-theme-options-tab-content-active">
						<div class="sds-about-page-section">
							<h3><?php printf( __( 'Welcome to %1$s', 'baton' ), $this->theme->get( 'Name' ) ); ?></h3>
							<p><?php _e( 'Thanks for choosing Slocum Themes. We\'ve created this page to help guide you through setting up your website. We hope that you enjoy our themes.', 'baton' ); ?></p>
						</div>

						<div class="sds-about-page-section">
							<h3><?php _e( 'Get Started in the Customizer', 'baton' ); ?></h3>
							<p><?php printf( __( '%1$s utilizes the WordPress Customizer. This means that you can customize everything in one place! Visit the <a href="%2$s">Customizer</a> to get started.', 'baton' ), $this->theme->get( 'Name' ), esc_url( wp_customize_url() ) ); ?></p>
							<p>
								<a href="<?php echo esc_url( wp_customize_url() ); ?>" class="button button-primary"><?php _e( 'Launch Customizer', 'baton' ); ?></a>
							</p>
						</div>

						<div class="sds-about-page-section">
							<h3><?php _e( 'General Documentation', 'baton' ); ?></h3>
							<p><?php printf( __( 'Be sure to have a look at our <a href="%1$s" target="_blank">General Documentation</a> for helpful guides.', 'baton' ), esc_url( 'https://slocumthemes.com/docs/section/general/' ) ); ?></p>
						</div>

						<div class="sds-about-page-section">
							<h3><?php _e( 'Build the Perfect WordPress Website Course', 'baton' ); ?></h3>
							<p><?php _e( 'We created free a 9-part course that will help you build the perfect WordPress website. We cover topics such as: security, lead generation, backups, and more.', 'baton' ); ?></p>
							<p>
							<a href="<?php echo esc_url( 'https://slocumthemes.com/freecourse' ); ?>" class="button button-primary" target="_blank"><?php _e( 'Enroll in the free course', 'baton' ); ?></a>
						</div>

						<?php do_action( 'sds_about_page_getting_started' ); ?>
					</div>

					<?php
					/*
					 * Free vs. Pro
					 */
					?>
					<div id="free-vs-pro-tab-content" class="sds-theme-options-tab-content">
						<div class="sds-about-page-section">
							<h3><?php _e( 'Free vs. Pro', 'baton' ); ?></h3>
							<p><?php printf( __( 'Use the table below to determine if the Pro version of %1$s is right for you.', 'baton' ), $this->theme->get( 'Name' ) ); ?></p>

							<table class="sds-about-page-table free-vs-pro-table">
								<tr>
									<th></th>
									<th><?php echo $this->theme->get( 'Name' ); ?></th>
									<th><?php printf( __( '%1$s Pro', 'baton' ), $this->theme->get( 'Name' ) ); ?></th>
								</tr>

								<?php
									/*
									 * Color Schemes
									 */

									// Count number of defined color schemes (ignoring "default")
									$sds_color_schemes_count = ( function_exists( 'sds_color_schemes' ) ) ? count( sds_color_schemes() ) : 0;

									// Free color scheme count
									$free_color_schemes_count = apply_filters( 'sds_about_page_free_color_schemes_count', $sds_color_schemes_count );

									// Pro color scheme count
									$pro_color_schemes_count = apply_filters( 'sds_about_page_pro_color_schemes_count', 0 );
								?>

								<?php if ( function_exists( 'sds_color_schemes' ) || $pro_color_schemes_count > 0 ) : ?>
									<tr>
										<td class="sds-about-page-free-vs-pro-component">
											<h4><?php _e( 'Color Schemes', 'baton' ); ?></h4>
											<p>
												<?php
													// Free and Pro color schemes
													if ( $free_color_schemes_count > 0 && $pro_color_schemes_count > 0 )
														printf( __( 'Both %1$s and %1$s Pro have color schemes.', 'baton' ), $this->theme->get( 'Name' ) );
													// Pro color schemes only
													else if ( $free_color_schemes_count === 0 && $pro_color_schemes_count > 0 )
														printf( __( '%1$s Pro has color schemes.', 'baton' ), $this->theme->get( 'Name' ) );
													// Free color schemes only
													else if ( $free_color_schemes_count > 0 && $pro_color_schemes_count === 0 )
														printf( __( '%1$s has color schemes.', 'baton' ), $this->theme->get( 'Name' ) );

													// Free vs Pro color scheme count
													if ( $free_color_schemes_count < $pro_color_schemes_count )
														printf( __( ' %1$s Pro offers more color schemes for you to choose from.', 'baton' ), $this->theme->get( 'Name' ) );
												?>
											</p>
										</td>
										<td class="sds-about-page-free-vs-pro-free-component">
											<span class="fa <?php echo ( $free_color_schemes_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $free_color_schemes_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Color Scheme', '%1$s Color Schemes', $free_color_schemes_count, 'baton' ), $free_color_schemes_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Color Schemes', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
										<td class="sds-about-page-free-vs-pro-pro-component">
											<span class="fa <?php echo ( $pro_color_schemes_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $pro_color_schemes_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Color Scheme', '%1$s Color Schemes', $pro_color_schemes_count, 'baton' ), $pro_color_schemes_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Color Schemes', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>

								<?php
									/*
									 * Web Fonts
									 */

									// Count number of defined web fonts
									$sds_web_fonts_count = ( function_exists( 'sds_web_fonts' ) ) ? count( sds_web_fonts() ) : 0;

									// Free web font count
									$free_web_fonts_count = apply_filters( 'sds_about_page_free_web_fonts_count', $sds_web_fonts_count );

									// Pro web font count
									$pro_web_fonts_count = apply_filters( 'sds_about_page_pro_web_fonts_count', 0 );
								?>

								<?php if ( function_exists( 'sds_web_fonts' ) || $pro_web_fonts_count > 0 ) : ?>
									<tr>
										<td class="sds-about-page-free-vs-pro-component">
											<h4><?php _e( 'Web Fonts', 'baton' ); ?></h4>
											<p>
												<?php
													// Free and Pro web fonts
													if ( $free_web_fonts_count > 0 && $pro_web_fonts_count > 0 )
														printf( __( 'Both %1$s and %1$s Pro have web fonts.', 'baton' ), $this->theme->get( 'Name' ) );
													// Pro web fonts only
													else if ( $free_web_fonts_count === 0 && $pro_web_fonts_count > 0 )
														printf( __( '%1$s Pro has web fonts.', 'baton' ), $this->theme->get( 'Name' ) );
													// Free web fonts only
													else if ( $free_web_fonts_count > 0 && $pro_web_fonts_count === 0 )
														printf( __( '%1$s has web fonts.', 'baton' ), $this->theme->get( 'Name' ) );

													// Free vs Pro web font count
													if ( $free_web_fonts_count < $pro_web_fonts_count )
														printf( __( ' %1$s Pro offers more web fonts for you to choose from.', 'baton' ), $this->theme->get( 'Name' ) );
												?>
											</p>
										</td>
										<td class="sds-about-page-free-vs-pro-free-component">
											<span class="fa <?php echo ( $free_web_fonts_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $free_web_fonts_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Web Font', '%1$s Web Fonts', $free_web_fonts_count, 'baton' ), $free_web_fonts_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Web Fonts', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
										<td class="sds-about-page-free-vs-pro-pro-component">
											<span class="fa <?php echo ( $pro_web_fonts_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $pro_web_fonts_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Web Font', '%1$s Web Fonts', $pro_web_fonts_count, 'baton' ), $pro_web_fonts_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Web Fonts', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>

								<?php
									/*
									 * Content Layouts
									 */

									// Count number of defined content layouts (ignoring "default")
									$sds_content_layouts_count = ( function_exists( 'sds_content_layouts' ) ) ? ( count( sds_content_layouts() ) - 1 ) : 0;

									// Free content layout count
									$free_content_layouts_count = apply_filters( 'sds_about_page_free_content_layouts_count', $sds_content_layouts_count );

									// Pro content layout count
									$pro_content_layouts_count = apply_filters( 'sds_about_page_pro_content_layouts_count', 0 );
								?>

								<?php if ( function_exists( 'sds_content_layouts' ) || $pro_content_layouts_count > 0 ) : ?>
									<tr>
										<td class="sds-about-page-free-vs-pro-component">
											<h4><?php _e( 'Content Layouts', 'baton' ); ?></h4>
											<p>
												<?php
													// Free and Pro content layouts
													if ( $free_content_layouts_count > 0 && $pro_content_layouts_count > 0 )
														printf( __( 'Both %1$s and %1$s Pro have content layouts.', 'baton' ), $this->theme->get( 'Name' ) );
													// Pro content layouts only
													else if ( $free_content_layouts_count === 0 && $pro_content_layouts_count > 0 )
														printf( __( '%1$s Pro has content layouts.', 'baton' ), $this->theme->get( 'Name' ) );
													// Free content layouts only
													else if ( $free_content_layouts_count > 0 && $pro_content_layouts_count === 0 )
														printf( __( '%1$s has content layouts.', 'baton' ), $this->theme->get( 'Name' ) );

													// Free vs Pro content layout count
													if ( $free_content_layouts_count < $pro_content_layouts_count )
														printf( __( ' %1$s Pro offers more content layouts for you to choose from.', 'baton' ), $this->theme->get( 'Name' ) );
												?>
											</p>
										</td>
										<td class="sds-about-page-free-vs-pro-free-component">
											<span class="fa <?php echo ( $free_content_layouts_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $free_content_layouts_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Content Layout', '%1$s Content Layouts', $free_content_layouts_count, 'baton' ), $free_content_layouts_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Content Layouts', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
										<td class="sds-about-page-free-vs-pro-pro-component">
											<span class="fa <?php echo ( $pro_content_layouts_count > 0 ) ? 'fa-check' : 'fa-times'; ?>"></span>

											<?php if ( $pro_content_layouts_count > 0 ) : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php printf( _n( '%1$s Content Layout', '%1$s Content Layouts', $pro_content_layouts_count, 'baton' ), $pro_content_layouts_count ); ?></span>
											<?php else : ?>
												<br />
												<span class="sds-about-page-free-vs-pro-desc"><?php _e( 'No Content Layouts', 'baton' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>

								<?php
									/*
									 * Priority Support
									 */
								?>
								<tr>
									<td class="sds-about-page-free-vs-pro-component">
										<h4><?php _e( 'Priority Support', 'baton' ); ?></h4>
										<p><?php printf( __( 'Get priority helpdesk support by upgrading to %1$s Pro.', 'baton' ), $this->theme->get( 'Name' ) ); ?></p>
									</td>
									<td class="sds-about-page-free-vs-pro-free-component">
										<span class="fa fa-times"></span>
									</td>
									<td class="sds-about-page-free-vs-pro-pro-component">
										<span class="fa fa-check"></span>
									</td>
								</tr>

								<?php
									/*
									 * Footer Copyright & Branding
									 */
								?>
								<tr>
									<td class="sds-about-page-free-vs-pro-component">
										<h4><?php _e( 'Footer Copyright &amp; Branding', 'baton' ); ?></h4>
										<p><?php _e( 'Adjust footer copyright &amp branding messages in the Customizer.', 'baton' ); ?></p>
									</td>
									<td class="sds-about-page-free-vs-pro-free-component">
										<span class="fa fa-times"></span>
									</td>
									<td class="sds-about-page-free-vs-pro-pro-component">
										<span class="fa fa-check"></span>
									</td>
								</tr>

								<?php
									/*
									 * Custom Scripts and Styles
									 */
								?>
								<tr>
									<td class="sds-about-page-free-vs-pro-component">
										<h4><?php _e( 'Custom Scripts and Styles', 'baton' ); ?></h4>
										<p><?php _e( 'Add custom scripts and custom CSS styles to your site.', 'baton' ); ?></p>
									</td>
									<td class="sds-about-page-free-vs-pro-free-component">
										<span class="fa fa-times"></span>
									</td>
									<td class="sds-about-page-free-vs-pro-pro-component">
										<span class="fa fa-check"></span>
									</td>
								</tr>

								<?php do_action( 'sds_about_page_free_vs_pro_table' ); ?>
							</table>
						</div>

						<div class="sds-about-page-section sds-about-page-section-free-vs-pro-upgrade sds-about-page-section-center">
							<p>
								<a href="<?php echo esc_url( ( function_exists( 'sds_get_pro_link' ) ) ? sds_get_pro_link( 'free-vs-pro-upgrade' ) : 'https://slocumthemes.com/' ); ?>" class="button button-primary" target="_blank"><?php printf( __( 'Upgrade to %1$s Pro!', 'baton' ), $this->theme->get( 'Name' ) ); ?></a>
							</p>
						</div>

						<?php do_action( 'sds_about_page_free_vs_pro' ); ?>
					</div>

					<?php do_action( 'sds_about_page_content' ); // Hook for extending content ?>
				</div>

				<div id="sds-theme-options-ads" class="sidebar">
					<?php do_action( 'sds_theme_options_ads' ); ?>

					<div class="sds-theme-options-ad">
						<div class="slocum-themes">
							<?php printf( __( 'Brought to you by <a href="%1$s" target="_blank">Slocum Themes</a>', 'baton' ), 'http://slocumthemes.com/' ); ?>
						</div>
					</div>
				</div>
			</div>
		<?php
		}

		/**
		 * This function sanitizes input from the user when saving options.
		 */
		function sds_theme_options_sanitize( $input ) {
			// Reset to Defaults
			// TODO: Allow for reset in the Customizer?
			if ( isset( $input['reset'] ) )
				return $this->get_sds_theme_option_defaults();

			// Remove Logo
			if ( isset( $input['remove-logo'] ) ) {
				unset( $input['remove-logo'] ); // We don't want to store this value in the options array

				$input['logo_attachment_id'] = false;
			}

			// Parse arguments, replacing defaults with user input
			$input = wp_parse_args( $input, $this->get_sds_theme_option_defaults() );

			// General
			$input['logo_attachment_id'] = ( ! empty( $input['logo_attachment_id'] ) ) ? ( int ) $input['logo_attachment_id'] : '';
			$input['color_scheme'] = sanitize_text_field( $input['color_scheme'] );
			$input['web_font'] = ( ! empty( $input['web_font'] ) && $input['web_font'] !== 'default' ) ? sanitize_text_field( $input['web_font'] ) : false;
			$input['hide_tagline'] = ( $input['hide_tagline'] ) ? true : false;

			// Color Scheme (remove content/background colors if they match another color scheme's default values)
			if ( ! empty( $input['color_scheme'] ) ) {
				// Get color schemes
				$color_schemes = ( function_exists( 'sds_color_schemes' ) ) ? sds_color_schemes() : array();

				if ( ! empty( $color_schemes ) ) {
					unset( $color_schemes[$input['color_scheme']]); // Remove current color scheme

					// Get current theme mods
					$theme_mod_content_color = get_theme_mod( 'content_color' );
					$theme_mod_background_color = get_theme_mod( 'background_color' );

					// Loop through color schemes
					foreach( $color_schemes as $color_scheme_id => $color_scheme ) {
						// Check to see if the current content color theme mod matches this color scheme's default value
						if ( $color_scheme['content_color'] === $theme_mod_content_color )
							remove_theme_mod( 'content_color' );

						// Check to see if the current background color theme mod matches this color scheme's default value
						if ( isset( $color_scheme['background_color'] ) && ltrim( $color_scheme['background_color'], '#' ) === $theme_mod_background_color )
							remove_theme_mod( 'background_color' );
					}
				}
			}

			// Content Layouts
			foreach ( $input['content_layouts'] as $key => &$value )
				$value = ( $value !== 'default' ) ? sanitize_text_field( $value ) : false;

			// Social Media
			foreach ( $input['social_media'] as $key => &$value ) {
				// RSS Feed (use site feed)
				if ( $key === 'rss_url_use_site_feed' && $value ) {
					$value = true;

					$input['social_media']['rss_url'] = '';
				}
				else
					$value = esc_url( $value );
			}

			// Ensure the 'rss_url_use_site_feed' key is set in social media
			if ( ! isset( $input['social_media']['rss_url_use_site_feed'] ) )
				$input['social_media']['rss_url_use_site_feed'] = false;


			return $input;
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns default values for SDS Theme Options
		 */
		public static function get_sds_theme_option_defaults() {
			$defaults = array(
				// General
				'logo_attachment_id' => false,
				'color_scheme' => false,
				'hide_tagline' => false,
				'web_font' => false,

				// Content Layouts
				'content_layouts' => array(
					'global' => false,
					'front_page'=> false,
					'home' => false,
					'single' => false,
					'page' => false,
					'archive' => false,
					'category' => false,
					'tag' => false,
					'404' => false
				),

				// Social Media
				'social_media' => array(
					'facebook_url' => '',
					'twitter_url' => '',
					'linkedin_url' => '',
					'google_plus_url' => '',
					'youtube_url' => '',
					'vimeo_url' => '',
					'instagram_url' => '',
					'pinterest_url' => '',
					'flickr_url' => '',
					//'yelp_url' => '',
					'foursquare_url' => '',
					'rss_url' => '',
					'rss_url_use_site_feed' => false
				)
			);

			return apply_filters( 'sds_theme_options_defaults', $defaults );
		}

		/**
		 * This function returns a formatted list of Google Web Font families for use when enqueuing styles.
		 */
		function get_google_font_families_list() {
			if ( function_exists( 'sds_web_fonts' ) ) {
				$web_fonts = sds_web_fonts();
				$web_fonts_count = count( $web_fonts );
				$google_families = '';

				if ( ! empty( $web_fonts ) && is_array( $web_fonts ) ) {
					foreach( $web_fonts as $name => $atts ) {
						// Google Font Name
						$google_families .= $name;

						if ( $web_fonts_count > 1 )
							$google_families .= '|';
					}

					// Trim last | when multiple fonts are set
					if ( $web_fonts_count > 1 )
						$google_families = substr( $google_families, 0, -1 );
				}

				return $google_families;
			}

			return false;
		}

		/**
		 * This function returns the details of the current parent theme.
		 */
		public function get_parent_theme() {
			if ( is_a( $this->theme, 'WP_Theme' ) )
				return $this->theme;

			return ( is_child_theme() ) ? wp_get_theme()->parent() : wp_get_theme();
		}


		/********************
		 * Helper Functions *
		 ********************/

		/**
		 * This function returns the current option values.
		 */
		public static function get_sds_theme_options() {
			global $sds_theme_options;

			$sds_theme_options = wp_parse_args( get_option( self::$option_name ), SDS_Theme_Options::get_sds_theme_option_defaults() );

			return $sds_theme_options;
		}

		/**
		 * This function returns the current option name.
		 */
		public static function get_option_name() {
			return self::$option_name;
		}

		/**
		 * This function returns the directory for SDS Core without a trailing slash. A relative directory
		 * can be returned by passing true for the $relative parameter.
		 */
		public static function sds_core_dir( $relative = false ) {
			// Replace backslashes on Windows machines
			$template_dir = str_replace( array( '\\\\', '\\' ), '/', get_template_directory() );
			$file_dir = str_replace( array( '\\\\', '\\' ), '/', dirname( __FILE__ ) );

			return untrailingslashit( ( $relative ) ? str_replace( $template_dir, '', $file_dir ) : $file_dir );
		}

		/**
		 * This function returns the url for SDS Core without a trailing slash.
		 */
		public static function sds_core_url() {
			return untrailingslashit( get_template_directory_uri() . self::sds_core_dir( true ) );
		}
	}


	function SDS_Theme_Options_Instance() {
		return SDS_Theme_Options::instance();
	}

	// Instantiate SDS_Theme_Options
	SDS_Theme_Options_Instance();
}