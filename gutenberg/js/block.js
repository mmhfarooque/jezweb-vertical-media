/**
 * Jezweb Vertical Media Gutenberg Block
 *
 * @package JezwebVerticalMedia
 */

( function( blocks, element, blockEditor, components, i18n ) {
    'use strict';

    const { registerBlockType } = blocks;
    const { createElement: el, Fragment } = element;
    const { InspectorControls, useBlockProps, BlockControls, BlockAlignmentToolbar } = blockEditor;
    const { PanelBody, TextControl, SelectControl, RangeControl, ToggleControl, Placeholder } = components;
    const { __ } = i18n;

    // Get localized data
    const blockData = window.jvmBlockData || {};
    const texts = blockData.i18n || {};

    // Video icon SVG
    const videoIcon = el(
        'svg',
        {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            width: 24,
            height: 24,
            fill: 'currentColor'
        },
        el( 'path', {
            d: 'M18.5 5.5H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h12.5c1.1 0 2-.9 2-2v-9c0-1.1-.9-2-2-2zm-7 10.5v-8l5.5 4-5.5 4z'
        } )
    );

    // Detect platform from URL
    function detectPlatform( url ) {
        if ( ! url ) return null;

        url = url.toLowerCase();

        if ( url.includes( 'youtube.com' ) || url.includes( 'youtu.be' ) ) {
            return 'youtube';
        }
        if ( url.includes( 'instagram.com' ) ) {
            return 'instagram';
        }
        if ( url.includes( 'tiktok.com' ) ) {
            return 'tiktok';
        }

        return null;
    }

    // Get platform display name
    function getPlatformName( platform ) {
        const platforms = blockData.platforms || {};
        return platforms[ platform ] || platform;
    }

    // Register block
    registerBlockType( 'jezweb/vertical-media', {
        edit: function( props ) {
            const { attributes, setAttributes } = props;
            const {
                videoUrl,
                platform,
                aspectRatio,
                maxWidth,
                autoplay,
                loop,
                muted,
                align
            } = attributes;

            const blockProps = useBlockProps( {
                className: 'jvm-block-editor'
            } );

            // Determine detected platform
            const detectedPlatform = platform === 'auto' ? detectPlatform( videoUrl ) : platform;

            // Build preview classes
            const aspectClass = aspectRatio === '10:16' ? 'jvm-aspect-10-16' : 'jvm-aspect-9-16';
            const alignClass = 'jvm-align-' + ( align || 'center' );

            // Inspector controls
            const inspectorControls = el(
                InspectorControls,
                null,
                // Video Settings Panel
                el(
                    PanelBody,
                    {
                        title: texts.videoSettings || 'Video Settings',
                        initialOpen: true
                    },
                    el( TextControl, {
                        label: texts.videoUrl || 'Video URL',
                        help: texts.videoUrlHelp || 'Enter a YouTube Shorts, Instagram Reels, or TikTok URL',
                        value: videoUrl,
                        onChange: function( value ) {
                            setAttributes( { videoUrl: value } );
                        }
                    } ),
                    el( SelectControl, {
                        label: texts.platform || 'Platform',
                        value: platform,
                        options: Object.keys( blockData.platforms || {} ).map( function( key ) {
                            return {
                                label: blockData.platforms[ key ],
                                value: key
                            };
                        } ),
                        onChange: function( value ) {
                            setAttributes( { platform: value } );
                        }
                    } ),
                    el( SelectControl, {
                        label: texts.aspectRatio || 'Aspect Ratio',
                        value: aspectRatio,
                        options: Object.keys( blockData.aspectRatios || {} ).map( function( key ) {
                            return {
                                label: blockData.aspectRatios[ key ],
                                value: key
                            };
                        } ),
                        onChange: function( value ) {
                            setAttributes( { aspectRatio: value } );
                        }
                    } )
                ),
                // Playback Settings Panel
                el(
                    PanelBody,
                    {
                        title: texts.playbackSettings || 'Playback Settings',
                        initialOpen: false
                    },
                    el( ToggleControl, {
                        label: texts.autoplay || 'Autoplay',
                        help: texts.autoplayHelp || 'Autoplay requires video to be muted',
                        checked: autoplay,
                        onChange: function( value ) {
                            setAttributes( { autoplay: value } );
                            if ( value ) {
                                setAttributes( { muted: true } );
                            }
                        }
                    } ),
                    el( ToggleControl, {
                        label: texts.loop || 'Loop',
                        checked: loop,
                        onChange: function( value ) {
                            setAttributes( { loop: value } );
                        }
                    } ),
                    el( ToggleControl, {
                        label: texts.muted || 'Muted',
                        checked: muted,
                        onChange: function( value ) {
                            setAttributes( { muted: value } );
                        }
                    } )
                ),
                // Style Settings Panel
                el(
                    PanelBody,
                    {
                        title: texts.styleSettings || 'Style Settings',
                        initialOpen: false
                    },
                    el( RangeControl, {
                        label: texts.maxWidth || 'Max Width (px)',
                        value: maxWidth,
                        onChange: function( value ) {
                            setAttributes( { maxWidth: value } );
                        },
                        min: 100,
                        max: 800,
                        step: 10
                    } )
                )
            );

            // Block alignment toolbar
            const blockToolbar = el(
                BlockControls,
                null,
                el( BlockAlignmentToolbar, {
                    value: align,
                    onChange: function( value ) {
                        setAttributes( { align: value } );
                    },
                    controls: [ 'left', 'center', 'right' ]
                } )
            );

            // Render placeholder if no URL
            if ( ! videoUrl ) {
                return el(
                    Fragment,
                    null,
                    inspectorControls,
                    el(
                        'div',
                        blockProps,
                        el(
                            Placeholder,
                            {
                                icon: videoIcon,
                                label: texts.title || 'Vertical Media',
                                instructions: texts.placeholder || 'Enter a video URL to display your vertical video.'
                            },
                            el( TextControl, {
                                placeholder: 'https://youtube.com/shorts/...',
                                value: videoUrl,
                                onChange: function( value ) {
                                    setAttributes( { videoUrl: value } );
                                }
                            } )
                        )
                    )
                );
            }

            // Render preview
            return el(
                Fragment,
                null,
                inspectorControls,
                blockToolbar,
                el(
                    'div',
                    blockProps,
                    el(
                        'div',
                        {
                            className: 'jvm-container ' + alignClass,
                            style: { '--jvm-max-width': maxWidth + 'px' }
                        },
                        el(
                            'div',
                            { className: 'jvm-video-wrapper ' + aspectClass },
                            el(
                                'div',
                                { className: 'jvm-editor-preview' },
                                videoIcon,
                                el( 'p', null, texts.previewNote || 'Video preview will appear on the frontend' ),
                                el( 'small', null, videoUrl ),
                                detectedPlatform && el(
                                    'span',
                                    { className: 'jvm-platform-badge' },
                                    getPlatformName( detectedPlatform )
                                )
                            )
                        )
                    )
                )
            );
        },

        save: function() {
            // Server-side rendering
            return null;
        }
    } );

} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
