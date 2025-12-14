<?php
/**
 * Video Parser Class
 *
 * Handles URL parsing for YouTube Shorts, Instagram Reels, and TikTok videos.
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Video Parser Class
 */
class Video_Parser {

    /**
     * Supported platforms
     */
    const PLATFORM_YOUTUBE   = 'youtube';
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_TIKTOK    = 'tiktok';

    /**
     * Parse a video URL and return video data
     *
     * @param string $url The video URL.
     * @param string $platform Optional platform hint ('auto', 'youtube', 'instagram', 'tiktok').
     * @return array|false Video data array or false if parsing fails.
     */
    public function parse( $url, $platform = 'auto' ) {
        $url = esc_url_raw( trim( $url ) );

        if ( empty( $url ) ) {
            return false;
        }

        // Auto-detect platform if not specified
        if ( 'auto' === $platform ) {
            $platform = $this->detect_platform( $url );
        }

        if ( ! $platform ) {
            return false;
        }

        switch ( $platform ) {
            case self::PLATFORM_YOUTUBE:
                return $this->parse_youtube( $url );

            case self::PLATFORM_INSTAGRAM:
                return $this->parse_instagram( $url );

            case self::PLATFORM_TIKTOK:
                return $this->parse_tiktok( $url );

            default:
                return false;
        }
    }

    /**
     * Detect platform from URL
     *
     * @param string $url The video URL.
     * @return string|false Platform identifier or false if not detected.
     */
    public function detect_platform( $url ) {
        $host = wp_parse_url( $url, PHP_URL_HOST );

        if ( ! $host ) {
            return false;
        }

        $host = strtolower( $host );

        // YouTube patterns
        if ( preg_match( '/(?:youtube\.com|youtu\.be)/i', $host ) ) {
            return self::PLATFORM_YOUTUBE;
        }

        // Instagram patterns
        if ( preg_match( '/instagram\.com/i', $host ) ) {
            return self::PLATFORM_INSTAGRAM;
        }

        // TikTok patterns
        if ( preg_match( '/(?:tiktok\.com|vm\.tiktok\.com)/i', $host ) ) {
            return self::PLATFORM_TIKTOK;
        }

        return false;
    }

