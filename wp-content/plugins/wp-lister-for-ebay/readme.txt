=== WP-Lister Lite for eBay ===
Contributors: wp-lab
Tags: ebay, woocommerce, products, export
Requires at least: 4.2
Tested up to: 5.4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

List products from WordPress on eBay. The easy way.

== Description ==

WP-Lister connects your WooCommerce shop with your eBay Store. You can select multiple products right from your products page, select a profile to apply a set of predefined options and list them all on eBay with just a few clicks.

We worked hard to make WP-Lister easy to use but flexible. The workflow of listing items requires not a single click more than neccessary. Due to its tight integration in WordPress you will feel right at home.

= Features =

* list any number of items
* create listing profiles and apply multiple products
* verify items and get listing fees before actually listing them
* choose categories from eBay and your eBay Store
* national and international shipping options
* support for product variations
* create simple listing templates using WordPress post editor
* advanced template editor with HTML / CSS syntax highlighting
* download / upload listing templates – makes life easy for 3rd party template developers

= Localization =

* english
* german
* french
* italian
* spanish
* dutch
* korean
* latvian
* bulgarian

= Screencast =

http://www.youtube.com/watch?feature=player_embedded&v=XvbHob8pfes

= More information and Pro version =

Visit <https://www.wplab.com/plugins/wp-lister/> to read more about WP-Lister including documentation, installation instructions and user reviews.

