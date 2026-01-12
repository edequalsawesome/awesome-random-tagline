<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://edequalsaweso.me
 * @since             1.2
 * @package           Awesome_Random_Tagline
 *
 * @wordpress-plugin
 * Plugin Name:       Awesome Random Site Tagline
 * Plugin URI:        https://edequalsaweso.me/random-site-description
 * Description:       Adds a "Random Site Tagline" variation to the core Site Tagline block, displaying a random tagline from a custom list on each page load.
 * Version:           2026.01.12
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            eD! Thomas
 * Author URI:        https://edequalsaweso.me/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       awesome-random-tagline
 * Domain Path:       /languages
 */
 
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'AWESOME_RANDOM_TAGLINE_VERSION', '2026.01.12' );
define( 'AWESOME_RANDOM_TAGLINE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AWESOME_RANDOM_TAGLINE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AWESOME_RANDOM_TAGLINE_PLUGIN_FILE', __FILE__ );
define( 'AWESOME_RANDOM_TAGLINE_PLUGIN_BASE', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_awesome_random_description_block() {
	// Add any activation functionality here if needed
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_awesome_random_description_block() {
	// Add any deactivation functionality here if needed
}

register_activation_hook( __FILE__, 'activate_awesome_random_description_block' );
register_deactivation_hook( __FILE__, 'deactivate_awesome_random_description_block' );

/**
 * Load plugin text domain for translations.
 */
function awesome_random_description_block_load_textdomain() {
	load_plugin_textdomain( 'awesome-random-tagline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'awesome_random_description_block_load_textdomain' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once AWESOME_RANDOM_TAGLINE_PLUGIN_DIR . 'includes/class-awesome-random-tagline.php';

/**
 * The new site tagline variation class.
 * This extends the core site-tagline block with random tagline functionality.
 */
require_once AWESOME_RANDOM_TAGLINE_PLUGIN_DIR . 'includes/class-random-tagline-variation.php';