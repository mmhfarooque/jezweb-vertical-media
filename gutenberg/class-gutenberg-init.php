<?php
/**
 * Gutenberg Block Integration Class
 *
 * Registers and handles the Gutenberg block for vertical media.
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg Integration Class
 */
class Gutenberg_Init {

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
        $this->register_block();
    }

    /**
     * Register the Gutenberg block
     */
    private function register_block() {
        // Register block scripts and styles
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

        // Register the block
        register_block_type(
            JVM_PLUGIN_DIR . 'gutenberg/block.json',
            array(
                'render_callback' => array( $this, 'render_block' ),
            )
        );
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        // Editor script
        wp_enqueue_script(
            'jvm-block-editor',
            JVM_PLUGIN_URL . 'gutenberg/js/block.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-editor',
                'wp-components',
                'wp-i18n',
                'wp-block-editor',
            ),
            JVM_VERSION,
            true
        );

        // Editor styles
        wp_enqueue_style(
            'jvm-block-editor',
            JVM_PLUGIN_URL . 'assets/css/admin.css',
            array( 'wp-edit-blocks' ),
            JVM_VERSION
        );

        // Frontend styles for preview
        wp_enqueue_style(
            'jvm-frontend',
            JVM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            JVM_VERSION
        );

        // Localize script with translations and data
        wp_localize_script(
            'jvm-block-editor',
            'jvmBlockData',
            array(
                'platforms'    => array(
                    'auto'      => __( 'Auto Detect', 'jezweb-vertical-media' ),
                    'youtube'   => __( 'YouTube Shorts', 'jezweb-vertical-media' ),
                    'instagram' => __( 'Instagram Reels', 'jezweb-vertical-media' ),
                    'tiktok'    => __( 'TikTok', 'jezweb-vertical-media' ),
                ),
                'aspectRatios' => array(
                    '9:16'  => '9:16 (Standard)',
                    '10:16' => '10:16 (Wider)',
                ),
                'i18n'         => array(
                    'title'           => __( 'Vertical Media', 'jezweb-vertical-media' ),
                    'description'     => __( 'Embed vertical videos from YouTube Shorts, Instagram Reels, or TikTok.', 'jezweb-vertical-media' ),
                    'videoUrl'        => __( 'Video URL', 'jezweb-vertical-media' ),
                    'videoUrlHelp'    => __( 'Enter a YouTube Shorts, Instagram Reels, or TikTok URL', 'jezweb-vertical-media' ),
                    'platform'        => __( 'Platform', 'jezweb-vertical-media' ),
                    'aspectRatio'     => __( 'Aspect Ratio', 'jezweb-vertical-media' ),
                    'maxWidth'        => __( 'Max Width (px)', 'jezweb-vertical-media' ),
                    'autoplay'        => __( 'Autoplay', 'jezweb-vertical-media' ),
                    'autoplayHelp'    => __( 'Autoplay requires video to be muted', 'jezweb-vertical-media' ),
                    'loop'            => __( 'Loop', 'jezweb-vertical-media' ),
                    'muted'           => __( 'Muted', 'jezweb-vertical-media' ),
                    'placeholder'     => __( 'Enter a video URL to display your vertical video.', 'jezweb-vertical-media' ),
                    'previewNote'     => __( 'Video preview will appear on the frontend', 'jezweb-vertical-media' ),
                    'videoSettings'   => __( 'Video Settings', 'jezweb-vertical-media' ),
                    'playbackSettings' => __( 'Playback Settings', 'jezweb-vertical-media' ),
                    'styleSettings'   => __( 'Style Settings', 'jezweb-vertical-media' ),
                ),
            )
        );
    }

    /**
     * Render block callback
     *
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @return string HTML output.
     */
    public function render_block( $attributes, $content ) {
        $args = array(
            'url'          => isset( $attributes['videoUrl'] ) ? $attributes['videoUrl'] : '',
            'platform'     => isset( $attributes['platform'] ) ? $attributes['platform'] : 'auto',
            'aspect_ratio' => isset( $attributes['aspectRatio'] ) ? $attributes['aspectRatio'] : '9:16',
            'max_width'    => isset( $attributes['maxWidth'] ) ? $attributes['maxWidth'] . 'px' : '400px',
            'autoplay'     => isset( $attributes['autoplay'] ) ? $attributes['autoplay'] : false,
            'loop'         => isset( $attributes['loop'] ) ? $attributes['loop'] : true,
            'muted'        => isset( $attributes['muted'] ) ? $attributes['muted'] : true,
            'align'        => isset( $attributes['align'] ) ? $attributes['align'] : 'center',
        );

        // Get the shortcode instance and render
        $plugin = Plugin::get_instance();
        return $plugin->render_video( $args );
    }
}
