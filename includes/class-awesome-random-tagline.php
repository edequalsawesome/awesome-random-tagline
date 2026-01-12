<?php
/**
 * Random Description Block Class
 *
 * @package     Random_Site_Description
 * @since       1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Awesome_Random_Description_Block class
 */
class Awesome_Random_Description_Block {

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
		// Register block.
		add_action( 'init', array( $this, 'register_block' ) );

		// Register frontend scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );

		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . AWESOME_RANDOM_TAGLINE_PLUGIN_BASE, array( $this, 'add_plugin_links' ) );
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
	 * Register the block.
	 */
	public function register_block() {
		// Register block script and style.
		$asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . 'build/index.asset.php';

		wp_register_script(
			$this->slug . '-editor',
			plugins_url( 'build/index.js', dirname( __FILE__ ) ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_register_style(
			$this->slug . '-editor',
			plugins_url( 'build/index.css', dirname( __FILE__ ) ),
			array(),
			$this->version
		);

		wp_register_style(
			$this->slug,
			plugins_url( 'build/style-index.css', dirname( __FILE__ ) ),
			array(),
			$this->version
		);

		// Register block.
		register_block_type_from_metadata(
			plugin_dir_path( dirname( __FILE__ ) ) . 'src/blocks/random-description',
			array(
				'editor_script'   => $this->slug . '-editor',
				'editor_style'    => $this->slug . '-editor',
				'style'           => $this->slug,
				'render_callback' => array( $this, 'render_block' ),
			)
		);

		// Set translations.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $this->slug . '-editor', 'awesome-random-tagline', plugin_dir_path( dirname( __FILE__ ) ) . 'languages' );
		}
	}

	/**
	 * Register frontend assets.
	 */
	public function register_frontend_assets() {
		$asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . 'build/frontend.asset.php';

		wp_register_script(
			$this->slug . '-frontend',
			plugins_url( 'build/frontend.js', dirname( __FILE__ ) ),
			array(),
			$asset_file['version'],
			true
		);
		
		// Only register, don't enqueue here - we'll enqueue in render_block when needed
	}

	/**
	 * Render the block on the server side.
	 *
	 * @param array $attributes Block attributes.
	 * @param string $content Block content.
	 * @return string Block content.
	 */
	public function render_block( $attributes, $content ) {
		// Security check: Validate user permissions
		if ( ! $this->validate_user_permissions() ) {
			return '';
		}
		
		// Validate and sanitize all attributes
		$attributes = is_array( $attributes ) ? $attributes : array();
		$safe_attributes = $this->validate_block_attributes( $attributes );
		
		// Extract the safe attributes
		$class_name = isset( $safe_attributes['className'] ) ? $safe_attributes['className'] : '';
		$style = isset( $safe_attributes['style'] ) ? $this->build_styles( $safe_attributes ) : '';
		$align = isset( $safe_attributes['align'] ) ? 'align' . $safe_attributes['align'] : '';
		$text_align = isset( $safe_attributes['textAlign'] ) ? $safe_attributes['textAlign'] : '';
		$taglines = isset( $safe_attributes['taglines'] ) ? $safe_attributes['taglines'] : array();
		
		// Select a random tagline on the server-side to prevent flash
		$current_tagline = '';
		if ( ! empty( $taglines ) ) {
			// Use wp_rand() instead of array_rand() to ensure each block instance 
			// gets a different random selection on the same page
			$random_index = wp_rand( 0, count( $taglines ) - 1 );
			$current_tagline = wp_kses_post( $taglines[ $random_index ] );
		}

		// Add the align class if it exists
		if ( ! empty( $align ) ) {
			$class_name .= ' ' . $align;
		}

		// Add the text align class if it exists
		if ( ! empty( $text_align ) ) {
			$class_name .= ' has-text-align-' . $text_align;
		}

		// Build the block HTML with accessibility attributes
		$html = sprintf(
			'<div class="wp-block-awesome-random-description %1$s" style="%2$s" aria-live="polite" role="region" aria-label="Site description">
				<div class="random-description-content">
					<p>%3$s</p>
				</div>
			</div>',
			esc_attr( trim( $class_name ) ),
			esc_attr( $style ),
			esc_html( $current_tagline )
		);

		return $html;
	}

	/**
	 * Build CSS styles from block attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Inline CSS styles.
	 */
	private function build_styles( $attributes ) {
		$styles = array();

		// Get style attribute
		$style = isset( $attributes['style'] ) ? $attributes['style'] : array();
		
		// Allowed CSS properties and sides for security
		$allowed_css_properties = array( 'padding', 'margin' );
		$allowed_css_sides = array( 'top', 'right', 'bottom', 'left' );
		
		// Handle spacing styles
		if ( isset( $style['spacing'] ) ) {
			$spacing = $style['spacing'];
			
			// Handle padding
			if ( isset( $spacing['padding'] ) && is_array( $spacing['padding'] ) ) {
				foreach ( $spacing['padding'] as $side => $value ) {
					// Validate side parameter
					if ( ! in_array( $side, $allowed_css_sides, true ) ) {
						continue;
					}
					
					// Sanitize CSS value
					$sanitized_value = $this->sanitize_css_value( $value );
					if ( $sanitized_value ) {
						$styles[] = sprintf( 'padding-%s: %s', sanitize_key( $side ), $sanitized_value );
					}
				}
			}
			
			// Handle margin
			if ( isset( $spacing['margin'] ) && is_array( $spacing['margin'] ) ) {
				foreach ( $spacing['margin'] as $side => $value ) {
					// Validate side parameter
					if ( ! in_array( $side, $allowed_css_sides, true ) ) {
						continue;
					}
					
					// Sanitize CSS value
					$sanitized_value = $this->sanitize_css_value( $value );
					if ( $sanitized_value ) {
						$styles[] = sprintf( 'margin-%s: %s', sanitize_key( $side ), $sanitized_value );
					}
				}
			}
		}

		return ! empty( $styles ) ? implode( '; ', $styles ) : '';
	}

	/**
	 * Sanitize CSS values to prevent injection attacks.
	 *
	 * @param string $value CSS value to sanitize.
	 * @return string|false Sanitized CSS value or false if invalid.
	 */
	private function sanitize_css_value( $value ) {
		// Ensure we have a string
		if ( ! is_string( $value ) ) {
			return false;
		}
		
		// Allow WordPress preset values
		if ( preg_match( '/^var:preset\|spacing\|[a-z0-9\-_]+$/i', $value ) ) {
			$spacing_value = str_replace( 'var:preset|spacing|', '', $value );
			// Sanitize the spacing value to ensure it's safe
			$spacing_value = preg_replace( '/[^a-z0-9\-_]/i', '', $spacing_value );
			return sprintf( 'var(--wp--preset--spacing--%s)', sanitize_key( $spacing_value ) );
		}
		
		// Allow safe CSS units (px, em, rem, %, vh, vw) with numeric values
		if ( preg_match( '/^(\d+(?:\.\d+)?)(px|em|rem|%|vh|vw)$/i', $value ) ) {
			return sanitize_text_field( $value );
		}
		
		// Allow zero value
		if ( $value === '0' ) {
			return '0';
		}
		
		return false; // Reject all other values
	}

	/**
	 * Add links to plugin action links.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_plugin_links( $links ) {
		$plugin_links = array(
			'<a href="https://wordpress.org/support/plugin/random-site-description/" target="_blank">' . __( 'Support', 'awesome-random-tagline' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Validate user permissions for block operations.
	 *
	 * @return bool True if user has permission, false otherwise.
	 */
	private function validate_user_permissions() {
		// Allow frontend rendering for all users
		if ( ! is_admin() ) {
			return true;
		}
		
		// In admin, check if user can edit posts (minimum capability for blocks)
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Validate and sanitize block attributes.
	 *
	 * @param array $attributes Raw block attributes.
	 * @return array Sanitized attributes.
	 */
	private function validate_block_attributes( $attributes ) {
		$safe_attributes = array();
		
		// Validate className
		if ( isset( $attributes['className'] ) && is_string( $attributes['className'] ) ) {
			$safe_attributes['className'] = sanitize_html_class( $attributes['className'] );
		}
		
		// Validate align
		if ( isset( $attributes['align'] ) && is_string( $attributes['align'] ) ) {
			$allowed_alignments = array( 'left', 'center', 'right', 'wide', 'full' );
			if ( in_array( $attributes['align'], $allowed_alignments, true ) ) {
				$safe_attributes['align'] = sanitize_key( $attributes['align'] );
			}
		}
		
		// Validate textAlign
		if ( isset( $attributes['textAlign'] ) && is_string( $attributes['textAlign'] ) ) {
			$allowed_text_alignments = array( 'left', 'center', 'right', 'justify' );
			if ( in_array( $attributes['textAlign'], $allowed_text_alignments, true ) ) {
				$safe_attributes['textAlign'] = sanitize_key( $attributes['textAlign'] );
			}
		}
		
		// Validate taglines
		if ( isset( $attributes['taglines'] ) && is_array( $attributes['taglines'] ) ) {
			$safe_taglines = array();
			foreach ( $attributes['taglines'] as $tagline ) {
				if ( is_string( $tagline ) && strlen( trim( $tagline ) ) > 0 && strlen( $tagline ) <= 500 ) {
					$safe_taglines[] = sanitize_text_field( $tagline );
				}
			}
			$safe_attributes['taglines'] = array_slice( $safe_taglines, 0, 100 ); // Limit to 100 taglines
		}
		
		// Validate style (will be processed by build_styles)
		if ( isset( $attributes['style'] ) && is_array( $attributes['style'] ) ) {
			$safe_attributes['style'] = $attributes['style']; // build_styles will handle validation
		}
		
		return $safe_attributes;
	}

}

// Initialize the class.
Awesome_Random_Description_Block::get_instance();