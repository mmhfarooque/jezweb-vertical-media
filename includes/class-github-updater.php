<?php
/**
 * GitHub Plugin Updater Class
 *
 * Enables automatic updates from GitHub releases.
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GitHub Updater Class
 *
 * Checks GitHub releases for plugin updates and integrates with WordPress update system.
 */
class GitHub_Updater {

    /**
     * Plugin file path
     *
     * @var string
     */
    private $plugin_file;

    /**
     * Plugin slug
     *
     * @var string
     */
    private $plugin_slug;

    /**
     * GitHub username/organization
     *
     * @var string
     */
    private $github_username;

    /**
     * GitHub repository name
     *
     * @var string
     */
    private $github_repo;

    /**
     * Current plugin version
     *
     * @var string
     */
    private $current_version;

    /**
     * GitHub API response cache
     *
     * @var object|null
     */
    private $github_response = null;

    /**
     * Cache key for transient
     *
     * @var string
     */
    private $cache_key;

    /**
     * Cache duration in seconds (1 hour - shorter for better update detection)
     *
     * @var int
     */
    private $cache_duration = 3600;

    /**
     * Constructor
     *
     * @param string $plugin_file     Full path to the main plugin file.
     * @param string $github_username GitHub username or organization.
     * @param string $github_repo     GitHub repository name.
     */
    public function __construct( $plugin_file, $github_username, $github_repo ) {
        $this->plugin_file     = $plugin_file;
        $this->plugin_slug     = plugin_basename( $plugin_file );
        $this->github_username = $github_username;
        $this->github_repo     = $github_repo;
        $this->current_version = JVM_VERSION;
        $this->cache_key       = 'jvm_github_update_' . md5( $this->plugin_slug );

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Check for updates
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

        // Plugin information popup
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );

