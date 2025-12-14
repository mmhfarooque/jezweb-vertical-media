/**
 * Jezweb Vertical Media - Frontend JavaScript
 *
 * Handles lazy loading, embed initialization, and dynamic features
 *
 * @package JezwebVerticalMedia
 */

( function() {
    'use strict';

    // Wait for DOM to be ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

    /**
     * Initialize the frontend functionality
     */
    function init() {
        initLazyLoading();
        initEmbedScripts();
        handleResize();
    }

    /**
     * Initialize lazy loading for iframes
     */
    function initLazyLoading() {
        // Check for native lazy loading support
        if ( 'loading' in HTMLIFrameElement.prototype ) {
            // Browser supports native lazy loading
            return;
        }

        // Fallback: Use Intersection Observer for lazy loading
        if ( ! ( 'IntersectionObserver' in window ) ) {
            // No IntersectionObserver support, load all iframes immediately
            loadAllIframes();
            return;
        }

        var iframes = document.querySelectorAll( '.jvm-video-wrapper iframe[loading="lazy"]' );

        var observer = new IntersectionObserver( function( entries, obs ) {
            entries.forEach( function( entry ) {
                if ( entry.isIntersecting ) {
                    var iframe = entry.target;
                    if ( iframe.dataset.src ) {
                        iframe.src = iframe.dataset.src;
                        delete iframe.dataset.src;
                    }
                    obs.unobserve( iframe );
                }
            } );
        }, {
            rootMargin: '200px 0px', // Start loading 200px before visible
            threshold: 0
        } );

        iframes.forEach( function( iframe ) {
            observer.observe( iframe );
        } );
    }

    /**
     * Load all iframes immediately (fallback)
     */
    function loadAllIframes() {
        var iframes = document.querySelectorAll( '.jvm-video-wrapper iframe[data-src]' );
        iframes.forEach( function( iframe ) {
            iframe.src = iframe.dataset.src;
            delete iframe.dataset.src;
        } );
    }

    /**
     * Initialize embed scripts for Instagram and TikTok
     */
    function initEmbedScripts() {
        // Check for Instagram embeds
        var instagramEmbeds = document.querySelectorAll( '.jvm-platform-instagram .jvm-video-wrapper' );
        if ( instagramEmbeds.length > 0 ) {
            loadInstagramEmbed();
        }

        // Check for TikTok embeds
        var tiktokEmbeds = document.querySelectorAll( '.jvm-platform-tiktok .jvm-video-wrapper' );
        if ( tiktokEmbeds.length > 0 ) {
            loadTikTokEmbed();
        }
    }

    /**
     * Load Instagram embed script
     */
    function loadInstagramEmbed() {
        if ( window.instgrm ) {
            window.instgrm.Embeds.process();
            return;
        }

        // Check if script is already being loaded
        if ( document.querySelector( 'script[src*="instagram.com/embed.js"]' ) ) {
            return;
        }

        var script = document.createElement( 'script' );
        script.async = true;
        script.src = 'https://www.instagram.com/embed.js';
        script.onload = function() {
            if ( window.instgrm ) {
                window.instgrm.Embeds.process();
            }
        };
        document.body.appendChild( script );
    }

    /**
     * Load TikTok embed script
     */
    function loadTikTokEmbed() {
        // Check if script is already being loaded
        if ( document.querySelector( 'script[src*="tiktok.com/embed.js"]' ) ) {
            return;
        }

        var script = document.createElement( 'script' );
        script.async = true;
        script.src = 'https://www.tiktok.com/embed.js';
        document.body.appendChild( script );
    }

    /**
     * Handle window resize
     */
    function handleResize() {
        var resizeTimer;
        window.addEventListener( 'resize', function() {
            clearTimeout( resizeTimer );
            resizeTimer = setTimeout( function() {
                // Re-process embeds after resize
                if ( window.instgrm ) {
                    window.instgrm.Embeds.process();
                }
            }, 250 );
        } );
    }

    /**
     * Public API for reinitialization (useful for AJAX loaded content)
     */
    window.JezwebVerticalMedia = {
        init: init,
        reinit: function() {
            initEmbedScripts();
        }
    };

} )();
