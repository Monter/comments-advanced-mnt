=== Comments-advanced ===
Contributors: webvitaly, Monter
Tags: comments, comment
Requires at least: 4.0
Tested up to: 4.7.3
Stable tag: 1.4.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html


Edit WordPress comment's info: post id, parent comment id, user id, author IP, date and author user agent.


== Description ==

Edit comment's info:

* post id (by drop-down list)
* parent comment id (by drop-down list)
* user id
* author IP
* comment date (with seconds)
* author User Agent


== Screenshots ==

1. comments meta-box v1.1
2. comments meta-box v1.2
3. comments meta-box v1.3
4. comments meta-box v1.42


== Changelog ==

= 1.4.2 - 2017-24-03 =
* added a hint for the current ID of the edit comment

= 1.4.1 - 2017-20-03 =
* add/repair prefix for WPDB Queries

= 1.4 - 2017-20-03 =
* change get_user & get_userdata WP functions to WPDB Query for reducing the number of database queries

= 1.3 - 2017-18-03 =
* replace WP functions to reducing the number of database queries
* parent_id from now is automatically reset when you move the comment to another post
* improve the readability of drop-down lists
* other minor fixes and improvements

= 1.2 - 2017-17-03 =
* remove "Post ID" input and conversion to the drop-down list of existing posts
* remove "Parent comment ID" input and conversion to the drop-down list of existing comments
* remove "User ID" input and conversion to the drop-down list of existing users
* added comment date input field (with seconds)
* added warning about manual reset parent id when you move the comment to another post
* other minor fixes and improvements

= 1.1 - 2012-14-05 =
* minor changes

= 1.0 =
* initial release


== Installation ==

1. install and activate the plugin on the Plugins page
2. go to edit comment -> info
