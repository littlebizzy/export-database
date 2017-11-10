=== Export Database (Backup & Download) ===

Contributors: littlebizzy
Tags: export, backup, migrate, database, db
Requires at least: 4.4
Tested up to: 4.8
Requires PHP: 7.0
Multisite support: No
Stable tag: 1.0.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: EXPDBS

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

== Description ==

Quickly and easily export your WordPress database with a single click for the purposes of migration, testing, or backup (in either SQL or ZIP format).

* [Plugin Homepage](https://www.littlebizzy.com/plugins/export-database-littlebizzy)
* [Plugin GitHub](https://github.com/littlebizzy/export-database-littlebizzy)
* [SlickStack.io](https://slickstack.io)

#### The Long Version ####

Export Database is a simple plugin that does one thing only: with a single click you are able to export your WordPress site's database exactly as it exists on the live site.

The first version does not offer any search/replace functions nor is it meant to. It is for developers who need a quick and easy way to either back up their database, migrate to a local or another site, or so forth, without interactive options.

In future versions we may add more options.

#### Compatibility ####

This plugin has been designed for use on LEMP (Nginx) web servers with PHP 7.0 and MySQL 5.7 to achieve best performance. All of our plugins are meant for single site WordPress installations only; for both performance and security reasons, we highly recommend against using WordPress Multisite for the vast majority of projects.

#### Plugin Features ####

* Settings Page: Yes
* Premium Version Available: No
* Includes Media (Images, Icons, Etc): No
* Includes CSS: No
* Database Storage: Yes
  * Transients: No
  * Options: Yes
  * Creates New Tables: No
* Database Queries: Backend Only (Options API Cache)
* Must-Use Support: Yes (Use With [Autoloader](https://github.com/littlebizzy/autoloader))
* Multisite Support: No
* Uninstalls Data: Yes

#### WP Admin Notices ####

This plugin generates multiple [Admin Notices](https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices) in the WP Admin dashboard. The first is a notice that fires during plugin activation which recommends several related free plugins that we believe will enhance this plugin's features; this notice will re-appear approximately once every 5 months as our code and recommendations evolve. The second is a notice that fires a few days after plugin activation which asks for a 5-star rating of this plugin on its WordPress.org profile page. This notice will re-appear approximately once every 8 months. These notices can be dismissed by clicking the **(x)** symbol in the upper right of the notice box. These notices may confuse certain users, but are appreciated by the majority of our userbase, who understand that these notices support our free contributions to the WordPress community. If you feel that these notices are too "annoying" than we encourage you to consider one or more of our upcoming premium plugins that combine several free plugin features into a single control panel. Another alternative would be to develop your own plugins for WordPress, if you feel that supporting free plugin authors is not something that interests you.

#### Code Inspiration ####

This plugin was partially inspired either in "code or concept" by the open-source software and discussions mentioned below:

* [WP Migrate DB](https://wordpress.org/plugins/wp-migrate-db/)

#### Recommended Plugins ####

We invite you to check out a few other related free plugins that our team has also produced that you may find especially useful:

* [404 To Homepage](https://wordpress.org/plugins/404-to-homepage-littlebizzy/)
* [Disable Author Pages](https://wordpress.org/plugins/disable-author-pages-littlebizzy/)
* [Disable Cart Fragments](https://wordpress.org/plugins/disable-cart-fragments-littlebizzy/)
* [Disable Embeds](https://wordpress.org/plugins/disable-embeds-littlebizzy/)
* [Disable Emojis](https://wordpress.org/plugins/disable-emojis-littlebizzy/)
* [Disable Empty Trash](https://wordpress.org/plugins/disable-empty-trash-littlebizzy/)
* [Disable Image Compression](https://wordpress.org/plugins/disable-image-compression-littlebizzy/)
* [Disable Search](https://wordpress.org/plugins/disable-search-littlebizzy/)
* [Disable WooCommerce Status](https://wordpress.org/plugins/disable-wc-status-littlebizzy/)
* [Disable WooCommerce Styles](https://wordpress.org/plugins/diable-wc-styles-littlebizzy/)
* [Disable XML-RPC](https://wordpress.org/plugins/disable-xml-rpc-littlebizzy/)
* [Download Media](https://wordpress.org/plugins/download-media-littlebizzy/)
* [Download Plugin](https://wordpress.org/plugins/download-plugin-littlebizzy/)
* [Download Theme](https://wordpress.org/plugins/download-theme-littlebizzy/)
* [Duplicate Post](https://wordpress.org/plugins/duplicate-post-littlebizzy/)
* [Export Database](https://wordpress.org/plugins/export-database-littlebizzy/)
* [Force HTTPS](https://wordpress.org/plugins/force-https-littlebizzy/)
* [Force Strong Hashing](https://wordpress.org/plugins/force-strong-hashing-littlebizzy/)
* [Google Analytics](https://wordpress.org/plugins/ga-littlebizzy/)
* [Index Autoload](https://wordpress.org/plugins/index-autoload-littlebizzy/)
* [Maintenance Mode](https://wordpress.org/plugins/maintenance-mode-littlebizzy/)
* [Profile Change Alerts](https://wordpress.org/plugins/profile-change-alerts-littlebizzy/)
* [Remove Category Base](https://wordpress.org/plugins/remove-category-base-littlebizzy/)
* [Remove Query Strings](https://wordpress.org/plugins/remove-query-strings-littlebizzy/)
* [Server Status](https://wordpress.org/plugins/server-status-littlebizzy/)
* [StatCounter](https://wordpress.org/plugins/sc-littlebizzy/)
* [View Defined Constants](https://wordpress.org/plugins/view-defined-constants-littlebizzy/)
* [Virtual Robots.txt](https://wordpress.org/plugins/virtual-robotstxt-littlebizzy/)

#### Special Thanks ####

We thank the following groups for their generous contributions to the WordPress community which have particularly benefited us in developing our own free plugins and paid services:

* Matt Mullenweg, Mika Epstein, Samuel Wood, Scott Reilly, Pippin Williamson, Jeff Starr, Jeff Chandler, Leland Fiegel
* [Automattic](https://automattic.com)
* [Delicious Brains](https://deliciousbrains.com)
* [Roots](https://roots.io)
* [rtCamp](https://rtcamp.com)
* [WP Tavern](https://wptavern.com)

#### Disclaimer ####

We released this plugin in response to our managed hosting clients asking for better access to their server, and our primary goal will remain supporting that purpose. Although we are 100% open to fielding requests from the WordPress community, we kindly ask that you keep the above mentioned goals in mind, thanks!

== Installation ==

1. Upload to `/wp-content/plugins/export-database-littlebizzy`
2. Activate via WP Admin > Plugins
3. Navigate to WP Admin > Tools > Export Database

== FAQ ==

= How can I change this plugin's settings? =

There is a settings page to "export" your database, no settings needed.

= I have a suggestion, how can I let you know? =

Please avoid leaving negative reviews in order to get a feature implemented. Instead, we kindly ask that you post your feedback on the wordpress.org support forums by tagging this plugin in your post. If needed, you may also contact our homepage.

== Changelog ==

= 1.0.6 =
* optimized plugin code
* updated recommended plugins
* added rating request

= 1.0.5 =
* minor code tweaks

= 1.0.4 =
* updated recommended plugins

= 1.0.3 =
* added recommended plugins

= 1.0.2 =
* tested with WordPress 4.8

= 1.0.1 =
* updated plugin meta

= 1.0.0 =
* initial release
