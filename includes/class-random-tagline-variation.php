<?php
/**
 * Random Tagline Variation Class
 *
 * Extends the core site-tagline block with random tagline functionality.
 *
 * @package     Awesome_Random_Tagline
 * @since       2026.01.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Random_Tagline_Variation class
 */
class Random_Tagline_Variation {

	/**
	 * Instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '2026.01.12';

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $slug = 'awesome-random-tagline';

	/**
	 * Constructor
	 */
	private function __construct() {
		// Register scripts for the variation.
		add_action( 'init', array( $this, 'register_variation_assets' ) );

		// Filter the site-tagline block output.
		add_filter( 'render_block_core/site-tagline', array( $this, 'render_random_tagline' ), 10, 2 );

		// Add admin notice for legacy block migration.
		add_action( 'admin_notices', array( $this, 'maybe_show_migration_notice' ) );
		add_action( 'wp_ajax_dismiss_random_tagline_migration_notice', array( $this, 'dismiss_migration_notice' ) );
	}

	/**
	 * Get instance of this class
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register assets for the block variation.
	 */
	public function register_variation_assets() {
		$asset_file = include AWESOME_RANDOM_TAGLINE_PLUGIN_DIR . 'build/index.asset.php';

		// The main script already includes our variation registration.
		// We just need to ensure it's loaded for the site-tagline block.
		wp_register_script(
			$this->slug . '-variation',
			plugins_url( 'build/index.js', dirname( __FILE__ ) ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		// Register editor styles for our custom controls.
		wp_register_style(
			$this->slug . '-variation-editor',
			plugins_url( 'build/index.css', dirname( __FILE__ ) ),
			array(),
			$this->version
		);

		// Enqueue in editor.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Enqueue editor assets.
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( $this->slug . '-variation' );
		wp_enqueue_style( $this->slug . '-variation-editor' );

		// Pass data to JavaScript.
		wp_localize_script(
			$this->slug . '-variation',
			'awesomeRandomTagline',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'awesome_random_tagline' ),
			)
		);
	}

	/**
	 * Filter the site-tagline block output to show random tagline.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Modified block content.
	 */
	public function render_random_tagline( $block_content, $block ) {
		// Check if this is our random tagline variation.
		if ( empty( $block['attrs']['isRandomTagline'] ) ) {
			return $block_content;
		}

		// Get taglines from attributes.
		$taglines = isset( $block['attrs']['taglines'] ) ? $block['attrs']['taglines'] : array();

		// If no taglines, return original content.
		if ( empty( $taglines ) || ! is_array( $taglines ) ) {
			return $block_content;
		}

		// Validate and sanitize taglines.
		$safe_taglines = array();
		foreach ( $taglines as $tagline ) {
			if ( is_string( $tagline ) && strlen( trim( $tagline ) ) > 0 && strlen( $tagline ) <= 500 ) {
				$safe_taglines[] = sanitize_text_field( $tagline );
			}
		}

		// Limit to 100 taglines.
		$safe_taglines = array_slice( $safe_taglines, 0, 100 );

		if ( empty( $safe_taglines ) ) {
			return $block_content;
		}

		// Select a random tagline.
		$random_index    = wp_rand( 0, count( $safe_taglines ) - 1 );
		$random_tagline  = esc_html( $safe_taglines[ $random_index ] );

		// Replace the tagline content in the existing HTML structure.
		// The core site-tagline block outputs: <p class="wp-block-site-tagline">content</p>
		// We need to replace the inner content while preserving the wrapper.
		$modified_content = preg_replace(
			'/(<p[^>]*class="[^"]*wp-block-site-tagline[^"]*"[^>]*>).*?(<\/p>)/s',
			'$1' . $random_tagline . '$2',
			$block_content
		);

		// If regex replacement failed, return original.
		if ( null === $modified_content ) {
			return $block_content;
		}

		return $modified_content;
	}

	/**
	 * Check if there are legacy random description blocks in the database.
	 *
	 * @return bool True if legacy blocks exist.
	 */
	private function has_legacy_blocks() {
		$posts = $this->get_legacy_block_posts();
		return ! empty( $posts );
	}

