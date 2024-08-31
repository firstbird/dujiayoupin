=== Fancy Product Designer | PRO Export add-on ===
Contributors: radykal


== Installation ==

Simply drop the fancy-product-designer-export folder into your wp-content/plugins folder.


== Information ==

If you need any help with the plugin or searching for the documentation, please visit:

https://support.fancyproductdesigner.com

== Changelog ==

= 1.3.1 =
* Dropbox authentication is now using OAuth2.0.

= 1.3.0 =
* Remote server for creating print files has changed.

= 1.2.7 =
* Replaced get_site_url with get_rest_url for the webhook url.
* Imagick not required anymore.

= 1.2.6 =
* Download Link Login Required option: The customer needs to log into his account to download the print file.

= 1.2.5 =
* Creating export-file via webhook for all export interfaces.

= 1.2.4 =
* Creating export-file via webhook in order viewer.

= 1.2.3 =
* Download print-ready file only when logged in.
* Added a webhook which receives the print-ready file.

= 1.2.2 =
* Bug: Error when $order is not an instance of WC_Order in "woocommerce_email_attachments" filter.

= 1.2.1 =
* New option: "Hide Crop Marks".
* Bug: When ordering a Printful item with additional prices for the different views and these views did not have any customization, the price is still calculated on Printul.

= 1.2.0 =
* Connect your Printful store.
* New Remote Service is now enabled by default

= 1.1.2 =
* New remote service for generating the print-ready files.

= 1.1.1 =
* Rotated clipping support for individual objects

= 1.1.0 =
* Automatically upload print-ready file to your Dropbox or AWS S3 cloud when an order is received.

= 1.0.7 =
* Problem with PHP7.3 and TCPDF fixed.

= 1.0.6 =
* Imagick version info added to Status page.

= 1.0.5 =
* Image DPI can now be set for automated export.

= 1.0.4 =
* ZIP containing PDF and custom images for Automated Export added.
* Removed code that is not necessary anymore for FPD 4.2.0.
* Checking if TCPDF class exists before requiring it.

= 1.0.3 =
* Bug Fix: When Gravity Form notification mail is sent without print data, it causes a PHP error.
* Bug Fix: When Order is saved in Order Viewer and resending the order notification, the print data was not correctly encoded.

= 1.0.2 =
* In some cases the download process does not end well, added exit() method

= 1.0.1 =
* Italic font variant of Google fonts was not embbeded.
