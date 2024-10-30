=== Monetizer101 Widget Shortcode ===
Contributors: argosk
Plugin Name: Monetizer101
Tags: widget, shortcode, affiliation, admin
Requires at least: 3.0
Tested up to: 5.6.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monetizer101 shortcode is a tool used by publishers to add price comparison widgets to their WordPress website.

== Description ==

Monetizer101 shortcode is a tool used by publishers to add price comparison widgets to their WordPress website.
You can create the shortcode through the Monetizer101 Tool or create it yourself manually by following the instructions below.

You must have a Monetizer101 account in order to use this plugin (https://monetizer101.com/).

== Installation ==

This section describes how to install the plugin and get it working.

= FROM WORDPRESS =

1.	Visit ‘Plugins > Add New’
2.	Search for ‘Monetizer101’
3.	Activate Monetizer101 from your Plugins page.
4.	Go to “AFTER ACTIVATION” below.

= MANUALLY =

1.	Upload the monetizer101 folder to the /wp-content/plugins/ directory
2.	Activate the Monetizer101 plugin through the Plugins menu in WordPress
3.	Go to “AFTER ACTIVATION” below.

= AFTER ACTIVATION =

1.	You will see a new page under Plugins called Monetizer101
2.	Enter your API Key and Site ID from your Monetizer101 account.
3.	Use the [monetizer101] shortcode to display your price-comparison widget on your WordPress pages.


== SHORTCODE ==

= [m101widget] =

Setting up a price comparison widget.

Parameters:

- **type**: “price-comparison”. This field is required.
- **template**: Template name that you want to use with your widget.
- **barcode**: The barcode of the product to search, can be one of: UPC,EAN,GTIN,ISBN,ASIN.
- **plainlink**: The link of the product web page in a merchant's site. Note: the link must be plain, if you provide a tracked link the service may not work.
- **search-keywords**: List of keywords to search in the product name, separated by space. For example: 'Dickies hat red' or 'iPhone X'.
- **exclude-keywords**: List of keywords to exclude from the search, separated by space. Any result matching any of the provided keywords will be stripped out from the results. For example: 'Dickies hat red' or 'iPhone 10'.
- **price-range**: Range of prices where the 'salePlace' of the returned products should belong. The range is defined in the form <min-price>-<max-price>, for example 20-30 or 19.9-26.8. The star can be used to avoid to limit one side of the range, for example *-20 equals to 0-20 and 20-* will take all results with a sale price >= 20 Note: using *-* is the same as not defining any filter.
- **filter-merchant**: Comma separated list of merchant IDs ex. 8902,3840,3905 In this case the search is limited to the merchants in the list. The list can be followed by ':exclude' ex. 8902,3840,3905:exclude. In this case the merchants in the list will be excluded from the search.
- **limit**: Limits the number of results, for example if limit=3 the service will return up to 3 products. The products in the response will be those from the best performing merchants according to our merchant-ranking syste.
- **sid**: Custom source ID to be used for tracking purposes. Can contain only letters, digits, and the following punctuation characters: '-','_','.','~'. Cannot be longer than 32 characters.
- **title**: Widget title. Example: “Apple IPhone”


Real example of shortcode:

[m101widget type="price-comparison" title="Best deals" plainlink="https://www.amazon.com/SAMSUNG-65-inch-Class-QLED-Built/dp/B08F2WS438" search-keywords="Samsung 50 Class Q80T Smart TV" price-range="500-*" template="default"]