        // After plugin update, clear cache
        add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );

        // Add "Check for updates" link
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

        // Enable auto-updates for this plugin
        add_filter( 'auto_update_plugin', array( $this, 'auto_update_plugin' ), 10, 2 );

        // Add plugin to auto-update plugins list
        add_filter( 'plugin_auto_update_setting_html', array( $this, 'auto_update_setting_html' ), 10, 3 );

        // Clear our cache when WordPress force-checks for updates
        add_action( 'load-update-core.php', array( $this, 'maybe_clear_cache' ) );

        // Also clear on plugins page when checking for updates
        add_action( 'load-plugins.php', array( $this, 'maybe_clear_cache' ) );

        // Handle our custom force check action
        add_action( 'admin_init', array( $this, 'handle_force_check' ) );

        // Show admin notice after successful check
        add_action( 'admin_notices', array( $this, 'show_check_notice' ) );
    }

    /**
     * Show admin notice after update check
     */
    public function show_check_notice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['jvm_checked'] ) || '1' !== $_GET['jvm_checked'] ) {
            return;
        }

        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        $latest_version = $this->get_latest_version();
        $has_update     = $latest_version && version_compare( $this->current_version, $latest_version, '<' );

        if ( $has_update ) {
            $message = sprintf(
                /* translators: 1: Plugin name 2: New version */
                esc_html__( '%1$s: Update available! Version %2$s is ready to install.', 'jezweb-vertical-media' ),
                '<strong>Jezweb Vertical Media</strong>',
                esc_html( $latest_version )
            );
            $class = 'notice notice-info';
        } else {
            $message = sprintf(
                /* translators: 1: Plugin name 2: Current version */
                esc_html__( '%1$s: You are running the latest version (%2$s).', 'jezweb-vertical-media' ),
                '<strong>Jezweb Vertical Media</strong>',
                esc_html( $this->current_version )
            );
            $class = 'notice notice-success';
        }

        printf( '<div class="%s is-dismissible"><p>%s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
    }

    /**
     * Clear cache if force-check parameter is present
     */
    public function maybe_clear_cache() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['force-check'] ) && '1' === $_GET['force-check'] ) {
            $this->force_check();
        }
    }

    /**
     * Handle our custom force check action from plugins page
     */
    public function handle_force_check() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['jvm_force_check'] ) && '1' === $_GET['jvm_force_check'] ) {
            if ( ! current_user_can( 'update_plugins' ) ) {
                return;
            }

            // Verify nonce
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'jvm_force_check' ) ) {
                return;
            }

            // Clear our cache
            $this->force_check();

            // Also delete WordPress update transient to force fresh check
            delete_site_transient( 'update_plugins' );

            // Redirect back to plugins page with success message
            wp_safe_redirect( admin_url( 'plugins.php?jvm_checked=1' ) );
            exit;
        }
    }

    /**
     * Enable auto-updates for this plugin
     *
     * @param bool   $update Whether to auto-update.
     * @param object $item   The update offer object.
     * @return bool
     */
    public function auto_update_plugin( $update, $item ) {
        if ( isset( $item->plugin ) && $item->plugin === $this->plugin_slug ) {
            // Check if user has enabled auto-updates for this plugin
            $auto_updates = (array) get_site_option( 'auto_update_plugins', array() );
            return in_array( $this->plugin_slug, $auto_updates, true );
        }
        return $update;
    }

    /**
     * Customize auto-update setting HTML for this plugin
     *
     * @param string $html        The HTML for the auto-update setting.
     * @param string $plugin_file The plugin file.
     * @param array  $plugin_data The plugin data.
     * @return string
     */
    public function auto_update_setting_html( $html, $plugin_file, $plugin_data ) {
        if ( $plugin_file !== $this->plugin_slug ) {
            return $html;
        }

        // Always generate our own HTML for this plugin (WordPress returns "Auto-updates disabled" for non-WP.org plugins)
        $auto_updates = (array) get_site_option( 'auto_update_plugins', array() );
        $is_enabled   = in_array( $this->plugin_slug, $auto_updates, true );

        if ( $is_enabled ) {
            $text   = __( 'Disable auto-updates', 'jezweb-vertical-media' );
            $action = 'disable';
        } else {
            $text   = __( 'Enable auto-updates', 'jezweb-vertical-media' );
            $action = 'enable';
        }

        return sprintf(
            '<a href="%s" class="toggle-auto-update aria-button-if-js" data-wp-action="%s">
                <span class="dashicons dashicons-update spin hidden" aria-hidden="true"></span>
                <span class="label">%s</span>
            </a>',
            wp_nonce_url( admin_url( 'plugins.php?action=' . $action . '-auto-update&plugin=' . urlencode( $this->plugin_slug ) ), 'updates' ),
            $action . '-auto-update',
            esc_html( $text )
        );
    }

    /**
     * Get repository info from GitHub API
     *
     * @return object|false Repository data or false on failure.
     */
    private function get_repository_info() {
        // Check cache first
        if ( null !== $this->github_response ) {
            return $this->github_response;
        }

        // Try to get cached response
        $cached = get_transient( $this->cache_key );
        if ( false !== $cached ) {
            $this->github_response = $cached;
            return $this->github_response;
        }

        // Fetch from GitHub API
        $url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        );

        $response = wp_remote_get(
            $url,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept'     => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
                ),
            )
        );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( json_last_error() !== JSON_ERROR_NONE || empty( $data ) ) {
            return false;
        }

        // Cache the response
        $this->github_response = $data;
        set_transient( $this->cache_key, $data, $this->cache_duration );

        return $this->github_response;
    }

    /**
     * Get the latest version from GitHub
     *
     * @return string|false Version string or false.
     */
    private function get_latest_version() {
        $repo_info = $this->get_repository_info();

        if ( ! $repo_info || empty( $repo_info->tag_name ) ) {
            return false;
        }

        // Remove 'v' prefix if present
        return ltrim( $repo_info->tag_name, 'v' );
    }

    /**
     * Get the download URL for the latest release
     *
     * @return string|false Download URL or false.
     */
    private function get_download_url() {
        $repo_info = $this->get_repository_info();

        if ( ! $repo_info ) {
            return false;
        }

        // Check for uploaded asset first (zip file)
        if ( ! empty( $repo_info->assets ) && is_array( $repo_info->assets ) ) {
            foreach ( $repo_info->assets as $asset ) {
                if ( isset( $asset->browser_download_url ) && preg_match( '/\.zip$/i', $asset->name ) ) {
                    return $asset->browser_download_url;
                }
            }
        }

        // Fallback to zipball URL
        if ( ! empty( $repo_info->zipball_url ) ) {
            return $repo_info->zipball_url;
        }

        return false;
    }

    /**
     * Check for plugin updates
     *
     * @param object $transient Update transient data.
     * @return object Modified transient data.
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $latest_version = $this->get_latest_version();
        $download_url   = $this->get_download_url();

        if ( ! $latest_version || ! $download_url ) {
            return $transient;
        }

        // Compare versions
        if ( version_compare( $this->current_version, $latest_version, '<' ) ) {
            $repo_info = $this->get_repository_info();

            $plugin_data = array(
                'slug'        => dirname( $this->plugin_slug ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $latest_version,
                'url'         => sprintf( 'https://github.com/%s/%s', $this->github_username, $this->github_repo ),
                'package'     => $download_url,
                'icons'       => array(),
                'banners'     => array(),
                'tested'      => '',
                'requires_php' => JVM_MINIMUM_PHP_VERSION,
            );

            $transient->response[ $this->plugin_slug ] = (object) $plugin_data;
        } else {
            // No update available
            $transient->no_update[ $this->plugin_slug ] = (object) array(
                'slug'        => dirname( $this->plugin_slug ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $this->current_version,
                'url'         => sprintf( 'https://github.com/%s/%s', $this->github_username, $this->github_repo ),
            );
        }

        return $transient;
    }

    /**
     * Plugin information popup
     *
     * @param false|object|array $result The result object or array.
     * @param string             $action The type of information being requested.
     * @param object             $args   Plugin API arguments.
     * @return false|object Plugin information or false.
     */
    public function plugin_info( $result, $action, $args ) {
        // Check if this is our plugin
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || dirname( $this->plugin_slug ) !== $args->slug ) {
            return $result;
        }

        $repo_info = $this->get_repository_info();

        if ( ! $repo_info ) {
            return $result;
        }

        $latest_version = $this->get_latest_version();
        $download_url   = $this->get_download_url();

        // Get plugin data
        $plugin_data = get_plugin_data( $this->plugin_file );

        $plugin_info = array(
            'name'              => $plugin_data['Name'],
            'slug'              => dirname( $this->plugin_slug ),
            'version'           => $latest_version,
            'author'            => $plugin_data['Author'],
            'author_profile'    => $plugin_data['AuthorURI'],
            'homepage'          => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections'          => array(
                'description' => $plugin_data['Description'],
                'changelog'   => $this->get_changelog( $repo_info ),
            ),
            'download_link'     => $download_url,
            'requires'          => JVM_MINIMUM_WP_VERSION,
            'requires_php'      => JVM_MINIMUM_PHP_VERSION,
            'tested'            => get_bloginfo( 'version' ),
            'last_updated'      => isset( $repo_info->published_at ) ? $repo_info->published_at : '',
        );

        return (object) $plugin_info;
    }

    /**
     * Get changelog from GitHub release
     *
     * @param object $repo_info Repository info from GitHub.
     * @return string Formatted changelog HTML.
     */
    private function get_changelog( $repo_info ) {
        if ( empty( $repo_info->body ) ) {
            return '<p>No changelog available.</p>';
        }

        // Convert markdown to HTML (basic conversion)
        $changelog = $repo_info->body;
        $changelog = esc_html( $changelog );
        $changelog = nl2br( $changelog );

        // Convert markdown headers
        $changelog = preg_replace( '/^### (.+)$/m', '<h4>$1</h4>', $changelog );
        $changelog = preg_replace( '/^## (.+)$/m', '<h3>$1</h3>', $changelog );
        $changelog = preg_replace( '/^# (.+)$/m', '<h2>$1</h2>', $changelog );

        // Convert markdown lists
        $changelog = preg_replace( '/^\* (.+)$/m', '<li>$1</li>', $changelog );
        $changelog = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $changelog );

        return '<div class="changelog">' . $changelog . '</div>';
    }

    /**
     * After plugin update, clear cache
     *
     * @param \WP_Upgrader $upgrader Upgrader instance.
     * @param array        $options  Update options.
     */
    public function after_update( $upgrader, $options ) {
        if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
            delete_transient( $this->cache_key );
            $this->github_response = null;
        }
    }

    /**
     * Add "Check for updates" link in plugin row
     *
     * @param array  $links Plugin row meta links.
     * @param string $file  Plugin file path.
     * @return array Modified links.
     */
    public function plugin_row_meta( $links, $file ) {
        if ( $this->plugin_slug === $file ) {
            // Use our custom force check action that properly clears GitHub API cache
            $check_url = wp_nonce_url(
                admin_url( 'plugins.php?jvm_force_check=1' ),
                'jvm_force_check'
            );
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $check_url ),
                esc_html__( 'Check for updates', 'jezweb-vertical-media' )
            );
        }

        return $links;
    }

    /**
     * Force check for updates (clears cache)
     */
    public function force_check() {
        delete_transient( $this->cache_key );
        $this->github_response = null;
    }
}
