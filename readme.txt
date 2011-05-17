=== Benchmark Email Lite ===
Contributors: beautomated
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B22PPZ3SC6WZE
Tags: widget, widgets, api, list, email, mail, news, register, registration, plugin, plugins, wordpress, sidebar, newsletter, benchmark email, benchmark email lite, beAutomated, beautomated
Requires at least: 2.9
Tested up to: 3.1.2
Stable tag: 1.0.1

Benchmark Email Lite creates a newsletter signup form widget.

== Description ==

Benchmark Email Lite creates a newsletter signup form widget. The widget creates a simple W3C and WAVE validated signup form to instantly subscribe visitors to a Benchmark Email contact list, requesting the subscriber's first name, last name, and email address. If the subscriber preexists on the list, this will update the subscriber's first and last names on the mailing list.

The signup form uses standard HTML list items so it can be manipulated by CSS within your theme to match your site's design. Some themes will automatically style the widget to match the design. Contact us or the designer of your theme if you are having difficulties getting the form to match your theme, or add your own child theme with CSS that styles the elements accordingly.

If you do not currently have a Benchmark Email account, please consider clicking our affiliate link to try their [30-day FREE trial](http://www.benchmarkemail.com/?p=68907 "Try Benchmark Email"). Clicking this affiliate link will, if you eventually signup for a paid account with Benchmark Email, earn us a commission that we can use to support this plugin and develop it further.

The widget can be placed in any widget compatible area of your theme. It contains administrative controls where you enter an optional title, your Benchmark Email API Key, and the name of the list for your visitors to be subscribed to. There is an optional setting to limit the plugin to a single page, if desired.

The widget can be extended to allow subscriptions to multiple lists or even multiple Benchmark Email API accounts. This is accomplished by installing multiple instances of the widget in various widget compatible areas of your theme. You may use a third party plugin to control which pages or posts the widget appears on. There are several plugins available for doing that, including the following:

* [Widget Logic](http://wordpress.org/extend/plugins/widget-logic/ "Get Widget Logic Plugin")
* [Widget Context](http://wordpress.org/extend/plugins/widget-context/ "Get Widget Context Plugin")
* [Widgets on Pages](http://wordpress.org/extend/plugins/widgets-on-pages/ "Get Widgets on Pages Plugin")
* [Add Widgets to Pages](http://wordpress.org/extend/plugins/add-widgets-to-pages/ "Get Add Widgets to Pages Plugin")

Please [Contact Us](http://www.beautomated.com/contact/ "Contact Us") if you have any questions or issues with this plugin.

== Installation ==

1. Upload `benchmark-email-lite` folder and its contents to your `/wp-content/plugins/` directory.
1. Activate the plugin through the Plugins menu in WordPress.
1. Add the Widget to your sidebar through the Widgets menu in WordPress Appearance section.
1. If you are creating a new Benchmark Email account, please use the link [http://www.benchmarkemail.com/?p=68907](http://www.benchmarkemail.com/?p=68907 "Try Benchmark Email") to support the development of this plugin.
1. Obtain your Benchmark Email API Key by logging into Benchmark Email, click My Account, click My Account Settings, scroll to the big yellow box towards the bottom of the page and copy the API Key code.
1. Expand the Widget options. Enter an optional title, your Benchmark Email API Key and the name of the list for your visitors to be subscribed to. There is an optional setting to limit the plugin to a single page, if desired. Click Save when you're all done.
1. Test the plugin by going to your site and completing the form with temporary dummy data. You should see a "Successfully Added/Updated/Queued Subscription." message. Verify that the subscription went through by logging into Benchmark Email, clicking List, choosing your list. The new subscription should be at the top. You can delete the dummy entry once verified.

== Frequently Asked Questions ==

= Why aren't the First and Last name fields populating onto my list? =

Benchmark Email has a bug in their API that was introduced today, Monday May 16, 2011. We reported the problem and they are working to fix this issue.

= What does "Error: [L] transport error - could not open socket" mean? =

This means that the plugin is not able to connect with the Benchmark Email API server at [http://api.benchmarkemail.com](http://api.benchmarkemail.com "Test Connection to Benchmark Email API"). Their server could be down for maintenance, or there could be some other connection problem. To remedy this occasional problem, we are building connection failover handling into the next release of the plugin v1.0.2, which is currently developed and undergoing Quality Assurance testing. We also created a [monitoring job via Pingdom](http://stats.pingdom.com/ta1roodo4tet/345893 "View Monitoring Status").

= How do I control which page(s) the widget appears on, or subscribe to multiple lists? =

There is an optional setting to limit the plugin to a single page, if desired. The widget can be extended to allow subscriptions to multiple lists or even multiple Benchmark Email API accounts. This is accomplished by installing multiple instances of the widget in various widget compatible areas of your theme. You may use a third party plugin to control which pages or posts the widget appears on. There are several plugins available for doing that, including the following:

* [Widget Logic](http://wordpress.org/extend/plugins/widget-logic/ "Get Widget Logic Plugin")
* [Widget Context](http://wordpress.org/extend/plugins/widget-context/ "Get Widget Context Plugin")
* [Widgets on Pages](http://wordpress.org/extend/plugins/widgets-on-pages/ "Get Widgets on Pages Plugin")
* [Add Widgets to Pages](http://wordpress.org/extend/plugins/add-widgets-to-pages/ "Get Add Widgets to Pages Plugin")

= I use the Kubrick theme and I'm seeing bullets! =

We have confirmed that the Kuberick theme adds bullets in the sidebar. We cannot override this without forcing a new stylesheet, which would have a negative effect on other themes. So, we recommend you override this within your CSS with the entry at the bottom `.entry ul li::before,#sidebar ul ul li::before{content:"";}`

= How do I make the form match my theme? =

The signup form uses standard HTML list items so it can be manipulated by CSS within you theme to match your site's design. Some themes will automatically style the widget to match the design. Contact the designer of your theme if you are having difficulties getting the form to match your theme, or add your own child theme with CSS that styles the elements accordingly. The main classname value is `benchmarkemaillite_widget`.

== Screenshots ==

1. This is the widget control panel.
2. This is the default widget on Twenty Ten theme.
3. This is an example of the widget with customized CSS.

== Changelog ==

= 1.0.2 =

* Added failover handling. If the API becomes unavailable the plugin will dump subscriptions into a CSV buffering file in the plugin folder that will attempt to post to the API and clear the file upon each subsequent subscription submission.

* Changed the first name and last name field titles from "firstname" to "First Name" per the spec of the newly released API.

= 1.0.1 =

* Fixed bug in first name, last name, and email address submitted data sanitizing to be compatible with international symbols or anything that WordPress considers safe for data validation purposes. Reference: `sanitize_email()` and `sanitize_text_field()` functions on WordPress Codex.
* Fixed bug when the widget is installed multiple times on a single page leading to only one form pre-populating the entered data and some CSS conflicts. Multiple instances per page are now supported!
* Updated admin area widget field sanitization method to `sanitize_text_field()` function requiring v2.9.0.
* Added anchor `#benchmark-email-lite` into URL so that after form submission it puts the user on the proper screen position to view the server response.
* Adjusted the server response to clear out the submitted values upon successful form submission.
* Re-titled the Benchmark Email Token to the term "API Key" to match what Benchmark Email is calling it on their website.
* Added support for international language translation/localization.

= 1.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.2 =

* Added failover handling whe the API becomes unavailable and changed firstname/lastname field labels for their new API.

= 1.0.1 =

* Fixes a couple of bugs and adds a few minor features!
