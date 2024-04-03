<?php
/**
 * Plugin Name: Accessible Overlay Search
 * Description: A WordPress plugin for implementing an accessible overlay search.
 * Version: 1.0
 * Author: Henkka Avoketo
 * Text Domain: overlay-search
 */

// Define the namespace for the plugin
namespace AccessibleOverlaySearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'AOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Autoload plugin classes
require_once AOS_PLUGIN_DIR . 'autoload.php';

// Initialize the plugin
function initialize_plugin() {
    // Instantiate the main plugin class
    $plugin = new Plugin();

    // Initialize the plugin
    $plugin->init();
}
add_action( 'init', __NAMESPACE__ . '\initialize_plugin' );

// Add activation and deactivation hooks
register_activation_hook( __FILE__, [ 'AccessibleOverlaySearch\Plugin', 'activate_plugin' ] );
register_deactivation_hook( __FILE__, [ 'AccessibleOverlaySearch\Plugin', 'deactivate_plugin' ] );
