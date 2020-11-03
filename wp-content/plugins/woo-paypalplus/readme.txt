=== PayPal Plus for WooCommerce ===
Contributors: inpsyde, biont, cocreation
Tags: paypal, paypal plus, express checkout, woocommerce, payment, zahlungsarten, rechnung, lastschrift, kreditkarte
Requires at least: 4.4
Tested up to: 5.4.1
Stable tag: 2.2.1
Requires PHP: 5.6
License: GPLv2+
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

The official PayPal Plus Plugin for WooCommerce - adds payment via PayPal, Direct debit, Credit card and Pay upon invoice to your WooCommerce Shop.

== Description ==

= New to online business? =

Start your own online shop and use PayPal PLUS until July 31st, 2020 free of charge.

PayPal is your reliable partner - regardless of whether you are starting with PayPal from scratch or if you want to position your business online now due to the current situation. With PayPal PLUS you enable the four most popular payment methods from a single source. In addition, we relieve you and your business financially in these difficult times and cut the PayPal PLUS fees until July 31st, 2020. [Apply](https://www.paypal.com/de/webapps/mpp/paypal-plus?utm_source=partner&utm_campaign=zero-fees&utm_medium=woocommerce) for PayPal PLUS as soon as possible, integrate and activate it in your online shop and pay no fees until July 31st, 2020.

= Plugin Description =

PayPal Plus for WooCommerce is the official [PayPal Plus](https://www.paypal.com/de/webapps/mpp/paypal-plus) plugin. Without much effort, it integrates all PayPal Plus payment methods into your WooCommerce shop: PayPal, Direct debit, Credit card and Pay upon invoice

On the checkout page, the plugin loads an iFrame which is provided by PayPal. It offers all PayPal Plus payment methods to the buyer. Depending on their choice, the buyers are, after clicking the buy-button, guided to the PayPal form or to forms where they can enter the data to pay via Direct debit, Credit card or upon invoice.

= Benefits =

* New Express Checkout Gateway: Enable faster payments with the Express Checkout button, directly from the single product page or the shopping cart.
* More payment methods: PayPal Plus enables to integrate the four most popular payment methods into your shop: PayPal, Direct debit, Credit card, pay upon invoice.
* Easier integration: all payment methods in only one plugin.
* More safety: PayPal vendor protection protects against from losing money to chargebacks and reversals for all transactions.
* Attract more customers: with or without PayPal account.
* Userfriendly and responsive checkout: made by PayPal.
* Transaction amount is directly credited to your PayPal account after transaction
* Transparent and the same fees for all payment methods - without signup- or monthly fees.
* [PCI conformity](https://wikipedia.org/wiki/Payment_Card_Industry_Data_Security_Standard): On the checkout page, our plugin loads an iFrame being hosted by PayPal in which all transaction data are entered.

= Countries =
At the moment, **PayPal Plus** is only available for customers having their registered office in Germany. Nonetheless, international transactions work. Customers not being in Germany can only choose between the payment methods PayPal and Credit card.

= More information about PayPal Plus =

You want to have more information about PayPal Plus? You can find all details on [PayPal’s pages](https://www.paypal.com/de/webapps/mpp/paypal-plus).

= Official Tutorials =

* PayPal Plus for WooCommerce PDF guide - [download here](https://inpsyde.com/en/paypal-plus-for-woocommerce-pdf-manual/)

* PayPal Plus for WooCommerce - Youtube Video

https://www.youtube.com/watch?v=tFw6_f6oKOo


= Support =

You can find technical support for this plugin in the wordpress.org forum: [https://wordpress.org/support/plugin/woo-paypalplus](https://wordpress.org/support/plugin/woo-paypalplus)

Please read the FAQ (frequently asked questions) first and make sure you have installed the newest version of the plugin before contacting us.

**Made by [Inpsyde](https://inpsyde.com) &middot; We love WordPress**

== Installation ==

= Minimum Requirements =

* WooCommerce >= 3.6.4
Further minimum requirements are determined by the requirements for WooCommerce:
* PHP 5.6 or greater
* MySQL 5.6 or greater
* WordPress 4.4+
* WP Memory limit of 64 MB or greater (128 MB or higher is preferred)

Furthermore, you need a PayPal business account which is activated for PayPal. [You submit the application to PayPal.](https://www.paypal.com/de/webapps/mpp/paypal-plus).

= Automatic Installation =

This is the easiest way to install the PayPal Plus plugin.
1. Log into your WordPress installation.
2. Go to the menu item *Plugins* and then to *Install*.
3. Search for *PayPal Plus for WooCommerce*. In case several plugins are listed, check if *Inpsyde* is the plugin author.
4. Click *Install Now* and wait until WordPress reports the successful installation.
5. Activate the plugin. You can find the settings here: *WooCommerce => Settings => Checkout => PayPal Plus*.

**Attention:** You need WooCommerce 3.0 or higher to use PayPal Plus. Otherwise, the setting page of the plugin is not available. You will get a notification in your WordPress backend if you don’t use the correct WooCommerce version.

= Manual Installation =

In case the automatic installation doesn’t work, download the plugin from here via the *Download*-button. Unpack the archive and load the folder via FTP into the directory `wp-content\plugins` of your WordPress installation. Go to *Plugins => Installed plugins* and click *Activate* on *PayPal Plus für WooCommerce*.

== Frequently Asked Questions ==

= I installed WooCommerce in a lower version. Nevertheless, can I use PayPal Plus for WooCommerce? =

No, the plugin is only compatible with WooCommerce versions >= 3.0. We advise to make an update. But don’t forget to make a backup of your installation before. For making a backup use our free WordPress backup plugin [BackWPup](https://wordpress.org/plugins/backwpup/).

= What do I have to pay attention to when I use a PayPal account for several shops? =

It’s mandatory to assign a unique invoice prefix for each shop in the *PayPal Plus for WooCommerce* settings. Otherwise, PayPal won’t accept orders with the same invoice number.

= With PayPal Plus for WooCommerce, which payment methods can I integrate into my shop? =

With our plugin, you can integrate all those payment methods offered by PayPal: paying with your PayPal account, via direct debit, via credit card or paying via invoice.

= In my shop, the payment method pay upon invoice is not offered. What do I have to do? =

Your PayPal vendor account needs to be activated by PayPal in order to offer the payment method pay upon invoice. This takes place after verification by PayPal and may last a couple of weeks.

= Can I use PayPal Plus for digital products? =

Yes, it is possible, except when using the payment option "upon invoice".

= How can I hide the option to "pay upon invoice"? =

As a vendor, you are asked explicitly whether you want to have "invoice" as a payment option or not when signing up for PayPal Plus.

= Does PayPal Plus allow subscription payments? =

At the moment, subscription payments are not possible with PayPal Plus.

= Is payment by installments possible with PayPal Plus? =

Payment by installments is a separate product from PayPal Plus, and therefore requires a separate integration. Moreover, each vendor needs to be activated by PayPal individually. If both integrations, payment by installments and PayPal Plus, are used, payment by installments appears as another payment option under PayPal Plus. But payment by installments can also run as a single integration. In fact, payment by installments is theoretically possible, but there is no functional module for WooCommerce at the moment.

= Is it possible to offer payment via credit card or via direct debit only? =

No. The only payment option you can add or remove individually is purchase on account.

= Do vendors have the same guarantees for all payment options as with the classic PayPal? =

Yes, all PP+ payment options do have the vendor protection.

= Does a vendor get his money directly no matter the used payment option? =

Vendors usually get the money directly after the transaction, no matter which of the four PP+ payment options has been used.

= Does PayPal Plus work for international transactions, too? =

At the moment, PayPal Plus can only be used by vendors in Germany. But their buyers can buy and pay worldwide.

= How can I disable the PayPal Express Checkout button?

The Paypal Express Checkout button is available in the following places:
– Single Product Page
– Cart Page
– Mini Cart

For each of these, you can decide to enable/disable the button.
To do so just go into WooCommerce > Settings and select the Paypal Express Checkout settings page.

In the page you get it is possible to select where you want to see the buttons.

If you don’t want to have Express Checkout Gateway enabled just disable it from the same page.

== Screenshots ==

1. PayPal Plus for WooCommerce - plugin settings among WooCommerce => Settings => Checkout => PayPal Plus.
2. The four PayPal Plus payment methods: PayPal, Direct debit, credit card, pay upon invoice.
3. The PayPal Login form when paying via PayPal.
4. The PayPal Plus form when paying via Direct debit.
5. The PayPal Plus form when paying via credit card.
6. The PayPal Plus form when paying upon invoice.

== Changelog ==

= 2.2.1 =
- Fix: Paypal Banner error with third party Smart Coupons plugin
- Fix: PayPal Plus error on ftpext filesystem method
- Fix: Error on cart when deactivating caching mechanism
- Fix: Update WC version check to 3.6.4
- Fix: Paypal Banner error with third party Smart Coupons plugin

= 2.2.0 =
- Add: PayPal Financing Banner

= 2.1.2 =
- Fix: PayPal PLUS payments iframe does not load on checkout update

= 2.1.1 =
- Fix: compatibility for WooCommerce 3.9.x where payment methods for paypal plus does not appear on checkout page load

= 2.1.0 =
- Fix: Invalid PaymentId when executing the payment
- Fix: PayPalObjects.com not allowed because of CSP rules
- Fix: PayPal Plus is loading for a customer when an order was created by the merchant
- Fix: Express Checkout disappears after switching shipping methods
- Add: Cancel Url and WooCommerce Germanized Integration
- Add: Introduce filter `paypalplus.use_legacy_custom_patch_data` to allow third party developers to get back the custom data object which include order_key and order_id

= 2.0.4 =
- Fix: Compatibility with WooCommerce Admin plugin

= 2.0.3 =
- Fix: Non numeric value encountered when validate Refund Payment
- Fix: Ipn Order Note not added because order transition already changed by WooCommerce
- Fix: Support for IE 11 because of es6 transpilation and unavailable features
- Fix: Billing and shipping address fields are editable for express checkout

= 2.0.2 =
 - Fix: Admin Notice and `file_get_contents` unable to retrieve content from Url
 - Fix: Fatal Error in WooCommerce 3.6 because of WooCommerceSession methods are accessed too early

= 2.0.1 =
 - Fix: PayPal Express Checkout doesn't support formal and informal locales
 - Fix: Do not execute Frontend actions/filters in Admin context because WooCommerce Session isn't allowed in admin
 - Fix: Do not activate PayPal Express Checkout automatically on plugin installation or update
 - Fix: Use WooCommerce Logs instead of system temporary directory

= 2.0.0 =
 - Fix: IPN return 500 because of custom patch is not set correctly on PayPal
 - Fix: Do not send shipping address for digital goods
 - Add: Introduce Express Checkout feature

= 1.1.1 =
 - Fix: Decimal format for shipping details when order data are patched

= 1.1.0 =
 - Fix: When PayPal is activated the WooCommerce PayPal Gateway is now deactivated by default
 - Fix: Limit characters for logo URL to 127 characters
 - Fix: Show PayPal Plus availability on the settings page
 - Fix: Show PayPal payment data on invoice
 - Fix: Php error when running PHP 7.2
 - Fix: Settings are not deleted, when plugin is uninstalled
 - Fix: Error processing checkout - with Germanized and gateway fee
 - Add: BN-Codes
 - Add: Change the PayPalPlus description on payment overview page

= 1.0.8 =
 - Fix: compatibility with WooCommerce 3.4.0

= 1.0.7 =
 - Fix gateway description hidden on checkout page
 - Fix compatibility with WooCommerce 3.3.5

= 1.0.6 =
 - Fix incompatibility with plugins changing the order number
 - Fix -1 for error during checkout
 - Fix legal note text not displaying on thank you page
 - Fix Internal Server Error with Germanized Pro
 - Fix redirecting to blank page when credit card is not accepted
 - Fix incompatibility with jQuery 3.x
 - Fix legal note is shown with paragraph tag in email

= 1.0.5 =
 - Fix checkout error when adding or removing coupons on checkout page
 - Fix download of log file
 - Add WC compatibility headers

= 1.0.4 =
 - Make PayPal frame always visible
 - Allow cancelation URL to be set to custom a page
 - Fix handling of coupons

= 1.0.3 =
 - Allow user to download log
 - Fix to allow working with prices inclusive of tax
 - Allow changing the display title of the gateway
 - Another fix for price decimals
 - Move PayPal SDK in dedicated namespace to avoid collisions
 - Fix a possible shutdown if session data was cleared unexpectedly while performing the payment

= 1.0.2 =
 - Fix address patch request not always being included.
 - Fix an error with price decimals.
 - Improve session data handling.

= 1.0 =
Initial Release

== Upgrade Notice ==

= 1.0 =
This is the first version. Update your system when updates are available in order to ensure that the plugin works proper.
