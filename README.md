=== Google analytics Top post ===
Contributors: asentech, tranpura, bhupendraspatil, hsurekar
Author URI: https://www.asentechllc.com
Tags: GA, recent posts, latest articles, latest posts, most read, top posts, most read widget, most read short code
Requires at least: 4.9
Tested up to: 5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.3+


Display most read articles from fetching google analytics API

== Description ==

The [google-analytics-top-posts] is an open-source initiative aiming to fetch most read articles from google analytics. Most read articles will display real time top 5 articles after fetching them from GA and display them in your page, post, and sidebar using shortcode.

Features and capabilities provided by the plugin include:
- We can use shortcode to display top articles with dynamic attributes.
- We can use widget to display top articles with dynamic attributes.
- It will sync every week and also we have provided manually sync option.
- Settings of GA details.

== Installation Steps ==
1. Upload the folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.


== Configuration ==
1. Go to https://cloud.google.com/console Open the project, go to APIs & auth > Registered apps on the left.
2. Click on Register App, enter name and choose platform (for e.g. Web Application).
3. On the next page, open Certificate and click on Generate Certificate and download the .p12 private key.
4. Add profile ID
5. Add Site URL
6. Upload Key File
7. Service email which we can get from google analytics
8. Exclude URLs from fetched result from GA like home page.

== Screenshots ==
![Alt text](./screenshot-1.png?raw=true "Sidebar Widget")
![Alt text](./screenshot-2.png?raw=true "Settings Page")
