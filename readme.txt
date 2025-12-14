=== Jezweb Vertical Media ===
Contributors: jezweb, mahmudfarooque
Tags: vertical video, youtube shorts, instagram reels, tiktok, video embed, elementor widget, gutenberg block
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display vertical videos (YouTube Shorts, Instagram Reels, TikTok) with responsive 9:16 or 10:16 aspect ratio. Includes Elementor widget, Gutenberg block, and shortcode support.

== Description ==

**Jezweb Vertical Media** is a powerful WordPress plugin designed to seamlessly embed vertical videos from popular platforms like YouTube Shorts, Instagram Reels, and TikTok. The plugin ensures your videos display perfectly with responsive 9:16 or 10:16 aspect ratios across all devices.

= Key Features =

* **Multi-Platform Support** - Embed videos from YouTube Shorts, Instagram Reels, and TikTok
* **Responsive Design** - Videos maintain proper aspect ratio on all screen sizes
* **Multiple Integration Options** - Use via Elementor widget, Gutenberg block, or shortcode
* **Auto-Detect Platform** - Automatically detects the video platform from the URL
* **Customizable Aspect Ratio** - Choose between 9:16 (standard) or 10:16 (wider)
* **Playback Controls** - Configure autoplay, loop, and mute settings
* **Alignment Options** - Align videos left, center, or right
* **Lazy Loading** - Videos load only when scrolled into view
* **oEmbed Support** - Uses official oEmbed APIs for Instagram and TikTok

= Elementor Widget =

The plugin includes a dedicated Elementor widget with:

* Video URL input with platform auto-detection
* Aspect ratio selector
* Max width control with responsive options
* Autoplay, loop, and mute toggles
* Alignment controls
* Border radius customization
* Box shadow options

= Gutenberg Block =

The Gutenberg block offers:

* Easy video URL input
* Platform selection (auto-detect or manual)
* Aspect ratio options
* Max width slider
* Playback settings
* Block alignment support

= Shortcode Usage =

Use the shortcode anywhere in your content:

`[jezweb_vertical_media url="YOUR_VIDEO_URL"]`

**Available Attributes:**

* `url` (required) - The video URL
* `platform` - auto, youtube, instagram, or tiktok (default: auto)
* `aspect_ratio` - 9:16 or 10:16 (default: 9:16)
* `max_width` - Maximum container width (default: 400px)
* `autoplay` - true or false (default: false)
* `loop` - true or false (default: true)
* `muted` - true or false (default: true)
* `align` - left, center, or right (default: center)

**Example:**

`[jezweb_vertical_media url="https://youtube.com/shorts/VIDEO_ID" aspect_ratio="9:16" max_width="350px" align="center"]`

= Supported URL Formats =

**YouTube Shorts:**
* youtube.com/shorts/VIDEO_ID
* youtu.be/VIDEO_ID
* youtube.com/watch?v=VIDEO_ID

**Instagram Reels:**
* instagram.com/reel/REEL_ID/
* instagram.com/reels/REEL_ID/
* instagram.com/p/POST_ID/

**TikTok:**
* tiktok.com/@user/video/VIDEO_ID
* vm.tiktok.com/SHORT_CODE
* tiktok.com/t/SHORT_CODE

== Installation ==

1. Upload the `jezweb-vertical-media` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. For Elementor: Find the "Vertical Media" widget in the Jezweb category
4. For Gutenberg: Add the "Vertical Media" block from the embed category
5. For shortcode: Use `[jezweb_vertical_media url="YOUR_URL"]` in your content

= Minimum Requirements =

* PHP 7.4 or higher
* WordPress 5.8 or higher
* Elementor 3.0 or higher (for Elementor widget)

== Frequently Asked Questions ==

= Does this plugin require Elementor? =

No. The plugin works with Gutenberg (the default WordPress editor) and shortcodes. Elementor is optional - the Elementor widget will only be available if Elementor is installed and activated.

