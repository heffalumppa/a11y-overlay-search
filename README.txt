This WordPress plugin provides an accessible overlay search functionality, including real-time search results, supporting Custom Post Types, Relevanssi, and Polylang Pro.

== Usage ==

To implement the accessible overlay search, simply use the overlay_search_button() function provided by the plugin anywhere within your theme files.

Example:
<?php echo AccessibleOverlaySearch\Plugin::overlay_search_button(); ?>

Function will accept three optional parameters:
$show_label (boolean): Controls whether to display the button text visually. Defaults to true. If set to false, the text will be visible to screen readers only.
$class (string): Allows adding custom classes to the button. Defaults to none.
$svg (string): Allows using a custom SVG as the button icon. Defaults to the icon from Heroicons.

Example with parameters:
<?php echo AccessibleOverlaySearch\Plugin::overlay_search_button( false, 'my-custom-class', '<svg>...</svg>' ); ?>

== Polylang ==

Note that the free version of Polylang does not support live search results since it lacks REST API support.

If Polylang is enabled, you need to add locale fallback on Polylang settings for every language that has a two-part locale code (e.g., sv_SE, de_DE, etc.). For example, the locale fallback for sv_SE is sv_SE.

== Relevanssi ==

After activating Relevanssi, remember to re-index posts to ensure proper search functionality.
