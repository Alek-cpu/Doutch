=== Add Expires Headers ===
Contributors: passionatebrains, freemius
Donate link: http://www.addexpiresheaders.com/donate
Tags: expires header, expires headers, far future expiration, cache, expiry header, expiry, wp-cache, minify, gzip, speed optimization, etags
Requires at least: 3.5
Tested up to: 5.4.2
Stable tag: 2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will help to decrease page loading time and optimize your website by adding expires headers of various file types.

== Description ==
Plugin will improve your website loading speed by caching various types of static files in browser of User. It is light weight plugin but its impact on page loading speed in very crucial and easy noticeable.

= Advantages =
1) Reduce page loading time of website.

2) Improve user experience as page loads very quickly than before.

3) Decrease total data-size of page.

4) Larger band of predefined file types are covered so it will increase bandwidth of files which can have expiry headers.

= Pro Features =
1) Ability to add expires headers to External Resources

2) Adding new file types for adding expires headers

3) Refresh cache periodically

4) Unset Entity Tags

5) HTTP(Gzip) compression

6) Prevent Specific files from caching

7) Removing version info from files

= Documentation =
For Plugin documentation, please refer our <a href="https://www.addexpiresheaders.com/add-expires-headers-plugin/" rel="follow">plugin website</a>.

= Requirements =
1) Make sure that the "mod_expires" module is enabled on your website hosting server.

2) It is necessary to have read/write permission of .htaccess file to plugin. If not then update file permissions accordingly.

3) check status page of plugin for more info.

== Installation ==
1) Deactivate and uninstall any other expires headers plugin you may be using.

2) Login as an administrator to your WordPress Admin account. Using the “Add New” menu option under the “Plugins” section of the navigation, you can either search for: "add expires headers" or if you’ve downloaded the plugin already, click the “Upload” link, find the .zip file you download and then click “Install Now”. Or you can unzip and FTP upload the plugin to your plugins directory (wp-content/plugins/).

3) Activate the plugin through the "Plugins" menu in the WordPress administration panel.

== Usage ==

To use this plugin do the following:

1) Firstly activate Plugin.

2) Go to plugin settings page.

3) Check Files types you want to have expires headers and also add respective expires days for mime type using input box and make sure you enable respective mime type,for which group of files you want to add expires headers.

4) Once you hit "submit" button all options you selected in settings page saved database of website and accordingly .htaccess file will updated and add expires headers for respective selected files.

== Frequently Asked Questions ==

= Does this plugin have custom expiry time for different resources? =
Yes base on Mime Type you can have different expiry time.

= Does this plugin help in gzip compression of output html? =
No, But if you upgrade to pro verion you will have facility for same.

= Can we add custom file types for adding expires headers? =
No, But with upgrade you can have facility to add custom file types.

== Changelog ==

= 1.0 =
Initial Version of Plugin

= 1.1 =
Added Activation and Deactivation hooks.
Added Settings link on plugins page.

= 1.2 =
Adding functionality to disable Etags.

= 2.0 =
Basic feature for adding expires headers for pre define file types
Ability to have Pro-Version

= 2.1 =
Adding functionality for caching and adding expires headers to External resources
Added Plugin compatibility status page
Added more file formats

== Screenshots ==
1. Plugin Settings