= Why isn't my Instagram Reel loading? =

Instagram Reels must be from public accounts to be embedded. Private accounts' content cannot be displayed.

= Why isn't my TikTok video loading? =

Similar to Instagram, TikTok videos must be from public accounts and the video itself must have sharing enabled.

= Can I use this with page builders other than Elementor? =

Yes! You can use the shortcode with any page builder that supports WordPress shortcodes.

= How do I change the video size on mobile? =

The plugin is fully responsive. On mobile devices, videos automatically scale to fit the screen width while maintaining the aspect ratio.

= Can I disable autoplay? =

Yes. Set `autoplay="false"` in the shortcode, or toggle off the Autoplay option in the Elementor widget or Gutenberg block settings.

== Screenshots ==

1. Elementor widget settings panel
2. Gutenberg block in the editor
3. YouTube Shorts embed on the frontend
4. Instagram Reels embed on the frontend
5. TikTok video embed on the frontend
6. Mobile responsive view

== Privacy Notice ==

This plugin embeds videos from third-party services (YouTube, Instagram, TikTok). When these embeds are displayed:

* External scripts may be loaded from these services
* These services may collect visitor data according to their privacy policies
* Cookies may be set by these third-party services

We recommend informing your website visitors about third-party embeds in your privacy policy.

**Third-party Privacy Policies:**
* [YouTube/Google Privacy Policy](https://policies.google.com/privacy)
* [Instagram/Meta Privacy Policy](https://privacycenter.instagram.com/policy)
* [TikTok Privacy Policy](https://www.tiktok.com/legal/privacy-policy)

== Changelog ==

= 1.0.5 =
* Fixed: Auto-update toggle now displays correctly (Enable/Disable auto-updates link)

= 1.0.4 =
* Fixed: Release package folder structure for proper WordPress installation

= 1.0.3 =
* Fixed: GitHub update detection not working when clicking "Check for updates"
* Fixed: Auto-update toggle not functioning correctly
* Improved: Reduced API cache from 12 hours to 1 hour for faster update detection
* Added: Admin notice showing update check results
* Added: Proper cache clearing when WordPress force-checks for updates

= 1.0.2 =
* Security: Fixed inline script injection - now uses wp_enqueue_script()
* Security: Added iframe sandbox attributes for enhanced security
* Security: Improved output escaping in admin notices
* Security: Filtered inline scripts from oEmbed responses
* Privacy: Added privacy notice for third-party embeds
* Code: Improved WordPress coding standards compliance

= 1.0.1 =
* Fixed: Critical error when loading Elementor editor
* Fixed: Elementor widget constructor compatibility
* Fixed: Auto-update toggle not showing in plugins list
* Improved: Simplified Elementor and Gutenberg integration

= 1.0.0 =
* Initial release
* Support for YouTube Shorts, Instagram Reels, and TikTok
* Elementor widget with full controls
* Gutenberg block with inspector controls
* Shortcode with multiple attributes
* Responsive 9:16 and 10:16 aspect ratios
* Auto-detect platform from URL
* Lazy loading support
* oEmbed API integration for Instagram and TikTok

== Upgrade Notice ==

= 1.0.5 =
Fixes auto-update toggle display. You can now enable/disable auto-updates from the plugins page.

= 1.0.4 =
Fixed release package structure. Recommended for fresh installations.

= 1.0.3 =
Fixes GitHub auto-update detection. The "Check for updates" link now properly clears the API cache and shows results.

= 1.0.2 =
Security release - improved script handling, added iframe sandboxing, and enhanced output escaping. Recommended update for all users.

= 1.0.1 =
Bug fix release - fixes critical error with Elementor and adds auto-update support.

= 1.0.0 =
Initial release of Jezweb Vertical Media.

== Credits ==

* Developed by Mahmud Farooque
* Created for Jezweb (https://jezweb.com.au)
