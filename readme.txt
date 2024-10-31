=== WooCommerce ProductPrint ===
Contributors: kenrichman
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WLA6U8VXF4WUW
Tags: productprint, product print, print product, product, ecommerce, e-commerce, commerce, woo commerce, woocommerce, woo-commerce, wordpress ecommerce, store, sales, sell, shop, shopping, cart, configurable, reports, print, printing, printer, print button, printer friendly, print friendly, printer-friendly, printout
Requires at least: 3.5
Tested up to: 4.9
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce extension to create printer-friendly product pages. No longer supported - please switch to Print Flyers Lite.

== Description ==

WooCommerce extension to create printer-friendly product pages. No longer being developed - please switch to Print Flyers Lite, it's very similar.
WooCommerce ProductPrint makes it easy for potential customers to print off their own copy of product literature, straight from the product page via a button. You can configure the button to appear in one of a selection of positions on the single product page. A variety of options are available via the WordPress dashboard settings to control the appearance of the printed page. For example, you can change the size and position of the featured image, and you can choose from a selection of fonts. You can choose to show SKU or not; or stock status or not, etc. Requires WooCommerce 3 or above.

= Automatic installation =

To do an automatic install of ProductPrint, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

1. In the search field type “ProductPrint” and click Search Plugins. Once you’ve found the plugin, you can install it by simply clicking “Install Now”.
2. Click 'Activate plugin'.
3. Go to Settings -> ProductPrint
4. Click on the Settings tab and configure your options.

= Manual installation =

1. The manual installation method involves downloading our ProductPrint plugin for WooCommerce and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
2. Click 'Activate plugin'.
3. Go to Settings -> ProductPrint
4. Click on the Settings tab and configure your options.

== Frequently Asked Questions ==


== Screenshots ==

1. The print button added to the single product page.
2. The dashboard settings page.
3. More dashboard settings.
4. Sample print-out.

== Changelog ==

= Version 2.0.4 =

Notice: Information about Print Flyers added to tab settings
Bug fix: Removed #print-button from productprint.css as it has no effect adding it there. Add it to your theme's css if you need it.

= Version 2.0.3 =

More detail added to 'Go Pro' tab in settings page

= Version 2.0.2 =

Change to plugin's URI

= Version 2.0.1 =

Edits to this readme file

= Version 2.0 =

Compatibility: WooCommerce 3.0-compatible. Earlier versions would work with v3.0 but were generating Notices in the log.
Improvement: New option to display a printer icon instead of or as well as the print button legend.
Improvement: New print preview window displays a representation of the printed page before you print.
Improvement: css changes to make the printed page look better, mainly affects attributes table.
Improvement: Clearer design of options page in admin settings.
Minor amends: tpl-print.php renamed html-print.php; new include file featured-image.php


= Version 1.2.1 =

Bugfix: File gallery-images.php somehow got missed out of v1.2 - now included. This means gallery images will now display if selected to do so.

= Version 1.2 =

Improvement: Added 'nofollow' to the print button link to discourage indexing by search engines and prevent potential 404 errors.

Improvement: Added new printer font size setting to avoid having to amend this by css, should you feel the need to do so.

Improvement: Changed the way the gallery images are extracted from database to more closely follow how WooCommerce does it, and created separate php file for this code.

Tested compatible with WordPress 4.3 and updated readme.txt accordingly

Improvement: 

= Version 1.1 =

Bugfix: added test for presence of variant images to prevent warnings about missing variant images appearing in print-out, if variants don't have variant images.

Name changed in readme.txt from ProductPrint to WooCommerce ProductPrint

Added .pot file for translation

Tested compatible with WordPress 4.1 and updated readme.txt accordingly

= Version 1.0 =

Initial release.