	/**
	 * Get posts that contain legacy random description blocks.
	 *
	 * @return array Array of post objects with legacy blocks.
	 */
	private function get_legacy_block_posts() {
		global $wpdb;

		// Cache the result to avoid repeated queries.
		$cache_key = 'awesome_random_legacy_block_posts';
		$result    = get_transient( $cache_key );

		if ( false === $result ) {
			// Query uses hardcoded LIKE pattern - no user input, safe from SQL injection.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->get_results(
				"SELECT ID, post_title, post_type, post_status FROM {$wpdb->posts}
				WHERE post_content LIKE '%<!-- wp:awesome-random-description/random-description%'
				AND post_status IN ('publish', 'draft', 'pending', 'private')
				ORDER BY post_modified DESC
				LIMIT 20"
			);

			if ( empty( $result ) ) {
				$result = array();
			}

			set_transient( $cache_key, $result, HOUR_IN_SECONDS );
		}

		return $result;
	}

	/**
	 * Maybe show migration notice if legacy blocks exist.
	 */
	public function maybe_show_migration_notice() {
		// Only show to users who can edit posts.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Check if notice was dismissed.
		if ( get_user_meta( get_current_user_id(), 'awesome_random_tagline_migration_dismissed', true ) ) {
			return;
		}

		// Get posts with legacy blocks.
		$legacy_posts = $this->get_legacy_block_posts();

		// Check if legacy blocks exist.
		if ( empty( $legacy_posts ) ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible" id="awesome-random-tagline-migration-notice">
			<p>
				<strong><?php esc_html_e( 'Awesome Random Site Tagline', 'awesome-random-tagline' ); ?></strong>
			</p>
			<p>
				<?php
				esc_html_e(
					'We detected you have pages using the old "Random Description" block. These blocks will continue to work, but we recommend switching to the new "Random Site Tagline" variation of the core Site Tagline block for better compatibility and styling.',
					'awesome-random-tagline'
				);
				?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Pages with legacy blocks:', 'awesome-random-tagline' ); ?></strong>
			</p>
			<ul style="margin: 0.5em 0 1em 1.5em; list-style: disc;">
				<?php
				foreach ( $legacy_posts as $post ) {
					// Verify user can edit this specific post.
					if ( ! current_user_can( 'edit_post', $post->ID ) ) {
						continue;
					}

					// Get edit link - returns null if user can't edit.
					$edit_link = get_edit_post_link( $post->ID );
					if ( empty( $edit_link ) ) {
						continue;
					}

					// Get post type label for context.
					$post_type_obj = get_post_type_object( $post->post_type );
					$type_label    = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

					// Build display title with fallback for empty titles.
					$display_title = ! empty( $post->post_title ) ? $post->post_title : __( '(no title)', 'awesome-random-tagline' );

					// Add status indicator for non-published posts.
					$status_label = '';
					if ( 'publish' !== $post->post_status ) {
						$status_label = ' <em>(' . esc_html( $post->post_status ) . ')</em>';
					}

					printf(
						'<li><a href="%1$s">%2$s</a> (%3$s)%4$s</li>',
						esc_url( $edit_link ),
						esc_html( $display_title ),
						esc_html( $type_label ),
						$status_label // Already escaped above.
					);
				}
				?>
			</ul>
			<p>
				<?php
				esc_html_e(
					'To migrate: Edit the page, select the old block, and use the "Transform to" option to convert it to the new Site Tagline variation.',
					'awesome-random-tagline'
				);
				?>
			</p>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('#awesome-random-tagline-migration-notice').on('click', '.notice-dismiss', function() {
					$.post(ajaxurl, {
						action: 'dismiss_random_tagline_migration_notice',
						_wpnonce: '<?php echo esc_js( wp_create_nonce( 'dismiss_migration_notice' ) ); ?>'
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the migration notice.
	 */
	public function dismiss_migration_notice() {
		check_ajax_referer( 'dismiss_migration_notice' );

		if ( current_user_can( 'edit_posts' ) ) {
			update_user_meta( get_current_user_id(), 'awesome_random_tagline_migration_dismissed', true );
		}

		wp_die();
	}
}

// Initialize the class.
Random_Tagline_Variation::get_instance();
