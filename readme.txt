=== Export Database (Backup & Download Database) ===

Contributors: littlebizzy
Donate link: https://www.patreon.com/littlebizzy
Tags: export, backup, download, migrate, database
Requires at least: 4.4
Tested up to: 5.0
Requires PHP: 7.0
Multisite support: No
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: EXPDBS

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

== Description ==

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

* [**Join our FREE Facebook group for support**](https://www.facebook.com/groups/littlebizzy/)
* [**Worth a 5-star review? Thank you!**](https://wordpress.org/support/plugin/export-database-littlebizzy/reviews/?rate=5#new-post)
* [Plugin Homepage](https://www.littlebizzy.com/plugins/export-database)
* [Plugin GitHub](https://github.com/littlebizzy/export-database)

#### Current Features ####

Export Database is a simple plugin that does one thing only: with a single click you are able to export (dump) your WordPress site's database exactly as it exists on the live site in either SQL format or ZIP (or even GZIP) if supported by your server/PHP.

The current version does not offer any search/replace functions nor is it meant to. It is for developers who need a quick and easy way to either back up their database, migrate to a local or another site, or so forth, without interactive options.

In future versions we may add more options.

#### Compatibility ####

This plugin has been designed for use on [SlickStack](https://slickstack.io) web servers with PHP 7.2 and MySQL 5.7 to achieve best performance. All of our plugins are meant for single site WordPress installations only; for both performance and usability reasons, we highly recommend avoiding WordPress Multisite for the vast majority of projects.

Any of our WordPress plugins may also be loaded as "Must-Use" plugins by using our free [Autoloader](https://github.com/littlebizzy/autoloader) script in the `mu-plugins` directory.

#### Defined Constants ####

    /* Plugin Meta */
    define('DISABLE_NAG_NOTICES', true);
    
#### Technical Details ####

* Prefix: EXPDBS
* Parent Plugin: [**Dev Tools**](https://www.littlebizzy.com/plugins/dev-tools)
* Disable Nag Notices: [Yes](https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices#Disable_Nag_Notices)
* Settings Page: No
* PHP Namespaces: No
* Object-Oriented Code: No
* Includes Media (images, icons, etc): No
* Includes CSS: No
* Database Storage: Yes
  * Transients: No
  * WP Options Table: Yes
  * Other Tables: No
  * Creates New Tables: No
  * Creates New WP Cron Jobs: No
* Database Queries: Backend Only (Options API)
* Must-Use Support: [Yes](https://github.com/littlebizzy/autoloader)
* Multisite Support: No
* Uninstalls Data: Yes

#### Special Thanks ####

[Alex Georgiou](https://www.alexgeorgiou.gr), [Automattic](https://automattic.com), [Brad Touesnard](https://bradt.ca), [Daniel Auener](http://www.danielauener.com), [Delicious Brains](https://deliciousbrains.com), [Greg Rickaby](https://gregrickaby.com), [Matt Mullenweg](https://ma.tt), [Mika Epstein](https://halfelf.org), [Mike Garrett](https://mikengarrett.com), [Samuel Wood](http://ottopress.com), [Scott Reilly](http://coffee2code.com), [Jan Dembowski](https://profiles.wordpress.org/jdembowski), [Jeff Starr](https://perishablepress.com), [Jeff Chandler](https://jeffc.me), [Jeff Matson](https://jeffmatson.net), [Jeremy Wagner](https://jeremywagner.me), [John James Jacoby](https://jjj.blog), [Leland Fiegel](https://leland.me), [Luke Cavanagh](https://github.com/lukecav), [Mike Jolley](https://mikejolley.com), [Pau Iglesias](https://pauiglesias.com), [Paul Irish](https://www.paulirish.com), [Rahul Bansal](https://profiles.wordpress.org/rahul286), [Roots](https://roots.io), [rtCamp](https://rtcamp.com), [Ryan Hellyer](https://geek.hellyer.kiwi), [WP Chat](https://wpchat.com), [WP Tavern](https://wptavern.com)

#### Disclaimer ####

We released this plugin in response to our managed hosting clients asking for better access to their server, and our primary goal will remain supporting that purpose. Although we are 100% open to fielding requests from the WordPress community, we kindly ask that you keep these conditions in mind, and refrain from slandering, threatening, or harassing our team members in order to get a feature added, or to otherwise get "free" support. The only place you should be contacting us is in our free [**Facebook group**](https://www.facebook.com/groups/littlebizzy/) which has been setup for this purpose, or via GitHub if you are an experienced developer. Thank you!

#### Our Philosophy ####

> "Decisions, not options." -- WordPress.org

> "Everything should be made as simple as possible, but not simpler." -- Albert Einstein, et al

> "Write programs that do one thing and do it well... write programs to work together." -- Doug McIlroy

> "The innovation that this industry talks about so much is bullshit. Anybody can innovate... 99% of it is 'Get the work done.' The real work is in the details." -- Linus Torvalds

#### Search Keywords ####

back up, back up database, back up db, backup, backup database, backup db, db, download, download database, download db, export, export database, export db, gzip, migrate, migrate database, migrate db, migration, my sql, mysql, site, site back up, site backup, site migration, sql, website, website back up, website backup, website migration, zip

== Installation ==

1. Upload to `/wp-content/plugins/export-database-littlebizzy`
2. Activate via WP Admin > Plugins
3. Navigate to WP Admin > Tools > Export Database

== Frequently Asked Questions ==

= How can I change this plugin's settings? =

There is a basic Tools page to "export" your database, no settings needed.

= I have a suggestion, how can I let you know? =

Please avoid leaving negative reviews in order to get a feature implemented. Instead, join our free Facebook group.

== Changelog ==

= 1.1.0 =
* tested with WP 5.0
* updated plugin meta

= 1.0.9 =
* updated plugin meta

= 1.0.8 =
* added warning for Multisite installations
* updated recommended plugins

= 1.0.7 =
* tested with WP 4.9
* added support for `DISABLE_NAG_NOTICES`

= 1.0.6 =
* optimized plugin code
* updated recommended plugins
* added rating request notice

= 1.0.5 =
* optimized plugin code

= 1.0.4 =
* updated recommended plugins

= 1.0.3 =
* added recommended plugins notice

= 1.0.2 =
* tested with WP 4.8

= 1.0.1 =
* updated plugin meta

= 1.0.0 =
* initial release