    /**
     * Parse YouTube URL
     *
     * Supports:
     * - youtube.com/shorts/VIDEO_ID
     * - youtube.com/watch?v=VIDEO_ID
     * - youtu.be/VIDEO_ID
     * - youtube.com/embed/VIDEO_ID
     *
     * @param string $url The YouTube URL.
     * @return array|false Video data or false.
     */
    private function parse_youtube( $url ) {
        $video_id = null;

        // Pattern for youtube.com/shorts/VIDEO_ID
        if ( preg_match( '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Pattern for youtube.com/watch?v=VIDEO_ID
        elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Pattern for youtu.be/VIDEO_ID
        elseif ( preg_match( '/youtu\.be\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Pattern for youtube.com/embed/VIDEO_ID
        elseif ( preg_match( '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Direct video ID (11 characters)
        elseif ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $url ) ) {
            $video_id = $url;
        }

        if ( ! $video_id ) {
            return false;
        }

        return array(
            'platform'  => self::PLATFORM_YOUTUBE,
            'video_id'  => $video_id,
            'embed_url' => 'https://www.youtube.com/embed/' . $video_id,
            'url'       => $url,
        );
    }

    /**
     * Parse Instagram URL
     *
     * Supports:
     * - instagram.com/reel/REEL_ID/
     * - instagram.com/reels/REEL_ID/
     * - instagram.com/p/POST_ID/
     *
     * @param string $url The Instagram URL.
     * @return array|false Video data or false.
     */
    private function parse_instagram( $url ) {
        $video_id = null;

        // Pattern for instagram.com/reel/REEL_ID or /reels/REEL_ID
        if ( preg_match( '/instagram\.com\/reels?\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Pattern for instagram.com/p/POST_ID
        elseif ( preg_match( '/instagram\.com\/p\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }

        if ( ! $video_id ) {
            return false;
        }

        // Normalize URL for embed
        $embed_url = 'https://www.instagram.com/reel/' . $video_id . '/embed/';

        return array(
            'platform'  => self::PLATFORM_INSTAGRAM,
            'video_id'  => $video_id,
            'embed_url' => $embed_url,
            'url'       => $url,
        );
    }

    /**
     * Parse TikTok URL
     *
     * Supports:
     * - tiktok.com/@user/video/VIDEO_ID
     * - vm.tiktok.com/SHORT_CODE
     * - tiktok.com/t/SHORT_CODE
     *
     * @param string $url The TikTok URL.
     * @return array|false Video data or false.
     */
    private function parse_tiktok( $url ) {
        $video_id = null;

        // Pattern for tiktok.com/@user/video/VIDEO_ID
        if ( preg_match( '/tiktok\.com\/@[^\/]+\/video\/(\d+)/i', $url, $matches ) ) {
            $video_id = $matches[1];
        }
        // Pattern for vm.tiktok.com/CODE or tiktok.com/t/CODE (short URLs)
        elseif ( preg_match( '/(?:vm\.tiktok\.com|tiktok\.com\/t)\/([a-zA-Z0-9]+)/i', $url, $matches ) ) {
            // For short URLs, we'll use the short code as the identifier
            $video_id = $matches[1];
        }

        if ( ! $video_id ) {
            return false;
        }

        // TikTok embed URL
        $embed_url = 'https://www.tiktok.com/embed/v2/' . $video_id;

        return array(
            'platform'  => self::PLATFORM_TIKTOK,
            'video_id'  => $video_id,
            'embed_url' => $embed_url,
            'url'       => $url,
        );
    }

    /**
     * Get oEmbed data for a video URL
     *
     * @param string $url The video URL.
     * @param string $platform The platform.
     * @return array|false oEmbed data or false.
     */
    public function get_oembed_data( $url, $platform ) {
        $oembed_url = '';

        switch ( $platform ) {
            case self::PLATFORM_INSTAGRAM:
                $oembed_url = 'https://graph.facebook.com/v10.0/instagram_oembed?url=' . urlencode( $url ) . '&access_token=';
                // Note: Instagram oEmbed requires a Facebook App access token
                // Falling back to iframe embed for now
                return false;

            case self::PLATFORM_TIKTOK:
                $oembed_url = 'https://www.tiktok.com/oembed?url=' . urlencode( $url );
                break;

            default:
                return false;
        }

        // Make API request
        $response = wp_remote_get(
            $oembed_url,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json',
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE || empty( $data ) ) {
            return false;
        }

        return $data;
    }

    /**
     * Get supported platforms
     *
     * @return array
     */
    public function get_supported_platforms() {
        return array(
            'auto'                  => __( 'Auto Detect', 'jezweb-vertical-media' ),
            self::PLATFORM_YOUTUBE  => __( 'YouTube Shorts', 'jezweb-vertical-media' ),
            self::PLATFORM_INSTAGRAM => __( 'Instagram Reels', 'jezweb-vertical-media' ),
            self::PLATFORM_TIKTOK   => __( 'TikTok', 'jezweb-vertical-media' ),
        );
    }

    /**
     * Validate video ID format
     *
     * @param string $video_id The video ID.
     * @param string $platform The platform.
     * @return bool
     */
    public function validate_video_id( $video_id, $platform ) {
        switch ( $platform ) {
            case self::PLATFORM_YOUTUBE:
                // YouTube video IDs are 11 characters
                return (bool) preg_match( '/^[a-zA-Z0-9_-]{11}$/', $video_id );

            case self::PLATFORM_INSTAGRAM:
                // Instagram IDs are alphanumeric
                return (bool) preg_match( '/^[a-zA-Z0-9_-]+$/', $video_id );

            case self::PLATFORM_TIKTOK:
                // TikTok video IDs are numeric (long) or alphanumeric (short codes)
                return (bool) preg_match( '/^[a-zA-Z0-9]+$/', $video_id );

            default:
                return false;
        }
    }
}
