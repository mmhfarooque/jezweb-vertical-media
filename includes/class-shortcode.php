<?php
/**
 * Shortcode Handler Class
 *
 * Registers and handles the [jezweb_vertical_media] shortcode.
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode Handler Class
 */
class Shortcode {

    /**
     * Video Parser instance
     *
     * @var Video_Parser
     */
    private $video_parser;

    /**
     * Counter for unique IDs
     *
     * @var int
     */
    private static $instance_counter = 0;

    /**
     * Constructor
     *
     * @param Video_Parser $video_parser Video Parser instance.
     */
    public function __construct( Video_Parser $video_parser ) {
        $this->video_parser = $video_parser;
        $this->register_shortcode();
    }

    /**
     * Register the shortcode
     */
    private function register_shortcode() {
        add_shortcode( 'jezweb_vertical_media', array( $this, 'shortcode_callback' ) );
    }

    /**
     * Shortcode callback
     *
     * @param array  $atts    Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string HTML output.
     */
    public function shortcode_callback( $atts, $content = '' ) {
        $atts = shortcode_atts(
            array(
                'url'          => '',
                'platform'     => 'auto',
                'aspect_ratio' => '9:16',
                'max_width'    => '400px',
                'autoplay'     => 'false',
                'loop'         => 'true',
                'muted'        => 'true',
                'align'        => 'center',
            ),
            $atts,
            'jezweb_vertical_media'
        );

        return $this->render( $atts );
    }

    /**
     * Render the video embed
     *
     * @param array $args Video arguments.
     * @return string HTML output.
     */
    public function render( $args ) {
        // Parse arguments
        $url          = isset( $args['url'] ) ? sanitize_text_field( $args['url'] ) : '';
        $platform     = isset( $args['platform'] ) ? sanitize_text_field( $args['platform'] ) : 'auto';
        $aspect_ratio = isset( $args['aspect_ratio'] ) ? sanitize_text_field( $args['aspect_ratio'] ) : '9:16';
        $max_width    = isset( $args['max_width'] ) ? sanitize_text_field( $args['max_width'] ) : '400px';
        $autoplay     = isset( $args['autoplay'] ) ? filter_var( $args['autoplay'], FILTER_VALIDATE_BOOLEAN ) : false;
        $loop         = isset( $args['loop'] ) ? filter_var( $args['loop'], FILTER_VALIDATE_BOOLEAN ) : true;
        $muted        = isset( $args['muted'] ) ? filter_var( $args['muted'], FILTER_VALIDATE_BOOLEAN ) : true;
        $align        = isset( $args['align'] ) ? sanitize_text_field( $args['align'] ) : 'center';

        // Validate URL
        if ( empty( $url ) ) {
            return $this->render_error( __( 'Please provide a video URL.', 'jezweb-vertical-media' ) );
        }

        // Parse video URL
        $video_data = $this->video_parser->parse( $url, $platform );

        if ( ! $video_data ) {
            return $this->render_error( __( 'Invalid video URL. Supported platforms: YouTube Shorts, Instagram Reels, TikTok.', 'jezweb-vertical-media' ) );
        }

        // Enqueue assets
        wp_enqueue_style( 'jvm-frontend' );
        wp_enqueue_script( 'jvm-frontend' );

        // Generate unique ID
        self::$instance_counter++;
        $unique_id = 'jvm-' . self::$instance_counter . '-' . substr( md5( $video_data['video_id'] ), 0, 8 );

        // Calculate aspect ratio
        $aspect_class = 'jvm-aspect-9-16';
        if ( '10:16' === $aspect_ratio ) {
            $aspect_class = 'jvm-aspect-10-16';
        }

        // Alignment class
        $align_class = 'jvm-align-' . $align;

        // Build container styles
        $container_style = sprintf( '--jvm-max-width: %s;', esc_attr( $max_width ) );

        // Start output buffering
        ob_start();

        // Container
        printf(
            '<div id="%s" class="jvm-container %s %s" style="%s">',
            esc_attr( $unique_id ),
            esc_attr( $align_class ),
            esc_attr( 'jvm-platform-' . $video_data['platform'] ),
            esc_attr( $container_style )
        );

        // Video wrapper
        printf( '<div class="jvm-video-wrapper %s">', esc_attr( $aspect_class ) );

        // Render based on platform
        switch ( $video_data['platform'] ) {
            case Video_Parser::PLATFORM_YOUTUBE:
                $this->render_youtube_embed( $video_data, $autoplay, $loop, $muted );
                break;

            case Video_Parser::PLATFORM_INSTAGRAM:
                $this->render_instagram_embed( $video_data );
                break;

            case Video_Parser::PLATFORM_TIKTOK:
                $this->render_tiktok_embed( $video_data, $url );
                break;
        }

        echo '</div>'; // .jvm-video-wrapper
        echo '</div>'; // .jvm-container

        return ob_get_clean();
    }

