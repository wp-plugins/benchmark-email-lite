=== Benchmark Email Lite ===
Contributors: beautomated, seanconklin, randywsandberg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B22PPZ3SC6WZE
Tags: widget, widgets, api, list, email, mail, news, register, registration, plugin, plugins, wordpress, sidebar, newsletter, benchmark email, benchmark email lite, beAutomated, mailing list
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 2.4.3
License: GPLv2

Benchmark Email Lite lets you build an email list from your WordPress site, and easily send your subscribers email versions of your posts and pages.

== Description ==

If you want your blog to reach every reader, you have to use email. The Benchmark Email Lite WordPress Plugin builds an email list right from the pages of your WordPress site, and sends your subscribers email versions of your posts and pages in a flash. See when, where, and who opens or clicks on links or unsubscribes—all live from your Wordpress dashboard!

With this version you can:

* Control up to 5 separate Benchmark Email accounts.
* Build your list of subscribers with a custom signup form widget or shortcode.
* Send test emails of your blog post before deciding on the final send.
* Easily send or schedule a formatted email version of your blog posts.
* View live reports of opened, bounced, unsubscribed, forwarded, and unopened email as well as clicked links.

**From Blog to Email:** Available as an easy-to-integrate widget, this signup form allows subscribers to sign up for email versions of your blog posts with just their email address. You can further customize the signup form with 27 additional fields (e.g., First Name, Last Name, Address, Phone, etc.). If a user signs up and they're already a subscriber, the widget automatically updates the person's data in your email list.

**Themes to Match Your Blog:** You want a signup form that looks like your blog, right? The Benchmark Email Lite widget uses standard HTML list items so you can code your CSS to make the widget match your blog’s design. Most themes will automatically match your signup form to the style of your blog.

**Fully Customizable, Anywhere on Your Blog:** Administrative controls allow you to fully customize the signup form. Choose which email list you want signups to be added to. Best of all, the signup widget can be placed on any page you want—or even every page of your site.

**Test Versions of Your Email:** Send yourself a test email before sending the content to your subscribers. The Plugin's interface inside your post/page editor enables you to email a simple version of your posts (including images) the moment that you finish creating them on your site. Customize the email, From name and subject line when you send to achieve email marketing perfection.

**Formatted Email Versions of Your Blog:** Once you're done testing, select a contact list from the Benchmark Email Lite Plugin and click send! The formatted email version of your blog will be sent to your subscribers right away. You can also schedule your email to be sent at a later date and time.

**View Live Reports:** Once an email is sent, you can see the live reports of its success from within your WordPress dashboard. Learn who opens your emails, clicks on links, unsubscribes and forwards your blogs to friends—even see which email contacts bounce and where your subscribers are reading from.

