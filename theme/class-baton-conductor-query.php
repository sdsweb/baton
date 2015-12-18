<?php
/**
 * Baton Conductor Query - Default class used for querying content within Baton Conductor.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if( ! class_exists( 'Baton_Conductor_Query' ) ) {
	class Baton_Conductor_Query {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var array
		 */
		public $instance = false;

		/**
		 * @var mixed, Content query
		 */
		public $query = null;

		/**
		 * @var string, Query type (single or many)
		 */
		public $query_type = 'many';

		/**
		 * @var string, Post Not In (current query)
		 */
		public static $query_post__not_in = array();

		/**
		 * @var mixed, Instance of Query used for output
		 */
		public $output = null;

		/**
		 * @var array
		 */
		public $post_count = -1;

		/**
		 * @var WP_Post, Current post
		 */
		public $post = null;

		/**
		 * @var WP_Post, Global post
		 */
		public $global_post = null;

		/**
		 * @var array, List of actions/filters this class has added/created
		 */
		public $hooks = array();

		/**
		 * @var int, List of actions/filters this class has added/created
		 */
		public $display_content_args_count = 0;


		/**
		 * @var Conductor, Instance of the class
		 */
		protected static $_instance;

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}


		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		function __construct( $args = array() ) {
			// Populate properties
			$keys = array_keys( get_class_vars( __CLASS__ ) );
			foreach ( $keys as $key ) {
				if ( isset( $args[$key] ) )
					$this->$key = $args[$key];
			}

			// TODO: which vars should the user not be able to customize?
			$this->query = null;

			// If we have an instance
			if ( $this->instance ) {
				// Query content piece(s)
				$this->query( $this->query_type );

				// Output Hooks
				$this->hooks['baton_conductor_display_content'] = array();

				// Opening Wrapper Elements
				add_action( 'baton_conductor_display_content', array( $this, 'baton_conductor_wrapper' ), 1, $this->display_content_args_count );
				add_action( 'baton_conductor_display_content', array( $this, 'baton_conductor_content_wrapper' ), 2, $this->display_content_args_count );

				$this->hooks['baton_conductor_display_content'] += array(
					1 => array( get_class(), 'baton_conductor_wrapper' ), // Static callback
					2 => array( get_class(), 'baton_conductor_content_wrapper' ) // Static callback
				);

				// Sortable Elements
				if ( isset( $this->instance['output'] ) && ! empty( $this->instance['output'] ) )
					foreach ( $this->instance['output'] as $priority => $element ) {
						$callback = $element['callback'];

						// Array callback
						// Only add this action if the callback exists, it's callable, and the element is visible
						if ( is_array( $callback ) && method_exists( $callback[0], $callback[1] ) && $element['visible'] ) {
							add_action( 'baton_conductor_display_content', array( $callback[0], $callback[1] ), $priority, $this->display_content_args_count );

							$this->hooks['baton_conductor_display_content'] += array( $priority => array( $callback[0], $callback[1] ) );

							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );
							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );

						}
						// String/other callbacks within this class
						// Only add this action if the callback exists, it's callable, and the element is visible
						else if ( ! is_array( $callback ) && method_exists( $this, $callback ) && is_callable( array( $this, $callback ) ) && $element['visible'] ) {
							add_action( 'baton_conductor_display_content', array( $this, $callback ), $priority, $this->display_content_args_count );

							$this->hooks['baton_conductor_display_content'] += array( $priority => array( get_class(), $callback ) );

							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );
							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );
						}

						// String/other callbacks outside of this class
						// Only add this action if the callback exists, it's callable, and the element is visible
						else if ( ! is_array( $callback ) && function_exists( $callback ) && is_callable( $callback ) && $element['visible'] ) {
							add_action( 'baton_conductor_display_content', $callback, $priority, $this->display_content_args_count );

							$this->hooks['baton_conductor_display_content'] += array( $priority => $callback );

							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );
							do_action( 'baton_conductor_query_add_display_content', $element, $priority, $this->display_content_args_count, $this );
						}
					}

				// Closing Wrapper Elements
				add_action( 'baton_conductor_display_content', array( $this, 'baton_conductor_content_wrapper_close' ), 999, $this->display_content_args_count );
				add_action( 'baton_conductor_display_content', array( $this, 'baton_conductor_wrapper_close' ), 1000, $this->display_content_args_count );

				$this->hooks['baton_conductor_display_content'] += array(
					999 => array( get_class(), 'baton_conductor_content_wrapper_close' ), // Static callback
					1000 => array( get_class(), 'baton_conductor_wrapper_close' ) // Static callback
				);

				/*
				 * Baton Conductor logic to move the wrapper elements accordingly
				 */

				$output_elements_before_featured_image = 0;
				$featured_image_priority = 0;
				$featured_image_only = true; // Flag to determine if the featured image is the only visible output element

				// If we have hooks
				if ( ! empty( $this->hooks ) && isset( $this->hooks['baton_conductor_display_content'] ) ) {
					// Store a reference to the list of hooks for this widget
					$hooks = &$this->hooks['baton_conductor_display_content'];

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
						foreach ( $this->instance['output'] as $priority => $output ) {
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
								// baton_conductor_content_wrapper; $callback[1] is the function name
								if ( is_array( $callback ) && $callback[1] === 'baton_conductor_content_wrapper' && $priority < $featured_image_priority ) {
									// Determine new priority for content wrapper opening element
									$new_priority = ( $priority + $featured_image_priority );

									// Remove the default action (if there are no output elements before the featured image)
									if ( ! $output_elements_before_featured_image ) {
										remove_action( 'baton_conductor_display_content', array( $this, $callback[1] ), $priority, $this->display_content_args_count );

										// Adjust the "hooks" property
										unset( $hooks[$priority] );
									}
									// Otherwise we have elements before the featured image, ensure the default wrapper is closed
									else {
										// Determine new priority for content wrapper closing element
										$closing_wrapper_priority = ( $featured_image_priority - $priority );

										// Add the action before the featured image
										add_action( 'baton_conductor_display_content', array( $this, 'baton_conductor_content_wrapper_close' ), $closing_wrapper_priority, $this->display_content_args_count );

										// Adjust the "hooks" property
										$hooks += array( $closing_wrapper_priority => array( get_class( $this ), 'baton_conductor_content_wrapper_close' ) ); // Static callback
									}

									// Add the action after the featured image element
									add_action( 'baton_conductor_display_content', array( $this, $callback[1] ), $new_priority, $this->display_content_args_count );

									// Adjust the "hooks" property
									$hooks += array( $new_priority => array( get_class( $this ), $callback[1] ) ); // Static callback
									ksort( $hooks ); // Sort the hooks by key
								}
					}
				}
			}

			return $this;
		}

		/**
		 * This function is used to create a query and return results from that query.
		 * @uses WP_Query
		 */
		public function query( $type = false ) {
			// Use class query_type if no $type was passed
			if ( ! $type )
				$type = $this->query_type;

			// Many pieces of content
			if ( $type === 'many' ) {
				global $wp_query;

				// Post count
				$this->post_count = wp_count_posts( 'post' );

				/**
				 * Set up query arguments
				 */
				$query_args = array(
					'post_status' => 'publish',
					'posts_per_page' => ( ! empty( $this->instance['posts_per_page'] ) && $this->instance['posts_per_page'] !== 0 ) ? $this->instance['posts_per_page'] : $this->post_count->publish,
					'cat' => $this->instance['category'],
					'ignore_sticky_posts' => true,
					'orderby' => 'date',
					'order' => 'DESC',
					'_conductor' => array()
				);

				// Get the "true" paged query variable from the main query (defaulting to 1)
				$paged = $query_args['paged'] = ( int ) get_query_var( 'paged' );

				// Use the paged query var if set
				if ( empty( $query_args['paged'] ) && isset( $wp_query->query['paged'] ) )
					$paged = $query_args['paged'] = ( int ) $wp_query->query['paged'];
				// Single post uses "page" instead of "paged"
				else if ( is_single() && ( int ) get_query_var( 'page' ) )
					$paged = $query_args['paged'] = ( int ) get_query_var( 'page' );
				// Otherwise assume page 1
				else if ( empty( $query_args['paged'] ) )
					$paged = $query_args['paged'] = 1;


				// Set a custom parameter so that we know when a Conductor query is executed
				$query_args['_conductor'] += array(
					'instance' => $this->instance,
					'max_num_posts' => '',
					'paged' => $paged
				);

				// Allow filtering of query arguments
				$query_args = apply_filters( 'baton_conductor_query_args', $query_args, $type, $this );

				$this->query = new WP_Query( $query_args ); // Initiate the query
			}
			// Other Types
			else
				$this->query = apply_filters( 'baton_conductor_query_other_types', $this->query, $type, $this->instance, $this );
		}

		/**
		 * This function is used to retrieve the current query (with results)
		 */
		public function get_query() {
			return $this->query;
		}

		/**
		 * This function resets the global $post variable using data stored on the class.
		 */
		public function reset_global_post() {
			global $post;

			// If we have a global $post reference
			if ( $this->global_post ) {
				// Reset/restore the global $post
				$post = $this->global_post;

				// Clear the global $post reference
				$this->global_post = null;
			}
		}

		/**
		 * This function is used to determine if the current query has posts (uses query class have_posts() if exists).
		 */
		public function have_posts() {
			// Use the query object's default have_posts() method if it exists
			if ( method_exists( $this->query, 'have_posts' ) )
				return $this->query->have_posts();

			return apply_filters( 'baton_conductor_query_have_posts', false, $this );
		}

		/**
		 * This function is used to move to the next post within the current query (uses query class next_post() if exists).
		 */
		public function next_post() {
			// Use the query object's default next_post() method if it exists
			if ( method_exists( $this->query, 'next_post' ) )
				$this->query->next_post();

			do_action( 'baton_conductor_query_next_post', $this->query->post, $this );

			return $this->query->post;
		}

		/**
		 * This function is used to move to the next post within the current query (uses query class the_post() if exists
		 * and will also set global $post data).
		 */
		public function the_post() {
			// Use the query object's default the_post() method if it exists
			if ( method_exists( $this->query, 'the_post' ) )
				$this->query->the_post();

			do_action( 'baton_conductor_query_the_post', $this->query->post, $this );

			return $this->query->post;
		}

		/**
		 * This function is used to determine the current post in the query.
		 */
		public function get_current_post( $single = false ) {
			// Single Content Pieces
			if ( $single )
				$post = $this->get_query(); // WP_Post Object
			// Multiple Content Pieces
			else
				$post = $this->get_query()->post; // WP_Post Object

			return apply_filters( 'baton_conductor_query_current_post', $post, $single, $this );
		}

		/**
		 * This function determines if the current query has pagination.
		 */
		public function has_pagination() {
			$has_pagination = false;

			/*
			 * Pagination checks:
			 *
			 * - Make sure posts_per_page is not empty,
			 * - Make sure posts_per_page does not equal max_num_pages,
			 * - Make sure found_posts is greater than posts_per_page,
			 * - Or if we're on a Conductor query on the last page
			 */
			if ( ! empty( $this->instance['posts_per_page'] ) && $this->instance['posts_per_page'] !== 0 && $this->instance['posts_per_page'] < $this->post_count->publish )
				$has_pagination = true;

			return apply_filters( 'baton_conductor_query_has_pagination', $has_pagination, $this );
		}

		/**
		 * This function returns or will echo pagination for a query depending on parameters.
		 *
		 */
		public function get_pagination_links( $query = false, $echo = true ) {
			// Use class query if no $query was passed
			if ( empty( $query ) )
				$query = $this->query;

			// Permalink structure
			$permalink_structure = get_option( 'permalink_structure' );

			$paginate_links_args = array(
				'base' => untrailingslashit( esc_url( get_pagenum_link() ) ) . '%_%', // %_% will be replaced with format below
				'format' => ( $permalink_structure ) ? '/page/%#%/' : '&paged=%#%', // %#% will be replaced with page number
				'current' => max( 1, $query->get( 'paged' ) ), // Get whichever is the max out of 1 and the current page count
				'total' => ( ! empty( $this->instance['posts_per_page'] ) && $this->instance['posts_per_page'] !== 0 ) ? ceil( $this->post_count->publish / $this->instance['posts_per_page'] ) : 1, // Get total number of pages in current query
				'next_text' => __( 'Next &#8594;', 'baton' ),
				'prev_text' => __( '&#8592; Previous', 'baton' ),
				'type' => ( ! $echo ) ? 'array' : 'list'  // Output this as an array or unordered list
			);

			// Front page
			if ( is_front_page() )
				$paginate_links_args['format'] = ( $permalink_structure ) ? '/page/%#%/' : '/?paged=%#%';

			// Single post uses "page" instead of "paged"
			if ( is_single() ) {
				$paginate_links_args['base'] = untrailingslashit( esc_url( get_permalink() ) ) . '%_%';
				$paginate_links_args['format'] = ( get_option( 'permalink_structure' ) ) ? '/%#%/' : '&page=%#%'; // %#% will be replaced with page number
			}

			$paginate_links_args = apply_filters( 'baton_conductor_query_paginate_links_args', $paginate_links_args, $query, $echo, $this );

			$paginate_links = paginate_links( $paginate_links_args );

			if ( $echo )
				echo $paginate_links;
			else
				return $paginate_links;
		}


		/**
		 * This function gets the excerpt of a specific post ID or object.
		 * @see https://pippinsplugins.com/a-better-wordpress-excerpt-by-id-function/
		 */
		public function get_excerpt_by_id( $post, $length = 55, $tags = array(), $extra = '...' ) {
			// Get the post object of the passed ID
			if( is_int( $post ) )
				$post = get_post( $post );
			else if( ! is_object( $post ) )
				return false;

			// Only return the password form if excerpt length is greater than 0
			if ( $length && post_password_required( $post ) )
				return get_the_password_form( $post );

			// Allowed HTML tags in excerpt
			$tags = apply_filters( 'baton_conductor_excerpt_allowable_tags', ( array ) $tags, $post );
			$tags = implode( '', $tags );

			$the_excerpt = ( has_excerpt( $post->ID ) ) ? $post->post_excerpt : $post->post_content;
			$the_excerpt = strip_shortcodes( strip_tags( $the_excerpt, $tags ) );
			$words_array = preg_split( "/[\n\r\t ]+/", $the_excerpt, $length + 1, PREG_SPLIT_NO_EMPTY );
			$sep = ' ';

			if ( count( $words_array ) > $length ) {
				array_pop( $words_array );
				$the_excerpt = implode( $sep, $words_array );
				$the_excerpt .= $extra;
			}
			else
				$the_excerpt = implode( $sep, $words_array );

			return apply_filters( 'the_content', $the_excerpt );
		}

		/**
		 * This function returns the content of a specific post ID or object. The functionality is virtually identical
		 * to get_the_content() in core except it can be used outside of "The Loop" and some bits of functionality
		 * were removed as they were not needed (global variables, $more, some teaser functionality, read more).
		 *
		 * Retrieve the post content.
		 *
		 */
		public function get_content_by_id( $post, $strip_teaser = false ) {
			// Get the post object of the passed ID
			if( is_int( $post ) )
				$post = get_post( $post );
			else if( ! is_object( $post ) )
				return false;

			$output = '';
			$has_teaser = false;

			// If post password required and it doesn't match the cookie.
			if ( post_password_required( $post ) )
				return get_the_password_form( $post );

			$content = $post->post_content;
			if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
				$content = explode( $matches[0], $content, 2 );

				$has_teaser = true;
			} else {
				$content = array( $content );
			}

			if ( false !== strpos( $post->post_content, '<!--noteaser-->' ) )
				$strip_teaser = true;

			$teaser = $content[0];

			if ( $strip_teaser && $has_teaser )
				$teaser = '';

			$output .= $teaser;

			$output = apply_filters( 'the_content', $output );
			$output = str_replace( ']]>', ']]&gt;', $output );

			return $output;
		}

		/**
		 * This function returns CSS classes for use in this widget.
		 */
		public function get_wrapper_css_classes( $post, $instance, $baton_conductor ) {
			$css_classes = get_post_class( $baton_conductor->get_css_classes( $instance ), $post->ID );

			// If we have hooks
			if ( ! empty( $baton_conductor->baton_conductor_query->hooks ) && isset( $baton_conductor->baton_conductor_query->hooks['baton_conductor_display_content'] ) ) {
				$content_wrapper_elements = 0;

				// Store a reference to the list of hooks for this widget
				$hooks = &$baton_conductor->baton_conductor_query->hooks['baton_conductor_display_content'];

				// Loop through hooks to find the featured image priority
				foreach ( $hooks as $priority => $callback )
					// conductor_widget_content_wrapper; $callback[1] is the function name
					if ( is_array( $callback ) && $callback[1] === 'baton_conductor_content_wrapper' )
						$content_wrapper_elements++;

				// If there are multiple content wrapper elements
				if ( $content_wrapper_elements > 1 ) {
					// Add CSS classes
					$css_classes[] = 'multiple-content-wrapper-elements';
					$css_classes[] = 'conductor-multiple-content-wrapper-elements';
					$css_classes[] = 'baton-multiple-content-wrapper-elements';
				}
			}

			// Ensure CSS classes are a string
			$css_classes = implode( ' ', $css_classes );

			return $css_classes;
		}

		/**
		 * This function returns the HTML element name used for the main wrapper elements.
		 */
		public function get_wrapper_html_element( $post, $instance, $baton_conductor, $query ) {
			return apply_filters( 'baton_conductor_wrapper_html_element', 'div', $post, $instance, $baton_conductor, $query, $this );
		}

		/**
		 * This function returns the HTML element name used for content wrapper elements.
		 */
		public function get_content_wrapper_html_element( $post, $instance, $baton_conductor, $query ) {
			return apply_filters( 'baton_conductor_content_wrapper_html_element', 'section', $post, $instance, $baton_conductor, $query, $this );
		}


		/************************
		 * Output Functionality *
		 ************************/

		/**
		 * This function outputs the opening wrapper for Conductor Widgets.
		 */
		public function baton_conductor_wrapper( $post, $instance, $baton_conductor, $query ) {
		?>
			<<?php echo $this->get_wrapper_html_element( $post, $instance, $baton_conductor, $query  ); ?> class="<?php echo apply_filters( 'baton_conductor_wrapper_css_classes', $this->get_wrapper_css_classes( $post, $instance, $baton_conductor ), $post, $instance, $baton_conductor, $query ); ?>">
		<?php
			do_action( 'baton_conductor_output_before', $post, $instance );
		}

		/**
		 * This function outputs the opening content wrapper for Conductor Widgets.
		 */
		public function baton_conductor_content_wrapper( $post, $instance, $baton_conductor, $query ) {
		?>
			<<?php echo $this->get_content_wrapper_html_element( $post, $instance, $baton_conductor, $query ); ?> class="<?php echo apply_filters( 'baton_conductor_content_wrapper_css_classes', 'content post-content conductor-cf', $post, $instance, $baton_conductor, $query ); ?><?php echo ( has_post_thumbnail( $post->ID ) ) ? ' has-post-thumbnail content-has-post-thumbnail' : false; ?>">
		<?php
		}

		/**
		 * This function outputs the featured image for Conductor Widgets.
		 */
		public function baton_conductor_featured_image( $post, $instance, $baton_conductor, $query ) {
			$output = array();

			// Find the featured image output element data
			foreach ( $instance['output'] as $output_element )
				// Featured Image
				if ( $output_element['id'] === 'featured_image' ) {
					$output = $output_element;

					break;
				}

			do_action( 'baton_conductor_featured_image_before', $post, $instance );

			if ( has_post_thumbnail( $post->ID ) ) :
		?>
				<div class="thumbnail post-thumbnail featured-image <?php echo ( ! $output['link'] ) ? 'no-link' : false; ?>">
					<?php
						// If a featured image size is set
						if ( ! empty( $instance['post_thumbnails_size'] ) )
							$baton_conductor_thumbnail_size = $instance['post_thumbnails_size'];
						else
							// Switch based on the number of columns
							switch ( $instance['flexbox_columns'] ) {
								// "Large"
								case 1:
									$baton_conductor_thumbnail_size = 'large';
								break;
								// "Medium"
								case 2:
								case 3:
									$baton_conductor_thumbnail_size = 'medium';
								break;
								// "Small"
								case 4:
								case 5:
								case 6:
									$baton_conductor_thumbnail_size = 'thumbnail';
								break;
							}

						$baton_conductor_thumbnail_size = apply_filters( 'baton_conductor_featured_image_size', $baton_conductor_thumbnail_size, $instance, $post );

						// Link featured image to post
						if ( $output['link'] ) :
					?>
							<a href="<?php echo get_permalink( $post->ID ); ?>">
								<?php echo get_the_post_thumbnail( $post->ID, $baton_conductor_thumbnail_size ); ?>
							</a>
					<?php
						// Just output the featured image
						else:
							echo get_the_post_thumbnail( $post->ID, $baton_conductor_thumbnail_size );
						endif;
					?>
				</div>
		<?php
			endif;

			do_action( 'baton_conductor_featured_image_after', $post, $instance );
		}

		/**
		 * This function outputs the post title for Conductor Widgets.
		 */
		public function baton_conductor_post_title( $post, $instance, $baton_conductor, $query ) {
			$output = array();

			// Find the post title output element data
			foreach ( $instance['output'] as $output_element )
				// Post Title
				if ( $output_element['id'] === 'post_title' ) {
					$output = $output_element;

					break;
				}

			do_action( 'baton_conductor_post_title_before', $post, $instance );

			$link = ( ! $output['link'] ) ? ' no-link' : false;
		?>
			<h2 class="<?php echo apply_filters( 'baton_conductor_post_title_css_classes', 'post-title entry-title' . $link, $output ); ?>">
				<?php
					// Link post title to post
					if ( $output['link'] ) :
				?>
						<a href="<?php echo get_permalink( $post->ID ); ?>">
							<?php echo get_the_title( $post->ID ); ?>
						</a>
				<?php
					// Just output the post title
					else:
						echo get_the_title( $post->ID );
					endif;
				?>
			</h2>
		<?php
			do_action( 'baton_conductor_post_title_after', $post, $instance );
		}

		/**
		 * This function outputs the author byline for Conductor Widgets.
		 */
		public function baton_conductor_author_byline( $post, $instance, $baton_conductor, $query ) {
			do_action( 'baton_conductor_author_byline_before', $post, $instance );
		?>
			<p class="post-author"><?php printf( __( 'Posted by <a href="%1$s">%2$s</a> on %3$s', 'baton' ) , get_author_posts_url( get_the_author_meta( 'ID' , $post->post_author ) ), get_the_author_meta( 'display_name', $post->post_author ), get_the_time( 'F jS, Y', $post ) ); ?></p>
		<?php
			do_action( 'baton_conductor_author_byline_after', $post, $instance );
		}

		/**
		 * This function outputs the post content for Conductor Widgets.
		 */
		public function baton_conductor_post_content( $post, $instance, $baton_conductor, $query ) {
			$output = array();

			// Find the post content output element data
			foreach ( $instance['output'] as $output_element )
				// Post Content
				if ( $output_element['id'] === 'post_content' ) {
					$output = $output_element;

					break;
				}

			do_action( 'baton_conductor_post_content_before', $post, $instance );

			// Determine which type of content to output
			switch ( $output['value'] ) {
				// Excerpt - the_excerpt()
				case 'excerpt':
					echo $this->get_excerpt_by_id( $post, $instance['excerpt_length'] );
				break;

				// the_content()
				case 'content':
				default:
					echo $this->get_content_by_id( $post );
				break;
			}
			do_action( 'baton_conductor_post_content_after', $post, $instance );
		}

		/**
		 * This function outputs the read more link for Conductor Widgets.
		 */
		public function baton_conductor_read_more( $post, $instance, $baton_conductor, $query ) {
			$output = array();

			// Find the read more output element data
			foreach ( $instance['output'] as $output_element )
				// Read More
				if ( $output_element['id'] === 'read_more' ) {
					$output = $output_element;

					break;
				}

			do_action( 'baton_conductor_read_more_before', $post, $instance );

			// Link read more to post
			if ( $output['link'] ) :
		?>
				<a class="more read-more more-link" href="<?php echo get_permalink( $post->ID ); ?>">
					<?php echo $output['label']; ?>
				</a>
		<?php
			// Just output the read more
			else:
				echo $output['label'];
			endif;

			do_action( 'baton_conductor_read_more_after', $post, $instance );
		}

		/**
		 * This function outputs the closing content wrapper for Conductor Widgets.
		 */
		public function baton_conductor_content_wrapper_close( $post, $instance, $baton_conductor, $query ) {
		?>
			</<?php echo $this->get_content_wrapper_html_element( $post, $instance, $baton_conductor, $query ); ?>>
		<?php
		}

		/**
		 * This function outputs the closing wrapper for Conductor Widgets.
		 */
		public function baton_conductor_wrapper_close( $post, $instance, $baton_conductor, $query ) {
			do_action( 'baton_conductor_output_after', $post, $instance );
		?>
			</<?php echo $this->get_wrapper_html_element( $post, $instance, $baton_conductor, $query ); ?>>
		<?php
		}
	}
}

/**
 * Create an instance of the Baton_Conductor_Query class.
 */
function Baton_Conductor_Query() {
	return Baton_Conductor_Query::instance();
}

Baton_Conductor_Query(); // Conduct your content!