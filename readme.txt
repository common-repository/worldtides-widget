=== Worldtides Widget ===
Contributors:       Brainware LLC
Donate link:        https://www.worldtides.info
Tags:               tides, tides widget, tides plugin, tide predictions, ocean tide, surf report, costal tides
Requires at least:  4.0
Tested up to:       6.4
Stable tag:         1.4
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html



== Description ==

This widget is perfect for anyone who wants accurate, low-cost tide predictions on their website. WorldTides.info provides tide predictions at consumer-level prices. Globally, WorldTides.info is the leading provider of tide predictions through an API.

= API Key =
You can get an API Key from worldtides.info and use it for one month (100 credits) for free. Because it is optimized, it only requires two credits per one-day or 7-day prediction. Use your 100 free credits wisely because you'll only get 50 predictions. After that, you can buy 10,000 credits (5,000 predictions) for $10; using more credits reduces the cost. Check this page before you use this plugin. [Pricing](https://www.worldtides.info/developer)

>If you have any questions or suggestions for improvements, please email support at support@brainware.net.
>[Support](https://wordpress.org/support/plugin/worldtides-widget/)

WorldTides™ Widget Features
* Automatically find the nearest tide stations based on your latitude and longitude.
* Specifying a timezone allows you to customize graphs, and the station timezone is usually the best choice.
* The graph takes daylight saving time into account.
* AM/PM and 24-hour time formats.
* Three graph heights are available, or you can disable them.
* Predict high/low tides for up to 7 days.
* Enable/disable the fine grid for the graph.
* Select the background color.

= TIDE ICONS =
Icons for WorldTides™ are exclusive to this app. Each display shows 1 to 5 lines, depending on the tide level.

= ONE TO SEVEN DAY PREDICTIONS =
The prediction is available for 1 or 7 days, and no cost difference exists between these options.

= PLACEMENT IN CONTENT OR SIDEBAR =
You can place the WorldTides™ widget in a sidebar or footer. Additionally, you can use a shortcode to embed the widget in an article.

= ANY LOCATION WORLDWIDE =
Thhere are thousands of coastal stations worldwide, and when those are unavailable, we use satellite data. Tide predictions are not possible overland or along rivers that are too far from the coast.

= UNITS =
You can display tide heights in feet or meters. Set the time format to AM/PM or 24-hour format.

= MULTIPLE WIDGETS OPTION =
A page can have as many widgets as you want.

= Tags =
tides, tides widget, tides plugin, tide predictions, tide forcast, ocean tide, surf report, costal tides


== Installation ==

= From within your Admin panel =
1. Visit "Plugins > Add New"
1. Search for "Worldtides Widget"
1. Activate the "Worldtides Widget" through the "Plugins" menu in Admin Panel

= or Manually =
1. Download the "worldtides" zip (from https://wordpress.org/plugins/worldtides/) and unzip
1. Upload the "worldtides" folder to the "/wp-content/plugins/" directory using FTP client
1. Activate the "Worldtides Widget" through the "Plugins" menu in Admin Panel

= Activating =
1. Go to "Appearance > Widgets" in Admin panel
1. Under "Available widgets" section, find "Worldtides Widget" and "Add Widget"
1. Create an account at worldtides.info and get your API Key from the Settings page.
1. You can also get the Latitude/Longitude from the home page.
1. Enter in your API Key and other settings under the Widget settings area.

= Shortcode =
To add this widget on a page, just add a short code with attributes matching the fields in the widget editor. For example:

	[shortcode-worldtides key="XXXX-XXXX-XXXX-XXXX" latitude="22.319" longitude="114.169" graph="medium"]

(of course, replace the key with the one you get at worldtides.info)

Here are the names of the attributes and values you can use:

	key - API Key
	latitude - Latitude (ex. 33.1)
	longitude - Longitude (ex. -118.1)
	timezone - (e.g. "America/Los_Angeles")
	units - "meters", "feet" (') or "altfeet" (ft)
	days - 1 to 7
	timemode - "hour24" or "ampm"
	graphmode - "none", "short", "medium", "tall"
	finegrid - "on", "off"
	bgcolor - any CSS color (e.g. "#FFFFFF", "red", "rgba(255,255,255,64)", etc...)
	dotcolor - any CSS color

== Changelog ==
= 1.0.0 =
*Release Date: 2019-10-02*
* Initial release of the plugin.

= 1.1.0 =
* Fixed findgrid shortcode attribute

= 1.2.0 =
* Added dot color option and alternate feet symbol