    /**
     * Render YouTube embed
     *
     * @param array $video_data Video data.
     * @param bool  $autoplay   Autoplay setting.
     * @param bool  $loop       Loop setting.
     * @param bool  $muted      Muted setting.
     */
    private function render_youtube_embed( $video_data, $autoplay, $loop, $muted ) {
        $params = array(
            'rel'      => 0,
            'showinfo' => 0,
            'modestbranding' => 1,
            'playsinline' => 1,
        );

        if ( $autoplay ) {
            $params['autoplay'] = 1;
            $params['mute']     = 1; // Autoplay requires mute
        }

        if ( $loop ) {
            $params['loop']     = 1;
            $params['playlist'] = $video_data['video_id'];
        }

        if ( $muted ) {
            $params['mute'] = 1;
        }

        $embed_url = add_query_arg( $params, $video_data['embed_url'] );

        printf(
            '<iframe
                src="%s"
                title="%s"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                loading="lazy"
                sandbox="allow-scripts allow-same-origin allow-presentation allow-popups"
            ></iframe>',
            esc_url( $embed_url ),
            esc_attr__( 'YouTube video player', 'jezweb-vertical-media' )
        );
    }

    /**
     * Render Instagram embed
     *
     * @param array $video_data Video data.
     */
    private function render_instagram_embed( $video_data ) {
        // Try to get oEmbed data first
        $oembed_data = $this->video_parser->get_oembed_data( $video_data['url'], Video_Parser::PLATFORM_INSTAGRAM );

        if ( $oembed_data && ! empty( $oembed_data['html'] ) ) {
            // Use oEmbed HTML
            echo wp_kses_post( $oembed_data['html'] );
        } else {
            // Fallback to iframe embed
            printf(
                '<iframe
                    src="%s"
                    title="%s"
                    frameborder="0"
                    scrolling="no"
                    allowtransparency="true"
                    allowfullscreen
                    loading="lazy"
                    sandbox="allow-scripts allow-same-origin allow-presentation"
                ></iframe>',
                esc_url( $video_data['embed_url'] ),
                esc_attr__( 'Instagram video player', 'jezweb-vertical-media' )
            );
        }

        // Enqueue Instagram embed script properly
        wp_enqueue_script(
            'instagram-embed',
            'https://www.instagram.com/embed.js',
            array(),
            null,
            true
        );
    }

    /**
     * Render TikTok embed
     *
     * @param array  $video_data Video data.
     * @param string $url        Original URL.
     */
    private function render_tiktok_embed( $video_data, $url ) {
        // Try to get oEmbed data
        $oembed_data = $this->video_parser->get_oembed_data( $url, Video_Parser::PLATFORM_TIKTOK );

        if ( $oembed_data && ! empty( $oembed_data['html'] ) ) {
            // Use oEmbed HTML (contains blockquote) - filter out inline scripts for security
            $safe_html = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $oembed_data['html'] );
            echo wp_kses(
                $safe_html,
                array(
                    'blockquote' => array(
                        'class'         => true,
                        'cite'          => true,
                        'data-video-id' => true,
                    ),
                    'section'    => array(),
                    'a'          => array(
                        'target' => true,
                        'title'  => true,
                        'href'   => true,
                    ),
                    'p'          => array(),
                )
            );
        } else {
            // Fallback to iframe embed
            printf(
                '<iframe
                    src="%s"
                    title="%s"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                    sandbox="allow-scripts allow-same-origin allow-presentation allow-popups"
                ></iframe>',
                esc_url( $video_data['embed_url'] ),
                esc_attr__( 'TikTok video player', 'jezweb-vertical-media' )
            );
        }

        // Enqueue TikTok embed script properly
        wp_enqueue_script(
            'tiktok-embed',
            'https://www.tiktok.com/embed.js',
            array(),
            null,
            true
        );
    }

    /**
     * Render error message
     *
     * @param string $message Error message.
     * @return string HTML output.
     */
    private function render_error( $message ) {
        // Only show errors to logged-in users who can edit posts
        if ( ! current_user_can( 'edit_posts' ) ) {
            return '';
        }

        return sprintf(
            '<div class="jvm-error"><p>%s</p></div>',
            esc_html( $message )
        );
    }
}
