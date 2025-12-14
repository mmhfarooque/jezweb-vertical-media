<?php
/**
 * Main Plugin Class
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Plugin Class
 *
 * Handles plugin initialization and loading of all components.
 */
class Plugin {

    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Video Parser instance
     *
     * @var Video_Parser|null
     */
    public $video_parser = null;

    /**
     * Shortcode instance
     *
     * @var Shortcode|null
     */
    public $shortcode = null;

    /**
     * Get plugin instance (Singleton)
     *
     * @return Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->register_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once JVM_PLUGIN_DIR . 'includes/class-video-parser.php';
        require_once JVM_PLUGIN_DIR . 'includes/class-shortcode.php';
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize Video Parser
        $this->video_parser = new Video_Parser();

        // Initialize Shortcode
        $this->shortcode = new Shortcode( $this->video_parser );

        // Initialize Elementor integration if Elementor is active
        if ( did_action( 'elementor/loaded' ) ) {
            add_action( 'elementor/init', array( $this, 'init_elementor' ) );
        }

        // Initialize Gutenberg block
        add_action( 'init', array( $this, 'init_gutenberg' ) );
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Enqueue admin/editor assets
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Add plugin action links
        add_filter( 'plugin_action_links_' . JVM_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
    }

    /**
     * Initialize Elementor integration
     */
    public function init_elementor() {
        // Check Elementor version
        if ( ! jvm_check_elementor_version() ) {
            return;
        }

        require_once JVM_PLUGIN_DIR . 'elementor/class-elementor-init.php';
        new Elementor_Init();
    }

    /**
     * Initialize Gutenberg block
     */
    public function init_gutenberg() {
        require_once JVM_PLUGIN_DIR . 'gutenberg/class-gutenberg-init.php';
        new Gutenberg_Init();
    }

    /**
     * Enqueue frontend styles and scripts
     */
    public function enqueue_frontend_assets() {
        // Frontend CSS
        wp_register_style(
            'jvm-frontend',
            JVM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            JVM_VERSION
        );

        // Frontend JavaScript
        wp_register_script(
            'jvm-frontend',
            JVM_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            JVM_VERSION,
            true
        );

        // Localize script with plugin data
        wp_localize_script(
            'jvm-frontend',
            'jvmData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'jvm_nonce' ),
            )
        );
    }

    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();

        // Only load on relevant admin pages
        if ( ! $screen ) {
            return;
        }

        wp_enqueue_style(
            'jvm-admin',
            JVM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JVM_VERSION
        );
    }

    /**
     * Add plugin action links
     *
     * @param array $links Existing links.
     * @return array
     */
    public function add_action_links( $links ) {
        $plugin_links = array(
            '<a href="https://jezweb.com.au" target="_blank">' . esc_html__( 'Documentation', 'jezweb-vertical-media' ) . '</a>',
        );
        return array_merge( $plugin_links, $links );
    }

    /**
     * Get Video Parser instance
     *
     * @return Video_Parser
     */
    public function get_video_parser() {
        return $this->video_parser;
    }

    /**
     * Render video embed HTML
     *
     * @param array $args Video arguments.
     * @return string HTML output.
     */
    public function render_video( $args ) {
        return $this->shortcode->render( $args );
    }
}
