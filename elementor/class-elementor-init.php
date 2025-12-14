<?php
/**
 * Elementor Integration Class
 *
 * Initializes Elementor widgets and categories.
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Integration Class
 */
class Elementor_Init {

    /**
     * Video Parser instance
     *
     * @var Video_Parser
     */
    private $video_parser;

    /**
     * Constructor
     *
     * @param Video_Parser $video_parser Video Parser instance.
     */
    public function __construct( Video_Parser $video_parser ) {
        $this->video_parser = $video_parser;
        $this->register_hooks();
    }

    /**
     * Register Elementor hooks
     */
    private function register_hooks() {
        // Register widget category
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );

        // Register widgets
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

        // Enqueue editor styles
        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );

        // Enqueue preview styles
        add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_preview_styles' ) );
    }

    /**
     * Register custom widget category
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
     */
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'jezweb',
            array(
                'title' => __( 'Jezweb', 'jezweb-vertical-media' ),
                'icon'  => 'eicon-video-playlist',
            )
        );
    }

    /**
     * Register Elementor widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_widgets( $widgets_manager ) {
        require_once JVM_PLUGIN_DIR . 'elementor/widgets/class-vertical-media-widget.php';

        $widgets_manager->register( new Vertical_Media_Widget( $this->video_parser ) );
    }

    /**
     * Enqueue editor styles
     */
    public function enqueue_editor_styles() {
        wp_enqueue_style(
            'jvm-elementor-editor',
            JVM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JVM_VERSION
        );
    }

    /**
     * Enqueue preview styles
     */
    public function enqueue_preview_styles() {
        wp_enqueue_style(
            'jvm-frontend',
            JVM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            JVM_VERSION
        );
    }
}
