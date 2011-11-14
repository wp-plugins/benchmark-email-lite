=== Benchmark Email Lite ===
Contributors: beautomated, seanconklin, randywsandberg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B22PPZ3SC6WZE
Tags: widget, widgets, api, list, email, mail, news, register, registration, plugin, plugins, wordpress, sidebar, newsletter, benchmark email, benchmark email lite, beAutomated, mailing list
Requires at least: 2.9
Tested up to: 3.2.1
Stable tag: 1.0.5

Benchmark Email Lite creates a newsletter signup form widget.

== Description ==

Benchmark Email Lite creates a newsletter signup form widget. The widget creates a simple W3C and WAVE validated signup form to instantly subscribe visitors to a Benchmark Email contact list, requesting the subscriber's first name, last name, and email address. If the subscriber preexists on the list, this will update the subscriber's first and last names on the mailing list.

The signup form uses standard HTML list items so it can be manipulated by CSS within your theme to match your site's design. Some themes will automatically style the widget to match the design. Contact us or the designer of your theme if you are having difficulties getting the form to match your theme, or add your own child theme with CSS that styles the elements accordingly.

If you do not currently have a Benchmark Email account, please consider clicking our affiliate link to try their [30-day FREE trial](http://www.benchmarkemail.com/?p=68907 "Try Benchmark Email"). Clicking this affiliate link will, if you eventually signup for a paid account with Benchmark Email, earn us a commission that we can use to support this plugin and develop it further.

The widget can be placed in any widget compatible area of your theme. It contains administrative controls where you enter an optional title, your Benchmark Email API Key, and the name of the list for your visitors to be subscribed to. There is an optional setting to limit the plugin to a single page, if desired.

The widget can be extended to allow subscriptions to multiple lists or even multiple Benchmark Email API accounts. This is accomplished by installing multiple instances of the widget in various widget compatible areas of your theme. You may use a third party plugin to control which pages or posts the widget appears on. There are several plugins available for doing that; please refer to the FAQ tab for examples.

Please visit our [Support Forum](http://wordpress.org/tags/benchmark-email-lite?forum_id=10 "Visit our WordPress Support Forum") if you have any questions or issues regarding this plugin.

== Installation ==

Automatic Installation

1. Login to your blog and go to the Plugins page.
1. Click on the Add New button.
1. Search for beCounted.
1. Click Install now.
1. (sometimes required) Enter your FTP or FTPS username and password, as provided by your web host.
1. Click Activate plugin.
1. Add the widget to a widget capable area of your theme through the Appearance->Widgets menu.
1. If you are creating a new Benchmark Email account, please use the link [http://www.benchmarkemail.com/?p=68907](http://www.benchmarkemail.com/?p=68907 "Try Benchmark Email") to support the development of this plugin.
1. Obtain your Benchmark Email API Key by logging into Benchmark Email, click My Account, click My Account Settings, scroll to the big yellow box towards the bottom of the page and copy the API Key code.
1. Expand the Widget options. Enter an optional title, your Benchmark Email API Key and the name of the list for your visitors to be subscribed to. There is an optional setting to limit the plugin to a single page, if desired. Click Save when you're all done.
1. Test the plugin by going to your site and completing the form with temporary dummy data. You should see a "Successfully Added/Updated/Queued Subscription." message. Verify that the subscription went through by logging into Benchmark Email, clicking List, choosing your list. The new subscription should be at the top. You can delete the dummy entry once verified.

Manual Installation

1. Download the plugin and un-zip it.
1. Upload the `benchmark-email-lite` folder to your `wp-content/plugins/` directory.
1. Activate the plugin through the Plugins menu in WordPress.
1. Add the widget to a widget capable area of your theme through the Appearance->Widgets menu.
1. If you are creating a new Benchmark Email account, please use the link [http://www.benchmarkemail.com/?p=68907](http://www.benchmarkemail.com/?p=68907 "Try Benchmark Email") to support the development of this plugin.
1. Obtain your Benchmark Email API Key by logging into Benchmark Email, click My Account, click My Account Settings, scroll to the big yellow box towards the bottom of the page and copy the API Key code.
1. Expand the Widget options. Enter an optional title, your Benchmark Email API Key and the name of the list for your visitors to be subscribed to. There is an optional setting to limit the plugin to a single page, if desired. Click Save when you're all done.
1. Test the plugin by going to your site and completing the form with temporary dummy data. You should see a "Successfully Added/Updated/Queued Subscription." message. Verify that the subscription went through by logging into Benchmark Email, clicking List, choosing your list. The new subscription should be at the top. You can delete the dummy entry once verified.

== Frequently Asked Questions ==

= Why did the widget suddenly stop connecting with Benchmark Email? =

Please check your API key. We observed on 11/09/2011 that our own API key was deleted/reset on Benchmark Email's server. We had to generate a new one and place the new key code into our widget settings. To generate a new API key, log into Benchmark Email via their website and go to My Account, then Account Settings, then scroll towards the bottom of the page.

= Why did you switch to double opt-in in v1.0.4?  =

Two reasons. First, Benchmark Email requested that we use this method because it ensures list quality, which in effect keeps everyone out of trouble and keeps their prices down. Second, in the event somebody unsubscribes and chooses "Do Not Contact" checkbox and gets placed on the Master Unsubscribe List, they can only be removed from this list by the Benchmark Email customer opening a ticket for removal or the subscriber re-subscribing with a confirmed double opt-in method. This provides an easy way for somebody to re-subscribe.

= Where do I go to change the double opt-in confirmation email text? =

1. Log into your Benchmark Email account.
1. Click on Lists tab, then Signup Forms sub-tab.
1. Either create a new signup form or click to edit the "Sample Signup Form".
1. Complete the section titled "Opt-in Mail".

= Why do I occasionally get "Successfully Queued Subscription"? =

This occurs when the plugin is not able to immediately connect with the Benchmark Email API server at [http://api.benchmarkemail.com](http://api.benchmarkemail.com "Test Connection to Benchmark Email API"). To remedy this occasional problem, we built in a connection failover capability to queue subscriptions into a CSV file stored in the plugin folder, and automatically attempt to unload the queue every 5 minutes until successfull to the Benchmark Email API server. We also created a [monitoring job via Pingdom](http://stats.pingdom.com/ta1roodo4tet/345893 "View Monitoring Status").

= How do I control which page(s) the widget appears on, or subscribe to multiple lists? =

There is an optional setting to limit the plugin to a single page, if desired. The widget can be extended to allow subscriptions to multiple lists or even multiple Benchmark Email API accounts. This is accomplished by installing multiple instances of the widget in various widget compatible areas of your theme. You may use a third party plugin to control which pages or posts the widget appears on. There are several plugins available for doing that, including the following:

* [Widget Logic](http://wordpress.org/extend/plugins/widget-logic/ "Get Widget Logic Plugin")
* [Widget Context](http://wordpress.org/extend/plugins/widget-context/ "Get Widget Context Plugin")
* [Widgets on Pages](http://wordpress.org/extend/plugins/widgets-on-pages/ "Get Widgets on Pages Plugin")
* [Add Widgets to Page](http://wordpress.org/extend/plugins/add-widgets-to-page/ "Get Add Widgets to Page Plugin")

= I want to put the widget somewhere that widgets aren't currently allowed! =

The Benchmark Email Lite plugin does not currently support a shortcode for inclusion in a page body. We might be adding this capability down the road. The good news is that you can still use the plugin without needing a sidebar, if you can customize your theme! You can add a little code to the theme to allow the widget wherever you wish it to be - even inside the page body if you want it there. In order to enable the widget where you want it to go, add the following code to your theme files:

functions.php
`if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => '*my_custom_widget_bar*',
		'before_widget' => '<div class="*MyCustomWidgetBarWidgetClass*">',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
}`
.

footer.php or page.php
(or another file where you want the widget to go within your theme's markup)
`<!-- HTML markup that goes before the placement of the widget -->
<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('my_custom_widget_bar') ) { } ?>
<!-- HTML markup that goes after the placement of the widget -->`
.

= I use the Kubrick theme and I'm seeing bullets! =

We have confirmed that the Kuberick theme adds bullets in the sidebar. We cannot override this without forcing a new stylesheet, which would have a negative effect on other themes. So, we recommend you override this within your CSS with the entry at the bottom `.entry ul li::before,#sidebar ul ul li::before{content:"";}`

= How do I make the form match my theme? =

The signup form uses standard HTML list items so it can be manipulated by CSS within you theme to match your site's design. Some themes will automatically style the widget to match the design. Contact the designer of your theme if you are having difficulties getting the form to match your theme, or add your own child theme with CSS that styles the elements accordingly. The main classname value is `benchmarkemaillite_widget`.

== Screenshots ==

1. This is the widget control panel.
2. This is the default widget on Twenty Ten theme.
3. This is an example of the widget with customized CSS.

== Changelog ==

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

* Added: Failover handling. If the API becomes unavailable the plugin will dump subscriptions into a CSV buffering file in the plugin folder that will attempt to post to the API and clear the file upon each subsequent subscription submission.
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

* Added: Initial plugin release.

== Upgrade Notice ==

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
