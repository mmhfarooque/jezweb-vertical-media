<?php
/**
 * Elementor Vertical Media Widget
 *
 * @package JezwebVerticalMedia
 */

namespace JezwebVerticalMedia;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Vertical Media Widget for Elementor
 */
class Vertical_Media_Widget extends Widget_Base {

    /**
     * Video Parser instance
     *
     * @var Video_Parser
     */
    private $video_parser;

    /**
     * Constructor
     *
     * @param array        $data         Widget data.
     * @param array        $args         Widget arguments.
     * @param Video_Parser $video_parser Video Parser instance.
     */
    public function __construct( $data = array(), $args = null, $video_parser = null ) {
        parent::__construct( $data, $args );

        if ( $video_parser ) {
            $this->video_parser = $video_parser;
        } else {
            $this->video_parser = new Video_Parser();
        }
    }

    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name() {
        return 'jezweb_vertical_media';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function get_title() {
        return __( 'Vertical Media', 'jezweb-vertical-media' );
    }

    /**
     * Get widget icon
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-video-playlist';
    }

    /**
     * Get widget categories
     *
     * @return array
     */
    public function get_categories() {
        return array( 'jezweb', 'general' );
    }

    /**
     * Get widget keywords
     *
     * @return array
     */
    public function get_keywords() {
        return array(
            'video',
            'vertical',
            'shorts',
            'reels',
            'tiktok',
            'youtube',
            'instagram',
            'jezweb',
        );
    }

    /**
     * Get style dependencies
     *
     * @return array
     */
    public function get_style_depends() {
        return array( 'jvm-frontend' );
    }

    /**
     * Get script dependencies
     *
     * @return array
     */
    public function get_script_depends() {
        return array( 'jvm-frontend' );
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Video', 'jezweb-vertical-media' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'video_url',
            array(
                'label'       => __( 'Video URL', 'jezweb-vertical-media' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'https://youtube.com/shorts/...', 'jezweb-vertical-media' ),
                'description' => __( 'Enter a YouTube Shorts, Instagram Reels, or TikTok URL', 'jezweb-vertical-media' ),
                'label_block' => true,
                'dynamic'     => array(
                    'active' => true,
                ),
            )
        );

        $this->add_control(
            'platform',
            array(
                'label'   => __( 'Platform', 'jezweb-vertical-media' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => array(
                    'auto'      => __( 'Auto Detect', 'jezweb-vertical-media' ),
                    'youtube'   => __( 'YouTube Shorts', 'jezweb-vertical-media' ),
                    'instagram' => __( 'Instagram Reels', 'jezweb-vertical-media' ),
                    'tiktok'    => __( 'TikTok', 'jezweb-vertical-media' ),
                ),
            )
        );

        $this->add_control(
            'aspect_ratio',
            array(
                'label'   => __( 'Aspect Ratio', 'jezweb-vertical-media' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '9:16',
                'options' => array(
                    '9:16'  => '9:16 (Standard)',
                    '10:16' => '10:16 (Wider)',
                ),
            )
        );

        $this->end_controls_section();

        // Playback Settings Section
        $this->start_controls_section(
            'playback_section',
            array(
                'label' => __( 'Playback', 'jezweb-vertical-media' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label'        => __( 'Autoplay', 'jezweb-vertical-media' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'jezweb-vertical-media' ),
                'label_off'    => __( 'No', 'jezweb-vertical-media' ),
                'return_value' => 'yes',
                'default'      => '',
                'description'  => __( 'Autoplay requires video to be muted', 'jezweb-vertical-media' ),
            )
        );

        $this->add_control(
            'loop',
            array(
                'label'        => __( 'Loop', 'jezweb-vertical-media' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'jezweb-vertical-media' ),
                'label_off'    => __( 'No', 'jezweb-vertical-media' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'muted',
            array(
                'label'        => __( 'Muted', 'jezweb-vertical-media' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'jezweb-vertical-media' ),
                'label_off'    => __( 'No', 'jezweb-vertical-media' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Container', 'jezweb-vertical-media' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'max_width',
            array(
                'label'      => __( 'Max Width', 'jezweb-vertical-media' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%', 'vw' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 100,
                        'max'  => 800,
                        'step' => 10,
                    ),
                    '%'  => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                    'vw' => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 400,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .jvm-container' => '--jvm-max-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'align',
            array(
                'label'   => __( 'Alignment', 'jezweb-vertical-media' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'left'   => array(
                        'title' => __( 'Left', 'jezweb-vertical-media' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __( 'Center', 'jezweb-vertical-media' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => __( 'Right', 'jezweb-vertical-media' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default' => 'center',
                'toggle'  => false,
            )
        );

        $this->add_control(
            'border_radius',
            array(
                'label'      => __( 'Border Radius', 'jezweb-vertical-media' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'default'    => array(
                    'top'    => 12,
                    'right'  => 12,
                    'bottom' => 12,
                    'left'   => 12,
                    'unit'   => 'px',
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .jvm-video-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'box_shadow',
                'label'    => __( 'Box Shadow', 'jezweb-vertical-media' ),
                'selector' => '{{WRAPPER}} .jvm-video-wrapper',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $args = array(
            'url'          => $settings['video_url'],
            'platform'     => $settings['platform'],
            'aspect_ratio' => $settings['aspect_ratio'],
            'max_width'    => isset( $settings['max_width']['size'] ) ? $settings['max_width']['size'] . $settings['max_width']['unit'] : '400px',
            'autoplay'     => 'yes' === $settings['autoplay'],
            'loop'         => 'yes' === $settings['loop'],
            'muted'        => 'yes' === $settings['muted'],
            'align'        => $settings['align'],
        );

        // Get the shortcode instance and render
        $plugin = Plugin::get_instance();
        echo $plugin->render_video( $args );
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <#
        if ( ! settings.video_url ) {
            #>
            <div class="jvm-placeholder">
                <div class="jvm-placeholder-inner">
                    <i class="eicon-video-playlist"></i>
                    <p><?php esc_html_e( 'Enter a video URL to display your vertical video.', 'jezweb-vertical-media' ); ?></p>
                </div>
            </div>
            <#
        } else {
            var aspectClass = settings.aspect_ratio === '10:16' ? 'jvm-aspect-10-16' : 'jvm-aspect-9-16';
            var alignClass = 'jvm-align-' + settings.align;
            var maxWidth = settings.max_width.size + settings.max_width.unit;
            #>
            <div class="jvm-container {{ alignClass }}" style="--jvm-max-width: {{ maxWidth }};">
                <div class="jvm-video-wrapper {{ aspectClass }}">
                    <div class="jvm-editor-preview">
                        <i class="eicon-video-playlist"></i>
                        <p><?php esc_html_e( 'Video preview will appear on the frontend', 'jezweb-vertical-media' ); ?></p>
                        <small>{{ settings.video_url }}</small>
                    </div>
                </div>
            </div>
            <#
        }
        #>
        <?php
    }
}