To find out more about the different versions have a look on our [feature comparison table](https://www.wplab.com/plugins/wp-lister/feature-comparison/).

== Installation ==

1. Install WP-Lister for eBay either via the WordPress.org plugin repository, or by uploading the files to your server.
2. After activating the plugin, visit the new "eBay" page and follow the setup instructions. 

== Frequently Asked Questions ==

= Does WP-Lister work with all eBay sites? =

Yes, it does.

= What are the requirements to run WP-Lister? =

WP-Lister requires a decent version of WordPress (4.2+) with WooCommerce (3.0+) installed. Your server should run on Linux and have PHP 5.6 or better with cURL support.

Please check out the [list of incompatible hosting providers](https://docs.wplab.com/article/8-wp-lister-for-ebay-requirements) as well. If your provider is on that list, we will not be able to provide any kind of support.

= I use products variations on my site but eBay doesn’t allow variations in the selected category. How can I find out in which categories variations are allowed? =

To learn more about variations and allowed categories you should visit this page: <http://pages.ebay.com/help/sell/listing-variations.html>. There you will find a link to eBay’s look up table for categories allowing variations. If you can only list to categories where no variations are allowed, consider purchasing WP-Lister Pro which can split variations into single listings.

= I already have listed my products on eBay. Can WP-Lister import them to WooCommerce? =

No, WP-Lister itself was created to let you manage your products in WordPress - and list them *from* WordPress *to* eBay. 

But if you need to import all your items from eBay to WooCommerce first to be able to use WP-Lister, you can use the [importer add-on plugin](https://www.wplab.com/plugins/import-from-ebay-to-woocommerce/) we developed to get you started. Since importing from eBay is rather complex and support intensive this add-on plugin does have a price tag attached. 

= Does WP-Lister support windows servers? =

No, and there are no plans on adding support for IIS.

= Are there any more FAQ? =

Yes, there are! Please check out our growing knowledgebase at <https://www.wplab.com/plugins/wp-lister/faq/>.

= Is there a WP-Lister for Amazon? =

Yes, there is. WP-Lister for Amazon is currently in beta and we still have to work on the documentation, but you are welcome to give it a try: <https://wordpress.org/plugins/wp-lister-for-amazon/>

== Screenshots ==

1. Listings Page
2. Orders Page
3. Profile Editor - part 1
4. Profile Editor - part 2
5. Profile Editor - part 3
6. Template Editor

== Changelog ==
= 2.6.2 – 2020-07-28 =
* Tweak: Disable the empty price check to allow empty WooCommerce prices and only use profile start prices
* Tweak: Override the woocommerce_product_is_visible filter to WP-Lister can see _all_ variations in WooCommerce
* Tweak: Skip revising empty variables even when force mode is enabled
* Tweak: Ignore listing status when running forced reviseInventoryStatus
* Tweak: Record sales tax as order tax (like VAT)
* Tweak: Exclude cancelled orders from Unshipped filter
* Fixed: Listings per page setting not getting saved
* Fixed: Regard "Use attributes as item specifics" setting when pulling item specifics from attributes
* Fixed: Issue where German Market plugin could cause a fatal error
* Fixed: Make sure ActionScheduler is available before using the library
* Fixed: PHP Warnings in EbayCategoriesModel
* Fixed: Link expired error message when relisting listings
* Dev: Added the filter wplister_shipping_method_title filter hook
* Dev: Added the wple_enqueue_async_action compatibility function for older WC versions
* Dev: Use WC_Order::add_item() to add order line items

= 2.6.1 – 2020-07-01 =
* Fixed: Issue where the custom profile price could be added twice when revising listings

= 2.6 – 2020-06-25 =
* New: Option to automatically queue all changed listings to be revised in the background 
* New: Check stock levels automatically with the Background Inventory Check tool
* New: Select and set product identifiers from the available product attributes (edit product page)
* New: Support for Woo Product Variation Gallery
* New: Support for ATUM Product Levels (BOM)
* New: Support for AELIA Currency Switcher - use the profile's currency value
* Tweak: The new "Revert Stock Changes" setting option replaces the wplister_revert_stock_reduction filter hook
* Tweak: Retry inventory sync errors from WooCommerce orders after 5 minutes
* Tweak: Improved the search function on the Listings page
* Tweak: Remove copied setting description
* Tweak: Check if the Product GTIN plugin is active before trying to use its value
* Fixed: Prevent WP-Lister from restocking cancelled orders
* Fixed: Profile price being ignored if there's no product-level eBay start price
* Fixed: Use wc_get_product in ProductWrapper for some methods to prevent using a cached version of WC_Product
* Fixed: Possible issue with special characters in the FeedbackText
* Fixed: Product-level shipping profile shouldn't be used if the shipping service type is disabled for the product
* Fixed: Wrong account_id being used in ItemBuilderModel::buildCategories()
* Fixed: Listings with warnings getting displayed in the With Errors view
* Fixed: Skip invalid custom item specifics
* Fixed: Tax order items did not have the rate_percent value set
* Fixed: Some PHP warnings
* Dev: Added the filters wplister_record_tax_details_as_fees and wplister_fee_label_$TaxDescription

= 2.5.4 – 2020-05-18 =
* New: Setting option to enable sorting by SKU (disabled by default)
* New: Experimental Grid Editor (can be enabled in advanced settings for purposes of alpha testing and user feedback)
* Fixed: Issue where default item conditions were always being loaded instead of the category-specific conditions
* Fixed: Possible PHP Warning "touch(): Utime failed a utime warning"
* Fixed: Possible PHP Notice "Trying to access array offset on null"

= 2.5.3 – 2020-05-05 =
* New: Added support for the "Product GTIN (EAN, UPC, ISBN) for WooCommerce" plugin
* New: Added setting to toggle the wpautop calls when generating the templates
* New: Added profile setting to use SKU as MPN
* New: Added option to convert cm to inches
* Fixed: Perfect WooCommerce Brands and WooCommerce Brands extension using the same attribute/field ID
* Fixed: Slider gallery HTML not getting saved in template editor
* Fixed: PHP warnings triggered by eBatNs API library
* Fixed: Undefined index: line_items warning
* Tweak: Use the listing's title as the primary image's alt text
* Tweak: Include the primary image in the product_gallery shortcode
* Dev: Added filter wplister_get_ebay_weight
* Dev: Added filter wplister_order_shipping_taxes
* Dev: Added filter wplister_use_wc_get_product_id_by_sku
* Dev: Added filter wplister_revert_stock_reduction

= 2.5.2 – 2020-04-14 =
* Fixed: Issue with ((product_gallery)) template shortcode
* Fixed: Issue editing category assignments for eBay US when multiple marketplaces are enabled
* Fixed: Issue where the progress window would halt the current process when encountering certain errors
* Fixed: "Can't use function return value in write context" error on dashboard
* Tweak: Enabled thumbnail files editing in the Edit Template page
* Tweak: Readded the max-width and max-height to the thumbnail image styles to prevent thumbnails from stretching
* Tweak: Don't restock if the order has already been marked as refunded or cancelled in WooCommerce 
* Tweak: When processing new orders, check for and skip duplicate OrderIDs in the same request

= 2.5.1 – 2020-04-03 =
* New: Added setting option to select thumbnail size on Listings page
* New: Added option to select where to store the eBay Order ID when creating orders in WooCommerce
* Fixed: With Errors view should only show active listings and skip warnings
* Fixed: Stop archived listings from being loaded in findItemBySku()
* Fixed: Button layout on category settings page
* Fixed: Issue where WP-Lister incorrectly adds variation back into the request if it is unable to mark it as for deletion because the variation has sales
* Tweak: Enabled image column by default
* Tweak: Reordered columns on Listings page to be consistent with Amazon version: Image, SKU, Title, Quantity, Price
* Tweak: Specify in the order notes if inventory update will be done in the background
* Tweak: Replace wc_clean with wp_kses in item specifics
* Tweak: Refresh eBay details automatically after adding new account

= 2.5 - 2020-03-26 =
* New: Added the new ((product_gallery)) template shortcode
* New: Allow the (meta_) shortcode in profile item specifics
* New: Added support for PWB (Perfect WooCommerce Brands)
* New: Reorganised the Tools page and added naviagtion tabs
* New: Added direct links to all tools tabs to admin toolbar
* New: Added a With Errors view in the Listings table
* Fixed: Broken layout on profile selection message on WooCommerce product page
* Fixed: Match the default value with the default option of the Show eBay Product Totals setting in the Advanced Settings page
* Fixed: Convert local decimal points prior to saving using wc_format_decimal
* Fixed: Skip adding the Maßeinheit attribute from WC Germanized if it already exists in the profile
* Fixed: Use profile values over WC Germanized unit and unit_quantity
* Fixed: Layout issues on edit product page, profile editor and some warning messages
* Tweak: Inventory check: when checking orders compare actual number of line items against recorded line item count
* Tweak: Improved line item check and clarify warning message on eBay Orders page
* Tweak: Store eBay User ID in a private order note instead of the order comments

= 2.4.3 - 2020-03-15 =
* New: Added a "Refresh" button on top of eBay Orders page
* New: Improved layout and colors scheme on WP-Lister pages
* Fixed: Issue where listings could show as deleted for single split variations
* Fixed: Background CSS change while modal window is open
* Tweak: Make the current profile visible permanently on switch profile form
* Tweak: Regard the CompleteStatus field on order which are missing the PaidTime field
* Tweak: Various layout tweaks and CSS adjustments
* Tweak: Show message on order details that some order details are unavailable in WP-Lister Lite
* Dev: Removed deprecated setting option "Enable API auto relist" and deprecated method EC::reviseInventoryForProducts()

= 2.4.2 - 2020-02-21 =
* Fixed: Saving template content like functions.php was broken since 2.4.1
* Fixed: Check the reduced_stock property before reverting stock reduction in WooCommerce
* Fixed: Skipped listings were counted as stock reduced when checking for stock reduction mismatch
* Tweak: Filter out archived listings when querying by eBay ID and SKU

= 2.4.1 - 2020-02-14 =
* Added: Support for ATUM Multi-Inventory plugin
* Fixed: When checking if an order is paid, make sure we are checking using the latest data from eBay
* Fixed: Test the returned value of wc_get_product() before accessing its property to avoid PHP errors
* Fixed: Trying to access the default account when no accounts exist throws a notice
* Fixed: Broken settings tab links from the stocks log page
* Tweak: Display the account name in products with multiple listings
* Tweak: Add a history entry when skipping line item processing for archived listings
* Tweak: Checking both revise listing and switch profile should not leave status to Changed
* Dev: Added wplister_meta_nl2br_value filter hook
* Dev: Use wp_kses_post_data filter when parsing HTML input on edit template page

= 2.4 - 2020-01-24 =
* New: For products linked to multiple listings, the profile switcher allows to define separate profiles per listing
* Fixed: Use wple_clean instead of wc_clean to prevent fatal errors when updating WooCommerce
* Fixed: Issue where the WooCommerce iOS app could crash when viewing orders from WP-Lister
* Fixed: Link expired error in the apply profile view of the Listings page
* Fixed: Fixed the processListingItem() method loading archived listings
* Fixed: Alignment of the quick edit elements
* Fixed: PHP Warning: Invalid argument for foreach
* Fixed: PHP Warning: Illegal string offset
* Tweak: Load the product-level shipping policy if the shipping service type is not available
* Tweak: Renamed the Revise All Items Limit so it's clear that it affects the Revise Changed Items action
* Tweak: Split fetching eBay user details into separate tasks to prevent issues on slow servers
* Tweak: Limit the getAllPublishedWithTemplate query so it only pulls what's needed
* Dev: Added a Stocks Log view in the Developer Settings page
* Dev: Added wplister_ended_item action hook

= 2.3.2 - 2020-01-08 =
* Fixed: Possible issue where order processing would result in zero items being processed (since 2.3)
* Fixed: Possible error in the preview modal window when calling is_plugin_active
* Fixed: PHP Warning "get_class() expects parameter 1 to be object"
* Tweak: Sort and display the categories in the Settings page alphabetically
* Dev: Use wc_get_product() instead of get_post() to get product description

= 2.3.1 - 2020-01-04 =
* New: Added a setting to remove the tracking data added by eBay to the address one field for AU transactions
* Fixed: Possible issue where order processing could get stuck calling the getPaidTime() method
* Fixed: When processing cancelled orders, remove the flag marking order as reduced to prevent WC from restocking the products
* Fixed: PHP Warning "count on non-countable variable"
* Dev: Added the wple_revise_inventory_status_object filter to allow plugins to modify the InventoryStatusType object prior to being sent to eBay
* Dev: Added the wple_order_item_quantity_purchased filter hook
* Dev: Added the wple_disable_product_trash_check filter hook

= 2.3 - 2019-12-29 =
* New: Added support for the DHL for WooCommerce plugin
* New: Added setting to set the default shipping provider in the Advanced Settings page
* Tweak: Use PaidTime to determine the real status of eBay orders
* Fixed: Added the wple_clean() function to avoid errors when updating or deactivating WooCommerce
* Fixed: Defined the missing EBAT_SEVERITY_ERROR constant
* Fixed: Non-numeric value encountered and other warnings
* Dev: Added the wple_use_excerpt_as_subtitle filter hook

= 2.2.11 =
* Fixed: Call to undefined method Order::exists() when cancelling an order
* Fixed: Inventory check will count stocks in variable products correctly now

= 2.2.10 =
* Fixed: Error message "curl_error 56 OpenSSL SSL_read: Success" that some users recently started seeing
* Fixed: Locked variable listing profile prices were not getting applied when using ReviseInventoryStatus
* Fixed: Properly process the CancelPending status from eBay when checking for cancelled orders
* Fixed: Possible issue where data was added in the Street1 order field 
* Fixed: Current value of the 'media' custom field type not being displayed

= 2.2.9 =
* Fixed: Issue processing refunds for users of the German Market plugin

= 2.2.8 =
* Fixed: Name on billing address in WooCommerce orders
* Fixed: Test eBay Connection on Tools page

= 2.2.7 =
* Fixed: Template editor was stripping some HTML

= 2.2.6 =
* Fixed: Link expired message on the Update on eBay order button
* Fixed: Using count on a non-countable variable warning message

= 2.2.5 =
* Fixed: Link expired error when editing profile and other cases

= 2.2.4 =
* Fixed: Improved security and compatibility by following best practice guidelines 
* Fixed: The "Apply profile to eBay price" setting was ignored when revising inventory status on (locked) variable items
* Tweak: Changed the capability required to access the eBay metaboxes from manage_ebay_listings to prepare_ebay_listings
* Tweak: Hide certain links and options if the current user does not have the proper user capability

= 2.2.3 =
* Added: UnitInfo support for the German Market plugin
* Fixed: Possible issue processing orders that was introduced in 2.2.2
* Fixed: Price logic for flattened variations
* Fixed: Preload OrderWrapper to fix class not found error
* Tweak: Search for the ExtendedOrderId when searching through the Orders table
* Tweak: Import and Export category mappings based on the currently selected account
* Tweak: Ignore Buy It Now price on non-auction listings

= 2.2.2 =
* Fixed: Possible stock sync issue in case the eBay API sends multiple order records using the same OrderID
* Fixed: Restock listing when an orders status switches from Completed to Cancelled
* Fixed: PHP Warning "empty string to a string offset"
* Fixed: Only show valid action links on Accounts page
* Tweak: Cache the listings summary to improve performance on loading the Listings page
* Tweak: Skip new stocks check if stock management is disabled for the product
* Tweak: Skip stock processing for "Active" orders to avoid rare inventory sync issues
* Tweak: Improved stock reduction check icon and tooltips on Orders page
* Tweak: Show warning for users still on WooCommerce 2.x
* Tweak: Improved most settings pages - more descriptions, sections, "default" tags, etc.
* Dev: Added the wplister_create_order_pre_save and wplister_update_order_pre_save action hooks
* Dev: Added the wple_apply_profile_to_item_data filter hook
* Dev: Added the Item Specifics Limit setting on the developer settings page (Default 100)
* Dev: Added developer setting to send email notification if the order stock reduction check fails

= 2.2.1 =
* Fixed: The "Some variations have no price" error message some users were receiving on version 2.2 should be gone again
* Tweak: Translations on translate.wordpress.org should now be possible
* Dev: Added the wplister_check_item filter to allow 3rd-party devs to insert their own check prior to publishing/revising listings 

= 2.2 =
* Added: New Background Revisions setting option which can be enabled on the advanced settings page  
* Added: Support for Admin Columns Pro 
* Added: Support for multithreading when ending items in bulk 
* Fixed: Nonce error when using the Publish to eBay link from the Edit Product screen 
* Fixed: Various messages about deprecated calls 
* Fixed: Nonce issues on the Messages page 
* Tweak: Changed the text domain to wp-lister-for-ebay 
* Tweak: Apply the profile price adjustments to variable prices 
* Tweak: Removed the Hotline block in the default template 
* Tweak: Set default max item specifics to 20 to avoid recent issues 
* Dev: Added the wplister_apply_profile_auction_title filter hook 
* Dev: Added the wplister_force_product_update_action hook to allow to force the handle_product_stock_update() to execute 

= 2.1.4 =
* Added: Option to use the Extended Order ID in the Advanced Settings page 
* Fixed: Deprecated notice for get_woocommerce_term_meta() 
* Fixed: Prevent GTC listings from being archived after being sold 
* Tweak: Do not autoload options that tend to contain lots of data 
* Dev: Added the wple_orderbuilder_prices_include_tax filter to allow custom fix for tax in prices mismatch between stores 

= 2.1.3 =
* Added: Support for ATUM Inventory 
* Added: Sort listings table by quantity 
* Fixed: Set the correct quantity in the _reduced_stock item meta 
* Fixed: Issue where sales tax was removed from order 
* Fixed: Issue where product variations were not stored in the local listings 
* Fixed: Missing Details object in WooOrderBuilder::getProductTax() 
* Fixed: Incorrect variable passed in wplister_added_order_item_meta 
* Fixed: Ignore custom prices if the Custom Price setting is disabled 
* Fixed: Fatal error Uncaught ArgumentCountError: Too few arguments to function wpdb::prepare() 
* Fixed: Misc PHP warnings and errors 
* Fixed: Apply profile quantity settings in the Listings table 
* Fixed: Bulk edit was not triggering listing update 
* Tweak: Record the AdjustmentAmount cost as a fee 
* Tweak: Trigger wplister_product_has_changed instead so the profile gets reapplied prior to revising 
* Tweak: Read unit info from WC Germanized plugin 

= 2.1.2 =
* Added: Support for WooCommerce Stock Manager
* Added: Support for PWB Bulk Edit
* Added: Support for GPDR removing the customer note with the eBay User ID in orders from eBay 
* Added: Listen to sale price changes and call ReviseInventoryStatus on affected products 
* Fixed: Issue where WooCommerce would not restock the item when its refunded
* Fixed: Issue where EAN was set to 1 regardless of the value
* Fixed: SQL error when sorting listings by SKU
* Fixed: PHP Notice: Undefined index: exclude_attributes (and other PHP notices)
* Fixed: PHP Error: $this->EC is not an object
* Tweak: Improved invalid token warning message
* Tweak: Recheck token status automatically as error 931 can be temporary
* Tweak: Display message to refresh details on edit profile page if countries list is empty
* Tweak: Strip HTML tags from the listing title
* Dev: Added the wplister_switch_profile_profiles filter hook
* Dev: Added the wplister_added_order_item_meta action hook
* Dev: Added developer setting to disable pop-up errors in the profile edit page 

= 2.1.1 =
* Added: Exclude Attributes setting in Profile Editor 
* Added: Support for the WP Intense External Images plugin 
* Added: Tool to check the history log on all orders for stock reduction discrepancies 
* Fixed: Do not insert GTINs for parent products 
* Fixed: Various possible PHP warnings 
* Fixed: Saving category mappings if they span into several pages 
* Fixed: Issue where listings were revised even if unlocked and Revise checkbox is unchecked 
* Fixed: display_errors getting enabled even if WP_DEBUG is disabled 
* Dev: Added wplister_item_relisted action hook 
* Dev: Added support for the 'media' field type for loading images from the Media Library into the listing template 

= 2.1.0 =
* Added the Rename Template Functions tool 
* Added the ability to sort listings by SKU 
* Added option to view eBay Messages within WP-Lister (Pro only)
* Added billing and shipping address index to make the orders searchable 
* Added a Retry link in the order notes if completing the sale failed for some reason 
* Added an empty "use profile setting" option in the Package Type selection on edit product page 
* Added a check for the PayPal Express plugin to fix listings getting revised twice 
* Added class names to the variations table HTML 
* Fixed issue where listings were stuck as changed when bought via WooCommerce 
* Fixed Create Order link showing up in the Lite version 
* Fixed the pagination in the Categories Map table 
* Fixed the date_created date when creating WooCommerce orders from eBay 
* Fixed line_total calculation in getProductTaxFromProfile 
* Rename template functions automatically when duplicating a listing template
* Improved handlling error code 17470 (expired token) and prompt user to renew their token 
* Trigger wplister_revise_inventory_status instead of wplister_product_has_changed after stock has been modified in WooCommerce 
* Force error logging when WPLISTER_DEBUG is enabled 
* Added wplister_disable_pip_emails filter hook 
* Added wplister_update_ebay_order_address filter hook 
* Added wplister_delete_unsold_variations filter hook 
* Added second parameter to the wplister_revise_inventory_status hook to disregard the stock quantity cache check 

= 2.0.43 =
* WC3.5: Fixed double stock reduction on eBay orders in WooCommerce 3.5 
* Added support for searching and listing specific listings using comma-separated eBay IDs 
* Added option to disable attribute mapping in the profile to prevent getting variation mismatch errors 
* Added a note in the Reserve Price field about incurring additional fees when enabled 
* Added language file for DE_formal 
* Fixed WC error message when trying to store an invalid email address 
* Fixed issue where listings are being marked as changed after importing orders from eBay 
* Fixed autodetect taxes not working 
* Disable order emails from Print Invoice and Packing plugin 
* Store the _order_tax postmeta by setting the $order->set_cart_tax() method 
* Convert image URLs to HTTPS before sending to EPS 
* Store variations in split listings and use them instead of the post id when comparing variations 
* Apply the Out of Stock Threshold when running ReviseInventoryStatus 
* Deduct the Out-of-stock threshold from the WC products when checking the inventory numbers 
* Deduct the out-of-stock threshold from variations 
* Make sure quantity does not go below zero after applying the out of stock threshold 
* Regard out-of-stock threshold when checking product inventory 
* Check all active templates for function name conflicts 
* Added wplister_skip_foreign_order_override filter hook 
* Added wple_before_process_listing_item action hook 

= 2.0.42 =
* Fixed issue where admin notices would stack up when processing tasks in the background
* Fixed issue where some images in the listing template would still use HTTP instead of HTTPS 
* Fixed issue where the incorrect quantity was displayed on the Listings page after revising inventory status 
* Fixed 'non-numeric value encountered' warning message 
* Fixed issue when filtering with On and Not On eBay 
* Remove the shipping tax for GSP orders 
* When revising items update listing status to published if no changed variations have been found 
* Dont set the order status to Refunded when processing eBay refunds to prevent WC from refunding the entire order total 
* Reset variation quantity cache after processing order line items 
* Use wc_get_product_id_by_sku() when matching sales by SKU 
* Fixed issue where not all HTML entities were decoded for all shortcodes 
* Added wplister_order_builder_line_item filter hook 

= 2.0.41 =
* When a product is trashed, archive ended and sold listings linked to the trashed product 
* Do not allow products with active listings to be trashed 
* Added support for custom tracking providers in WC Shipment Tracking v1.6.6+ 
* Removed incompatibility warning message for WooTabs 
* Fixed support for Aelia Currency Converter 
* Fixed disabling partial refund emails in orders imported from eBay 
* Fixed rare template functions conflict error 
* Fixed double slash in URL causing 403/404 error in some servers 
* Clear post and object cache before updating order status 
* Use the woocommerce_tax_based_on option when calculating for the shipping tax 
* Listen to events from wc_product_update_stock() to be able to mark listings as changed 
* Store the notices in the wple_admin_notices transient so they can be displayed even after a reload 
* Use get_date_from_gmt() to display local date and time 
* Added wplister_use_title_ellipsis filter hook 
* Added wplister_order_builder_line_item filter hook

= 2.0.40 =
* Added option to mark an order as unpaid when completing the sale on eBay 
* Added the Unlocked table listings view 
* Added a developer setting to disable the item specifics cache 
* Added wplister_set_listing_variations_quantity filter hook 
* Added wplister_before_build_item action hook 
* Added wplister_get_stock filter hook 
* Skip duplicate variation specifics 
* Decode HTML entities in the listing subtitle before sending it to eBay 
* Create a wpnonce form field instead of using wp_nonce_field() to prevent getting looping query strings 
* Use WC_Product::is_on_sale() to check if a product is currently on sale 
* Compare URLs of the additional images against the main image instead of just the base name 
* When matching order items by SKU, attempt to locate using the variation's SKU in case the parent has no SKU set 
* Use WC_Refund::get_id() if supported 
* Use WC_Order::save() to trigger events in the Aelia Currency Converter plugin 
* Use WC_Order::set_status() then WC_Order::save() to prevent WPLister from making order status overrides useless 
* Fixed the Delete action in the Profiles table 
* Strip invalid characters from the tracking carrier when completing sale on eBay 
* Store the item's tax class regardless of the vat_enabled status 
* Delete shipping line item cost from orders with multileg shipping 
* Set the order's billing address as the customer's address for tax calculation purposes 

= 2.0.39 =
* Added setting to record COD Cost from the eBay orders 
* Added WC Rest API v2 support for editing products and variations 
* Added message about ebay proxy for the user to accept (GDPR compliance) 
* Fixed some links in the Listings and Accounts table 
* Fixed warnings shown by PHP Compatibility Checker 
* Fixed version details links on Plugins page 
* Force update the title after new split listings have been created 
* Show all products (in stock / out of stock) when filtering for "on ebay" and "not on ebay" - for increased clarity and performance 

= 2.0.38 =
* Fixed error "Input data for tag Item.eBayPlus is invalid or missing"

= 2.0.37 =
* Added full support to enable/disable the eBay Plus flag - enable if set on either product or profile level, disable otherwise 
* Added a setting option to enable the product counts on "On Amazon" and "Not on Amazon" views 
* Disabled the product counts by default to avoid rare performance issues on slow servers 
* Do not allow user to select non-leaf store categories as they cause errors with eBay 
* Fixed Edit Account page 
* Fixed bulk actions on Listings page 
* Fixed force update check button on settings page 
* Fixed the Create Order link 
* Fixed javascript error with italian language file 
* Added wple_product_stock_decreased action hook 
* Added wple_filter_unchanged_variations filter hook to allow forcing a revision 
* Added wple_category_specifics_max_names and wple_category_specifics_max_name_values filter hooks 

= 2.0.36 =
* Fixed Lock/Unlock All actions on Tools page 
* Fixed WPLE breaking Delete Permanently links 
* Fixed link handling in short product description (excerpt) 
* Added wple_template_strip_anchor_text filter hook to retain the anchor text after removing the link 
* Added more nonces to protect against CSRF (cross site request forgery) 
* Run wplister_product_has_changed action hook using the parent id for variations 

= 2.0.35 beta =
* Added counts to the On eBay and Not on eBay filters 
* Added support for eBay Plus 
* Added wple_product_attribute_values filter 
* Handle refunded emails as well in the `Disable emails on status change` setting 
* Make sure handle_product_update() only runs during WC product imports 
* Skip items without eBay ID when using Reset ended listings bulk action
* Fixed rare issue with very long ebay usernames 
* Fixed shipping tax bug where compound rates were being ignored 
* Fixed update interval settings of 15min, 30min and external in Lite version 

= 2.0.34 beta =
* Disabled private listing option because it has been removed in eBay API version 1045 
* Fixed possible fatal error (undefined method setShippingPackage) 

= 2.0.33 beta =
* Use eBay API version 1045 
* Indicate eBay Plus orders on Orders overview pages 
* Added wplister_meta_shortcode_value filter hook 

= 2.0.32 beta =
* Added support for the built-in WooCommerce products importer 
* Added option to match order items by SKU instead of their eBay IDs 
* Added wplister_item_specifics_processed_attributes filter 
* Minor CSS fixes for WooCommerce 3.3 
* Minor CSS improvements on edit product page 
* Enable handling of refunds by default for all new users 
* Updated italian and german language files 
* Query for parent_id when filtering Not on eBay listings 
* Fixed a few rare PHP warnings 
* Fixed issue with variation images not being passed when attributes need to be translated 
* Fixed "Find matching product" option on edit product page 
* Fixed possible issue with store categories map and multiple accounts 

= 2.0.31 =
* Added new "HTTPS conversion" advanced setting option to enforce using HTTPS when processing a listing template 
* Added option to convert all HTTP content to HTTPS for the entire listing template/description 
* Fixed issue where gallery thumbnail images were still using HTTP instead of HTTPS (unless HTTPS conversion was enabled) 
* Fixed order creation process to take into account the taxes before updating the shipping cost 
* Fixed possible issue when importing listings (using the importer add-on) with multithreading enabled 
* Added qTranslate support to translate attribute keys and values 
* Added wplister_process_text_shortcodes filter hook 
* Added wplister_ebay_order_shipping_total filter hook 

= 2.0.30 =
* Added option to download and upload listing profiles (backup and restore) 
* Added more Update interval options on Lite version, including external cron job 
* Added additional eBay Order filter options (Shipped/Unshipped and In WooCommerce/Not In WooCommerce) 
* Added new button on Tools page to update the Shipped filter data for existing eBay orders 
* Added wple_account_locale filter hook 
* Updated and fixed language files - and included updated Italian language file provided by Roberto 
* Fixed possible fatal error when trying to revise listings for deleted products, added getProductTitle() method 
* Fixed some rare PHP errors and warnings 

= 2.0.29 =
* Added partial support to qTranslate to translate the title and description based on the account's site code 
* Added wplister_updated_item_details action hook 
* Fixed Use SKU as EAN/UPC option, which could get overwritten by Does not apply text 
* Fixed possible PHP error on Orders page (Lite version only)
* Fixed CSS conflict if WP-Smushit is installed 
* Use WC_Product::get_title() instead of get_the_title() for consistent behavior 
* Prevent illegal string offset warning message

= 2.0.28 =
* Update the shipping total when updating orders from eBay 
* Added a developer setting to set a limit to the number of changed items to revise using the Revise All Changed Listings button 
* Fixed On eBay/Not on eBay filter not working in combination with some other filter options 
* Fixed possible fatal error in ProductWrapper_woo.php line 74 

= 2.0.27 =
* Fixed "WC_Product::get_total_stock is deprecated" warning 
* Do not process the sales tax from eBay orders if no sales tax rate ID has been selected 
* Store the shipping discount profiles in the ebay_accounts table 
* Set the vat_enabled flag to true if a shipping tax exists 

= 2.0.26 =
* Added a new Link Handling option to leave ebay links in the description 
* Added missing support for using SKU as EAN in variations 
* Fixed issue where image links within listing description were not using https 
* For GTC listings, insert default values if brand and MPN are both empty 
* Store the shipping tax line item separately if there are no item taxes in the order 
* Strip slashes when saving profile data 

= 2.0.25 =
* Added a profile setting to use the SKU as the EAN 
* Remove the ReservePrice from eBay if none is set in the listing 
* Store the sales record number as order postmeta 
* Fixed rare issue where WP-Lister would display false error messages when revising items 
* Fixed issue when publishing or revising items which have spaces in image filenames 
* Fixed possible "Input data for tag Item.UseTaxTable is invalid or missing" error message 
* Use native WC3.x methods to update the order date after updating its status to prevent the status update from resetting 

= 2.0.24 =
* Improved compatibility with WooCommerce 3.x 
* Added an option to use eBay Order Numbers in WC Orders 
* Added multithread support to Import from eBay add-on 
* Added wplister_created_customer_from_order action hook 
* Use the parent ID for variable products when updating stock status after a refund 
* Strip unnecessary slashes before sending item specifics to eBay 
* Strip spaces when storing _billing_phone 
* Adjusted the ShippingService column length to be to store longer service names 

= 2.0.23 =
* Improved compatibility with WooCommerce 3.x 
* Added profile setting to disable secondary category 
* Use wc_price() when displaying variation prices in the [[product_variations]] shortcode to include currency 
* Do not output any errors or notifications on the frontend 
* Added filter wplister_status_summary to allow 3rd-party code to prevent notifications from showing up 
* Added 'wplister_include_vat_in_order_total' filter to allow external code to prevent VAT from being added to the order total 
* Added the ability to store multiple tax rates for a singe line item 
* Added support for eBay error 21916750 that gets thrown when trying to revise an ended Fixed Price Item listing 
* Added the wplister_order_post_data filter to allow 3rd-party code to modify the post data used when creating WC Orders 
* Applied the fix for thumbnails div being hidden to the default-with-gallery template 
* Listen to product variation updates via the REST API 
* Revise locked split variation listings on product save 
* Tweak: Set the value of IncludePrefilledItemInformation to honor the Use Catalog Details profile setting 

= 2.0.22 =
* added support for WC Shipment Tracking v1.6.6 
* load the brand from the parent product for split/single variations 
* improved WooCommerce 3.0 compatibility: 
* decrease the stock quantity without relying on the inconsistent 3rd parameter of wc_update_product_stock() 
* skip calling ListingsModel::setListingVariationsQuantity() when updating from the edit product screen 
* use WC_Product::get_default_attributes() if available, in place of the deprecated WC_Product::get_variation_default_attributes() 
* tested on WordPress 4.8 

= 2.0.21 =
* fixed an issue decreasing stock for variable products after a sale on WC3.0 
* fixed an issue creating order line items for split variations 
* fixed an issue loading categories for split variations 
* set WC Brands attribute's ID to _ebay_brand to overwrite the default attribute value 
* store the specs' name instead of attribute to prevent adding duplicate NameValue pairs 
* automatically reapply listing profile when auto-relisting products to include updates seller policies 
* fixed ProductWrapper::getVariationParent() to return the corrent ID in WC2.x and WC3.0 
* fixed a CSS issue where the thumbnails div is being hidden 
* removed jQueryFileTree connectors due to a potential security issue 

= 2.0.20 =
* fixed adjusting stock levels in WooCommerce 2.6 when product is sold on eBay (since 2.0.19)
* fixed the order total value when autodect taxes is enabled 

= 2.0.19 =
* fixed warning “listing’s site and profile’s site don't match” showing up for listings on eBay Motors (false positive) 
* fixed stock values getting pulled from the old product instance when sending stock notification emails 
* fixed warning in the WooCommerce email settings page (when $order is null) 
* fixed the handling of serialized metadata when duplicating products 
* updated code to use new CRUD schema for accessing products and orders on WooCommerce 3.0 
* include the WP REST API when checking for REST requests in wple_request_is_rest() 
* store the shipping tax line inside a 'total' index to make shipping taxes appear in the order items list 

= 2.0.18 =
* fixed issue where disabled emails are still sent on WooCommerce 3.0 
* fixed issue where eBay metadata was not copied when duplicating a product on WooCommerce 3.0 
* fixed possible “Notice: Undefined variable: tax_rate_id in WooOrderBuilder.php:856” when creating WC order 
* show warning if a listing’s site and its profile’s site don't match 

= 2.0.17 =
* added option to use multithreading for verify, revise and update from eBay actions (Pro only) 
* added 'wplister_complete_order_data' filter to allow 3rd-party code to modify the order data before completing the order on eBay 
* added option to fill 'Does not apply' to both UPC and EAN if both are empty 
* fixed check whether primary category requires UPC / EAN and fill in "Does not apply" automatically (broken since 2.0.9.21) 
* fixed issue where attribute placeholders in post title were not working
* fixed sync issues with orders for variable items placed on Amazon 
* fixed fatal error when plugin update check doesn't return a valid response 
* fixed On eBay filter being reset when using search form, direct pagination or other filter options on Products page 
* fixed possible PHP warning in ListingsModel::setListingVariationsQuantity() 
* fixed possible page_to_screen_id() PHP notice when convert_to_screen is called earlier that usual 
* fixed issue that caused wp_ebay_sites table not being created during initial setup on some servers 
* removed the shipping tax from the order tax line item if order is multi-leg shipping 
* always use full product title for product_title shortcode in listing description, even if title is longer than 80 characters 

= 2.0.16 =
* added an option to offset X number of days when scheduling listings 
* made the Sold column sortable 
* made the order comments translatable 
* strip out links from the description only 
* record shipment tracking data from eBay orders 
* improved procesing PayPal Transaction ID when updating orders from eBay 
* changed the order of loading eBay categories to using mapped categories over profile categories 
* display split variation products in the Products table when the On eBay filter is selected 
* update the listing variations stocks WP-Lister so it's in-sync even after updating the WC product 
* import products IDs from WP-Lister for Amazon: Read the profile's external ID type of the product's ID type is empty 
* prevent accidentally ending all listings without a parent by running the “End listings on eBay” bulk action without any products selected 
* added back the viewport meta to pass eBay's responsive test tool (in header.php in the default template)
* check if the request is from AJAX or REST before printing any admin messages 
* prevent messages from displaying when on a SagePay endpoint 
* set the correct account when revising and relisting from the Edit Product screen 
* show SQL errors when attempting to convert tables to utf8mb4 
* fixed custom title suffix option

= 2.0.15 =
* added support for WooCommerce Out of Stock Threshold 
* auto-detect staging site - if domain contains "staging" or "wpstagecoach" 
* added action hooks wplister_listing_published and wplister_listing_revised to allow 3rd party plugins to run custom code after a listing has been published or revised 
* make sure the daily cron schedule is executed when wp-cron is broken and an external cron job is used (trigger daily cron by external cron if not executed for 36 hours) 
* add the DiscountPriceInfo element only if it will be used (prevent eBay from throwing warnings on variable products) 
* add MPN, if set, to the ItemSpecifics collection 
* force all date/time functions to use UTC (use gmdate instead of date) 
* raised the limit of category specifics from 250 to 1000 
* record the refund reference ID to prevent creating duplicate refund line items 
* removed the viewport meta tag which was causing issues when viewing listings on mobile devices 
* renamed “Update details from eBay” bulk action to “Update status from eBay” 

= 2.0.14 =
* added support for eBay's Minimum Advertised Price (MAP) option 
* added the ability to edit the ebay start price as well as revise an item from the Product Quick Edit box 
* added new shortcode (product_thumbnails) to display thumbnails for all product images without active content 
* added new shortcode (product_tags) to display product tags 
* added filter hook wplister_compatibility_heading to allow the compatibility table heading to be renamed/translated 
* added filter hook wplister_order_builder_line_item to allow 3rd-party code to modify the line item data before they are added to the order 
* added support for additional variation images provided by WooThumbs plugin 
* added support for autodetecting shipping taxes when creating Woo orders 
* added an option to sort store categories alphabetically 
* added order history log message if “Ignore backorders” option is active and product has backorders enabled 
* improved backorders notice on listings page
* revise inventory of other listings if the product being processed is linked to multiple listings when processing orders
* listen for product updates made via the new WC REST API in WC 2.6+ 
* strip out links that could have been added by shortcodes 
* item titles inside the job runner window link to the edit product page now 
* fixed sort order for store categories 

= 2.0.13 =
* new responsive (mobile friendly) default template without active content 
* added filter hook wplister_attribute_values_separator to allow the changing of attribute values separator (e.g. from <br> to a comma) 
* added option to allow the profile price rules to be applied to a product's ebay_start_price 
* added support for refunded ebay orders by setting the order's status to refunded, creating a wc_refund line item and restore product stock level
* improved WooCommerce Orders page layout on mobile devices 
* improved support for Loco Translate, created new .pot file and fixed the text domain on some strings 
* include the post_excerpt when processing shortcodes and links 
* pull meta data from the parent listing if the item is a single (split) variation 
* do not output HTML errors for request through AJAX or the REST API 
* increased maximum length for ItemSpecifics and VariationSpecifics from 50 to 65 characters and added maxlength attribute to form fields 
* store the currency before storing the order total to allow currency switcher to work when saving order totals 
* fixed possible division by 0 warnings when creating orders in WooCommerce
* fixed possible incorrect listing duration on listings page when running search query 

= 2.0.12 =
* reverted the initial order status to pending to trigger new order emails again 
* when completing an eBay order manually and no feedback text was entered, use default feedback text 
* fixed wrong translation in german language file (Use SKU as UPC) 

= 2.0.11 =
* fixed profile not getting applied when preparing to list from the product page sidebar 
* fixed issue where created WooCommerce order line items would have VAT subtracted from their prices even though no VAT is applied to the order 
* added button to repair crashed MySQL tables on developer tools page 

= 2.0.10 =
* IMPORTANT: Please update to 2.0.10 as soon as possible. Older versions of WP-Lister will stop functioning in autumn 2016!
* use WP Lab server cluster to connect to eBay API to avoid exceeding eBay's call limit 
* fixed variation images if selected variation attribute is mapped to different item specifics 
* fixed possible PHP warning when running inventory check without any published listings 
* fixed issue when updating details from eBay for items which do not exist on eBay anymore 

= 2.0.9.23 =
* added option to list/prepare a product and switch listing profile from the Edit Product screen's sidebar 
* allow variation prices to be entered with decimal comma and automatically convert to decimal point 
* added option to enable/disable automatic tax calculation when creating orders in WooCommerce 
* fixed incorrect order date when creating orders in WooCommerce 2.6 
* fixed issue where attribute_* shortcode would show all variation attribute values for single split variations 
* improved 'On eBay' and 'Not on eBay' product filters for large sites 

= 2.0.9.22 =
* added Interlink Express shipping provider 
* added missing chosen.js script for WooCommerce 2.6+ 
* prevent sending CompleteSale request when creating WooCommerce orders from already shipped eBay orders 
* fixed possible Fatal error: Call to a member function getSupportedSellerProfiles() on a non-object 
* fixed wp_remote_* calls which will be returning objects in WP 4.6 

= 2.0.9.21 =
* added "End listings on eBay" bulk action on Products page 
* added "deep scan" option to image check tool to find images below 500px even if WP attachment meta data is incorrect 
* added internal shortcodes for listing template developers (admin_ajax_url and wpl_listing_id) 
* automatically revise inventory status on eBay when a product's stock has been restored after setting the order's status to cancelled or refunded in WooCommerce
* improved performance on Listings page when displaying products with dozens or hundreds of variations 
* improved display of errors and warnings in preview window 
* fixed missing variation image if variation image is the same as main product image 
* fixed issue where revising locked variable item would ignore custom eBay price set for parent variation 
* fixed issue refreshing site specific eBay details for eBay US if default account uses a different site than US 
* fixed possible fatal error when revising items using a category that no longer exists (Call to a member function getConditionEnabled() on a non-object) 
* fixed possible PHP notice during bulk revise with Upload to EPS enabled ("Trying to get property of non-object in WPL_AjaxHandler.php on line 453") 

= 2.0.9.20 =
* automatically check whether primary category requires UPC / EAN and fill in "Does not apply" if no UPC / EAN is set 
* added support for WooCommerce Sequential Order Numbers Pro 1.7.0+ 
* added warning about additional listing fees for bold title and subtitle on edit profile page 
* improved customer note on created WooCommerce orders - renamed eBay Sales Record ID and removed eBay Order ID 
* allow custom code to modify prices using woocommerce_get_price, woocommerce_get_regular_price and woocommerce_get_sale_price filter hooks 
* fixed issue revising locked flattened variations (fall back to full revision to prevent Error 21916799: SKU Mismatch) 
* fixed possible fatal error when revising or verifying items on PHP 5.4 and older 
* fixed previously set subtitle and bold title not being removed when revising a listing 

= 2.0.9.19 =
* allow to search listings by partial SKU 
* improved tax information in created WooCommerce orders if prices are entered without tax (fix issue with WooCommerce Print Invoice & Delivery Note plugin) 
* remove invisible control characters (like 0x1f) from listing title to prevent error 90002 
* trim custom admin menu label to prevent empty tables issue 
* do not send main product image as variation image - prevent duplicate image thumbnails in eBay gallery 
* fixed blank details page if WooCommerce Product Reviews Pro plugin is active 
* fixed option to fill in Missing Product Identifiers with "Does not apply" not working on flattened variations 
* fixed issue where only one product ID (UPC or EAN or ISBN) would be sent to eBay - for variable items 
* fixed issue where only a single listing item was revised when updating multiple locked listing for the same product via CSV import (or wplister_revise_inventory_status action hook) 
* make sure wplister_revise_inventory_status, wplister_revise_item and wplister_relist_item action hooks use the right eBay account for a given listing (fix possible error 21916294 "Revise item denied" when updating items via CSV import) 
* fixed issue with recommended item specific values containing broken UTF-8 characters, causing item specifics to break on some servers 
* updated german translation and .pot file 

= 2.0.9.18 =
* added experimental support for completing orders with tracking information via CSV import using WP All Import 
* show message if falling back to ReviseItem instead of ReviseInventoryStatus - and show warning in listings table if locked variable item does not have unique SKUs 
* if only some variations have MPNs, fill in missing MPNs with "Does not apply" automatically (prevent Error 21916587: Missing name in name-value list.) 
* fixed issue revising variable items which have MPNs set on the variation level (Error 21916587 and others) 
* fixed product image upscale tool not processing gallery images 
* automatically prefix invalid 12 digit EANs with '0' if the resulting 13 digit EAN is valid 
* regard maximum batch size to apply profiles when updating listing template as well 
* added incompatible plugin warning for "WooCommerce Multiple Free Gift PRO" 
* improved support for WooCommerce Product CSV Import Suite (fix issue where updated listings were not marked as changed) 
* improved handling of templates with additional files 

= 2.0.9.17 =
* added option to keep sales data for N days (remove older eBay orders from WP-Lister automatically) 
* added Direct Freight shipping provider 
* added filter hook wplister_listing_column_(column_name) - allow 3rd party devs to handle custom columns 
* added filter hook wple_order_has_vat_enabled to allow disabling VAT processing on specific WooCommerce orders 
* pass $ItemObj parameter to wplister_process_template_html filter hook 
* improved creating orders: convert country state names to ISO code (New South Wales -> NSW) (requires WC2.3+) 
* allow SSL/https image URLs in listing description / template shortcodes 
* improved related listings widget: try to remove X-Frame-Options HTTP header on PHP5.3+ (instead of setting it to GOFORIT) 
* increased range for img_X and img_url_X short code to 1-99 
* improved memory footprint when using "Import WPLA Product IDs" tool (call wp_cache_flush() to clear cache after get_post_meta()) 
* updated labeling and tooltips for deprecated / not recommended options "Auto update ended items" and "Enable API auto relist" 
* fixed missing success message when preparing items in bulk from Products page 
* fixed issue updating product details on PHP5.6 with Suhosin patch installed (and suhosin.post.disallow_nul option on) 
* fixed On eBay / Not on eBay product filter for split variation listings 
* fixed deprecated constructor PHP warnings on PHP7 
* fixed URL for eBay HK (ebay.com.hk instead of ebay.hk) 

= 2.0.9.16 =
* added option to skip orders containing only foreign items from being created in WooCommerce 
* added dev option to limit batch size for inventory check tool 
* added ajax action hook wpl_ebay_item_query - to get the ItemID for a given listing_id from listing template via AJAX 
* added wple_run_scheduled_tasks ajax action hook to trigger only the eBay cron job (equal to wplister_run_scheduled_tasks) 
* limit number of orders to 25 and disable pagination when fetching orders from cron job 
* store SKU as order line item meta when creating orders in WooCommerce 
* make sure gallery widget items use same account as reference listing 
* show _custom_tracking_provider value on edit order page (fixes empty provider when completing sale via wple_complete_sale_on_ebay action hook) 
* improved error handling for active EPS upload mode - and fixed issue on servers where image URL was not publicly accessible 
* explain errors 21919152, 21919153, 21919154 (Shipping policy is required, etc.) and updated tooltips as well 
* when fetching orders from eBay, make sure each account (eBay user name) is only processed once 
* log to db when cron job is triggered 
* format multiple attribute values - replace pipe symbol (|) with line break 
* improved inventory check memory requirements - disable autoload for temp data (requires WP4.2+) 
* replace all occurrences of split() with explode() for PHP 7 
* trigger stock status notifications when reducing stock level 
* fixed sale price being applied even if sale start date was in the future 
* fixed cron job warning showing up on designated staging site 
* fixed ebay_item_id shortcode 

= 2.0.9.15 =
* fixed possible issue with empty item specifics on some servers 
* fixed auto replenish option ignoring fixed quantity set in profile 
* fixed issue caused by invalid item specifics data returned by eBay (ignore recommended item specifics nodes with empty Name property) 
* added support for unknown tracking providers / shipping carriers when completing sale via wple_complete_sale_on_ebay action hook 
* log to db when wple_complete_sale_on_ebay action hook is triggered 
* check for missing database tables and show warning on settings pages 
* improved performance when updating products via CSV import 
* relabeled "Inventory Sync" option to "Synchronize sales" 

= 2.0.9.14 =
* fixed possible layout issue caused by 3rd party CSS 
* fixed VAT tax rate not sent when B2B option is enabled 

= 2.0.9.13 =
* fixed possible PHP warning during checkout (if no items to revise on eBay and PHP warnings are shown to the browser) 
* fixed warning: For multiple-variation listings, GTIN values are specified at the variation level. (21919420) 
* fixed update interval message on bottom of eBay messages page 
* hide eBay meta boxes on edit product page if current user is not allowed to manage eBay listings 
* improved category settings page (improved labelling and added second button to save settings on top of the page) 
* added B2B only profile option 
* added UK Mail shipping provider 
* added norwegian language files 

= 2.0.9.12 =
* added the value NONE to the Exclude Locations profile option - and improved tooltip and layout 
* added Deutsche Post shipping provider 
* show warning on edit product page if stock management is enabled for parent but disabled for variations 
* removed deprecated sandbox option from developer settings 
* include variation MPNs in VariationSpecificsSet container - prevent Error: Variation Specifics Mismatch. (21916664) and Error: Missing name in name-value list. (21916587) 
* fixed possible Error 10019: Inconsistent shipping parameters 
* fixed issue with recommended item specific values containing UTF-8 BOM (broken characters) causing item specifics to break on some servers 
* fix possible invalid eBay token error after reconnecting eBay account 
* enabled full item specifics support in WP-Lister Lite 

= 2.0.9.11 =
* fixed possible fatal error on revise and preview 
* fixed issues on servers with localized PHP settings (decimal comma in StartPrice if profile price is calculated) 
* improved error 21916543 - suggest to set EPS transfer mode to active if uploading images to EPS fails 

= 2.0.9.10 =
* check if item specifics have more values than allowed and remove additional values automatically 
* fixed issue with "0" sizes as variation attributes (duplicate item specific would be set to Does not apply) 
* improved progress window: auto scroll, fixed cancel button and improved error display 
* added JSONP support for dynamic gallery and dynamic categories AJAX requests 
* added action hook wplister_end_item 
* added DHL Global Mail shipping provider 

= 2.0.9.9.1 =
* fixed Error: Requires Unique Variation Specifics and Item Specifics (21916626) 
* fixed issue where only one product ID (UPC or EAN or MPN) would be used even when multiple IDs are set 
* fixed product identifiers (UPC, EAN, MPN, etc.) for split variations 
* fixed error 37 on servers with localized PHP settings 
* fixed item specifics for eBay Motors categories 

= 2.0.9.9 =
* fill in missing required item specifics with "Does not apply" automatically 
* profile editor: set Brand / MPN item specifics automatically to pull value from eBay options meta box 
* validate UPCs and EANSs and show warning on edit product page 
* if UPC or EAN are empty when saving a product, use UPC / EAN from WPLA if present 
* added button to import Product IDs (UPC/EAN) from WPLA (tools page) 
* indicate promotional sale in listings table - show original price and tooltip 
* omit price and shipping when revising an item with an active promotional sale 
* fixed empty item specifics on products imported from eBay 
* fixed possible issue fetching available item specifics for eBay Motors categories (if eBay UK/AU are used in additional to US) 
* fixed issue where variable items could be incorrectly marked as sold when auto replenish option is enabled 
* database upgrade to version 48 - store item specifics and conditions in bay_categories table 
* change details columns to medium text for listings and orders (prevent large orders from not being stored in the database) 
* code cleanup - moved static methods from ListingsModel to new WPLE_ListingQueryHelper class 
* added Smart Send shipping provider 
* added filter hook wple_process_single_variation_title 
* added filter wple_gallery_iframe_attributes to customize html attributes on gallery iframe tag 

= 2.0.9.8.1 =
* added option to filter orders by eBay account on WooCommerce Orders page 
* added Star Track shipping carrier 
* automatically reapply profile when resetting ended items 
* changed default status for new orders to Processing on new sites 
* show warning on Auto Complete option if default status is set to Completed 
* show warning if max_post_vars is too low on category settings page 
* listing page: reduce database queries for variations 
* added filters hooks wple_local_shipping_services / wple_international_shipping_services 
* added filter hooks for add-ons (wple_filter_listing_item, wple_after_basic_ebay_options, ...) 
* added action hooks wple_before_advanced_settings, wple_after_advanced_settings and wple_save_settings 
* fixed issue where imported GTC items would have their status changed to ended if a non-GTC listing profile was assigned during import 
* fixed error for paypal when testing connection to eBay 

= 2.0.9.8 =
* added support for Brand/MPN and ISBN on product and variation level 
* added support for custom order statuses on settings page 
* added support for WooCommerce CSV importer 3.x 
* added support for WooCommerce MSRP Pricing extension 
* added profile option to use MSRP as STP (DiscountPriceInfo.OriginalRetailPrice) 
* improved attribute selector in item specifics - separate product attributes and custom attributes (SKU, MPN, Brand) 
* improved edit product page: moved all product identifiers (UPC, EAN, MPN, etc.) in new meta box 
* improved messages page - added account filter and fixed view links with search query 
* relabeled "Prefilled info" profile option to "Use Catalog Details" and improved tooltip 
* fixed saving variations via AJAX on WooCommerce 2.4 
* fixed possible display issue on category settings page 

= 2.0.9.7.1 =
* added support for WooCommerce Additional Variation Images Addon 
* added button to manually convert custom tables to utf8mb4 on WordPress 4.2+ (fix "Illegal mix of collations" sql error) 
* category settings page: indicate if product category was imported from eBay 
* improved error handling if update server is unreachable 

= 2.0.9.7 =
* use eBay UserID as default title for new accounts 
* added option to remove listings from archive N days after they ended 
* relabeled seller profiles to business policies (applies to shipping, payment and return policy) 
* fixed issue where using Automotive category on eBay Canada would attempt to list on eBayMotors US (make sure to only enable eBayMotors if US site is selected) 
* fixed php warning: Invalid argument supplied for foreach() in ProfilesPage.php on line 144 (and meta box) 

= 2.0.9.6 =
* added support for WooCommerce Brands extension 
* fixed issue where adding a new eBay account would overwrite the token for the current default account 
* fixed "Use SKU as UPC" profile option for variable products 
* fixed empty product description for split variations 
* fixed empty Automotive category on eBay Canada 

= 2.0.9.5 =
* added product identifiers (UPC/EAN) on variation level 
* added advanced setting option to handle "Missing Product Identifiers" (auto fill in "Does not apply" if missing) 
* fetch site specific "Does not apply" text when refreshing eBay details 
* hide parent level EAN/UPC fields for variable products 
* fixed issue where product_price template shortcode would show sale price instead of custom eBay price 
* allow fixed values for custom attributes defined by wplister_custom_attributes filter hook 
* updated eBay API SDK to version 927 

= 2.0.9.4 =
* fixed redundant "Duplicate request, seller has already marked paid" error message on completed orders 
* fixed issue where reseted ended items were skipped when publishing prepared items in bulk (remove eBay ID, and expiry date when resetting an item) 
* fixed warnings not being stored for items that were successfully published 
* fixed enforced single attribute value mode 

= 2.0.9.3 =
* added option to control whether product attributes should be converted to item specifics 
* fixed empty weight issue for variable products (Error: Package weight is not valid or is missing) 
* wple_complete_sale_on_ebay action hook: use default feedback text unless FeedbackText parameter is set 

= 2.0.9.2 =
* variable listings: regard product attribute to item specifics mapping table defined in listing profile 
* indicate orders where stock has been reduced by WP-Lister automatically on eBay Orders page 
* improved order details view - show shipping fee and order total 
* show subtitle in listing preview 
* fixed "View in WP-Lister" toolbar link on frontend 
* fixed storing payment date in created WooCommerce orders 
* fixed rare issue where "skipped listing ... status is neither..." warning would break URL redirect 
* fixed rare issue where gallery widget would show duplicate featured items (caused by duplicate _featured keys in wp_postmeta) 
* fixed issue where shipping cost would show up as tax - if VAT was enabled in profile but no global VAT rate set 
* fixed eBay specific details not working for grouped child products (but pulled from parent instead) 
* fixed line breaks when pulling custom WYSIWYG field using meta_ shortcode by running content through nl2br() (Advanced Custom Fields plugin) 
* when an order is updated (paid / shipped) on eBay, do not update WooCommerce order status if a custom order status is set (WooCommerce Order Status Manager extension) 

= 2.0.9.1 =
* fixed issue where items were ended even though OOSC was enabled 
* indicate when OOSC is enabled in listing table view (icon and tooltip) 
* store payment date in created WooCommerce orders (used by REST API and 3rd party plugins) 
* improved profile gallery option labeling - and made options translatable 
* improved error handling when uploading and downloading listing templates 
* fixed rare blank profile selector issue (404 error on admin-ajax.php) on some themes 
* allow listing templates to fetch eBay store categories dynamically (wpl_ebay_store_categories) 
* show warning when running into max_input_vars limit - causing partial data being saved when updating a product with 40+ variations 

= 2.0.9 =
* automatically detect whether the Out Of Stock Control option is enabled when updating an eBay account
* ignore Out Of Stock Control preference when processing non-GTC listings

= 2.0.8.12 =
* added option to set eBay Store Categories directly on edit product page
* added option to exclude specific variation attribute values (like colors) from being listed on eBay
* improved recommended item specifics - fetch up to 15 names and 250 values per name from eBay
* use HTTP POST to contact update server (fix connection error on pantheon.io)

= 2.0.8.11 =
* added cancel button to progress window
* increased HTTP timeout for uploading images to EPS to 300s
* trigger WooCommerce webhook order.created when creating orders
* fixed wrong quantity being sent to eBay when revising locked items with sales (since 2.0.8.10)
* fixed issue with woocommerce-advanced-bulk-edit
* fixed issue with product titles longer than 255 characters (eBay limit is 80 characters)

= 2.0.8.10 =
* fixed negative or incorrect quantity in listings table after updating product - should fix a rare sync issue as well
* fixed previous ItemID not being added to history when item was relisted
* fixed negative quantity in listings table after revising inventory status
* fixed end date column for relisted items - use date_finished only for ended items

= 2.0.8.9 =
* added option to enter EAN on edit product page
* fixed processing product updates triggered via the WooCommerce REST API
* fixed profile price modifier being ignored when revising locked variable listings
* fixed cost of goods integration
* fixed previous ItemID not being added to history when item was relisted
* ignore ReservedPrice on fixed price items - prevent Error 82
* use eBay API version 919

= 2.0.8.8 =
* implemented support for WooCommerce variation attribute sort order
* split variations use actual variation weight and dimensions (instead of using parent weight and dimensions)
* improved eBay category section on edit product page - show eBay and store categories defined by listing profile
* sanitize prices entered on edit product page - convert decimal comma to decimal point
* only show products in stock when filtering for products Not on eBay
* added bulk action to clear EPS cache for selected items (to force re-upload on next revise request)
* show errors for failed CompleteSale requests on top of order details page
* improved order meta box - show status for "marked as shipped" and "feedback left"
* added action hook wple_complete_sale_on_ebay - allow other plugins to call CompleteSale request with tracking information
* get notified when MCF order was shipped via FBA - submit tracking information to eBay

= 2.0.8.7 =
* added listing duration options (14 and 28 days)
* show gallery status warnings on published items only
* database upgrade to version 44 - change seller profile columns to MEDIUMTEXT
* improved EPS upload - and added developer option to enable active transfer mode
* improved order filter views (on eBay/not on eBay) - fixed issue where 3000+ eBay orders would cause an empty result
* fixed issue combining listing filter views
* fixed tax amount in created orders not being based on tax rate from listing profile
* fixed empty VAT / GST column in WooCommerce orders
* fixed "More than one Item Specifics value provided" warning on split variations - use the right attribute value instead of all values

= 2.0.8.6 =
* improved displaying errors and warnings on listings page
* renamed Cross Border Trade to International Site Visibility
* fixed pagination on WordPress 4.2

= 2.0.8.5 =
* fixed templates page on WordPress 4.2
* fixed log page on WordPress 4.2

= 2.0.8.4 =
* added search box on profiles page
* added method ListingsModel::updateWhere()
* run inventory checks in batches to prevent server timeout and reduce memory requirements
* fixed XSS issue with add_query_arg() and remove_query_arg()
* fixed listing profile column on WordPress 4.2

= 2.0.8.3 =
* show ImageProcessingError message on listings page
* make sure feedback is only left once - and process Error 55 (Feedback already left)
* added incompatible plugin warning for Yet Another Stars Rating (causes blank page when applying profile)
* improved variation options layout
* fixed rare "Field 'user_details' doesn't have a default value" error on some MySQL servers (when adding new eBay account)
* fixed issue where sold items without stock would show up in eBay / Listings / Relist
* fixed php error when fetching order in free version
* fixed strike-through price (STP) for variable listings
* fixed custom gallery.php and thumbnails.php files for sites where wp-content folder is not at the default location
* fixed possible "Fatal error: Class 'WC_Product_Ebay' not found" when orders for foreign eBay items are completed via 3rd party web hooks (Shipstation plugin)
* fixed possible fatal error when switching a variable product to simple (which isn't possible after all if it has already been listed)

= 2.0.8.2 =
* added button to "Relist all restocked items" on Listing page / Relist
* added button to "Publish all prepared items" on listings page
* added new bulk action on listing page: "Reset ended items" will set the listing status to "prepared" so ended items can be listed a new items (using the same or different account)
* show when order was shipped on eBay / Orders page
* use custom order meta _ebay_marked_as_shipped to determine if order was successfully marked as shipped on eBay
* improved messages page and details view
* use http post to load task list in JobRunner.js (prevent Request URI Too Large error)
* added optional post_id url parameter to preview_template action (3rd party dev)
* added maxlength attribute for feedback text fields - prevent Comment Too Long error on CompleteSale
* improved error handling (order notice text) on CompleteSale requests
* fixed issue using seller shipping profiles for non-default accounts

= 2.0.8.1 =
* edit product page: if product exists in WP-Lister, show shipping and seller profiles based on the linked account instead of default account
* edit product page: show available item specifics based on profile category and eBay category map
* improved seller shipping profiles - sort by summary, fixed layout on edit product page
* improved process of completing orders on eBay  (error handling / order notes)
* performance improvements when applying profile to listings - check if there are shortcodes before processing
* optimized memory footprint when revising all changed items
* improved messages page - fixed search box and filter views
* fetch messages for active accounts automatically - if messages page is enabled
* fixed possible php warning with Product Add-Ons plugin installed
* added wple_get_listings_where() function for 3rd party devs
* added optional listing_id url parameter to preview_template action (3rd party dev)
* always init WooBackendIntegration class, even when is_admin() is false - to listen to order status change even triggered by external web hooks (like Shipstation)

= 2.0.8 =
* improved performance on Products page
* database upgrade to version 43

= 2.0.7.11 =
* improved display of eBay status in WooCommerce - split variations / multiple items per product
* show WooCommerce order status on Orders page and indicate when an order has been trashed or deleted (show Create Order link then)
* fixed seller profiles being shown for default account instead of selected account when editing listing profile
* fixed check for duplicates when multiple accounts are used
* fixed reducing eBay stock during checkout for multiple accounts
* fixed bug in woocommerce-woowaitlist (codecanyon version)
* fixed selecting shipping destinations on edit product page on WooCommerce 2.3 (chosen.js)
* fixed updating seller profiles when refreshing eBay account

= 2.0.7.10 =
* update legacy account data when adding new eBay account or when updating default account
* fixed rare issue where incorrect quantity was sent to eBay (when revising items with sales)
* prevent php warning on profile editor when no shipping packages are available

= 2.0.7.9 =
* retry without variation pictures when ReviseItem fails with error 21916734 (Variation pictures cannot be removed during restricted revise)
* tweaked default template to prevent issues in firefox
* various smaller improvements

= 2.0.7.8 =
* fixed variation options in WooCommerce 2.3

= 2.0.7.7 =
* added filter hook wplister_get_ebay_category_type
* added option to set WooCommerce order status for shipped eBay orders
* only show "marked as shipped on eBay" checkbox for orders that were placed on eBay
* send tracking details to eBay when an order is completed from the order details page
* fixed additional product content shortcode for split variations
* skip token expiry warning for inactive accounts

= 2.0.7.6 =
* allow to select non-leaf store categories
* keep custom eBay title and price when duplicating products
* fixed issue where ended items would incorrectly be marked as sold
* fixed eBay categories in listing preview for non-default accounts

= 2.0.7.5 =
* added option to hide single variations from eBay
* added support for sales tax when creating orders in WooCommerce
* added advanced option to disable sale prices
* fixed Ignore order before developer option on developer settings page

= 2.0.7.4 =
* fixed issue where items purchased in WooCommerce were not revised on eBay
* fixed product level shipping Package Type
* fixed license deactivation message

= 2.0.7.2 =
* fixed custom eBay price for locked variations
* fixed php warning when activating license (caused by new WCAM 1.3.8)
* fixed issue when PHP error reporting was set to forced production mode
* fixed possible PHP warning in WC_Product_Ebay() class
* store order note for WooCommerce orders when there are no eBay listings found
* removed duplicate batch buttons from listing table header (for smaller screens)

= 2.0.7.1 =
* show warning if a profile uses nonexisting eBay or store categories
* updated token expiry check for multi account support
* updated invalid token warning for multi account support
* fixed "Connect to eBay" button to refresh token

= 2.0.7 =
* added character count for eBay title and subtitle on edit product page
* make WooCommerce orders searchable by eBay OrderID and BuyerUserID
* prevent creation of duplicate listings (same post_id and account) and improved warning messages
* fixed archiving deleted listings when using "Update details from eBay"
* fixed inventory sync issue - WooCommerce sales did not reduce the inventory of split variation listings
* when saving a profile assigned to 1000+ items, apply it in batches to reduce server load
* when preparing new products show button to view prepared listings
* improved "Install Update" button in update notification

= 2.0.6 =
* show warning when trying to activate the plugin when another version is already active
* fixed missing variation attributes for split variation listing titles when updating profile
* fixed blank page issue when opening template or profile editor with 10k item using this template
* fixed empty countries and other issues in profile after upgrading from 1.x

= 2.0.5 =
* always show account column on listings and log page - and indicate invalid account IDs
* added 5min and 10min update interval options
* check for listings, orders and profiles using deleted accounts on accounts page
* if invalid data is found show warning and offer to assign all found items to the default account
* fixed "Error 90002: No Password and no token" and show warning if an invalid account is used in any request
* fixed converting https image URLs to http in some cases
* fixed inventory check - mark as change didn't work if only prices were different

= 2.0.4.1 =
* added missing item condition on edit product page
* added "View item in WP-Lister" toolbar link on single product page
* improved tooltip on auto-relist profile option
* improved button visibility and labeling on license page
* do not mark products with stock management disabled as out of stock when sold on eBay

= 2.0.4 =
* indicate shipped eBay orders in orders page
* automatically clean expired log records
* fixed missing paypal address issue
* fixed and improved update notification
* fixed link to account settings in notifications with "Accounts in main menu" option enabled

= 2.0.3 =
* added inventory check tool to only compare stock levels but ignore prices
* show inline errors on listings page for changed and verified items
* automatically set enabled sites for active accounts if no enabled sites found
* disable transaction conversion if updating from very old versions (1.3.5)
* fixed search box and status filter on orders page
* fixed order processing: do not change listing status to sold when quantity reaches zero and out of stock control is enabled for account
* fixed possible "Duplicate VariationSpecifics trait value" error (21916582)
* fixed possible "Duplicate custom variation label" error (21916585) - except for restricted revise
* fixed "Unknown category ID" for eBay Motors categories on category settings page
* fixed broken store category mappings after database migration from 1.5
* fixed editing accounts with "Accounts in main menu" option enabled

= 2.0.2 =
* added advanced option to make account settings page available in eBay main menu
* added filter hook wple_max_number_of_accounts
* fixed error when revising item or variation with negative quantity
* fixed log table filter for PartialFailure
* implemented new admin messages manager - wple_show_message()

= 2.0.1 =
* added account setting option to enable Out Of Stock Control for account
* skip variations stock check and do not end listing on zero quantity when Out Of Stock Control is enabled for account
* added button to refresh eBay details on category settings page again

= 2.0 =
* support for multiple eBay sites and accounts
* new updater - requires new license key

= 1.6.1 =
* improved stability of db upgrade process

= 1.6.0.11 =
* fixed attribute shortcodes for split variations
* fixed an issue with broken variation attributes caused by WP All Import
* prompt user to refresh site specific eBay data if required

= 1.6.0.10 =
* added account settings page and updated setup wizard
* fixed item condition in listing preview
* fixed WooCommerce deprecated notice
* fixed possible php warnings on initial plugin activation
* implemented alternative JSON output for gallery widget data (backend only)

= 1.6.0.9 =
* refactored JobRunner CSS - do not use jQuery UI custom theme for progress bar
* fixed eBay errors showing up as undefined in progress window
* fixed issue preparing listings in WPLA

= 1.6.0.8 =
* implemented new profile selector (modal window instead of redirect)
* improved process of preparing new listings - replaced List on eBay link with search icon in eBay column
* fixed eBay Motors and removed setting option (enabled by default for US site)

= 1.6.0.7 =
* store and display order currency on eBay Orders page
* implemented new profile selector (modal window instead of redirect)
* improved explanation of error 21916543 (ExternalPictureURL server not available) which might be caused by forced SSL
* explain eBay errors 21916635 and 21916564
* removed recent listings widget from default listing template

= 1.6.0.6 =
* remember last API errors and warnings - and display on listings and edit product page
* store PayPal transaction ID in created WooCommerce orders
* added maxlength attribute for Returns Description
* fixed SKU on split variations - use variation SKU instead of parent SKU

= 1.6.0.5 =
* improved tax rate selection in advanced settings
* added listing template shortcodes for SKU and eBay ID
* fixed possible php warning when saving profile
* fixed listing variable listing with sizes like 0, 00, 000

= 1.6.0.4 =
* fixed quotes in condition description (edit profile page)
* fixed issue with shipping cost in WooCommerce orders (was gross instead of net)
* added option to hide custom ebay price field for variations on edit product page
* added developer option to disable submitting parts compatibility lists to ebay

= 1.6.0.3 =
* added APC shipping service on order details page
* added developer option to enable edit listing link
* show account type in sidebar details box
* improved inventory check when using profile price modifier
* fixed missing db log records for GetUser and GetUserPreferences
* fixed possible php warnings on listings page
* fixed php warning when saving listing profile

= 1.6.0.2 =
* added SKU column in inventory check results
* added filter hooks wplister_set_shipping_date_for_order and wplister_set_shipping_provider_for_order
* fixed possible issue when variation attributes on eBay and WooCommerce do not match exactly (Size vs size)
* display variation attributes in order details view
* removed legacy code and cleaned up decreaseStockBy() method
* increased required WooCommerce version to 2.2.4

= 1.6.0.1 =
* added support for WooCommerce Store Exporter plugin (custom columns for eBay ID and status)
* fixed wplister_revise_inventory_status action hook for single variation product IDs (inventory sync issue from Amazon to eBay)

= 1.6.0.0 =
* improved shipping and tax information in created WooCommerce orders (WooCommerce 2.2)
* added advanced option to enter default tax rate (percentage) to be used on created orders
* implemented product data caching layer for improved performance on listings page
* fixed issue with item specifics for split variations
* fixed issue with product content when using the "Revise listing on update" checkbox on edit product page
* indicate in order history details when reducing stock for a variation failed because no variation_id was found
* updated database structure for version 2.0
* updated russian translation

= 1.5.2 =
* fixed issue with product content when using the "Revise listing on update" or "Relist item" checkbox on edit product page

= 1.5.1 =
* added wplister_get_ebay_id_from_post_id() function for 3rd party developers

= 1.5.0.10 =
* added support for listing eBay catalog products by EPID (eBay Product ID)
* added UI for searching products using the Shopping API (FindProducts)
* mark listing as ended if revise action fails with error #1047 (Auction closed)
* fixed another possible error 21916608 (Variation cannot be deleted during restricted revise)
* fixed issue with out of stock variations not showing up

= 1.5.0.10 =
* added support for custom eBay price for single variations
* fixed missing variation attributes in created orders on WooCommerce 2.2
* fixed issue on WooCommerce 2.2 when product description contains WooCommerce shortcodes
* fixed "not on eBay" and "not on Amazon" filters when used in combination on orders page
* fixed issue with Print Invoice plugin when printing multiple invoices in bulk
* added filter options to log table

= 1.5.0.9 =
* fixed bulk actions on listings page
* fixed missing new order admin emails for orders placed on eBay
* fixed error 21916608 - delete variations on eBay only when there have been no sales yet
* added russian translation

= 1.5.0.8 =
* fixed long multi value attributes / item specifics 
* fixed possible php warning when preparing listings without price 
* never update shipping address for orders with multi leg shipping enabled 

= 1.5.0.7 =
* archive ended and sold listings automatically after 90 days 
* archive listings which do not exist on eBay when Error 17 is returned for revise, end, or relist 
* fixed javascript error for item specific values containing single quotes 
* fixed variations with null values as variation attributes (sizes likes "0" and "000") 
* hide View on eBay link when ebay_id and ViewItemURL are empty 

= 1.5.0.6 =
* fixed invalid StartPrice error when revising inventory of locked items 
* added GLS to the list of shipping providers 
* added filter wplister_available_shipping_providers to clean up the list of shipping services on the order details page 
* don't show negative quantity in listings table (when a sold or ended product was updated in WooCommerce) 
* renamed "Prepare Listings" to "List on eBay" to avoid confusion with WP-Lister for Amazon 

= 1.5.0.5 =
* fixed duplicate orders issue in WooCommerce 2.2 
* fixed php warning when search query returned no results 
* fixed font in advanced settings page on french sites 
* skip price when revising inventory during checkout 
* added filter wplister_set_tracking_number_for_order to allow 3rd party code to fill in the tracking number automatically 
* added wplister_prepare_listing action hook 

= 1.5.0.4 =
* added button to cancel profile selection 
* skip price when revising inventory during checkout 
* fixed pagination on listings page when using search box 

= 1.5.0.3 =
* added option to use eBay shipping center address in created WooCommerce orders (Global Shipping Program) 
* added explanation for Error 10007 (Internal error to the application) 
* identify purchased variations by SKU if no matching variation attributes are found 

= 1.5.0.2 =
* changed default menu label to eBay 
* fixed custom eBay price for locked items 
* fixed issue with spaces in image folder path when uploading to EPS 
* fixed "On eBay" product filter when combined with "On Amazon" filter 
* made price column sortable on listing page 
* improved message display in log records 
* improved variation display in listings table 
* added more details to eBay order history section (include original stock value before processing) 
* added filter wplister_filter_listing_item 

= 1.5.0.1 =
* added option to always send weight and dimensions (restoring default 1.4.x behavior) 
* merged order type column for eBay and Amazon 

= 1.5.0 =
* added note that refund option is not applicable on AU and EU sites 
* always send weight and dimensions in ShippingPackageDetails node 
* fixed missing setup check messages 

= 1.4.9.4 =
* added option to filter listings by profile 
* added proper setup instructions for external cron jobs 
* show when cron schedule was last run in settings and orders page 
* fixed ignoring archived listings when queried by post_id 
* fixed rare price rounding issue 
* fixed update notification message 
* fixed PHP notice on WordPress 4.0 beta 

= 1.4.9.3 =
* fixed staging site option 
* fixed multi value attributes when attribute is mapped to item specifics 
* fixed eBay authentication on servers with non-standard arg_separator.output setting 

= 1.4.9.2 =
* remove ebay specific meta data from duplicated products 
* handle ebay error code 17 and automatically move deleted listings to the archive 

= 1.4.9.1 =
* fixed handling orders as unpaid when PaidTime is empty 
* fixed listing search when no status filter is selected 

= 1.4.9 =
* added best offer options on edit product page 
* improved payment methods in created WooCommerce orders for bacs and cod 
* improved order processing - fixed handling orders as unpaid when PaidTime is empty 
* improved behavior of listing filters and search box - remember filters when editing profile and template 
* improved german localization 
* fixed missing page reload after step 2 of the setup process 
* fixed warning when external cron was enabled 

= 1.4.8 =
* handle orders as unpaid when PaidTime is empty 
* show whether an order was paid and when in orders table 
* fixed attribute values when custom product attributes are used for variations 

= 1.4.7 =
* added option to use external cron jobs instead of wp_cron 
* added option to define staging site domain where updates should be disabled 
* fixed quantity in listings table not being updated when item was sold on Amazon 
* force AutoPay to be disabled when Freight shipping is used 

= 1.4.6 =
* fixed missing setup assistant messages 
* added option to remove part compatibility table from product 

= 1.4.5 =
* added support for adding new part compatibility tables 
* added filter to show only locked items on listings page 
* added update eBay data option to category settings page 
* improved behavior of listings and orders table - remember filter and search when using action links 
* fixed issue with listing title not being updated for items with sales 
* fixed width of profile selectors on edit product page 
* fixed word wrap in task window 

= 1.4.4 =
* added option to show product thumbnails on listings page 
* improved warning before deleting listings from archive 
* removed deprecated order update mode setting 
* fixed layout issues in edit product page 
* fixed various non-translatable strings 
* fixed php 5.4 strict messages 
* fixed issue with non-existing products with pdf invoice plugin 
* fixed site url for eBay Malaysia

= 1.4.3 =
* added option to limit number of items displayed by gallery widgets 
* added option to select tax rate to be used when creating orders with VAT enabled 
* added support for editing imported compatibility tables (beta)
* show warning when scheduled wp-cron jobs are not executed 
* improved "(not) on ebay" product filters 

= 1.4.2 =
* fixed issue with VAT being added to item price in orders when prices are entered with tax 
* fixed rare issue regarding line endings 
* updated italian translation (thanks Valerio) 

= 1.4.1 =
* added support for classified ads (beta) (Pro) 
* update ended items automatically when deactivating auto-relist profile option 
* show when auto-relist is enabled in applied profile on listings page 

= 1.4.0 =
* improved auto relist option - filter scheduled items and option to cancel schedule 
* fixed undefined method wpdb::check_connection error in pre 3.9 
* tested with WordPress 3.9.1 and WooCommerce 2.1.9 

= 1.3.9.2 =
* added option to perform unit conversion on dimensions 
* fixed db error message during first time install 
* fixed item specifics box when creating new profile 
* fixed issue with product description not being updated (introduced in 1.3.9.1) 
* auto sync quantity is default for new profiles 
* compatible with mysqli extension 

= 1.3.9.1 =
* fixed quick edit for locked products 
* enabled product_content and other shortcodes in title prefix and suffix 
* added debug data to order creation process 

= 1.3.9 =
* fully tested with WordPress 3.9 and WooCommerce 2.1.7 

= 1.3.8.5 =
* improved error handling for CompleteSale requests 
* check for duplicate listing when selecting a profile 
* hide eBay Motors from eBay site selection during setup 
* fixed possible fatal error on edit product page 
* updated css for WordPress 3.9 

= 1.3.8.4 =
* improved performance of listings page (if many variations are displayed) 
* added option to limit the maximum number of displayed items 
* fixed issues with auto relist feature 
* added spanish translation 

= 1.3.8.3 =
* added profile option to select product attribute for variation images 
* added option to filter orders which were placed on eBay in WooCommerce 
* improved error handling if no template assigned or found 
* fixed issue with variable products imported from Products CSV Suite 
* fixed issue with Captcha Bank plugin 

= 1.3.8.2 =
* implemented support for multi value attributes / item specifics 
* fixed issues when seller shipping profiles were changed on eBay 
* improved error display on log records page 

= 1.3.8.1 =
* added store pickup profile option 
* fixed removal of secondary eBay category 
* fixed product level eBay price for variable products 
* updated to eBay API version 841 

= 1.3.8 =
* various improvements and fixes - read the full changelog below

= 1.3.7.6 =
* fixed item condition on product level missing "use profile setting" option when a default category was set 

= 1.3.7.5 =
* automatically strip CDATA tags when template is saved 
* added option to lock and unlock all items on tools page 
* improved quantity display for changed items on listings page 
* fixed issue with wrong quantity after updating listing template 

= 1.3.7.4 =
* added option to update item details for ended listings automatically 
* fixed rare MySQL errors on Mac servers 
* fixed bug in new auto relist api option 

= 1.3.7.3 =
* added option to enable relisting ended items automatically when they updated to back in stock via the API 
* improved send to support feature 

= 1.3.7.2 =
* added option to disable notification emails when updating the order status for eBay orders manually 
* improved developer settings page 

= 1.3.7.1 =
* disable template syntax check by default 
* added translation for Dutch/Netherlands (nl_NL) 

= 1.3.7 =
* added optimize log button 
* improved detection of relisted items 
* fixed possible issue with wrong products in created orders 

= 1.3.6.9 =
* enable listing of private products 
* added option to show category settings page in main menu 
* make category settings available in the free version 

= 1.3.6.8 =
* added clean archive feature 
* check PHP syntax of listing template 
* make replace add to cart button option available in free version 

= 1.3.6.7 =
* improved relist option on edit product page 
* added option to select how long to keep log records 
* only display update notice if current user can update plugins 
* fixed issue when free shipping was enabled in profile but disabled on product level 

= 1.3.6.6 =
* added option to disable VAT processing when creating orders 

= 1.3.6.5 =
* fixed check for existing transactions when processing orders for auction items 
* improved manual inventory checks 
* improved order details view 

= 1.3.6.4 =
* fixed issue with ended listing not being marked as ended
* explain error 21919028 

= 1.3.6.2 =
* added support for payment and return profiles on product level
* fixed sticky shipping profile and locations after disabling product level shipping options 
* disable GetItFast if dispatch time does not allow it - fix possible issue revising imported items

= 1.3.6.1 =
* fixed soap error (invalid value for Item.AutoPay)
* updated translation 

= 1.3.6 =
* fixed disabling immediate payment option
* various improvements and fixes - read the full changelog below

= 1.3.5.8 =
* fixed issue with ShippingPackage not being set
* fixed rare issue selecting locations for new added shipping services
* added listing template shortcodes ebay_store_category_id, ebay_store_category_name, ebay_store_url, ebay_item_id

= 1.3.5.7 =
* allow relisting of sold items from product page
* fetch exclude shipping locations (database version 34)
* hide Selling Manager Pro profile options unless active
* added button to update all relisted items to warning message (if listing were manually relisted on ebay)
* fixed missing package type option for flat domestic and calculated international shipping mode
* fixed issue with ended items being marked as sold

= 1.3.5.6 =
* added new listing filter to show ended listings which can be relisted
* added support for all available exclude ship to locations - requires updating eBay details
* mark ended listing as sold if all units are sold when updating ended listings
* update transactions cache for past orders automatically (db upgrade)
* improved duplicate orders warning message with delete option
* improved check if WooCommerce is installed
* fixed issue with split variations creating duplicates
* only allow prepared items to be split

= 1.3.5.5 =
* show main image dimensions in listing preview
* added status filter and search box on transactions page
* added check for duplicate transactions and option to restore stock
* added transaction check on tools page to update transactions cache from orders
* prevent processing duplicate transactions in orders with different OrderIDs
* fix soap error when using CODCost with decimal comma
* improved display of warnings on edit product page
* clear EPS cache after upscaling images

= 1.3.5.4 =
* added images check and option to upscale images to 500px
* improved ebay log performance
* decode html entities in item specifics values
* fixed php 5.4 warning on edit product page
* define global constants WPL_EBAY_LISTING and WPL_EBAY_PREVIEW for 3rd party devs

= 1.3.5.3 =
* show warning when trying to prepare a single listing from a product draft
* fixed shipping date issue when completing orders automatically on eBay
* use BestEffort error handling option for CompleteSale

= 1.3.5.2 =
* added prepare listing action links on products page
* skip orders that are older than the oldest order in WP-Lister
* don't update order status if order is marked as completed, refunded, cancelled or failed
* compare rounded prices in inventory check

= 1.3.5.1 =
* added option to select order status for unpaid orders
* minor layout adjustments for WordPress 3.8 and WooCommerce 2.1
* show store category in listing preview
* fixed a rare XML decoding issue

= 1.3.5 =
* various improvements and fixes - read the full changelog below

= 1.3.4.11 =
* improved payment status on orders page
* fixed issue with IncludePrefilledItemInformation not being set
* compare variations price range (min / max) when running inventory check
* changed delete action parameters to avoid conflicts
* added wplister_relist_item action hook

= 1.3.4.10 =
* use site specific ShippingCostPaidBy options in profile
* strip invalid XML characters from listing description
* fixed variations cache for items with sales
* fixed empty IncludePrefilledItemInformation tag
* improved inventory check for variable products and custom quantities

= 1.3.4.9 =
* update ShippingPackageDetails with weight and dimensions of first variations
* fixed revising variable listings where both SKU and attributes were modified
* fixed issue with wpstagecoach
* WP 3.8.1 style adjustments and layout updates
* improved php error handling debug options
* show warning if WooCommerce is missing or outdated

= 1.3.4.8 =
* added inventory check on tools page - check price and stock for all published listings
* added option to mark listings as changed which were found by inventory check
* show warning on non-existing products on listings page and inventory check
* fixed calculating wrong VAT and store correct order total tax

= 1.3.4.7 =
* added option to mark locked listings as changed when updating a profile
* added message to deactivate the free version before installing WP-Lister Pro
* fixed order creation and VAT on WooCommerce 2.0 (was WC 2.1 only)
* fixed shipping cost in created WooCommerce orders

= 1.3.4.6 =
* check token expiration date and show warning if token expires in less than two weeka
* calculate VAT when creating WooCommerce orders for VAT enabled listings
* improved listings table - search by previous ebay id
* improved orders table - check if order has been deleted
* automatically switch old sites from transaction to order mode
* fixed possible issue of locked, reselected listings being stuck
* fixed incorrect cron job warning after fresh install

= 1.3.4.5 =
* added support for Woocommerce CSV importer
* added option to show link to ebay for all products - auctions and fixed price
* improved auto-complete order option - do not send seller feedback if default feedback text is empty
* mark ended listings as sold if no stock remains

= 1.3.4.4 =
* allow multiple shipping locations per international shipping service
* show warning if incompatible plugins detected (iThemes Slideshow)
* explain SOAP error 920002 caused by CDATA tags
* force UTF-8 for listing preview

= 1.3.4.3 =
* automatically reapply profile before relisting an ended item
* prevent running multiple cron jobs at the same time

= 1.3.4.2 =
* improved dynamic price parser to allow relative and absolute change at the same time (+10%+5)
* experimental support for WooCommerce Amazon Affiliates plugin (called via do-cron.php)
* log cURL errors messages like "Couldn't resolve host"

= 1.3.4.1 =
* added promotional shipping discount profile options
* added schedule minute profile option
* experimental support for WP All Import plugin
* don’t mark sold listings as ended when processing ended listings

= 1.3.4 =
* improved listing preview
* added ebay links for prepared, verified and ended items on edit product page
* added bulgarian translation
* tested with WooCommerce 2.1 beta

= 1.3.3.3 =
* improved updating locked variable items and messages on edit product page
* improved result of out of stock check on tools page
* added move to archive link for sold duplicates
* show product level start price in listings table
* fixed php warning on WooCommerce 2.1
* fixed issue when deleting wp content

= 1.3.3.2 =
* fixed issue when revising (ending) variation listings that are out of stock
* added option to skip orders from foreign ebay sites
* prevent editing of recommended item specifics names
* use total stock quantity for flattened variations

= 1.3.3.1 =
* added bold title profile option
* added gallery type profile option
* added profile option to disable including prefilled product information for catalog products listed by UPC
* added check for out of stock products in WooCommerce on tools page
* reschedule cron job if missing - and show warning once
* added tooltips on license page
* added log refresh button

= 1.3.3 =
* hide toolbar if user can’t manage listings
* added support for WooCommerce Sequential Order Numbers Pro 1.5.x
* fixed possible issue when upgrading from version 1.2.x with a huge number of imported products

= 1.3.2.16 =
* improved log record search feature
* added check for duplicate orders and warning message
* don't mark locked items as changed when listing template is updated
* don’t change listing status of archived items when updating product
* fixed ajax error when revising locked variations without changes
* fixed advanced setting update on multisite network

= 1.3.2.15 =
* fixed issue when relisting an ended auction as fixed price
* fixed shipping package option on edit product page
* fixed issue of split variations not being updated when the product is changed in WooCommerce
* prevent UUID issue when ending and relisting an item within a short time period

= 1.3.2.14 =
* fixed View on eBay button and error message for prepared auctions
* prevent deleting profiles which are still applied to listings
* improved error message if template file could not be found
* show optional ErrorParameters from ebay response
* added ajax shutdown handler to display fatal errors on shutdown

= 1.3.2.13 =
* implemented native auto relist feature (beta)

= 1.3.2.12 =
* set maxlength attribute for custom ebay title and subtitle input fields on edit product page
* fixed possible fatal error caused by weird UTF characters returns description
* fixed missing shipping weight for flattened variations
* regard default variation and remove variation attributes from item specifics for flattened variations

= 1.3.2.11 =
* fixed disabling best offer option on published listings
* added php error handling option to developers settings
* added update timespan option for manual order updates

= 1.3.2.10 =
* explain error 488 - Duplicate UUID used
* fixed product galleries for split variations
* fixed custom ebay titles for split variations
* added link to open variations table in thickbox

= 1.3.2.9 =
* improved listing eBay catalog items by UPC
* fixed listings not being marked as changed when products are update via bulk edit
* fixed issue when updating variable products through WooCommerce Product CSV Import Suite
* added test if max_execution_time is ignored by server on tools page
* measure task execution time and show message if a http error occurs after 30 seconds
* adjusted CSS for WP 3.8

= 1.3.2.8 =
* speed and stability improvements when updating locked variable products
* fixed missing css styles on edit profile page

= 1.3.2.7 =
* fixed javascript error on edit product page when no primary category was selected
* fixed thickbox window width on edit product page

= 1.3.2.6 =
* fixed javascript error on profile page

= 1.3.2.5 =
* fixed issue with item specifics for split variations
* improved splitting variations and indicate single variations in listings table
* improved item specifics on product level
* added wplister_custom_attributes filter to allow adding virtual attributes to pull item specifics values from custom meta fields

= 1.3.2.4 =
* added support for item specifics on product level
* update price as well as quantity when revising locked items

= 1.3.2.3 =
* added support for ShipToLocations and ExcludeShipToLocations
* fixed saving seller shipping profile on edit product page
* prevent user from deleting transactions / orders

= 1.3.2.2 =
* fixed issue with variations out of stock if "hide out of stock items" option was enabled in WooCommerce inventory settings
* added descrription to explain eBay error 21916543 (ExternalPictureURL server not available)

= 1.3.2.1 =
* added auto replenish profile option (beta)
* implemented max. quantity support for variations and locked items
* fixed issue when assign a different profile to published listings
* show warming and link to faq when no variations are found on a variable product
* show mysql errors during update process

= 1.3.2 =
* per product item condition
* support for CSV import plugins
* seller profiles (shipping, payment, return policy)
* various bug fixes and stability improvements - see details below

= 1.3.1.9 =
* fixed manual inventory status updates for locked variable products
* show messages and errors when revising a product from the edit product page
* try to find existing order by OrderID and TransactionID before creating new woo order
* added optional weight column to listings table

= 1.3.1.8 =
* added support for WooCommerce Product CSV Import Suite
* added support for Woo Product Importer plugin

= 1.3.1.7 =
* added support for seller profiles on product level
* fixed an issue with eBay Motors which was introduced in 1.3.1.4

= 1.3.1.6 =
* ignore fixed quantity profile option for locked items (to prevent accidentally disabling inventory sync for imported items)
* fixed currency display in transaction details
* hide custom quantity profile options by default to make it clear that these are not required and should be used with care
* support for WP-Lister for Amazon (sync inventory between eBay and Amazon)<br>

= 1.3.1.5 =
* fixed fatal php error when uploading images to EPS which was introduced in 1.3.1.4
* attempt to reconnect to database when mysql server has gone away

= 1.3.1.4 =
* changed default timespan for updating orders to one day
* fixed issue with product level options for split variations
* fixed possible issue with listing preview when external scripts were loaded
* increase mysql connection timeout to prevent "server has gone away" errors on some servers

= 1.3.1.3 =
* process bulk actions on selected listings using ajax to prevent timeout issues
* update user preferences when updating ebay data
* added option to update orders on tools page
* cleaned up advanced and developers settings

= 1.3.1.2 =
* added item condition option on edit product page
* implemented support for seller profiles (shipping, payment, return policy)

= 1.3.1.1 =
* fixed issue where locked items could get stuck when selecting a different profile
* minor bugfixes

= 1.3.1 =
* first stable release since 1.2.8

= 1.3.0.17 =
* fixed shipping fee in created orders
* only enable auto complete option if default order status is not completed
* include sales record number in order comments
* added instructions regarding shipping service priority error #21915307

= 1.3.0.16 =
* fixed issue of orders not being marked as shipped when no tracking details were provided
* added support for RefundOption
* added instructions regarding promotional sale / selling manager error 219422
* fixed issue when custom menu label contains spaces
* support for MP6

= 1.3.0.15 =
* added search box and status views on order page
* added support for shipping cost paid by option in profile (return policy)
* added instructions regarding item specifics on error #21916519
* fixed possible issue saving a profile when mysql is in strict mode

= 1.3.0.14 =
* added support for cash on delivery fee if available
* improved site changed message with button to update ebay data

= 1.3.0.13 =
* added update schedule status box on settings page - show warning if wp_cron has been disabled
* added action hooks for 3rd party developers: wplister_revise_item, wplister_revise_inventory_status and wplister_product_has_changed

= 1.3.0.12 =
* new feature: locked listings only have their inventory status synced while other changes are ignored
* fixed possible errors during transaction update
* fixed possible php warning when saving profile

= 1.3.0.11 =
* improved eBay inventory status update during checkout - requires variations to have a SKU
* added warnings if variations have no SKU
* added filter wplister_ebay_price to enable custom price rounding code
* prevent error if history data has been corrupted

= 1.3.0.10 =
* send package weight and dimensions when freight shipping is used
* fixed profile not being re-applied when revise listing on update option was checked
* added dedicated free shipping option to enable free shipping when using calculated shipping services
* added check license activation button
* added custom update notification and check

= 1.3.0.9 =
* added option to show item compatibility list as new tab on single product page
* fixed possible display of ebay error messages during checkout if ajax was disabled
* fixed issue when price is less than one currency unit
* improved license page and renamed to updates

= 1.3.0.8 =
* added errors section to log record view
* added option to auto complete ebay sales when order status is changed to completed
* added options to disable WooCommerce email notifications when new orders are created by WP-Lister

= 1.3.0.7 =
* new listing archive to clean out the listings view without having to delete historical data
* added metabox to WooCommerce orders created in the new "orders" mode
* fixed issue when previewing a listing template with embedded widgets
* fix inventory sync for variations in order update mode
* fixed email generation for ebay orders by implementing a custom WC_Product_Ebay class
* fixed order creation when cost-of-goods plugin is enabled and foreign listings imported in order update mode
* fixed "create orders when purchase has been completed" option
* fixed ebay column on products page when listing was deleted

= 1.3.0.6 =
* added maximum quantity profile option (Thanks Shawn!)
* fixed "Free shipping is only applicable to the first shipping service" warning
* send UUID to prevent duplicate AddItem or RelistItem calls

= 1.3.0.5 =
* added warning that update mode "Order" only works on WooCommerce 2.0
* use proper url for View on eBay link for imported products (edit product page)
* fixed possible recursion issue when products have no featured image assigned

= 1.3.0.4 =
* mark listing as ended if revise action fails with error #291 (Auction ended)
* fixed item status change from sold to changed when product was modified after being sold
* fixed tooltip on edit products page

= 1.3.0.3 =
* added option to enable inventory management on external products
* show imported item compatibility list on edit product page (no editing yet)
* cleaned up advanced settings page

= 1.3.0.2 =
* added option to create ebay customers as WordPress users when creating orders
* create orders by default only when ebay purchase has been completed
* added option to create order immediatly in advanced settings

= 1.3.0.1 =
* new permission management to control who has access to WP-Lister and is allowed to publish listings
* added option to customize WP-Lister main menu label
* create orders by default only when ebay purchase has been completed - can be changed in advanced settings

= 1.2.8.2 =
* fixed issue regarding variation images when upload to EPS is enabled
* added option to force using built-in XML formatter to display log records
* fixed php warning in XML_Beautifier

= 1.2.8.1 =
* fixed php warning on variations without proper attributes
* fixed php error on grouped products
* fixed order total in created WooCommerce orders in transaction mode
* fixed non-leaf category warning

= 1.2.8 =
* added clear log button and show current db log size
* updated german localization 

= 1.2.7.6 =
* fetch available returns within options from selected eBay site when updating eBay details
* show warning if local category is mapped to non-leaf ebay category on category mappings page 

= 1.2.7.5 =
* fixed wrong quantity issue when revising variable products that were imported from eBay 
* fixed check for changed shipping address on orders and transactions
* use wp_localize_script to allow translation of javascript code 

= 1.2.7.4 =
* update billing address when updating an order or transaction 
* show either transactions or orders page in menu and toolbar 
* fixed tooltip display issue on Firefox 

= 1.2.7.3 =
* added support for shipping discount profiles (beta) 
* improved link removal for multiple links per line
* fixed possible issue on processing GetItem results (on some MySQL servers)

= 1.2.7.2 =
* added option to set local timezone 
* convert order creation date from UTC to local time 
* update shipping address when updating an order or transaction 

= 1.2.7.1 =
* added option to disable the WP wysiwyg editor for editing listing templates 
* fixed issue when listing variations with individual variation images on the free version of WP-Lister 
* only show order history section in order details if an order in WooCommerce has been created 
* copy template config when duplicating listing templates 

= 1.2.7 =
* added template preview feature 
* added option to open listing preview in new tab 
* automatically convert iframe tags to allow embedding YouTube videos 

= 1.2.6.2 =
* show warning if item details are missing and display "Update from eBay" link 
* added note regarding ebay sites where STP / MAP are available 
* fixed issue saving profile condition when no category was selected 
* fixed possible issue when saving settings 
* removed line break from thumbnails.php 

= 1.2.6.1 =
* added tooltips to shipping options table headers 
* added warning symbols for fixed quantity and backorders if inventory sync is enabled 
* fixed issue when category mapping lead to the primary and secondary category being the same 
* reset invalid token status when check for token expiry time succeeds 
* minor visual alignment improvements 

= 1.2.6 =
* added tooltips to all settings and profile options 
* dynamically hide some profile options when not applicable
* increased range for [add_img_1] short code to 1-12 
* hide empty log records by default 

= 1.2.5.2 =
* fixed issue when uploading variation images to EPS 
* fixed default sort order on transactions and orders page 
* added warning if more than 5 shipping services are selected when editing a profile 
* added WC order notes to order details view 

= 1.2.5.1 =
* omit title and subtitle when revising an item that ends within 12 hours or has sales 
* new connection check tool to test outgoing connections 
* added check if PHP safe_mode is enabled 
* fixed shipping options on product level 
* fixed issue with variations for themes like Frenzy which hook into WC without proper data validation 

= 1.2.5 =
* minor cosmetic improvements

= 1.2.4.7 =
* added custom sort order for profiles
* display profiles ordered by profile name by default
* changed "link to ebay" behavior to "only if there are bids on eBay or the auction ends within 12 hours"
* show relist link on sold items
* show image URL when upload to EPS failed
* changed menu position to decimal to prevent colliding position ids

= 1.2.4.6 =
* compatibility update for Import from eBay 1.3.16
* added note and link to ebay for products which were imported from ebay

= 1.2.4.5 =
* added validation for shipping services on product page
* fixed missing validation for shipping services on profile page
* fixed issue when disabling shipping service type on product level

= 1.2.4.4 =
* added global option to allow backorders (off by default)
* show warning if backorders are enabled on a product
* fixed issue with attribute names containing an apostrophe

= 1.2.4.3 =
* fixed paypal addess not being stored when updating settings
* fixed order complete status (if new update was was enabled)
* added description on categories settings page

= 1.2.4.2 =
* fixed issue when preparing new listings
* prevent importing an order for an already imported transaction

= 1.2.4.1 =
* new update mode to process multiple line item orders instead of single transactions
* added advanced settings tab and cleaned out general settings
* skip pending products and drafts when preparing listings

= 1.2.4 =
* calculated shipping is no longer limited to WP-Lister Pro 

= 1.2.3.3 =
* added custom ebay gallery image to product level options 
* added freight shipping option 
* re-added warning if run on windows server
* fixed local "view on ebay" button for imported auction type listings without end date 
* fixed whitespace pasted from ms word and added wpautop filter to additional content 
* fixed undefined function is_ajax error on WPeC 

= 1.2.3.2 =
* added filter wplister_get_product_main_image 
* added beta support for qTranslate when preparing listings 
* show proper message when trying to use bulk actions without a transaction selected
* fixed possible mysql issue during categories update 

= 1.2.3.1 =
* added primary and secondary ebay category to product level options
* added ebay shipping services to product level options
* added listing duration to product level options
* strip slashes from custom item specifics values in profile
* improved handling of available item conditions
* set listing status to changed when product is updated on WP e-Commerce
* fallback for mb_strlen and mb_substr if PHP was compiled without multibyte support

= 1.2.3 =
* added transaction history 
* improved error handling when creating templates 
* improved handling of available item conditions 

= 1.2.2.16 =
* improved listing filter - added search by SKU and product ID 
* prevent listing title from growing beyond 80 chars by embedded attribute shortcodes 
* skip item specifics values with more than 50 characters 
* fixed custom product attributes 

= 1.2.2.15 =
* improved listing filter - search by profile, template, status, duration and more... 
* fixed order creation for variable products 
* fixed possible issue with multibyte values in details objects 

= 1.2.2.14 =
* create additional images thumbnails html from exchangeable thumbnails.php 
* fixed possible php warning 

= 1.2.2.13 =
* improved products filter - on ebay / not on ebay views 
* added option to hide products from "no on ebay" list 
* cleaned up ebay options on edit product screen 

= 1.2.2.12 =
* fixed wrong order total on multiple item purchases 
* added option to disable variations 
* improved database log view 

= 1.2.2.11 =
* fixed listing title of split variations when when profile is applied 
* add main image to list of additional images when WC2 Product Gallery is used 
* added some hooks and filters for virtual categories 

= 1.2.2.10 =
* replace add to cart button in products archive if product is on auction 
* process template shortcodes in condition description 
* make profile and template titles clickable 

= 1.2.2.9 =
* fixed issue regarding inventory sync for variations (wp to ebay) 
* fixed issue truncating listing titles on multibyte characters 
* support for windows servers (beta) 

= 1.2.2.8 =
* new option to customize product details page if an item is currently on auction 
* added import and export options for categories settings 

= 1.2.2.7 =
* fixed saving UPC field on edit products page 
* check if item specifics values are longer than 50 characters 
* added sold items filter on listings page 

= 1.2.2.6 =
* improved updating ended listings in order to relist 
* improved revise on local sale processing 

= 1.2.2.5 =
* added UPC field on edit product page 
* store previous item id when relisting an item 
* fixed a possible blank screen when connecting to ebay 

= 1.2.2.4 =
* added gallery image size and fallback options 
* fixed order total for multiple line item orders 

= 1.2.2.3 =
* fetch start price details from eBay and show warning if minimum price is not met 
* use full size featured image by default 

= 1.2.2.2 =
* improved listing status filter 
* added javascript click event for image thumbnails 
* url encode image filename for EPS upload 
* check if attribute values are longer than 50 characters 
* fix profile prices - remove $ sign and convert decimal point 
* fixed license deactivation after site migration 

= 1.2.2.1 =
* added new revise on update option on edit product screen 
* added filter wplister_product_images 
* fixed compatibility with Import from eBay plugin 1.3.8 and before 
* fix stock status update for products with stock level management disabled 
* force SQL to use GMT timezone when using NOW() 


= 1.2.1.6 = 
* fix for order total missing shipping fee 
* show warning if template contains CDATA tags 
* added hook wplister_after_create_order 
* updated localization 
* added pot file

= 1.2.1.5 = 
* remember and use images already uploaded to EPS 
* upload images to EPS one by one via ajax 
* fixed possible php warning 

= 1.2.1.4 = 
* added prepare listing action to toolbar 
* show warning if listing is missing a profile or template 
* add ebay transaction id to order notes 
* code cleanup 

= 1.2.1.3 = 
* added buy now price field on edit product page 

= 1.2.1.2 = 
* added restocking fee value option 
* fix for relative image urls
* improved account handling
* updated inline documentation

= 1.2.1.1 = 
* fixed some php warnings


= 1.2.0.20 =
* added WP-Lister toolbar links - along with a link to open a product on eBay in a new tab

= 1.2.0.19 =
* added option to show only product which have not been listed on eBay yet

= 1.2.0.18 =
* added options for listing type, start price and reserved price on product level

= 1.2.0.17 =
* fix to allow percent and plus sign in profile prices again
* changed column type for references to post_id to bigint(20)

= 1.2.0.16 =
* added Global Shipping option on product level (Pro only)
* added Payment Instructions on profile and product level

= 1.2.0.15 =
* added SKU column to listings page
* added verify and publish buttons on top of listings page
* remove currency symbols from profile prices automatically

= 1.2.0.14 =
* fixed possible "Invalid ShippingPackage" issue

= 1.2.0.12 =
* fixed possible issue where item conditions and item specifics were empty
* fixed weight conversion issue on WP e-Commerce

= 1.2.0.11 =
* fixed "You need to add at least one image to your product" if upload to EPS is disabled (Pro only)
* improved removing links from description
* filter options for log page

= 1.2.0.10 =
* fixed "Too Many Pictures" error

= 1.2.0.9 =
* improved sanity checks before listing and verifying
* fixed error message when no featured image is found

= 1.2.0.8 =
* new option to import transaction for items which were not listed by WP-Lister
* search listings by item id

= 1.2.0.7 =
* fetch item conditions via ajax when primary category is selected

= 1.2.0.6 =
* added support for item condition description

= 1.2.0.5 =
* transaction update reports shows listing titles again
* fixed cross selling widgets for servers which send the X-Frame-Options HTTP header

= 1.2.0.4 =
* beta support for variations with attributes without values (like "all Sizes" instead of a value)

= 1.2.0.3 =
* fix for new cross-selling widgets

= 1.2.0.2 =
* enabled listing product attributes as item specifics again

= 1.2.0.1 =
* new eBay metabox on edit product page to set listing title and subtitle on product level

= 1.2.0 =
* new default template with options and color pickers
* new cross-selling widgets to display your other active listings

= 1.1.7.5 =
* fixed missing package weight for split variations issue (Pro only)

= 1.1.7.4 =
* fixed item specifics and conditions for eBay Motors categories when using eBay US as main site

= 1.1.7.3 =
* fixed issue with empty titles when splitting variations (Pro only)

= 1.1.7.2 =
* support for WooCommerce 2.0 Product Galleries

= 1.1.7 =
* new option to schedule listings
* new template engine with hooks and custom template options (beta)

= 1.1.6.11 =
* load admin scripts and stylesheets using SSL if enabled
* WP-Lister won't update the order status if already completed orders anymore (Pro only)

= 1.1.6.9 =
* fixed an issue regarding inventory sync on WooCommerce 2.0 (Pro only)
* added order note when revising an item during checkout (Pro only)
* added one day listing duration

= 1.1.6.8 =
* fixed error regarding shipping service for flat shipping
* improved debug log submission

= 1.1.6.7 =
* fixed paging issue on transaction update
* more options for uploading images to EPS (Pro only)

= 1.1.6.5 =
* new option to duplicate listing templates
* catch invalid token error (931) and prompt user to re-authenticate
* added support for the WooCommerce Product Addons extension

= 1.1.6.3 =
* improvements on handling items manually relisted on ebay website

= 1.1.6 =
* updated eBay API to version 789
* fixed global shipping option
* minor improvements

= 1.1.5.6 =
* improvements for creating and updating orders in WooCommerce (Pro only)
* beta support for listing to eBay US and eBay Motors without switching sites
* set PicturePack to Supersize when uploading images to EPS (Pro only)
* added Global Shipping option
* fixed an issue with item specifics when no default category was selected

= 1.1.5 =
* beta support for Automated Relisting Rules (Seller Manager Pro account required) (Pro only)
* support for pulling best offer options from WooCommerce Name Your Price plugin
* fixed an issue with PackagingHandlingCosts when calculated shipping is used (Pro only)

= 1.1.4.1 =
* various improvements on calculated shipping services (Pro only)
* use SKU for item specifics
* new shortcodes for passing product excerpt through nl2br()
* faster inventory sync (Pro only)

= 1.1.3.4 =
* added italian localization (thanks Giuseppe)
* updated german localization
* various minor fixes and improvements

= 1.1.3 =
* compatible with WooCommerce 2.0 RC1
* fixed error when shipping options were not properly set up
* new default category for item conditions (Pro)

= 1.1.2 =
* new option to switch ebay accounts
* new network admin page to manage multisite networks
* improved multisite installation
* fixed issues creating orders on WooCommerce
* truncate listing title after 80 characters automatically

= 1.1.1 =
* support for JigoShop (beta) 
* support for custom post meta overrides
  (ebay_title, ebay_title_prefix, ebay_title_suffix, ebay_subtitle, ebay_image_url)
* more listing shortcodes for product category and custom product meta
* most listing shortcodes work in title prefix and suffix as well
* option to remove links from product description
* option to hide warning about duplicate listings
* fixed issues revising items
* fixed issue with PHP 5.4

= 1.1.0 =
* tested with WordPress 3.5
* various UI improvements
* new option for private listings
* code cleanup
* bug fixes

= 1.0.9.2 =
* support for multisite network activation (beta)
* support for product images from NextGen gallery
* improved support for Shopp
* new option to flatten variations
* other improvements and fixes

= 1.0.8.5 =
* new options to process variations
* update all prepared, verified or published items when saving a profile
* improved attribute handling in Shopp
* the usual bug fixes

= 1.0.7.4 =
* updated german localization
* support for variations using custom product attributes in WooCommerce
* proper error handling if uploads folder is not writeable

= 1.0.7 =
* various bug fixes
* support for new eBay to WooCommerce product importer
* developer options were not saved (free version only)
* support for tracking numbers, feedback and best offer added (Pro only)

= 1.0.6 =
* german localization
* improved inventory sync for WooCommerce (Pro only)

= 1.0.5 =
* various bug fixes

= 1.0.2 =
* improved inventory sync for variations
* added advanced options to listing edit page
* MarketPress: added support for calculated shipping services

= 1.0.1 =
* support for MarketPress

= 1.0 =
* Initial release

