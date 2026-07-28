=== Air Quality Data from Foobot ===
Contributors: markcbain
Donate link: https://bain.design/
Tags: air quality, foobot, pollution, air, monitor, particles, air, measure, CO2, humidity, temperature, API
Requires at least: 5.3
Tested up to: 7.0
Stable tag: 1.3.0
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display the Air Quality Data from multiple Foobot devices via a shortcode.  

== Description ==

This plugin lets you show the data from one or more [Foobot Air Quality Monitors](hhttps://foobot.io/ "Visit the Foobot website") on your WordPress site.Apart from a Foobot device, you will need to [request an API key](https://api.foobot.io/apidoc/index.html "request a Foobot API key") from the Foobot API website. 

== Installation ==

1. Download the plugin .zip archive and unpack it. 
1. Locate the plugin file and upload to your website Plugins folder.
1. Log into WordPress and activate the plugin. 
1. Once the plugin is activated, go to `Settings > Foobot API` and add your Foobot API username and API key ([Get your Foobot API key here](https://api.foobot.io/apidoc/index.html "Foobot API")).
1. Use the shortcode `[foobot-show-data device="foo"]`, where `foo` is your device name, to show the world your AQ data!


== Frequently Asked Questions ==

= Do I need to have a Foobot device to use this plugin =

No, but you do need access to one via the Foobot API in order to display data on your site. 

= Can you sell me a Foobot Air Quality Monitor? =

Sorry, no. I need both of mine for testing purposes.

== Screenshots ==

1. The data table.

== Changelog ==

= 1.3.0 =
* Add a list of devices found on your Foobot account to the settings page, each with a ready-to-copy shortcode
* Add a "Test Connection" button and a "Last data received" indicator to the settings page
* Add an option to show a device's latest reading in the WordPress admin bar, with the full sensor breakdown and last-updated time on hover
* Mask the saved API key on the settings screen instead of showing it in full
* Fix stored/reflected XSS: escape device name and Foobot API-derived sensor values before output
* Fix fatal error on PHP 8+ when no device data exists yet (e.g. fresh install with no API key configured)
* Fix uninstall.php referencing an undefined constant, which silently skipped cleanup on uninstall
* Stop sending the API key in the request URL query string (it's already sent via header)
* Add PHPUnit test suite and WordPress Coding Standards linting
* Add GitHub Actions CI (tests on every push/PR) and tag-triggered WordPress.org deploy
* Reformat codebase to WordPress Coding Standards
* Confirm compatibility with WordPress 7.0

= 1.2.1 =
* Fix database error

= 1.2 =
* Display data timestamp in WordPress format
* Use system fonts
* Force temp to show decimals

= 1.1 =
* Add credits and assets for plugin repo

= 1.0 =
* Initial plugin version

== Upgrade Notice ==

= 1.3.0 =
* Adds CI/testing infrastructure and WordPress Coding Standards reformatting; no functional changes.

= 1.2.1 =
* Fix database error

= 1.2 =
* Display data timestamp in WordPress format
* Use system fonts
* Force temp to show decimals

= 1.0 =
* Initial plugin version

== Credits ==

Icon made by [Vitaly Gorbachev](https://www.flaticon.com/authors/vitaly-gorbachev) from [www.flaticon.com](https://www.flaticon.com)