To get started, sign up for a Benchmark Email [30-day free trial](http://www.benchmarkemail.com/Register "Try Benchmark Email 30 days free"). With a free trial, you can use the Plugin and fully experience what Benchmark Email has to offer, including our Mobile Marketing iPhone App, Surveys, Polls and Autoresponders. We know you'll love what you see, so once you upgrade your plan you can still use the Plugin free of charge for as long as you’d like. The Plugin is integrated via API with your Benchmark Email account. You may control up to 5 Benchmark Email accounts from a single WordPress site.

Need help? Please call Benchmark Email at 800.430.4095.

== Installation ==

Update Instructions

1. Click to have the Plugin updated.
1. Click Benchmark Email Lite on the administration sidebar menu. Click the Settings tab. Check to ensure that your API keys are properly configured.
1. Navigate to Appearance, Widgets. Expand the Widget options for any existing sign-up form widgets. Verify that the settings are all correct. Click Save when you're all done.

New Automatic Installation

1. Log in to your blog and go to the Plugins page.
1. Click Add New button.
1. Search for Benchmark Email Lite.
1. Click Install Now link.
1. (sometimes required) Enter your FTP or FTPS username and password, as provided by your web host.
1. Click Activate Plugin link.
1. If you are creating a new Benchmark Email account, please use the link [http://www.benchmarkemail.com/Register](http://www.benchmarkemail.com/Register "Try Benchmark Email").
1. Obtain your Benchmark Email API Key by logging into Benchmark Email, click My Account, click My Account Settings, scroll to the big yellow box towards the bottom of the page and copy the API Key code. If the yellow box is missing, click the link to "generate your API key" first.
1. Back on your site, click Benchmark Email Lite on the administration sidebar menu. Click the Settings tab. Check to ensure that your API key is properly configured.
1. Now you can setup any number of signup form widgets on the Appearance, Widgets administration page, plus you can save any number of posts and pages to email campaigns right from the post or page editor!

New Manual Installation

1. Download the Plugin and un-zip it.
1. Upload the `benchmark-email-lite` folder to your `wp-content/plugins/` directory.
1. Activate the Plugin through the Plugins menu in WordPress.
1. If you are creating a new Benchmark Email account, please use the link [http://www.benchmarkemail.com/Register](http://www.benchmarkemail.com/Register "Try Benchmark Email").
1. Obtain your Benchmark Email API Key by logging into Benchmark Email, click My Account, click My Account Settings, scroll to the big yellow box towards the bottom of the page and copy the API Key code. If the yellow box is missing, click the link to "generate your API key" first.
1. Back on your site, click Benchmark Email Lite click Benchmark Email Lite on the administration sidebar menu. Click the Settings tab. Check to ensure that your API key is properly configured.
1. Now you can setup any number of signup form widgets on the Appearance, Widgets administration page, plus you can save any number of posts and pages to email campaigns right from the post or page editor!

== Frequently Asked Questions ==

= Where do I go for help with any issues? =

Please call Benchmark Email at 800.430.4095.

= How do I get the signup form shortcode to use on pages or posts? =

Open a new or existing signup form widget instance by dragging onto any sidebar of your theme or the "inactive widgets" sidebar. Dink down (open) the widget contents and copy the shortcode provided at the bottom of the widget. Paste the shortcode onto any page or post for the signup form to appear inline.

= Why did the widget suddenly stop connecting with Benchmark Email? =

Please check your API key. We observed on 11/09/2011 that our own API key was deleted/reset on Benchmark Email's server. We had to generate a new one and place the new key code into our widget settings. To generate a new API key, log into Benchmark Email via their website and go to My Account, then Account Settings, then scroll towards the bottom of the page.

= Why am I seeing "Error Connecting..." in the admin area? =

To combat occasional Benchmark Email API server slowdowns causing sluggish behavior in the WordPress admin area on pages containing this plugin's features, we added a feature to disable connectivity if the plugin detects a 5 second or greater delay in any given connection attempt. Disablement lasts for 5 minutes. The connection timeout can be customized to a value greater than 5 seconds via the plugin settings page. This error can also be triggered by your web host providing slow outbound connectivity, or your web host throttling your PHP processing speed due to other problems with your site, such as memory leaks or heavy traffic. Despite delays, subscriptions are never lost thanks to our queueing system.

= How do I control which page(s) the widget appears on, or subscribe to multiple lists? =

There is an optional setting to limit the Plugin to a single page, if desired. The widget can be extended to allow subscriptions to multiple lists or even multiple Benchmark Email API accounts. This is accomplished by installing multiple instances of the widget in various widget compatible areas of your theme.

= How can I customize the email template? =

In version 2.4.3 we added a filter to permit email template customization using external code. This allows you to customize the email template while continuing to keep the Plugin up to date. Use the following code in a new Plugin file `wp-content/plugins/my_custom_plugin/my_custom_plugin.php`:

`
<?php
/*
Plugin Name: Customize Benchmark Email Lite Email Template
Plugin URI: http://wordpress.org/extend/plugins/benchmark-email-lite/
Description: Customizes the Benchmark Email Lite Plugin's email template.
Version: 1.0
Author: Myself
Author URI: http://www.beautomated.com/contact/
License: GPL2
*/
add_filter( 'benchmarkemaillite_compile_email_theme', 'benchmarkemaillite_theme', 10, 1 );
function benchmarkemaillite_theme( $args ) {
	return "
		<html>
			<head>
				<title>{$args['title']}</title>
			</head>
			<body>
				<div style='background-color: #eee; padding: 15px; margin: 15px; border: 1px double #ddd;'>
					<h1>{$args['title']}</h1>
					{$args['body']}
				</div>
			</body>
		</html>
	";
}
?>
`

= How do I make the form match my theme? =

The signup form uses standard HTML list items so it can be manipulated by CSS within your theme to match your site's design. Some themes will automatically style the widget to match the design. Contact the designer of your theme if you are having difficulties getting the form to match your theme, or add your own child theme with CSS that styles the elements accordingly. The main classname value is `benchmarkemaillite_widget`.

= Where do I go to change the double opt-in confirmation email text? =

1. Log into your Benchmark Email account.
1. Click on Lists tab, then Signup Forms sub-tab.
1. Either create a new signup form or click to edit the "Sample Signup Form".
1. Complete the section titled "Opt-in Mail".

= What happens if a subscriber resubmits their subscription? =

If the subscriber's email address preexists on the list, this will update the other fields, if enabled on your widget and populated by the user.

= Why do I occasionally get "Successfully Queued Subscription"? =

This occurs when the Plugin is not able to immediately connect with the Benchmark Email API server at [http://api.benchmarkemail.com](http://api.benchmarkemail.com "Test Connection to Benchmark Email API"). To remedy this occasional problem, we built in a connection failover capability to queue subscriptions into a CSV file stored in the Plugin folder, and automatically attempt to unload the queue every 5 minutes until successfull to the Benchmark Email API server. We also created a [monitoring job via Pingdom](http://stats.pingdom.com/ta1roodo4tet/345893 "View Monitoring Status").

= Why are the Extra 1 and Extra 2 fields no longer available? =

Benchmark Email changed the default fields associated with newly created lists in mid 2011. The fields Extra 1 and Extra 2 became Date 1 and Date 2 on new lists created after that date. Users are able to change their list's field titles and types by clicking the Edit link on any list, then clicking Advanced. However, this plugin does not currently support non-default settings. We had to eliminate the Extra 1, Extra 2, Date 1, Date 2 options in order to comply with default settings before and after said change to the defaults.

= Why did you switch to double opt-in starting in v1.0.4?  =

Two reasons. First, Benchmark Email requested that we use this method because it ensures list quality, which in effect keeps everyone out of trouble and keeps their prices down. Second, in the event somebody unsubscribes and chooses "Do Not Contact" checkbox and gets placed on the Master Unsubscribe List, they can only be removed from this list by the Benchmark Email customer opening a ticket for removal or the subscriber re-subscribing with a confirmed double opt-in method. This provides an easy way for somebody to re-subscribe.

== Screenshots ==

1. This is the default shortcode on Twenty Eleven theme.
2. This is the default widget on Twenty Eleven theme.
3. This is the signup form widget control panel.
4. This is the page/post metabox control panel.
5. This is a sample email generated from a post.
6. This is the Plugin settings panel.
7. This is the sent email campaigns listing.
8. This is a sample email campaign report overview.

== Changelog ==

= 2.4.3 on 2012-12-05 =

* Added: Filter 'benchmarkemaillite_compile_email_theme' to allow external email template customization. See FAQ.
* Added: Automatic radio button selection of sending type on the metabox when sub elements are chosen.
* Added: Message when a widgets of deleted API keys are being deactivated.
* Updated: Feedback responses on the post metabox, especially for the successful sending of test email campaigns.
* Updated: Renamed some function names to match the WP hook they relate to.
* Updated: Changed message on sent test email campaigns to say 'sent' instead of 'created'.
* Updated: Updated WP admin notice box contents to look more normal, yellow or red based on status of message as error or update message.
* Updated: Minor enhancement to settings links code.
* Fixed: Bug regarding the Please Select option for widget administration.
* Fixed: Bug on defaulting of the list selection, by deactivating widgets of deleted API keys.
* Fixed: Bug with field validation when the same widget exists on a page in both a shortcode and a sidebar widget.
* Fixed: Bug with the missing API key error persisting during the first refresh after putting in an API key.

= 2.4.2 on 2012-11-07 =

* Added: Looping of detailed report data requests, to present full table of data (over 100 limit).
* Added: Line number counter to detailed reports.
* Added: Connection timeout warning atop the settings and reports interfaces with button to reconnect.
* Added: Descriptive text on the individual email detail reports.
* Updated: Moved report under Links Clicked to the email campaign page as Click Performance to more closely mirror the BME interface.
* Updated: Split email links report into parent and child reports per URL being referenced.
* Removed: Lower limit on timeout. Was set at a minimum of 10 seconds.
* Fixed: Changed the Opens By Country report on the main email campaign page to only show when there has been at least 1 recorded open.

= 2.4.1 on 2012-10-17 =

* Added: Ability to send test email copies to up to 5 addresses.
* Added: Administration sidebar menu flyouts to match tabs.
* Updated: Organized library, markup, and JS files into folders.
* Updated: Reorganized reports code for better optimization.
* Updated: Reports tab reporting when there are no reports, or no reports for specific keys.
* Updated: Adjusted reports table column widths.
* Updated: Set maxlength settings for posts/pages metabox text input fields.
* Fixed: Error message window was persisting for 5 seconds, when it should just display once.
* Fixed: Email template height 100% was causing extra space in footer of test emails.

= 2.4 on 2012-08-15 =

* Added: Shortcode that allows any sign up form widget to be used within pages and posts.

= 2.3.1 on 2012-07-18 =

* Fixed: Minor bug with the subtitle display during widget administration.

= 2.3 on 2012-06-04 =

* Added: Full email campaign reporting capabilities.
* Updated: Moved menu item from the Settings section to a top level item with icon.

= 2.2.1 on 2012-03-28 =

* Added: Setting for connection timeout to be customized for diagnostic purposes. See FAQ.
* Fixed: Removed Extra 1 and Extra 2 widget field options due to newer defaults. See FAQ. 

= 2.2 on 2012-03-26 =

* Added: Scheduling capabilities for post to campaigns feature.
* Added: Prevents admin area slowdowns by detecting API server connections over 5 seconds and disabling communications for 5 minutes.

= 2.1 on 2012-03-12 =

* Added: All additional fields supported by BME onto widget signup form administration.
* Added: Upgrade procedure from v2.0.x to 2.1 saved widgets.
* Fixed: Notices showing up upon page/post deletion.

= 2.0.2 on 2012-02-20 =

* Fixed: Queue unloading was failing when the API connection goes down and subscriptions come in.

= 2.0.1 on 2012-02-13 =

* Added: Green and red indicators adjacent to entered API keys on the Benchmark Email Lite settings page, showing status upon save.
* Added: Benchmark Email response code into error message after creating an email campaign failure.
* Fixed: Includes silent updates made to the v2.0 release concerning automatic upgrading of saved widget settings from earlier v1.x.
* Fixed: v3.3.0 compatibility when no widgets preexisted from earlier version caused a warning at the top of the screen.
* Fixed: After deleting API key, the warnings about the need to have an API key weren't being fired anymore.
* Fixed: Bad API key(s) were triggering an error "Unable to connect" that wasn't very helpful.
* Fixed: Bad API key(s) were causing the good API keys to not be considered and utilized.

= 2.0 on 2012-01-31 =

* Added: Ability to create Benchmark Email campaigns from WordPress pages and posts.
* Added: Ability to send post campaigns immediately to either a test address or a selected Benchmark Email list.
* Added: Plugin settings page for global API Key(s) and campaign settings.
* Updated: Moved API key setting from individual widgets to a new Plugin settings panel.
* Updated: Split PHP functions among several classes/files for organization and growth.
* Updated: Added warranty disclaimer text in the main Plugin file header.
* Fixed: W3C validation error on strict mode, caused by two hidden input fields.
* Fixed: Localizations (language) weren't being loaded properly for International support.

= 1.1 on 2011-12-07 =

* Added: Prepopulation of the fields for logged in users.
* Added: Ability to toggle first and last name fields off.
* Added: Optional text to display to your readers.
* Added: Ability to change the text of the subscribe button.
* Added: Added widget title display in widget subtitle bar area.
* Added: New link on widget admin panel to view list names on BME.
* Updated: Expanded widget administration panel width to 400 pixels.
* Updated: Moved widget back end and front end HTML markup code to separate files.
* Updated: Added text next to the API Key field regarding FAQ #1.
* Updated: Sanitization functions adjusted.
* Removed: Output of widget set-up instructions on website before the widget is configured.
* Fixed: Bug when extra spaces existed between words in the list name admin vs front end behavior differed.

= 1.0.5 on 2011-08-18 =

* Updated: Moved the subscriptions queueing in the event of API failure to WP cron instead of being triggered upon subsequent subscription.
* Updated: Moved the subscriptions queueing in the event of API failure storage from a CSV file type storage to storage in the WordPress database. Prevents filesystem permissions issues.
* Updated: Cleaned-up some code with unnecessary referencing of widget IDs during subscriptions processing.
* Updated: Renamed "cache" to "queue" for clarification about the failover handling support.
* Fixed: Added a new button on the widget configuration panel to "Verify API Key and List Name" as the previous tab-off event wasn't always being executed if users click Save without first tabbing off the elements to be tested.

= 1.0.4 on 2011-06-20 =

* Added: AJAX feedback mechanism in widget administration that checks the API key and list name fields against the Benchmark Email database and reports status.
* Updated: Subscription to utilize Benchmark Email's double opt-in method. This prevents the problem when somebody who wants to re-subscribe can't get out of the Master Unsubscribe List.

= 1.0.3 on 2011-05-23 =

* Added: BME API key to failover CSV temporary buffer file.
* Added: Spinner icon appearance upon front end form submission.
* Updated: The response texts to proper case.
* Updated: The CSV buffer file processing logic and combined with main process.
* Fixed: PHP notices showing up when debug mode is turned on in `wp-config.php`.
* Removed: Display of the front end form upon successful submission.

= 1.0.2 on 2011-05-18 =

* Added: Failover handling. If the API becomes unavailable the Plugin will dump subscriptions into a CSV buffering file in the Plugin folder that will attempt to post to the API and clear the file upon each subsequent subscription submission.
* Updated: The first name and last name field titles from "firstname" to "First Name" per the spec of the newly released API.
* Fixed: Bug when multiple widgets exist on a page and sometimes aren't being keyed properly, causing the processor to not always know which widget is being submitted.

= 1.0.1 on 2011-05-14 =

* Added: Support for international language translation/localization.
* Added: Anchor `#benchmark-email-lite` into URL so that after form submission it puts the user on the proper screen position to view the server response.
* Updated: Admin area widget field sanitization method to `sanitize_text_field()` function requiring v2.9.0.
* Updated: Title for the Benchmark Email Token to the term "API Key" to match what Benchmark Email is calling it on their website.
* Updated: The server response to clear out the submitted values upon successful form submission.
* Fixed: Bug in first name, last name, and email address submitted data sanitizing to be compatible with international symbols or anything that WordPress considers safe for data validation purposes. Reference: `sanitize_email()` and `sanitize_text_field()` functions on WordPress Codex.
* Fixed: Bug when the widget is installed multiple times on a single page leading to only one form pre-populating the entered data and some CSS conflicts. Multiple instances per page are now supported!

= 1.0 on 2011-05-12 =

* Added: Initial Plugin release.

== Upgrade Notice ==

= 2.4.3 =

* This is a bugfix release that also adds the ability to programmatically customize the email template.

= 2.4.2 =

* Several more updates to the reports tab, and better handling of connection timeout situations.

= 2.4.1 =

* Added ability to send to multiple test addresses. Many small updates included!

= 2.4 =

* Added signup form shortcodes.

= 2.3.1 =

* Minor bug fix with the subtitle display during widget administration.

= 2.3 =

* Added full email campaign reporting capabilities.

= 2.2.1 =

* Fixes for connection timeout setting, and Extra 1 and Extra 2 widget fields.

= 2.2 =

* Added scheduling capabilities for post to campaign feature. Also added API server speed detection/maneuvering.

= 2.1 =

* Added additional fields to the signup form widget administration.

= 2.0.2 =

* Fixed a single bug related to subscription queueing.

= 2.0.1 =

* Several minor fixes to API key settings and a couple minor related enhancements as well.

= 2.0 =

* New settings panel and post-to-campaign support. API keys are moved from widgets to settings panel on activation.

= 1.1 =

* Made name fields optional and several other enhancements. See changelog.

= 1.0.5 =

* New admin area verification button and utilizing WP cron system for failover queue (see changelog).

= 1.0.4 =

* New admin area verifications and use of douple opt-in (see changelog).

= 1.0.3 =

* Minor code updates and UI enhancements (see changelog).

= 1.0.2 =

* Added failover handling whe the API becomes unavailable and changed first name and last name field labels for their new API.

= 1.0.1 =

* Fixes a couple of bugs and adds a few minor features!
