=== Help Scout ===
Contributors: helpscout, sproutapps, dancameron, elanasparkle, avonville1, jamesros161
Tags: support, documentation, helpdesk, contact form help desk
Requires at least: 4.5
Tested up to: 6.8
Stable tag: 6.5.7
Release 6.5.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a contact form to your website, or embed Help Scout Beacon for instant answers from your knowledge base.

== Description ==

= Help Scout for WordPress =

Thousands of businesses in more than 140 countries use Help Scout to make every customer support interaction more human & more helpful.

= Features =

This simple plugin takes only a few minutes to setup. It enables you to do a couple important things:

* Add a contact form to your site, which submits inquiriest to Help Scout
* Add a [Help Scout Beacon](https://www.helpscout.net/embed-tools/) to your site. Just paste your embed code and save.

> <strong>Looking for more horsepower? Try Help Scout Desk.</strong><br>
> [Help Scout Desk](https://wphelpscout.com/?utm_medium=link&utm_campaign=hsfree&utm_source=wordpress.org) is a premium plugin that adds a full-featured customer portal to your WordPress site. Customers can submit inquiries, see their complete history and more.

== Installation ==

1. [Sign up for Help Scout](https://secure.helpscout.net/members/register/13/) if you don't already have an account.
1. Upload plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the **Plugins** menu in WordPress
1. Open the settings for the plugin, then add your Application ID, App Secret, and Mailbox ID.
1. Add `[hsd_form]` shortcode to a page or post to capture contact submissions OR use the Help Scout beacon.

Have a look at the [getting started guide](https://wphelpscout.com/support/docs/getting-started-with-help-scout-desk/) for more detailed information on setting up Help Scout for WordPress.

== Frequently Asked Questions ==

= Does this work if I'm on the Help Scout Free plan? =

Beacon is included on all Free plans, so you can embed your Beacon using this plugin. However, the contact form requires an API key, and API access is only available for paying plans.


== Screenshots ==

1. Submission form
2. Beacon


== Upgrade Notice ==

First Release

== Changelog ==

= 6.5.7 =
* **Fix:** Vulnerability fix [#25](https://github.com/BoldGrid/help-scout/issues/25)

= 6.5.6 =
* **Fix:** Fixes beacon script not loading [#60](https://github.com/BoldGrid/help-scout-desk/issues/60)

= 6.5.4 =
* **Fix:** Fixes issues when posts are saved causing nonce error.

= 6.5.3 =
* **Fix:** Updates to WordPress coding standards.

= 6.5.1 =
* **Fix:** SSL Verify update
* **Fix:** Label Changes


= 5.0 =

* NEW: Full Beacon 2.0 support
* NEW: Move to new domain
* NEW: Admin found under settings

= 4.2.1 =

* Fix: Admin CSS Issue

= 4.2 =

* New: Option to reset customer ids

= 4.0.5 =

* Fix: Variable typos

= 4.0.4 =

* Fix: WYSIWYG editor not showing for HSD
* Fix: Explicitly send credentials for AJAX requests, since WPEngine has some issues.

= 4.0.3 =

* Fix: Admin hidden becuase of missing capability

= 4.0.1 =

* New: Support for Free Help Scout Plugin
* Fix: All tags will be in-sync
