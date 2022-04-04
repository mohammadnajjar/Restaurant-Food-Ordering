=== Advanced WordPress Reset ===
Contributors: symptote
Donate Link: http://www.sigmaplugin.com/donation
Tags: database, reset database, reset, clean, restore
Requires at least: 4.0
Tested up to: 5.9
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reset and restore your WordPress database to its first original status, just like if you make a fresh installation.

== Description ==

Notice: If you are looking for cleaning up your database and delete orphaned items, use instead our plugin: <a href="https://wordpress.org/plugins/advanced-database-cleaner" target="_blank">Advanced Database Cleaner</a>

'Advanced WordPress reset' plugin will reset and restore your WordPress Database to its first original status in order to make a fresh installation without going through WordPress traditional installation. You can also reset specific items by executing tools such as: clean up the "uploads" folder, delete all comments, remove all plugins, etc.

This plugin will help you save time especially if you are a developer and you have to install WordPress from scratch every time after testing some plugins/themes.

The plugin provides two main features categories:

= Main reset (Reset all) =
* Runs a new installation without going through the 5 minutes WordPress installation
* Resets the database without deleting or modifying any of your files (all your WordPress, plugins, and themes files are kept as they are without modifying them in any way)
* Deletes all database customizations made by plugins and themes
* Deletes all content including posts, pages, options, menus, etc.
* Detects the Admin user and recreates it with its saved password. If the Admin user does not exist, the current logged in user will be recreated with its current password with wp_user_level 10
* Keeps the blog name after the reset

= Custom reset tools =
* Clean up 'uploads' folder (/wp-content/uploads) by deleting all its content. This includes images, videos, music, documents, subfolders, etc.
* Delete all themes (the plugin uses WordPress core functions to delete themes). You have the possibility to keep the currently active theme or delete it as well
* Delete all plugins (the plugin will deactivate them first then uninstall them using WordPress core functions)
* Clean up "wp-content" folder. All files and folders inside '/wp-content' directory will be deleted, except 'index.php' and the following folders: 'plugins', 'themes', 'uploads' and 'mu-plugins'
* Delete Must-use plugins. All MU plugins in '/wp-content/mu-plugins' will be deleted. These are plugins that cannot be disabled except by removing their files from the must-use directory
* Delete ".htaccess" file. This is a critical WordPress core file used to enable or disable features of websites hosted on Apache. In some cases, you may need to delete it to do some tests
* Delete all comments. All types of comments will be deleted. Comments meta will also be deleted
* Delete pending comments. These are the comments that are awaiting moderation
* Delete spam comments
* Delete trashed comments. These are comments that you have deleted and sent to the Trash
* Delete pingbacks. Pingbacks allow you to notify other website owners that you have linked to their article on your website
* Delete trackbacks. Although there are some minor technical differences, a trackback is basically the same things as a pingback

The use of the plugin is quick, convenient, and safe. It is impossible to accidentally click on the reset buttons without your permission. You are always invited to confirm your actions.

= Multisite Support =
* The plugin does not support Multisite installation for now. We will add compatibility as soon as possible.

== Installation ==

This section describes how to install the plugin and get it working.

= Single site installation =
* After extraction, upload the Plugin to your `/wp-content/plugins/` directory
* Go to "Dashboard" &raquo; "Plugins" and choose 'Activate'
* The plugin page can be accessed via "Dashboard" &raquo; "Tools" &raquo; "Advanced WP reset"

== Screenshots ==

1. Reset all - main reset feature
2. Custom reset tools
2. You are invited to confirm the reset for all tools

== Changelog ==

= 1.5 - 23/02/2022 =
- New: feature to clean up 'uploads' folder
- New: feature to delete all themes
- New: feature to delete all plugins
- New: feature to clean up 'wp-content' folder
- New: feature to delete MU plugins
- New: feature to delete the '.htaccess' file
- New: feature to delete all comments
- New: feature to delete pending comments
- New: feature to delete spam comments
- New: feature to delete trashed comments
- New: feature to delete pingbacks
- New: feature to delete trackbacks
- Tweak: completely rewriting the JavaScript code
- Tweak: enhancing the CSS code
- Tweak: enhancing the PHP code
- Tested with WordPress 5.9

= 1.1.1 - 17/09/2020 =
- Tweak: enhancing the JavaScript code
- Tweak: we are now using SweetAlert for all popup boxes
- Tweak: enhancing some blocks of code
- Tested with WordPress 5.5

= 1.1.0 =
* Some changes to CSS style
* Changing a direct text to _e() for localization
* Test the plugin with WP 5.1

= 1.0.1 =
* The plugin is now Reactivated after the reset
* Adding "Successful Reset" message

= 1.0.0 =
* First release: Hello world!

== Frequently Asked Questions ==

= What does mean "reset my database"? =
This option will reset your WordPress database back to its first original status, just like if you make a new installation. That is to say, a clean installation without any content or customizations

= Is it safe to reset my database? =
Yes, it is safe since you have no important content to lose. If there are any issues, we will support you :)

= Are there any files that will be deleted after the reset? =
No. All files are kept as they are. The plugin does not delete or modify any of your files.

= Are there any plugins or themes that will be deleted after the reset? =
No. All your plugins and themes will be kept. However, you will lose any settings in the database of those plugins/themes.

= Is this plugin compatible with multisite? =
No, it is not compatible with multisite. We will try to fix this compatibility as soon as possible.

= Is this plugin compatible with SharDB, HyperDB, or Multi-DB? =
The plugin is not supposed to be compatible with SharDB, HyperDB, or Multi-DB for now.