<?php
/**
 * Plugin Name: Jezweb Vertical Media
 * Plugin URI: https://jezweb.com.au
 * Description: Display vertical videos (YouTube Shorts, Instagram Reels, TikTok) with responsive 9:16 or 10:16 aspect ratio. Includes Elementor widget, Gutenberg block, and shortcode support.
 * Version: 1.0.1
 * Author: Jezweb
 * Author URI: https://jezweb.com.au
 * Developer: Mahmud Farooque
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jezweb-vertical-media
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package JezwebVerticalMedia
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'JVM_VERSION', '1.0.1' );
define( 'JVM_PLUGIN_FILE', __FILE__ );
define( 'JVM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JVM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JVM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Minimum version requirements
define( 'JVM_MINIMUM_PHP_VERSION', '7.4' );
define( 'JVM_MINIMUM_WP_VERSION', '5.8' );
define( 'JVM_MINIMUM_ELEMENTOR_VERSION', '3.0.0' );

// GitHub updater configuration
define( 'JVM_GITHUB_USERNAME', 'mmhfarooque' );
define( 'JVM_GITHUB_REPO', 'jezweb-vertical-media' );

/**
 * Check PHP version and display admin notice if requirements not met
 *
 * @return bool
 */
function jvm_check_php_version() {
    if ( version_compare( PHP_VERSION, JVM_MINIMUM_PHP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'jvm_php_version_notice' );
        return false;
    }
    return true;
}

/**
 * Display PHP version admin notice
 */
function jvm_php_version_notice() {
    $message = sprintf(
        /* translators: 1: Plugin name 2: Required PHP version 3: Current PHP version */
        esc_html__( '%1$s requires PHP version %2$s or higher. Your current PHP version is %3$s. Please upgrade PHP to use this plugin.', 'jezweb-vertical-media' ),
        '<strong>Jezweb Vertical Media</strong>',
        JVM_MINIMUM_PHP_VERSION,
        PHP_VERSION
    );
    printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
}

/**
 * Check WordPress version and display admin notice if requirements not met
 *
 * @return bool
 */
function jvm_check_wp_version() {
    global $wp_version;
    if ( version_compare( $wp_version, JVM_MINIMUM_WP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'jvm_wp_version_notice' );
        return false;
    }
    return true;
}

/**
 * Display WordPress version admin notice
 */
function jvm_wp_version_notice() {
    global $wp_version;
    $message = sprintf(
        /* translators: 1: Plugin name 2: Required WP version 3: Current WP version */
        esc_html__( '%1$s requires WordPress version %2$s or higher. Your current WordPress version is %3$s. Please upgrade WordPress to use this plugin.', 'jezweb-vertical-media' ),
        '<strong>Jezweb Vertical Media</strong>',
        JVM_MINIMUM_WP_VERSION,
        $wp_version
    );
    printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
}

/**
 * Check if Elementor meets minimum version requirement
 *
 * @return bool
 */
function jvm_check_elementor_version() {
    if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
        return false;
    }
    if ( version_compare( ELEMENTOR_VERSION, JVM_MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'jvm_elementor_version_notice' );
        return false;
    }
    return true;
}

/**
 * Display Elementor version admin notice
 */
function jvm_elementor_version_notice() {
    $message = sprintf(
        /* translators: 1: Plugin name 2: Required Elementor version 3: Current Elementor version */
        esc_html__( '%1$s requires Elementor version %2$s or higher for the Elementor widget. Your current Elementor version is %3$s. The Elementor widget will not be available until you upgrade.', 'jezweb-vertical-media' ),
        '<strong>Jezweb Vertical Media</strong>',
        JVM_MINIMUM_ELEMENTOR_VERSION,
        ELEMENTOR_VERSION
    );
    printf( '<div class="notice notice-warning"><p>%s</p></div>', $message );
}

/**
 * Autoload plugin classes
 *
 * @param string $class_name The class name to load.
 */
function jvm_autoloader( $class_name ) {
    // Check if the class belongs to our namespace
    if ( strpos( $class_name, 'JezwebVerticalMedia\\' ) !== 0 ) {
        return;
    }

    // Remove namespace prefix
    $class_name = str_replace( 'JezwebVerticalMedia\\', '', $class_name );

    // Convert class name to file path
    $class_file = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
    $class_file = str_replace( '_', '-', strtolower( $class_file ) );

    // Build file paths for different directories
    $paths = array(
        JVM_PLUGIN_DIR . 'includes/class-' . $class_file . '.php',
        JVM_PLUGIN_DIR . 'elementor/class-' . $class_file . '.php',
        JVM_PLUGIN_DIR . 'elementor/widgets/class-' . $class_file . '.php',
        JVM_PLUGIN_DIR . 'gutenberg/class-' . $class_file . '.php',
    );

    foreach ( $paths as $path ) {
        if ( file_exists( $path ) ) {
            require_once $path;
            return;
        }
    }
}

spl_autoload_register( 'jvm_autoloader' );

/**
 * Initialize the plugin
 */
function jvm_init() {
    // Check PHP version
    if ( ! jvm_check_php_version() ) {
        return;
    }

    // Check WordPress version
    if ( ! jvm_check_wp_version() ) {
        return;
    }

    // Load text domain
    load_plugin_textdomain( 'jezweb-vertical-media', false, dirname( JVM_PLUGIN_BASENAME ) . '/languages' );

    // Initialize the main plugin class
    require_once JVM_PLUGIN_DIR . 'includes/class-plugin.php';
    \JezwebVerticalMedia\Plugin::get_instance();

    // Initialize GitHub updater for automatic updates
    require_once JVM_PLUGIN_DIR . 'includes/class-github-updater.php';
    new \JezwebVerticalMedia\GitHub_Updater(
        JVM_PLUGIN_FILE,
        JVM_GITHUB_USERNAME,
        JVM_GITHUB_REPO
    );
}

add_action( 'plugins_loaded', 'jvm_init' );

/**
 * Plugin activation hook
 */
function jvm_activate() {
    // Check PHP version on activation
    if ( version_compare( PHP_VERSION, JVM_MINIMUM_PHP_VERSION, '<' ) ) {
        deactivate_plugins( JVM_PLUGIN_BASENAME );
        wp_die(
            sprintf(
                /* translators: 1: Required PHP version */
                esc_html__( 'Jezweb Vertical Media requires PHP version %s or higher.', 'jezweb-vertical-media' ),
                JVM_MINIMUM_PHP_VERSION
            ),
            'Plugin Activation Error',
            array( 'back_link' => true )
        );
    }

    // Check WordPress version on activation
    global $wp_version;
    if ( version_compare( $wp_version, JVM_MINIMUM_WP_VERSION, '<' ) ) {
        deactivate_plugins( JVM_PLUGIN_BASENAME );
        wp_die(
            sprintf(
                /* translators: 1: Required WordPress version */
                esc_html__( 'Jezweb Vertical Media requires WordPress version %s or higher.', 'jezweb-vertical-media' ),
                JVM_MINIMUM_WP_VERSION
            ),
            'Plugin Activation Error',
            array( 'back_link' => true )
        );
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'jvm_activate' );

/**
 * Plugin deactivation hook
 */
function jvm_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'jvm_deactivate' );
