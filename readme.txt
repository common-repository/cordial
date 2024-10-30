=== Plugin Name ===
Contributors: lostcoastweb
Donate link: http://lostcoastweb.com
Tags: comments, spam, cordial, moderation, discussion
Requires at least: 5.2.2
Tested up to: 5.2.2
Requires PHP: 5.6
Stable tag: 0.2.2
License: Apache 2.0
License URI: http://www.apache.org/licenses/
 
Use machine learing to automate comment moderation.   
 
== Description ==

## About
Cordial uses machine learning to automatically moderate your Wordpress comments.  Upon activation, Cordial will analyze and assign a "cordial score" to all comments made on your site.  Those that exceed a user-specified threshold will automatically be marked as "pending" on your site.  Note that Cordial differes from Akismet, whose focus is SPAM detetction, in that it analyzes post content for potentially aggressive or otherwise unwanted site content.  

## How it works 
When enabled, the Cordial Wordpress plugin will send all comments to the cordial server for analysis.  Each comment receives a score from 0-100 with a score of 0 being probably benign and 100 being probably offensive.  Depending on your site settings (see Dashboard -> Settings -> Cordial) comments meeting specific thresholds can be flagged as "pending" or "trash". 

## Examples
Below are some sample comments and their related Cordial score:
* (97) "This site sucks!"
* (91) "I hate you"
* (33) "Why are you being to terrible?"
* (19) "You don't make any sense"
* (12) "Your response makes no sense"

Try your own at [Cordial's Webiste](https://cordial-api.com)

## Disclaimer
Cordial is being actively developed by a small team and is in early beta.  If things don't work as expected, we would appreciate your help in resolving issues. Please leave feedback at https://gitlab.com/lcws/cordial-wp
 
== Installation ==
 
1. Upload the zipped folder to your plugins directory or install automatically through the Wordpress admin panel.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. From the admin dashboard, visit Settings -> Cordial to receive a Cordial API key and set thresholds for your site.
 
== Changelog ==

= 0.2.2 =
* Cordial scores now display for all comments processed by Cordial, not just those that were actually moderated by Cordial

= 0.2.1 =
* Bug fix related to receiving scores from server

= 0.2.0 =
* Comments admin page now displays the Coridal Score and gives Cordial-moderated comments a special background color
* Cordial-moderated comments that are overridden by an admin get sent to the Cordial API server in order to improve future comment analysis.

= 0.1.2 =
* UX enhancements to registration form.

= 0.1.0 =
* Initial public release
 
== Upgrade Notice ==
 
 
 