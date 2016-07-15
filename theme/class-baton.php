<?php
/**
 * Baton - This class manages all functionality with our Baton theme.
 *
 * @class Baton
 * @author Slocum Studio
 * @version 1.0.1
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton' ) ) {
	class Baton {
		/**
		 * @var string, Current version number
		 */
		public $version = '1.0.1';

		/**
		 * @var Baton, Instance of the class
		 */
		private static $instance;

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self();

			return self::$instance;
		}


		/*
		 * This function sets up all of the actions and filters on instantiation.
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 20 ); // Register image sizes
			add_action( 'after_switch_theme', array( $this, 'after_switch_theme' ), 1, 2 ); // Early
			add_action( 'init', array( $this, 'init' ), 20 ); // Init
			add_action( 'widgets_init', array( $this, 'widgets_init' ), 20 ); // Register sidebars
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); // Used to enqueue editor styles based on post type
			add_action( 'wp_head', array( $this, 'wp_head' ), 1 ); // Add <meta> tags to <head> section
			add_action( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 10, 2 ); // Output TinyMCE Setup function
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // Enqueue all stylesheets (Main Stylesheet, Fonts, etc...)
			add_filter( 'the_content_more_link', array( $this, 'the_content_more_link' ) ); // Adjust default more link
			add_filter( 'dynamic_sidebar_params', array( $this, 'dynamic_sidebar_params' ) ); // Dynamic sidebar parameters (Note/Conductor Widgets)
			add_filter( 'edit_post_link', array( $this, 'edit_post_link' ) ); // Adjust CSS class on post edit links
			add_action( 'wp_footer', array( $this, 'wp_footer' ) ); // Responsive navigation functionality

			// Customizer
			add_action( 'customize_register', array( $this, 'customize_register' ), 20 ); // Customize Register
			add_action( 'customize_controls_print_styles', array( $this, 'customize_controls_print_styles' ), 20 ); // Customizer Styles

			// Note
			add_filter( 'note_widget_template_types', array( $this, 'note_widget_template_types' ) ); // Note Widget Template Types
			add_filter( 'note_tinymce_editor_types', array( $this, 'note_tinymce_editor_types' ) ); // Note Widget Editor Types
			add_filter( 'note_tinymce_editor_settings', array( $this, 'note_tinymce_editor_settings' ), 10, 2 ); // Note Widget TinyMCE Editor Settings
			add_filter( 'note_widget_templates', array( $this, 'note_widget_templates' ), 10, 2 ); // Note Widget Templates
			add_filter( 'note_widget_css_classes', array( $this, 'note_widget_css_classes' ), 10, 3 ); // Note Widget CSS Classes
			add_action( 'note_widget_title_before', array( $this, 'note_widget_title_before' ), 10, 3 ); // Note Title Before
			add_action( 'note_widget_template_before', array( $this, 'note_widget_template_before' ), 10, 7 ); // Note Template Before
			add_action( 'note_widget_template_after', array( $this, 'note_widget_template_after' ), 10, 7 ); // Note Template After

			// Conductor
			add_filter( 'conductor_widget_defaults', array( $this, 'conductor_widget_defaults' ), 10, 2 ); // Adjust Conductor widget defaults
			add_filter( 'conductor_widget_displays', array( $this, 'conductor_widget_displays' ), 10, 3 ); // Adjust Conductor Widget displays
			add_filter( 'conductor_widget_instance', array( $this, 'conductor_widget_instance' ), 20, 3 ); // Adjust callback functions upon Conductor Widget display
			add_filter( 'conductor_sidebar_args', array( $this, 'conductor_sidebar_args' ), 1, 4 ); // Adjust Conductor sidebar parameters (early)
			add_action( 'conductor_widget_display_content', array( $this, 'conductor_widget_display_content' ), 10, 4 ); // Adjust content wrapper element position on Conductor Widgets
			add_filter( 'conductor_widget_wrapper_css_classes', array( $this, 'conductor_widget_wrapper_css_classes' ), 20, 5 ); // Adjust the CSS classes for the widget wrapper HTML element on Conductor Widgets (after Conductor)
			add_filter( 'conductor_widget_content_wrapper_html_element', array( $this, 'conductor_widget_content_wrapper_html_element' ) ); // Adjust the content wrapper HTML element on Conductor Widgets
			add_filter( 'conductor_widget_content_wrapper_css_classes', array( $this, 'conductor_widget_content_wrapper_css_classes' ), 10, 5 ); // Adjust the CSS classes for the content wrapper HTML element on Conductor Widgets
			add_filter( 'conductor_widget_before_widget_css_classes', array( $this, 'conductor_widget_before_widget_css_classes' ), 10, 5 ); // Adjust CSS classes on the before_widget wrapper element on Conductor Widgets
			add_filter( 'conductor_widget_featured_image_size', array( $this, 'conductor_widget_featured_image_size' ), 10, 2 ); // Adjust featured image size
			add_filter( 'conductor_content_wrapper_element_before', array( $this, 'conductor_content_wrapper_element_before' ) ); // Adjust Conductor opening wrapper element
			add_filter( 'conductor_content_wrapper_element_after', array( $this, 'conductor_content_wrapper_element_after' ) ); // Adjust Conductor closing wrapper element
			add_filter( 'conductor_content_element_before', array( $this, 'conductor_content_element_before' ) ); // Adjust Conductor content opening wrapper element
			add_filter( 'conductor_content_element_after', array( $this, 'conductor_content_element_after' ) ); // Adjust Conductor content closing wrapper element
			add_filter( 'conductor_primary_sidebar_element_before', array( $this, 'conductor_primary_sidebar_element_before' ) ); // Adjust Conductor primary sidebar opening wrapper element
			add_filter( 'conductor_primary_sidebar_element_after', array( $this, 'conductor_primary_sidebar_element_after' ) ); // Adjust Conductor primary sidebar closing wrapper element
			add_filter( 'conductor_secondary_sidebar_element_before', array( $this, 'conductor_secondary_sidebar_element_before' ) ); // Adjust Conductor secondary sidebar opening wrapper element
			add_filter( 'conductor_secondary_sidebar_element_after', array( $this, 'conductor_secondary_sidebar_element_after' ) ); // Adjust Conductor secondary sidebar closing wrapper element
			add_action( 'conductor_widget_pagination_before', array( $this, 'conductor_widget_pagination_before' ) ); // Output a wrapper element around Conductor Widget pagination
			add_action( 'conductor_widget_pagination_after', array( $this, 'conductor_widget_pagination_after' ) ); // Output a wrapper element around Conductor Widget pagination

			// Baton Conductor
			add_filter( 'baton_conductor_content_wrapper_html_element', array( $this, 'conductor_widget_content_wrapper_html_element' ) ); // Adjust the content wrapper HTML element on Conductor Widgets
			add_filter( 'baton_conductor_content_wrapper_css_classes', array( $this, 'conductor_widget_content_wrapper_css_classes' ), 10, 5 ); // Adjust the CSS classes for the content wrapper HTML element on Conductor Widgets
			add_filter( 'baton_conductor_instance', array( $this, 'conductor_widget_instance' ), 20, 3 ); // Adjust callback functions upon Conductor Widget display
			add_action( 'baton_conductor_pagination_before', array( $this, 'conductor_widget_pagination_before' ) ); // Output a wrapper element around Conductor Widget pagination
			add_action( 'baton_conductor_pagination_after', array( $this, 'conductor_widget_pagination_after' ) ); // Output a wrapper element around Conductor Widget pagination

			// Gravity Forms
			add_filter( 'gform_field_input', array( $this, 'gform_field_input' ), 10, 5 ); // Add placholder to newsletter form
			add_filter( 'gform_confirmation', array( $this, 'gform_confirmation' ), 10, 4 ); // Change confirmation message on newsletter form

			/* Woocommerce Hooks */
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'baton_single_product_thumb_html' ) );

			/* Woocommerce Archive product Hooks */
			add_action( 'woocommerce_before_main_content', array( $this, 'baton_woo_main_content_before' ) );
			add_action( 'woocommerce_after_main_content', array( $this, 'baton_woo_main_content_after' ) );
			
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
			
			add_filter( 'woocommerce_pagination_args', array( $this, 'baton_woo_pagination_args' ) );

		}


		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/


		/**
		* Woocommerce function to add a wrapping div in the product archive thumbnails
		*/

		public function baton_single_product_thumb_html() {
				echo woocommerce_get_product_thumbnail();
			if ( ! is_cart() ) {	
				echo '<div class="woo-baton-product-wrapper">';
			}
		}

		public function baton_woo_pagination_args( $array ) {
			global $wp_query;
			$array = array(
						'base'  => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
						'format'       => '',
						'add_args'     => false,
						'current'      => max( 1, get_query_var( 'paged' ) ),
						'total'        => $wp_query->max_num_pages,
						'prev_text'    => '&#8592; Previous',
						'next_text'    => 'Next &#8594;',
						'type'         => 'list',
						'end_size'     => 3,
						'mid_size'     => 3
					);

			return $array;
		}

		public function baton_woo_main_content_before() { ?>

			<main role="main" class="content-wrap content-wrap-page content-wrap-full-width-page baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Page Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-page-container">

		<?php 
		}

		public function baton_woo_main_content_after() { ?>

						<div class="clear"></div>
					</section>
				</div>
			<!-- End Page Content -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

		<?php 
		}

		/**
		 * This function adds images sizes to WordPress.
		 */
		public function after_setup_theme() {
			global $content_width;

			// Grab the Baton Customizer instance
			$baton_customizer = Baton_Customizer_Instance();

			// Set the Content Width for embedded items
			if ( ! isset( $content_width ) )
				$content_width = 1272;

			// Determine if the max width theme mod is set
			if ( ( $max_width = $baton_customizer->get_theme_mod( 'baton_max_width' ) ) && $max_width !== $content_width )
				$content_width = $max_width;

			// Change default core markup for search form, comment form, and comments, etc... to HTML5
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list'
			) );

			// Custom Background (color/image)
			add_theme_support( 'custom-background', array(
				'default-color' => $baton_customizer->get_current_color_scheme_default( 'background_color', '#f1f5f9' )
			) );

			// Yoast WordPress SEO Breadcrumbs (automatically enables breadcrumbs)
			//add_theme_support( 'yoast-seo-breadcrumbs', true );

			// Theme textdomain
			load_theme_textdomain( 'baton', get_template_directory() . '/languages' );

			add_image_size( 'baton-600x400', 600, 400, true ); // Portfolio Archive Page Featured Image Size
			add_image_size( 'baton-1200x9999', 1200, 9999, false ); // Single Post/Page Featured Image Size
			add_image_size( 'baton-conductor-small', 375, 9999, false ); // Conductor Small Widgets
			add_image_size( 'baton-conductor-small-cropped', 375, 250, true ); // Conductor Small Widgets (cropped)
			add_image_size( 'baton-conductor-medium', 760, 9999, false ); // Conductor Medium Widgets
			add_image_size( 'baton-conductor-medium-cropped', 760, 500, true ); // Conductor Medium Widgets (cropped)
			add_image_size( 'baton-conductor-large', 1200, 9999, false ); // Conductor Large Widgets
			add_image_size( 'baton-conductor-large-cropped', 1200, 1200, false ); // Conductor Large Widgets (cropped)

			// Register menus which are used in Baton
			register_nav_menus( array(
				'secondary_nav' => __( 'Secondary Navigation', 'baton' ),
			) );

			// Unregister menus which are registered in SDS Core
			unregister_nav_menu( 'top_nav' );
		}

		/**
		 * This function adjusts widgets and adds an admin notice upon activation
		 */
		public function after_switch_theme( $old_theme_name, $old_theme = false ) {
			// Admin Notices
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			/*
			 * Conductor
			 */

			// Update Conductor Widgets
			$this->update_conductor_widgets();
		}

		/**
		 * This function outputs admin notices.
		 */
		public function admin_notices() {
			printf( __( '<div class="updated"><p>Welcome to Baton! Get started by visiting the <a href="%1$s">Customizer</a>!</p></div>', 'baton' ), esc_url( wp_customize_url() ) );
		}

		/**
		 * This function sets up properties on this class and allows other plugins and themes
		 * to adjust those properties by filtering.
		 */
		public function init() {
			// Update Conductor Widgets
			$this->update_conductor_widgets();
		}

		/**
		 * This function registers/unregisters extra sidebars that are not used in this theme.
		 */
		public function widgets_init() {
			global $wp_registered_sidebars;

			// Unregister unused sidebars which are registered in SDS Core
			unregister_sidebar( 'front-page-slider-sidebar' );
			unregister_sidebar( 'header-call-to-action-sidebar' );
			unregister_sidebar( 'secondary-sidebar' );

			/*
			 * Adjust before_widget and after_widget wrapper elements for sidebars which are
			 * registered in SDS Core (changing from <section> elements to <div> elements).
			 */

			// If we have registered sidebars
			if ( ! empty( $wp_registered_sidebars ) ) {
				// SDS Core Sidebar IDs
				$sds_core_sidebar_ids = array(
					'primary-sidebar',
					'secondary-sidebar',
					'front-page-sidebar',
					'after-posts-sidebar',
					'footer-sidebar',
					'copyright-area-sidebar'
				);

				// Loop through registered sidebars
				foreach ( $wp_registered_sidebars as $sidebar_id => &$sidebar )
					// Make sure this is a sidebar registered in SDS Core
					if ( in_array( $sidebar_id, $sds_core_sidebar_ids ) ) {
						// before_widget
						$sidebar['before_widget'] = str_replace( 'section', 'div', $sidebar['before_widget'] );

						// after_widget
						$sidebar['after_widget'] = str_replace( 'section', 'div', $sidebar['after_widget'] );

						// Front Page Sidebar
						if ( $sidebar_id === 'front-page-sidebar' ) {
							// description
							$sidebar['description'] = __( '*This widget area is only displayed if a Front Page is selected via Settings &gt; Reading in the Dashboard.* This widget area is displayed on the Front Page and will replace the Front Page content.', 'baton' );

							// before_widget
							$sidebar['before_widget'] .= '<div class="in front-page-widget-in cf">';

							// after_widget
							$sidebar['after_widget'] = '</div>' . $sidebar['after_widget'];
						}

						// Footer Sidebar (adjust for flexbox display)
						if ( $sidebar_id === 'footer-sidebar' )
							// before_widget
							$sidebar['before_widget'] = str_replace( 'class="', 'class="baton-col baton-col-footer-widget ', $sidebar['before_widget'] );
					}
			}
		}

		/**
		 * This function adds editor styles based on post type, before TinyMCE is initalized.
		 * It will also enqueue the correct color scheme stylesheet to better match front-end display.
		 */
		public function pre_get_posts() {
			global $sds_theme_options, $post;

			$protocol = is_ssl() ? 'https' : 'http';

			// Admin only
			if ( is_admin() ) {
				add_editor_style( 'css/editor-style.css' );

				// Add correct color scheme if selected
				if ( function_exists( 'sds_color_schemes' ) && ! empty( $sds_theme_options['color_scheme'] ) && $sds_theme_options['color_scheme'] !== 'default' ) {
					$color_schemes = sds_color_schemes();
					add_editor_style( 'css/' . $color_schemes[$sds_theme_options['color_scheme']]['stylesheet'] );
				}

				// Open Sans Web Font (include only if a web font is not selected in Theme Options)
				if ( ! function_exists( 'sds_web_fonts' ) || empty( $sds_theme_options['web_font'] ) )
					add_editor_style( str_replace( ',', '%2C', $protocol . '://fonts.googleapis.com/css?family=Lato:400,700,900|Martel+Sans:400,600' ) ); // Google WebFonts (Open Sans)

				// Fetch page template if any on Pages only
				if ( ! empty( $post ) && $post->post_type === 'page' )
					$wp_page_template = get_post_meta( $post->ID,'_wp_page_template', true );
			}

			// Admin only and if we have a post using our full page or landing page templates
			if ( is_admin() && ! empty( $post ) && ( isset( $wp_page_template ) && ( $wp_page_template === 'template-full-width.php' || $wp_page_template === 'template-landing-page.php' ) ) )
				add_editor_style( 'css/editor-style-full-width.css' );

			// FontAwesome
			add_editor_style( '/includes/css/font-awesome.min.css' );
		}


		/**
		 * This function adds <meta> tags to the <head> element.
		 */
		public function wp_head() {
		?>
			<meta charset="<?php bloginfo( 'charset' ); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<?php
		}

		/**
		 * This function prints scripts after TinyMCE has been initialized for dynamic CSS in the
		 * content editor based on page template dropdown selection.
		 */
		public function tiny_mce_before_init( $mceInit, $editor_id ) {
			// Grab the Baton Customizer instance
			$baton_customizer = Baton_Customizer_Instance();

			// Default maximum width
			$max_width = 1272;

			// Determine if the max width theme mod is set
			if ( ( $max_content_width = $baton_customizer->get_theme_mod( 'baton_max_width' ) ) && $max_content_width !== $max_width )
				$max_width = $max_content_width;

			// Adjust max width for padding (22.5%)
			$max_width -= round( $max_width * 0.225 );

			// Only on the admin 'content' editor
			if ( is_admin() && ! isset( $mceInit['setup'] ) && $editor_id === 'content' ) {
				$mceInit['setup'] = 'function( editor ) {
					// Editor init
					editor.on( "init", function( e ) {
						// Only on the "content" editor (other editors can inherit the setup function on init)
						if( editor.id === "content" ) {
							var $page_template = jQuery( "#page_template" ),
								full_width_templates = ["template-full-width.php", "template-landing-page.php"],
								$content_editor_head = jQuery( editor.getDoc() ).find( "head" );

							// If the page template dropdown exists
							if ( $page_template.length ) {
								// When the page template dropdown changes
								$page_template.on( "change", function() {
									// Is this a full width template?
									if ( full_width_templates.indexOf( $page_template.val() ) !== -1 ) {
										// Add dynamic CSS
										if( $content_editor_head.find( "#' . get_template() . '-editor-css" ).length === 0 ) {
											$content_editor_head.append( "<style type=\'text/css\' id=\'' . get_template() . '-editor-css\'> body, body.wp-autoresize { max-width: ' . $max_width . 'px; } </style>" );
										}
									}
									else {
										// Remove dynamic CSS
										$content_editor_head.find( "#' . get_template() . '-editor-css" ).remove();

										// If the full width style was added on TinyMCE Init, remove it
										$content_editor_head.find( "link[href^=\'' . get_template_directory_uri() . '/css/editor-style-full-width.css\']" ).remove();
									}
								} );
							}
						}
					} );
				}';
			}

			return $mceInit;
		}

		/**
		 * This function enqueues all styles and scripts (Main Stylesheet, Fonts, etc...). Stylesheets can be conditionally included if needed.
		 */
		public function wp_enqueue_scripts() {
			// Determine current protocol
			$protocol = is_ssl() ? 'https' : 'http';

			// Grab the Conductor Widget instance (if it exists)
			$note_widget = ( class_exists( 'Note' ) && function_exists( 'Note_Widget' ) ) ? Note_Widget() : false;

			// Baton (main stylesheet)
			wp_enqueue_style( 'baton', get_template_directory_uri() . '/style.css', false, $this->version );

			// Enqueue the child theme stylesheet only if a child theme is active
			if ( is_child_theme() )
				wp_enqueue_style( 'baton-child', get_stylesheet_uri(), array( 'baton' ), $this->version );

			// Google Web Fonts - Lato & Martel Sans
			wp_enqueue_style( 'baton-google-web-fonts', $protocol . '://fonts.googleapis.com/css?family=Lato:400,700,900|Martel+Sans:400,600', false, $this->version );

			// Ensure jQuery is loaded on the front end for our footer script (@see wp_footer() below)
			wp_enqueue_script( 'jquery' );

			// Fitvids
			wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/js/fitvids.js', array( 'jquery' ), $this->version );

			// FontAwesome
			wp_enqueue_style( 'font-awesome-css-min', get_template_directory_uri() . '/includes/css/font-awesome.min.css' );

			// If Note isn't active or at least one Note Widget isn't active
			if ( ! class_exists( 'Note' ) || ( $note_widget && is_a( $note_widget, 'Note_Widget' ) && ! is_active_widget( false, false, $note_widget->id_base, true ) ) )
				// Note Flexbox Shim
				wp_enqueue_style( 'baton-note-flexbox', get_template_directory_uri() . '/css/note-flexbox.css', false, $this->version );

		}

		/**
		 * This function adds a clearing element before the more link in the_content(). It
		 * also adds a "button" CSS class to the link.
		 */
		public function the_content_more_link( $link ) {
			return '<div class="clear"></div>' . str_replace( 'class="', 'class="button ', $link );
		}

		/**
		 * This function adjusts the sidebar parameters for widgets.
		 */
		public function dynamic_sidebar_params( $params ) {
			// Bail if we're not on the front-end
			if ( is_admin() )
				return $params;

			// If Note exists
			if ( function_exists( 'Note_Widget' ) ) {
				// Grab the Note Widget instance
				$note_widget = Note_Widget();
	
				// Only on Note Widgets
				if ( _get_widget_id_base( $params[0]['widget_id'] ) === $note_widget->id_base ) {
					// Store a reference to the widget settings (all Note Widgets)
					$note_widget_settings = $note_widget->get_settings();
	
					// Determine if this is a valid Note widget
					if ( array_key_exists( $params[1]['number'], $note_widget_settings ) ) {
						// Grab widget settings
						$instance = $note_widget_settings[$params[1]['number']];
	
						// If we have a template
						if ( property_exists( $note_widget, 'templates' ) && isset( $instance['template'] ) && ! empty( $instance['template'] ) && array_key_exists( $instance['template'], $note_widget->templates ) ) {
							// Grab the template details for this widget
							$template = $note_widget->templates[$instance['template']];
	
							// CSS Classes
							$css_classes = array();
	
							// Check the template type first
							if ( isset( $template['type'] ) )
								$css_classes[] = sanitize_html_class( $template['type'] . '-widget' );
							// Then check the template
							if ( empty( $css_classes ) && isset( $template['template'] ) )
								$css_classes[] = sanitize_html_class( $template['template'] . '-widget' );
							// Otherwise fallback to the name
							if ( empty( $css_classes ) )
								$css_classes[] = sanitize_html_class( $instance['template'] . '-widget' );
	
							// Adjust the before_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
							$params[0]['before_widget'] = preg_replace( '/class="/', 'class="' . esc_attr( implode( ' ', $css_classes ) ) . ' ', $params[0]['before_widget'], 1 );
						}
					}
				}
			}

			// If Conductor exists
			if ( function_exists( 'Conduct_Widget' ) ) {
				// Grab the Conductor Widget instance
				$conductor_widget = Conduct_Widget();
	
				// Only on Conductor Widgets
				if ( _get_widget_id_base( $params[0]['widget_id'] ) === $conductor_widget->id_base ) {
					// Store a reference to the widget settings (all Conductor Widgets)
					$conductor_widget_settings = $conductor_widget->get_settings();
	
					// Determine if this is a valid Conductor widget
					if ( array_key_exists( $params[1]['number'], $conductor_widget_settings ) ) {
						// Grab widget settings
						$instance = $conductor_widget_settings[$params[1]['number']];
	
						// If we have flexbox display in the Front Page Sidebar
						if ( $params[0]['id'] === 'front-page-sidebar' && $this->conductor_has_flexbox_display() ) {
							// Grab this widget's display configuration
							$widget_display_config = ( isset( $instance['widget_size'] ) && isset( $conductor_widget->displays[$instance['widget_size']] ) ) ? $conductor_widget->displays[$instance['widget_size']] : false;

							// Verify that the widget size supports columns
							//isset( $instance['widget_size'] ) && $instance['widget_size'] === 'flexbox'
							if ( ! empty( $widget_display_config ) && $conductor_widget->widget_display_supports_customize_property( $widget_display_config, 'columns' ) ) {
								// Adjust the before_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
								$params[0]['before_widget'] = preg_replace( '/<div class="in front-page-widget-in cf">/', '', $params[0]['before_widget'], 1 );

								// Adjust the after_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
								$params[0]['after_widget'] = preg_replace( '/<\/div>/', '', $params[0]['after_widget'], 1 );
							}
						}
					}
				}
			}

			return $params;
		}

		/**
		 * This function adds a "button" CSS class on "edit post" links.
		 */
		public function edit_post_link( $link ) {
			return str_replace( '<a class="', '<a class="button ', $link );
		}

		/**
		 * This function outputs the necessary javascript for the responsive menus.
		 */
		public function wp_footer() {
		?>
			<script type="text/javascript">
					// <![CDATA[
				jQuery( function( $ ) {
					var $primary_nav_and_button = $( '.primary-nav-button, .primary-nav-mobile' ),
						$primary_nav_items = $primary_nav_and_button.find( 'li' ),
						$secondary_nav_and_button = $( '.secondary-nav-button, .secondary-nav' ),
						$secondary_nav_items = $secondary_nav_and_button.find( 'li' );

					// Primary Nav
					$primary_nav_and_button.on( 'click', function ( e ) {
						// Prevent Propagation (bubbling) to other elements and default
						e.stopPropagation();
						e.preventDefault();

						// Open
						if ( ! $primary_nav_and_button.hasClass( 'open' ) ) {
							$primary_nav_and_button.addClass( 'open' );

							// 500ms delay to account for CSS transition (if any)
							setTimeout( function() {
								$primary_nav_and_button.addClass( 'opened' );
							}, 500 );
						}
						// Close
						else {
							$primary_nav_and_button.removeClass( 'open opened' );
						}
					} );

					// Secondary Nav
					$secondary_nav_and_button.on( 'click', function ( e ) {
						// Prevent Propagation (bubbling) to other elements and default
						e.stopPropagation();
						e.preventDefault();

						// Open
						if ( ! $secondary_nav_and_button.hasClass( 'open' ) ) {
							$secondary_nav_and_button.addClass( 'open' );

							// 500ms delay to account for CSS transition (if any)
							setTimeout( function() {
								$secondary_nav_and_button.addClass( 'opened' );
							}, 500 );
						}
						// Close
						else {
							$secondary_nav_and_button.removeClass( 'open opened' );
						}
					} );

					// Primary Nav/Secondary Items
					$primary_nav_items.add( $secondary_nav_items ).each( function() {
						var $this = $( this );

						// Child elements
						if ( $this.hasClass( 'menu-item-has-children' ) || $this.hasClass( 'page_item_has_children' ) ) {
							$this.addClass( 'closed' ).append( '<span class="fa fa-chevron-down child-menu-button"></span>' );

							// Child menu buttons
							$this.find( '.child-menu-button' ).on( 'click', function( e ) {
								var $child_button = $( this );

								$this.toggleClass( 'closed open opened' );

								$child_button.toggleClass( 'fa-chevron-up fa-chevron-down' );
							} );
						}
					} );

					// Primary Nav/Secondary Nav Items Click
					$primary_nav_items.add( $secondary_nav_items ).on( 'click', function( e ) {
						// Prevent Propagation (bubbling) to other elements
						e.stopPropagation();
					} );

					// Document
					$( document ).on( 'click', function() {
						// Close Primary Nav
						$primary_nav_and_button.removeClass( 'open opened' );

						// Close Secondary Nav
						$secondary_nav_and_button.removeClass( 'open opened' );
					} );

					// Fitvids
					$( 'article.content, .widget' ).fitVids();
				} );
				// ]]>
			</script>
		<?php
		}


		/**************
		 * Customizer *
		 **************/

		/**
		 * This function registers Customizer components.
		 */
		function customize_register( $wp_customize ) {
			$wp_customize->add_section( 'baton_us', array(
				'title' => __( 'Upgrade Baton', 'baton' ),
				'priority' => 1
			) );
	
			$wp_customize->add_setting(
				'baton_us', // IDs can have nested array keys
				array(
					'default' => false,
					'type' => 'baton_us',
					'sanitize_callback' => 'sanitize_text_field'
				)
			);
	
			$wp_customize->add_control(
				new SDS_Theme_Options_Customize_US_Control(
					$wp_customize,
					'baton_us',
					array(
						'content'  => sprintf(
							__( 'Receive <strong>premium support</strong>, individual Customizer options for colors and fonts, and more! %1$s. %2$s %3$s %4$s', 'baton' ),
							sprintf(
								'<a href="%1$s" target="_blank">%2$s</a>',
								esc_url( sds_get_pro_link( 'customizer' ) ),
								__( 'Upgrade to Pro', 'baton' )
							),
							sprintf(
								'<h4>%1$s</h4>',
								__( 'Upgrade to Baton Pro to add the following features:', 'baton' )
							),
							sprintf(
								'<ul class="upgrade-features-list">
									<li>%1$s</li>
									<li>%2$s</li>
									<li>%3$s</li>
									<li>%4$s</li>
									<li>%5$s</li>
									<li>%6$s</li>
								</ul>',
								__( 'Priority Support', 'baton' ),
								__( 'Custom Scripts and Styles', 'baton' ),
								__( 'Individual Color Customisation', 'baton' ),
								__( 'Individual Font Families and Sizes', 'baton' ),
								__( 'Header Alignment', 'baton' ),
								__( '"Features 2" Baton Note Widget Display', 'baton' )
							),
							sprintf(
								'<a href="%1$s" class="button button-primary" target="_blank">%2$s</a>',
								esc_url( sds_get_pro_link( 'customizer' ) ),
								__( 'Upgrade Now!', 'baton' )
							)
						),
						'section' => 'baton_us',
					)
				)
			);
		}

		/**
		 * This function outputs Customizer styles.
		 */
		function customize_controls_print_styles() {
		?>
			<style type="text/css">
				#accordion-section-baton_us .accordion-section-title,
				#customize-theme-controls #accordion-section-baton_us .accordion-section-title:focus,
				#customize-theme-controls #accordion-section-baton_us .accordion-section-title:hover,
				#customize-theme-controls #accordion-section-baton_us .control-section.open .accordion-section-title,
				#customize-theme-controls #accordion-section-baton_us:hover .accordion-section-title,
				#accordion-section-baton_us .accordion-section-title:active {
					background: #444;
					color: #fff;
				}
	
				#accordion-section-baton_us .accordion-section-title:after,
				#customize-theme-controls #accordion-section-baton_us .accordion-section-title:focus::after,
				#customize-theme-controls #accordion-section-baton_us .accordion-section-title:hover::after,
				#customize-theme-controls #accordion-section-baton_us.open .accordion-section-title::after,
				#customize-theme-controls #accordion-section-baton_us:hover .accordion-section-title::after {
					color: #fff;
				}

				#accordion-section-baton_us .upgrade-features-list {
					margin-bottom: 2em;
					padding-left: 2em;
					list-style: disc;
				}
			</style>
		<?php
		}


		/********
		 * Note *
		 ********/

		/**
		 * This function adds Baton Note Widget template types to Note.
		 */
		public function note_widget_template_types( $types ) {
			// Hero type
			if ( ! isset( $types['baton-hero'] ) )
				$types['baton-hero'] = __( 'Baton Hero', 'baton' );

			// Features type
			if ( ! isset( $types['baton-features'] ) )
				$types['baton-features'] = __( 'Baton Features', 'baton' );

			return $types;
		}

		/**
		 * This function adds Baton Note Widget editor types to Note.
		 */
		public function note_tinymce_editor_types( $types ) {
			// Baton Hero
			if ( ! in_array( 'baton-hero', $types ) )
				$types[] = 'baton-hero';

			return $types;
		}

		/**
		 * This function adjusts Note Widget TinyMCE editor settings based on editor type.
		 */
		public function note_tinymce_editor_settings( $settings, $editor_type ) {
			// Switch based on editor type
			switch ( $editor_type ) {
				// Hero
				case 'baton-hero':
					// Make plugins an array
					$settings['plugins'] = explode( ' ', $settings['plugins'] );

					// Search for the 'note_image' TinyMCE plugin in existing settings
					$note_image = array_search( 'note_image', $settings['plugins'] );

					// If we have an index for the the 'note_image' TinyMCE plugin
					if ( $note_image !== false ) {
						// Remove the 'note_image' TinyMCE plugin
						unset( $settings['plugins'][$note_image] );

						// Reset array keys to ensure JavaScript logic receives an array
						$settings['plugins'] = array_values( $settings['plugins'] );
					}

					// Make plugins a string again
					$settings['plugins'] = implode( ' ', $settings['plugins'] );


					// Search for the 'wp_image' TinyMCE block in existing settings
					$wp_image = array_search( 'wp_image', $settings['blocks'] );

					// If we have an index for the the 'wp_image' TinyMCE block
					if ( $wp_image !== false ) {
						// Remove the 'wp_image' TinyMCE block
						unset( $settings['blocks'][$wp_image] );

						// Reset array keys to ensure JavaScript logic receives an array
						$settings['blocks'] = array_values( $settings['blocks'] );
					}
				break;

				// Default, all displays
				default:
					// Adjust the style formats
					$settings['style_formats'][] = array(
						'title' => __( 'Button', 'baton' ),
						'selector' => 'a',
						'attributes' => array(
							'class' => 'button'
						)
					);
					$settings['style_formats'][] = array(
						'title' => __( 'Button Alternate', 'baton' ),
						'selector' => 'a',
						'attributes' => array(
							'class' => 'button-alt'
						)
					);
				break;
			}

			return $settings;
		}

		/**
		 * This function adds Hero and Features templates to Note Widgets.
		 *
		 * @see Note for configuration details
		 */
		public function note_widget_templates( $templates, $widget ) {
			// Baton Hero 1
			if ( ! isset( $templates['baton-hero-1'] ) )
				$templates['baton-hero-1'] = array(
					// Label
					'label' => __( 'Baton Hero 1', 'baton' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2>%1$s</h2>
							<p data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 24px;">%2$s</span></strong></p>
							<p data-note-placeholder="false"><br data-note-placeholder="false" /></p>
							<p data-note-placeholder="false"><a href="#" class="button" data-note-placeholder="false">%3$s</a></p>',
						__( 'Hero 1', 'baton' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
						__( 'Button', 'baton' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Hero 2
			if ( ! isset( $templates['baton-hero-2'] ) )
				$templates['baton-hero-2'] = array(
					// Label
					'label' => __( 'Baton Hero 2', 'baton' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2 style="text-align: center;">%1$s</h2>
							<p style="text-align: center;">%2$s</p>
							<p style="text-align: center;" data-note-placeholder="false"><a href="#" class="button" data-note-placeholder="false">%3$s</a></p>',
						__( 'Hero 2', 'baton' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
						__( 'Button', 'baton' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Features 1
			if ( ! isset( $templates['baton-features-1'] ) )
				$templates['baton-features-1'] = array(
					// Label
					'label' => __( 'Baton Features 1', 'baton' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2 style="text-align: center;">%1$s</h2>
							<p style="text-align: center;">%2$s</p>',
						__( 'Features', 'baton' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ) ),
					// Type
					'type' => 'baton-features',
					// Template
					'template' => 'baton-features',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'columns' => true, // Columns
							'rows' => true // Rows
						),
						// Placeholder (Columns; used in place for "extra" columns that aren't found in configuration below)
						'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
								<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
								<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
							__( 'Feature', 'baton' ),
							__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
							__( 'Read More', 'baton' ) ),
						// Column configuration
						'columns' => array(
							// Column 1
							1 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
									__( 'Read More', 'baton' ) ),
							),
							// Column 2
							2 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
									__( 'Read More', 'baton' ) ),
							),
							// Column 2
							3 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
									__( 'Read More', 'baton' ) ),
							),
							// Column 4
							4 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton' ),
									__( 'Read More', 'baton' ) ),
							)
						)
					)
				);

			return $templates;
		}

		/**
		 * This function adjusts Note Widget CSS classes based on the widget instance settings.
		 */
		public function note_widget_css_classes( $classes, $instance, $widget ) {
			// If we are displaying a template
			if ( property_exists( $widget, 'templates' ) && isset( $instance['template'] ) && ! empty( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) {
				// Grab the template details for this widget
				$template = $widget->templates[$instance['template']];

				// Background Image Attachment ID
				if ( ( isset( $template['type'] ) && $template['type'] === 'baton-hero' ) && ( isset( $instance['extras'] ) && ( ! isset( $instance['extras']['background_image_attachment_id'] ) || ! $instance['extras']['background_image_attachment_id'] ) ) )
					$classes[] = 'has-default-baton-hero-image';
			}

			return $classes;
		}

		/**
		 * This function outputs an opening "in" wrapper element before the widget title on Note Widgets that
		 * have a Hero display selected and are set to display the widget title.
		 */
		public function note_widget_title_before( $instance, $args, $widget ) {
			// If we are displaying the widget title on a Hero display
			if ( isset( $instance['hide_title'] ) && ! $instance['hide_title'] && isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$template = $widget->templates[$instance['template']];

				if ( isset( $template['type'] ) && $template['type'] === 'baton-hero' ) :
			?>
				<div class="in note-widget-in note-hero-widget-in note-baton-hero-in note-baton-hero-widget-in cf">
			<?php
				endif;
			endif;
		}

		/**
		 * This function outputs an opening "in" wrapper element before the widget template content
		 * on Note Widgets that have a Hero display selected and are not set to display the widget title.
		 */
		public function note_widget_template_before( $template_name, $template, $data, $number, $instance, $args, $widget ) {
			// If we are not displaying the widget title on a Hero display
			if ( isset( $instance['hide_title'] ) && $instance['hide_title'] && isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$widget_template = $widget->templates[$instance['template']];

				if ( isset( $widget_template['type'] ) && $widget_template['type'] === 'baton-hero' ) :
			?>
				<div class="in note-widget-in note-hero-widget-in note-baton-hero-in note-baton-hero-widget-in cf">
			<?php
				endif;
			endif;
		}

		/**
		 * This function outputs a closing "in" wrapper element after the widget template content
		 * on Note Widgets that have a Hero display selected.
		 */
		public function note_widget_template_after( $template_name, $template, $data, $number, $instance, $args, $widget ) {
			// If we have a Hero display
			if ( isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$widget_template = $widget->templates[$instance['template']];

				if ( isset( $widget_template['type'] ) && $widget_template['type'] === 'baton-hero' ) :
			?>
				</div>
			<?php
				endif;
			endif;
		}


		/*************
		 * Conductor *
		 *************/

		/**
		 * This function adjusts the default setting values for Conductor widgets.
		 */
		public function conductor_widget_defaults( $defaults, $widget ) {
			// If Conductor has flexbox display
			if ( $this->conductor_has_flexbox_display( $widget ) )
				// Adjust default widget size (display) to flexbox
				$defaults['widget_size'] = 'flexbox';

			$author_byline = array();

			// Loop through output elements
			foreach ( $defaults['output'] as $priority => $output ) {
				// Read More
				if ( $output['id'] === 'read_more' ) {
					// Adjust default label to match Baton more link label
					$defaults['output'][$priority]['label'] = baton_more_link_label();
				}

				// Author Byline (store reference to priority and configuration)
				if ( $output['id'] === 'author_byline' ) {
					$author_byline = $output;

					// Remove author byline
					unset( $defaults['output'][$priority] );
				}
			}

			// Read More output features (Baton deault)
			$defaults['output_features']['read_more']['edit_label']['default'] = _x( 'Continue Reading', '"read more" label for Conductor widgets', 'baton' );

			/*
			 * Author Byline (move to bottom of default output elements)
			 */
			$output_elements = array();
			$default_priority_gap = 10;
			$count = 0;

			// Loop through the passed in widget settings
			foreach ( $defaults['output'] as $output ) {
				// Increase count
				$count++;

				// Add this element to the output elements
				$output_elements[( $default_priority_gap * $count )] = $output;
			}

			// Author Byline (increase count before multiplying)
			$output_elements[( $default_priority_gap * ++$count )] = $author_byline;

			// Set the default output
			$defaults['output'] = $output_elements;

			return $defaults;
		}

		/**
		 * This function depreciates legacy display options from Conductor Widgets.
		 */
		public function conductor_widget_displays( $conductor_widget_displays, $instance, $widget ) {
			// Only if the flexbox display exists
			if ( isset( $conductor_widget_displays['flexbox'] ) ) {
				// Remove Small legacy display
				if ( isset( $conductor_widget_displays['small'] ) )
					unset( $conductor_widget_displays['small'] );

				// Remove Medium legacy display
				if ( isset( $conductor_widget_displays['medium'] ) )
					unset( $conductor_widget_displays['medium'] );

				// Remove Large legacy display
				if ( isset( $conductor_widget_displays['large'] ) )
					unset( $conductor_widget_displays['large'] );
			}

			return $conductor_widget_displays;
		}

		/**
		 * This function adjusts the callback functions for various output elements.
		 */
		public function conductor_widget_instance( $instance, $args, $widget ) {
			// If we have output elements (i.e. this isn't a brand new Conductor Widget)
			if ( isset( $instance['output'] ) && ! empty( $instance['output'] ) )
				// Adjust the callback output elements
				foreach ( $instance['output'] as $priority => &$element ) {
					// Featured Image
					if ( $element['id'] === 'featured_image' )
						$element['callback'] = array( $this, 'conductor_widget_featured_image' );

					// Post Title
					if ( $element['id'] === 'post_title' )
						$element['callback'] = array( $this, 'conductor_widget_post_title' );

					// Post Content
					if ( $element['id'] === 'post_content' )
						$element['callback'] = array( $this, 'conductor_widget_post_content' );

					// Read More
					if ( $element['id'] === 'read_more' )
						$element['callback'] = array( $this, 'conductor_widget_read_more' );

					// Author Byline
					if ( $element['id'] === 'author_byline' )
						$element['callback'] = array( $this, 'conductor_widget_author_byline' );
				}

			return $instance;
		}

		/**
		 * This function adjusts sidebar parameters of Conductor sidebars.
		 */
		public function conductor_sidebar_args( $sidebar_args, $conductor_sidebar_id, $content_layout, $content_layouts ) {
			// before_widget
			$sidebar_args['before_widget'] = str_replace( 'section', 'div', $sidebar_args['before_widget'] );

			// after_widget
			$sidebar_args['after_widget'] = str_replace( 'section', 'div', $sidebar_args['after_widget'] );

			return $sidebar_args;
		}

		/**
		 * This function moves the opening Conductor content wrapper opening element on Conductor Widgets.
		 */
		public function conductor_widget_display_content( $post, $instance, $widget, $conductor_widget_query ) {
			// Bail if this isn't a flexbox display
			if ( $instance['widget_size'] !== 'flexbox' )
				return;

			$output_elements_before_featured_image = 0;
			$featured_image_priority = 0;
			$featured_image_only = true; // Flag to determine if the featured image is the only visible output element

			// If we have hooks
			if ( ! empty( $conductor_widget_query->hooks ) && isset( $conductor_widget_query->hooks['conductor_widget_display_content_' . $widget->number] ) ) {
				// Store a reference to the list of hooks for this widget
				$hooks = &$conductor_widget_query->hooks['conductor_widget_display_content_' . $widget->number];

				// Loop through hooks to find the featured image priority
				foreach ( $hooks as $priority => $callback )
					// conductor_widget_featured_image; $callback[1] is the function name
					if ( is_array( $callback ) && $callback[1] === 'conductor_widget_featured_image' ) {
						$featured_image_priority = $priority;
						break;
					}

				// If we have a featured image priority
				if ( $featured_image_priority ) {
					// Determine if only the featured image is visible
					foreach( $instance['output'] as $priority => $output ) {
						if ( $output['id'] !== 'featured_image' && $output['visible'] === true )
							$featured_image_only = false;

						// Increase the count of elements before the featured image
						if ( $output['id'] !== 'featured_image' && $priority < $featured_image_priority )
							$output_elements_before_featured_image++;
					}

					// If we have more than a featured image to output
					if ( ! $featured_image_only )
						// Loop through hooks again
						foreach ( $hooks as $priority => $callback )
							// conductor_widget_content_wrapper; $callback[1] is the function name
							if ( is_array( $callback ) && $callback[1] === 'conductor_widget_content_wrapper' && $priority < $featured_image_priority ) {
								// Determine new priority for content wrapper opening element
								$new_priority = ( $priority + $featured_image_priority );

								// Remove the default action (if there are no output elements before the featured image)
								if ( ! $output_elements_before_featured_image ) {
									remove_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, $callback[1] ), $priority, $conductor_widget_query->display_content_args_count );

									// Adjust the "hooks" property
									unset( $hooks[$priority] );
								}
								// Otherwise we have elements before the featured image, ensure the default wrapper is closed
								else {
									// Determine new priority for content wrapper closing element
									$closing_wrapper_priority = ( $featured_image_priority - $priority );

									// Add the action before the featured image
									add_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, 'conductor_widget_content_wrapper_close' ), $closing_wrapper_priority, $conductor_widget_query->display_content_args_count );

									// Adjust the "hooks" property
									$hooks += array( $closing_wrapper_priority => array( get_class( $conductor_widget_query ), 'conductor_widget_content_wrapper_close' ) ); // Static callback
								}

								// Add the action after the featured image element
								add_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, $callback[1] ), $new_priority, $conductor_widget_query->display_content_args_count );

								// Adjust the "hooks" property
								$hooks += array( $new_priority => array( get_class( $conductor_widget_query ), $callback[1] ) ); // Static callback
								ksort( $hooks ); // Sort the hooks by key
							}
				}
			}
		}

		/**
		 * This function adjusts the featured image size on Conductor widgets only if a size has not been selected by the user.
		 */
		public function conductor_widget_featured_image_size( $size, $instance ) {
			// Only adjust the size if a user has not selected one
			if ( isset( $instance['post_thumbnails_size'] ) && ! empty( $instance['post_thumbnails_size'] ) )
				return $instance['post_thumbnails_size'];

			// If we have a widget size
			if ( isset( $instance['widget_size'] ) ) {
				// Switch based on widget size
				switch ( $instance['widget_size'] ) {
					// Flexbox
					case 'flexbox':
						// Switch based on number of flexbox columns
						switch ( $instance['flexbox']['columns'] ) {
							// "Large"
							case 1:
								$size = 'baton-conductor-large';
							break;

							// "Medium"
							case 2:
							case 3:
								$size = 'baton-conductor-medium';
							break;

							// "Small"
							case 4:
							case 5:
								$size = 'baton-conductor-small';
							break;
							case 6:
								$size = 'thumbnail';
							break;
						}
					break;
				}
			}

			return $size;
		}


		/*********************
		 * Conductor Display *
		 *********************/

		/**
		 * This function adjusts the CSS classes on the widget wrapper element.
		 */
		public function conductor_widget_wrapper_css_classes( $css_classes, $post, $instance, $widget, $query ) {
			// Bail if this isn't a flexbox display
			if ( $instance['widget_size'] !== 'flexbox' )
				return $css_classes;

			// If we have hooks
			if ( ! empty( $query->hooks ) && isset( $query->hooks['conductor_widget_display_content_' . $widget->number] ) ) {
				$content_wrapper_elements = 0;

				// Store a reference to the list of hooks for this widget
				$hooks = &$query->hooks['conductor_widget_display_content_' . $widget->number];

				// Loop through hooks to find the featured image priority
				foreach ( $hooks as $priority => $callback )
					// conductor_widget_content_wrapper; $callback[1] is the function name
					if ( is_array( $callback ) && $callback[1] === 'conductor_widget_content_wrapper' )
						$content_wrapper_elements++;

				// If there are multiple content wrapper elements
				if ( $content_wrapper_elements > 1 ) {
					// Explode the CSS classes
					$css_classes = explode( ' ', $css_classes );

					// Add CSS classes
					$css_classes[] = 'multiple-content-wrapper-elements';
					$css_classes[] = 'conductor-multiple-content-wrapper-elements';
					$css_classes[] = 'baton-multiple-content-wrapper-elements';

					// Ensure CSS classes are a string
					$css_classes = implode( ' ', $css_classes );
				}
			}

			return $css_classes;
		}

		/**
		 * This function adjusts the HTML element used for content wrapper elements.
		 */
		public function conductor_widget_content_wrapper_html_element( $element ) {
			return 'article';
		}

		/**
		 * This function adjusts the CSS classes on the content wrapper element.
		 */
		public function conductor_widget_content_wrapper_css_classes( $css_classes, $post, $instance, $widget, $query ) {
			// List widget size (display) only
			if ( $instance['widget_size'] === 'list' )
				// Remove the "content" CSS class (only replacing once)
				$css_classes = preg_replace( '/content /', '', $css_classes, 1 );

			// If we have output elements
			if ( isset( $instance['output'] ) ) {
				// Keep track of the post_title and read_more output element priority
				$featured_image_priority = $post_title_priority = $read_more_priority = $author_byline_priority = -1;

				// Loop through output elements
				foreach ( $instance['output'] as $priority => $output ) {
					// Post Content (if hidden)
					if ( $output['id'] === 'post_content' && $output['visible'] === false )
						$css_classes .= ' no-content no-post-content';

					// Featured Image
					if ( $output['id'] === 'featured_image' )
						$featured_image_priority = $priority;

					// Post Title
					if ( $output['id'] === 'post_title' )
						$post_title_priority = $priority;

					// Read More
					if ( $output['id'] === 'read_more' )
						$read_more_priority = $priority;

					// Author Byine
					if ( $output['id'] === 'author_byline' )
						$author_byline_priority = $priority;
				}

				// If post_title output element appears right before the read_more element (10 is the default priority padding)
				if ( ( $post_title_priority + 10 ) === $read_more_priority )
					$css_classes .= ' post-title-before-read-more';

				// If featured_image output element appears right before the author_byline element (10 is the default priority padding)
				if ( ( $featured_image_priority + 10 ) === $author_byline_priority )
					$css_classes .= ' featured-image-before-author-byline';

				// If featured_image output element appears right after the author_byline element (10 is the default priority padding)
				if ( ( $author_byline_priority + 10 ) === $featured_image_priority )
					$css_classes .= ' featured-image-after-author-byline';
			}

			return $css_classes;
		}

		/**
		 * This function adjusts the CSS classes on the before_widget wrapper element on Conductor Widgets
		 * with flexbox displays on the Front Page sidebar only.
		 */
		public function conductor_widget_before_widget_css_classes( $css_classes, $params, $instance, $conductor_widget_settings, $widget ) {
			// Front Page Sidebar only
			if ( $params[0]['id'] === 'front-page-sidebar' ) {
				$css_classes[] = 'in';
				$css_classes[] = 'front-page-widget-in';
				$css_classes[] = 'cf';
			}

			return $css_classes;
		}

		/**
		 * This function outputs the featured image for Conductor Widgets.
		 */
		public function conductor_widget_featured_image( $post, $instance, $widget, $query ) {
			// Find the featured image output element data
			$priority = $instance['output_elements']['featured_image'];
			$output = $instance['output'][$priority];

			if ( has_post_thumbnail( $post->ID ) ) :
				do_action( 'conductor_widget_featured_image_before', $post, $instance );

				// Output desired featured image size
				if ( ! empty( $instance['post_thumbnails_size'] ) )
					$conductor_thumbnail_size = $instance['post_thumbnails_size'];
				else
					$conductor_thumbnail_size = ( $instance['widget_size'] !== 'small' ) ? $instance['widget_size'] : 'thumbnail';

				$conductor_thumbnail_size = apply_filters( 'conductor_widget_featured_image_size', $conductor_thumbnail_size, $instance, $post );
		?>
				<!-- Post Thumbnail/Featured Image -->
				<div class="article-thumbnail-wrap article-featured-image-wrap post-thumbnail-wrap featured-image-wrap cf">
					<?php sds_featured_image( ( bool ) $output['link'], $conductor_thumbnail_size ); ?>
				</div>
				<!-- End Post Thumbnail/Featured Image -->
		<?php
				do_action( 'conductor_widget_featured_image_after', $post, $instance );
			endif;
		}

		/**
		 * This function outputs the post title for Conductor Widgets.
		 */
		public function conductor_widget_post_title( $post, $instance, $widget, $query ) {
			// Find the post title output element data
			$priority = $instance['output_elements']['post_title'];
			$output = $instance['output'][$priority];

			do_action( 'conductor_widget_post_title_before', $post, $instance );
		?>
			<!-- Article Header -->
			<header class="article-title-wrap">
				<div class="article-categories-wrap"><?php the_category( ', ' ); ?></div>
				<?php if ( strlen( get_the_title() ) > 0 ) : ?>
					<h1 class="article-title">
						<?php
							// Link post title to post
							if ( $output['link'] ) :
						?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<?php
							// Just output the post title
							else:
								the_title();
							endif;
						?>
					</h1>
				<?php endif; ?>
			</header>
			<!-- End Article Header -->
		<?php
			do_action( 'conductor_widget_post_title_after', $post, $instance );
		}

		/**
		 * This function outputs the post content for Conductor Widgets.
		 */
		public function conductor_widget_post_content( $post, $instance, $widget, $query ) {
			do_action( 'conductor_widget_post_content_before', $post, $instance );
		?>
			<!-- Article Content -->
			<div class="article-content cf">
		<?php
			// Determine which type of content to output
			switch ( $instance['content_display_type'] ) {
				// Excerpt - the_excerpt()
				case 'excerpt':
					echo $query->get_excerpt_by_id( $post, $instance['excerpt_length'] );
				break;

				// the_content()
				case 'content':
				default:
					echo $query->get_content_by_id( $post );
				break;
			}
		?>
			</div>
			<!-- End Article Content -->
		<?php
			do_action( 'conductor_widget_post_content_after', $post, $instance );
		}

		/**
		 * This function outputs the read more link for Conductor Widgets.
		 */
		public function conductor_widget_read_more( $post, $instance, $widget, $query ) {
			// Find the read more output element data
			$priority = $instance['output_elements']['read_more'];
			$output = $instance['output'][$priority];

			do_action( 'conductor_widget_read_more_before', $post, $instance );
		?>
			<!-- Article Content -->
			<div class="article-content article-content-more-link cf">
				<p>
		<?php
			// Link read more to post
			if ( $output['link'] ) :
		?>
				<a class="more read-more more-link button" href="<?php echo get_permalink( $post->ID ); ?>">
					<?php echo $output['label']; ?>
				</a>
		<?php
			// Just output the read more
			else:
				echo $output['label'];
			endif;
		?>
				</p>
			</div>
			<!-- End Article Content -->
		<?php
			do_action( 'conductor_widget_read_more_after', $post, $instance );
		}

		/**
		 * This function outputs the author byline for Conductor Widgets.
		 */
		public function conductor_widget_author_byline( $post, $instance, $widget, $query ) {
			do_action( 'conductor_widget_author_byline_before', $post, $instance );
		?>
			<!-- Post Meta -->
			<div class="article-post-meta article-post-meta-archive baton-flex baton-flex-3-columns baton-flex-post-meta-archive">
				<?php sds_post_meta( true ); ?>
			</div>
			<!-- End Post Meta -->
		<?php
			do_action( 'conductor_widget_author_byline_after', $post, $instance );
		}

		/**
		 * This function adjusts the opening content wrapper on Conductor layouts.
		 */
		public function conductor_content_wrapper_element_before( $wrapper ) {
			$wrapper = '<!-- Main --><main role="main" class="content-wrap content-wrap-conductor baton-flex ';
			$wrapper .= ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs';
			$wrapper .= '">';

			return $wrapper;
		}

		/**
		 * This function adjusts the closing content wrapper on Conductor layouts.
		 */
		public function conductor_content_wrapper_element_after( $wrapper ) {
			$wrapper = '<div class="clear"></div></main><!-- End Main -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the content opening wrapper on Conductor layouts.
		 */
		public function conductor_content_element_before( $wrapper ) {
			$wrapper = '<!-- Home/Blog Content --><div class="conductor-content baton-col baton-col-content baton-col-conductor-content ' . Conductor::get_conductor_content_layout_sidebar_id( 'content' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'content' ) . '">';
			$wrapper .= '<section class="content-container content-conductor-container">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the content closing wrapper on Conductor layouts.
		 */
		public function conductor_content_element_after( $wrapper ) {
			$wrapper = '</div></section></div><!-- End Home/Blog Content -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the primary sidebar opening wrapper on Conductor layouts.
		 */
		public function conductor_primary_sidebar_element_before( $wrapper ) {
			$wrapper = '<!-- Primary Sidebar --><div class="conductor-sidebar conductor-primary-sidebar baton-col baton-col-sidebar baton-col-conductor-sidebar baton-col-conductor-primary-sidebar ' . Conductor::get_conductor_content_layout_sidebar_id( 'primary' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'primary' ) . '">';
			$wrapper .= '<section class="sidebar-container sidebar-conductor-container sidebar-conductor-primary-sidebar-container">';
			$wrapper .= '<aside class="sidebar sidebar-conductor-primary">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the primary sidebar closing wrapper on Conductor layouts.
		 */
		public function conductor_primary_sidebar_element_after( $wrapper ) {
			$wrapper = '</div></aside></section></div><!-- End Primary Sidebar -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the secondary sidebar opening wrapper on Conductor layouts.
		 */
		public function conductor_secondary_sidebar_element_before( $wrapper ) {
			$wrapper = '<!-- Secondary Sidebar --><div class="conductor-sidebar conductor-secondary-sidebar baton-col baton-col-sidebar baton-col-sidebar-secondary baton-col-conductor-sidebar baton-col-conductor-secondary-sidebar ' . Conductor::get_conductor_content_layout_sidebar_id( 'secondary' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'secondary' ) . '">';
			$wrapper .= '<section class="sidebar-container sidebar-conductor-container sidebar-conductor-secondary-sidebar-container">';
			$wrapper .= '<aside class="sidebar sidebar-conductor-secondary">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the secondary sidebar closing wrapper on Conductor layouts.
		 */
		public function conductor_secondary_sidebar_element_after( $wrapper ) {
			$wrapper = '</div></aside></section></div><!-- End Secondary Sidebar -->';

			return $wrapper;
		}

		/**
		 * This function outputs a wrapper element before pagination on Conductor Widgets.
		 */
		public function conductor_widget_pagination_before() {
		?>
			<footer class="pagination">
		<?php
		}

		/**
		 * This function outputs a wrapper element after pagination on Conductor Widgets.
		 */
		public function conductor_widget_pagination_after() {
		?>
			</footer>
		<?php
		}

		/*****************
		 * Gravity Forms *
		 *****************/

		/**
		 * This function adds the HTML5 placeholder attribute to forms with a CSS class of the following:
		 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
		 */
		public function gform_field_input( $input, $field, $value, $lead_id, $form_id ) {
			$form_meta = RGFormsModel::get_form_meta( $form_id ); // Get form meta

			// Ensure we have at least one CSS class
			if ( isset( $form_meta['cssClass'] ) ) {
				$form_css_classes = explode( ' ', $form_meta['cssClass'] );

				// Ensure the current form has one of our supported classes and alter the field accordingly if we're not on admin
				if ( ! is_admin() && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
					$input = '<div class="ginput_container"><input name="input_' . $field['id'] . '" id="input_' . $form_id . '_' . $field['id'] . '" type="text" value="" class="large" placeholder="' . $field['label'] . '" /></div>';
			}

			return $input;
		}

		/**
		 * This function alters the confirmation message on forms with a CSS class of the following:
		 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
		 */
		public function gform_confirmation( $confirmation, $form, $lead, $ajax ) {
			// Ensure we have at least one CSS class
			if ( isset( $form['cssClass'] ) ) {
				$form_css_classes = explode( ' ', $form['cssClass'] );

				// Confirmation message is set and form has one of our supported classes (alter the confirmation accordingly)
				if ( $form['confirmation']['type'] === 'message' && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
					$confirmation = '<div class="mc-gravity-confirmation mc_gravity-confirmation mc-newsletter-confirmation mc_newsletter-confirmation">' . $confirmation . '</div>';
			}

			return $confirmation;
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function inserts a value into an array before or after a specified key.
		 */
		public function array_insert( $type, $value, $action, $key, $original = array() ) {
			// Switch based on type
			switch ( $type ) {
				// Sidebar
				case 'sidebar':
					global $wp_registered_sidebars;

					// Where should we look (in global or passed original data)
					$where = ( ! empty( $original ) ) ? $original: $wp_registered_sidebars;

					// Check to see if the array key exists in the current array
					if ( array_key_exists( $key, $where ) ) {
						$new = array();

						foreach ( $where as $k => $v ) {
							// Before
							if ( $k === $key && $action == 'before' )
								$new[$value['id']] = $value;

							// Current
							$new[$k] = $v;

							// After
							if ( $k === $key && $action == 'after' )
								$new[$value['id']] = $value;
						}

						return $new;
					}

					// No key found, return the original array
					return $where;
				break;
				// Settings Section
				case 'settings-section':
					global $wp_settings_sections;

					// Where should we look (in global or passed original data)
					$where = ( ! empty( $original ) ) ? $original: $wp_settings_sections;

					// Check to see if the array key exists in the current array
					if ( array_key_exists( $key, $where ) ) {
						$new = array();
						$settings_section = $value;
						unset( $settings_section['page'] );

						foreach ( $where as $k => $v ) {
							// Before
							if ( $k === $key && $action == 'before' )
								$new[$value['id']] = $settings_section;

							// Current
							$new[$k] = $v;

							// After
							if ( $k === $key && $action == 'after' )
								$new[$value['id']] = $settings_section;
						}

						return $new;
					}

					// No key found, return the original array
					return $where;
				break;
			}

			return array();
		}

		/**
		 * This function updates legacy Conductor Widgets to ensure legacy widget displays/sizes are
		 * switched to the new custom (flexbox) display. This function ensures the current version of
		 * Conductor is at least 1.3.0.
		 *
		 * It also updates the order of the output elements to ensure that the author byline output
		 * element is at the bottom.
		 */
		public function update_conductor_widgets( $after_switch_theme = false ) {
			global $sds_theme_options;

			// Grab SDS Theme Options
			$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

			// If Conductor Widget exists and we haven't already updated legacy widget sizes
			if ( function_exists( 'Conduct_Widget' ) && ( ! isset( $sds_theme_options['baton_conductor_widgets_updated'] ) || ! $sds_theme_options['baton_conductor_widgets_updated'] || $after_switch_theme ) ) {
				// Grab the Conductor Widget instance
				$conductor_widget = Conduct_Widget();

				// Grab all Conductor Widget instances
				$all_instances = $conductor_widget->get_settings();

				// If Conductor is greater than 1.2.9 or Conductor Widget instance has the "displays" property, we can check to see if the custom display exists
				if ( $this->conductor_has_flexbox_display( $conductor_widget ) ) {
					// Loop through instances (passing by reference)
					foreach ( $all_instances as $number => &$instance ) {
						// Only if this instance isn't empty
						if ( !empty( $instance ) ) {
							// Legacy display
							if ( in_array( $instance['widget_size'], array( 'small', 'medium', 'large' ) ) ) {
								// Switch based on widget size
								switch ( $instance['widget_size'] ) {
									case 'small':
										// Flexbox Columns (4 columns)
										$instance['flexbox']['columns'] = 4;
										$instance['flexbox_columns'] = 4;
									break;

									// Medium
									case 'medium':
										// Flexbox Columns (2 columns)
										$instance['flexbox']['columns'] = 2;
										$instance['flexbox_columns'] = 2;
									break;

									// Large
									case 'large':
										// Flexbox Columns (1 column)
										$instance['flexbox']['columns'] = 1;
										$instance['flexbox_columns'] = 1;
									break;
								}

								// Widget Size (display)
								$instance['widget_size'] = 'flexbox'; // Custom (Flexbox)
							}
						}
					}

					// Set the update flag
					$sds_theme_options['baton_conductor_widgets_updated'] = true;
					update_option( SDS_Theme_Options::get_option_name(), $sds_theme_options );
				}

				// Only on after_switch_theme
				if ( $after_switch_theme ) {
					/*
					 * Conductor Output Elements
					 */
					$author_byline = array();

					// Remove the reference to the $instance
					unset( $instance );

					// Conductor output elements, Loop through instances (passing by reference)
					foreach ( $all_instances as $number => &$instance ) {
						// Only if this instance isn't empty
						if ( !empty( $instance ) && isset( $instance['output'] ) ) {
							// Loop through output elements
							foreach ( $instance['output'] as $priority => $output )
								// Author Byline (store reference to priority and configuration)
								if ( $output['id'] === 'author_byline' ) {
									$author_byline = $output;

									// Remove author byline
									unset( $instance['output'][$priority] );
								}

							/*
							 * Author Byline (move to bottom of default output elements)
							 */
							$output_elements = array();
							$default_priority_gap = 10;
							$count = 0;

							// Loop through the passed in widget settings
							foreach ( $instance['output'] as $output ) {
								// Increase count
								$count++;

								// Add this element to the output elements
								$output_elements[( $default_priority_gap * $count )] = $output;
							}

							// Author Byline (increase count before multiplying)
							$output_elements[( $default_priority_gap * ++$count )] = $author_byline;

							// Set the default output
							$instance['output'] = $output_elements;
						}
					}
				}

				// Update the database
				$conductor_widget->save_settings( $all_instances );
			}
		}

		/**
		 * This function checks to see if Conductor has the new flexbox display.
		 */
		public function conductor_has_flexbox_display( $conductor_widget = false ) {
			// Bail if Conductor doesn't exist
			if ( ! class_exists( 'Conductor' ) || ! function_exists( 'Conduct_Widget' ) )
				return false;

			// If we don't have a Conductor Widget reference, grab one now
			$conductor_widget = ( ! $conductor_widget ) ? Conduct_Widget() : $conductor_widget;

			// If Conductor is greater than 1.2.9 or Conductor Widget instance has the "displays" property, we can check to see if the custom display exists
			return ( ( version_compare( Conductor::$version, '1.2.9', '>' ) || property_exists( $conductor_widget, 'displays' ) ) && isset( $conductor_widget->displays['flexbox'] ) );
		}
	}


	function Baton_Instance() {
		return Baton::instance();
	}

	// Starts Baton
	Baton_Instance();
}