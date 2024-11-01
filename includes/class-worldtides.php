<?php

/**
 * The file that defines the core plugin class
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.worldtides.info
 * @package    WorldTides
 * @subpackage WorldTides/includes
 */

// This sets the option to cache calls to worldtides.info
// so you don't spend extra credits when a page is reloaded
define('WORLDTIDES_CACHE_API_CALLS', TRUE);

// This turns on HTML5 Canvas drawing which is far better than 
// drawing the graph in php.
define('WORLDTIDES_USE_HTML5_CANVAS', TRUE);

// These are just default values for the plug-in. Feel free to change these.
define('WORLDTIDES_DEFAULT_LAT', '33.752');
define('WORLDTIDES_DEFAULT_LON', '-118.227');
define('WORLDTIDES_DEFAULT_TIMEZONE', 'America/Los_Angeles');
define('WORLDTIDES_DEFAULT_UNITS', 'meters');
define('WORLDTIDES_DEFAULT_DAYS', 3);
define('WORLDTIDES_DEFAULT_TIMEMODE', 'hour24');
define('WORLDTIDES_DEFAULT_GRAPHMODE', 'medium');
define('WORLDTIDES_DEFAULT_FINEGRID', 'off');
define('WORLDTIDES_DEFAULT_BGCOLOR', '#446CA6');
define('WORLDTIDES_DEFAULT_DOTCOLOR', '#FF0000');

// This sets the number of hours in between each vertical line on the graph
define('WORLDTIDES_HOURS_PER_LINE', 4);

define('WORLDTIDES_DRAW_FINE_GRID', FALSE);

// These are used to set the short/medium/tall heights of the graph
// Feel free to change these if you like.
define('WORLDTIDES_SHORT_PLOT_IMAGE_HEIGHT', 150);
define('WORLDTIDES_MEDIUM_PLOT_IMAGE_HEIGHT', 225);
define('WORLDTIDES_TALL_PLOT_IMAGE_HEIGHT', 300);

// These are only used for the php graph drawing routine.
// You shouldn't have a need to change these unless you turn off WORLDTIDES_USE_HTML5_CANVAS
define('WORLDTIDES_SCALE_FACTOR', 3);
define('WORLDTIDES_PLOT_IMAGE_WIDTH', 280*WORLDTIDES_SCALE_FACTOR);
define('WORLDTIDES_PLOT_GRAPH_OFFSET_X', 20*WORLDTIDES_SCALE_FACTOR);
define('WORLDTIDES_PLOT_GRAPH_OFFSET_Y', 20*WORLDTIDES_SCALE_FACTOR);
define('WORLDTIDES_PLOT_FONT_SIZE', 7*WORLDTIDES_SCALE_FACTOR);

// The following are used for debugging the code. 
// Don't mess with these unless you're just testing.
define('WORLDTIDES_OVERRIDE_DATE', FALSE); // for test purposes
// define('WORLDTIDES_OVERRIDE_DATE', '2019-03-31'); // british daylight saving start
// define('WORLDTIDES_OVERRIDE_DATE', '2019-10-27'); // british daylight saving end

// This tells the server that the wordpress plugin is making API calls.
// You can change this to your server's IP Address and also change the
// the corrisponding option at worldtides.info under your settings in order
// to help protect your API Key from being used on another website.
// But, since your key is only used server-to-server, it is hidden.
define('WORLDTIDES_ORIGIN', 'WordPressPlugin_v1.0');

class WorldTides extends WP_Widget {
	protected $loader;
	protected $plugin_name;
	protected $version;
	
	// Load the dependencies, define the locale, and set the hooks for the admin area and
	// the public-facing side of the site.
	public function __construct() {
		$this->plugin_name = 'worldtides';
		$this->version     = '1.0.0';
		$widget_ops = array ('description' => 'Display tide predictions on your website.');
		parent::__construct(FALSE, 'WorldTides Widget', $widget_ops);
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	// User activates the plugin
	static function activate() {
		// nothing to do here
	}

	// Delete transients from the database when this plugin is deleted
	static function deactivate() {
		global $wpdb; // This is the handle to the database class
		$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE '%worldtides_transient_%'");
	}

	static function register_widget() {
		register_widget(__CLASS__);
	}

	//-----------------------	
	// Widget API Functions
	//-----------------------

	// Widget contents
	function widget($args, $instance) {
		$key       = isset($instance['key']) ? $instance['key'] : '';
		$latitude  = isset($instance['latitude']) ? $instance['latitude'] : WORLDTIDES_DEFAULT_LAT;
		$longitude = isset($instance['longitude']) ? $instance['longitude'] : WORLDTIDES_DEFAULT_LON;
		$timezone  = isset($instance['timezone']) ? $instance['timezone'] : WORLDTIDES_DEFAULT_TIMEZONE;
		$units     = isset($instance['units']) ? $instance['units'] : WORLDTIDES_DEFAULT_UNITS;
		$days      = isset($instance['days']) ? $instance['days'] : WORLDTIDES_DEFAULT_DAYS;
		$timemode  = isset($instance['timemode']) ? $instance['timemode'] : WORLDTIDES_DEFAULT_TIMEMODE;
		$graphmode = isset($instance['graphmode']) ? $instance['graphmode'] : WORLDTIDES_DEFAULT_GRAPHMODE;
		$finegrid  = isset($instance['finegrid']) ? $instance['finegrid'] : WORLDTIDES_DEFAULT_FINEGRID;
		$bgcolor   = isset($instance['bgcolor']) ? $instance['bgcolor'] : WORLDTIDES_DEFAULT_BGCOLOR;
		$dotcolor   = isset($instance['dotcolor']) ? $instance['dotcolor'] : WORLDTIDES_DEFAULT_DOTCOLOR;
		
		echo $args['before_widget'];
		echo $args['before_title'];
		if (!empty($args['heading'])) {
			echo apply_filters('widget_title', $args['heading']);
		}
		echo $args['after_title'];
		echo WorldTides::widgetHtml(array(
			'key' => $key,
	    	'latitude' => $latitude,
	    	'longitude' => $longitude,
	    	'timezone' => $timezone,
	    	'units' => $units,
	    	'days' => $days,
	    	'timemode' => $timemode,
	    	'graphmode' => $graphmode,
	    	'finegrid' => $finegrid,
	    	'bgcolor' => $bgcolor,
	    	'dotcolor' => $dotcolor,
	    ));
		echo $args['after_widget'];
	}
	
	 // Grab the user's choices
	function update($new_instance, $old_instance) {
		$instance              = $old_instance;
		$instance['key']       = strip_tags($new_instance['key']);
		$instance['latitude']  = strip_tags($new_instance['latitude']);
		$instance['longitude'] = strip_tags($new_instance['longitude']);
		$instance['timezone']  = strip_tags($new_instance['timezone']);
		$instance['units']     = strip_tags($new_instance['units']);
		$instance['days']      = strip_tags($new_instance['days']);
		$instance['timemode']  = strip_tags($new_instance['timemode']);
		$instance['graphmode'] = strip_tags($new_instance['graphmode']);
		$instance['finegrid'] = strip_tags($new_instance['finegrid']);
		$instance['bgcolor']  = strip_tags($new_instance['bgcolor']);
		$instance['dotcolor']  = strip_tags($new_instance['dotcolor']);
		return $instance;
	}

	// Helper
	static function selectHtml($id, $name, $options, $selected = NULL, $useValuesAsKeys = FALSE) {
		$output = "<select class='widefat' id='$id' name='$name' type='text'>";
		foreach ($options as $key => $value) {
			if ($useValuesAsKeys) {
				$key = $value;
			}
			$output .= '<option value="'.$key.'"'.selected($selected, $key, FALSE).'>'.esc_html($value).'</option>';
		}
		$output .= '</select>';
		return $output;
	}

	// Admin form	
	function form($instance) {
		$key       = isset($instance['key']) ? $instance['key'] : '';
		$latitude  = isset($instance['latitude']) ? $instance['latitude'] : WORLDTIDES_DEFAULT_LAT;
		$longitude = isset($instance['longitude']) ? $instance['longitude'] : WORLDTIDES_DEFAULT_LON;
		$timezone  = isset($instance['timezone']) ? $instance['timezone'] : WORLDTIDES_DEFAULT_TIMEZONE;
		$units     = isset($instance['units']) ? $instance['units'] : WORLDTIDES_DEFAULT_UNITS;
		$days      = isset($instance['days']) ? $instance['days'] : WORLDTIDES_DEFAULT_DAYS;
		$timemode  = isset($instance['timemode']) ? $instance['timemode'] : WORLDTIDES_DEFAULT_TIMEMODE;
		$graphmode = isset($instance['graphmode']) ? $instance['graphmode'] : WORLDTIDES_DEFAULT_GRAPHMODE;
		$finegrid = isset($instance['finegrid']) ? $instance['finegrid'] : WORLDTIDES_DEFAULT_FINEGRID;
		$bgcolor  = isset($instance['bgcolor']) ? $instance['bgcolor'] : WORLDTIDES_DEFAULT_BGCOLOR;
		$dotcolor  = isset($instance['dotcolor']) ? $instance['dotcolor'] : WORLDTIDES_DEFAULT_DOTCOLOR;

		echo '
			<h3 style="margin:1.5em 0 0 0">Settings</h3>
			<i>Note: Go to <a href="https://www.worldtides.info" target="_blank">worldtides.info</a> to get API Key, Latitude, Longitude, and Time Zone.</i><br><br>
			<label for="'.$this->get_field_id('key').'">API Key</label>
			<input class="widefat" id="'.$this->get_field_id('key').'" name="'.$this->get_field_name('key').'" type="text" value="'.$key.'" placeholder="(get your key from worldtides.info)">
			<br/><br/>
			<label for="'.$this->get_field_id('latitude').'">Latitude</label>
			<input class="widefat" id="'.$this->get_field_id('latitude').'" name="'.$this->get_field_name('latitude').'" type="text" value="'.$latitude.'">
			<br/><br/>
			<label for="'.$this->get_field_id('longitude').'">Longitude</label>
			<input class="widefat" id="'.$this->get_field_id('longitude').'" name="'.$this->get_field_name('longitude').'" type="text" value="'.$longitude.'">
			<br/><br/>
			<label for="'.$this->get_field_id('timezone').'">Time Zone</label>
			'.WorldTides::selectHtml($this->get_field_id('timezone'), $this->get_field_name('timezone'), DateTimeZone::listIdentifiers(), $timezone, TRUE).'
			<br/><br/>
			<label for="'.$this->get_field_id('units').'">Units</label>
			'.WorldTides::selectHtml($this->get_field_id('units'), $this->get_field_name('units'), array (
				'meters' => 'Meters (m)',
				'feet' => 'Feet (\')',
				'altFeet' => 'Feet (ft)',
			), $units).'
			<br/><br/>
			<label for="'.$this->get_field_id('timemode').'">Time Display</label>
			'.WorldTides::selectHtml(
				$this->get_field_id('timemode'),
				$this->get_field_name('timemode'),
				array (
					'hour24' => '24 Hour',
					'ampm' => 'AM/PM',
				), 
				$timemode
			).'
			<br/><br/>
			<label for="'.$this->get_field_id('graphmode').'">Show Graph</label>
			'.WorldTides::selectHtml(
				$this->get_field_id('graphmode'),
				$this->get_field_name('graphmode'),
				array (
					'none' => 'None',
					'short' => 'Short',
					'medium' => 'Medium',
					'tall' => 'Tall',
				), 
				$graphmode
			).'
			<br/><br/>
			<label for="'.$this->get_field_id('finegrid').'">Fine Grid</label>
			'.WorldTides::selectHtml(
				$this->get_field_id('finegrid'),
				$this->get_field_name('finegrid'),
				array (
					'on' => 'On',
					'off' => 'Off',
				), 
				$finegrid
			).'
			<br/><br/>
			<label for="'.$this->get_field_id('days').'">Days</label>
			'.WorldTides::selectHtml(
				$this->get_field_id('days'), 
				$this->get_field_name('days'), 
				array (
					'1' => '1 days',
					'2' => '2 days',
					'3' => '3 days',
					'4' => '4 days',
					'5' => '5 days',
					'6' => '6 days',
					'7' => '7 days',
				), 
				$days
			).'
			<br/><br/>
			<p>
			<label for="'.$this->get_field_id('bgcolor').'">
			Background Color
			</label><br />
			<input class="widefat color_picker" data-alpha="TRUE" id="'.$this->get_field_name('bgcolor').'" name="'.$this->get_field_name('bgcolor'). '" type="colorpicker" value="'.$bgcolor.'">
			<br />
			</p>
			<p>
			<label for="'.$this->get_field_id('dotcolor').'">
			Dot Color
			</label><br />
			<input class="widefat color_picker" data-alpha="TRUE" id="'.$this->get_field_name('dotcolor').'" name="'.$this->get_field_name('dotcolor'). '" type="colorpicker" value="'.$dotcolor.'">
			<br />
			</p>
			<hr/>
			<br/>';
	}

	static function widgetHtml($attributes) {
		$out = '';

		// Get Settings	
		$key       = isset($attributes['key']) ? $attributes['key'] : '';
		$latitude  = isset($attributes['latitude']) ? $attributes['latitude'] : WORLDTIDES_DEFAULT_LAT;
		$longitude = isset($attributes['longitude']) ? $attributes['longitude'] : WORLDTIDES_DEFAULT_LON;
		$timezone  = isset($attributes['timezone']) ? $attributes['timezone'] : WORLDTIDES_DEFAULT_TIMEZONE;
		$units     = isset($attributes['units']) ? $attributes['units'] : WORLDTIDES_DEFAULT_UNITS;
		$days      = isset($attributes['days']) ? $attributes['days'] : WORLDTIDES_DEFAULT_DAYS;
		$timemode  = isset($attributes['timemode']) ? $attributes['timemode'] : WORLDTIDES_DEFAULT_TIMEMODE;
		$graphmode = isset($attributes['graphmode']) ? $attributes['graphmode'] : WORLDTIDES_DEFAULT_GRAPHMODE;
		$finegrid  = isset($attributes['finegrid']) ? $attributes['finegrid'] : WORLDTIDES_DEFAULT_FINEGRID;
		$bgcolor   = isset($attributes['bgcolor']) ? $attributes['bgcolor'] : WORLDTIDES_DEFAULT_BGCOLOR;
		$dotcolor   = isset($attributes['dotcolor']) ? $attributes['dotcolor'] : WORLDTIDES_DEFAULT_DOTCOLOR;

		switch ($units) {
			default:
			case 'meters':  $unitSymbol = 'm';  $factor = 1; break;
			case 'feet':    $unitSymbol = '\''; $factor = 3.28084; break;
			case 'altFeet': $unitSymbol = ' ft'; $factor = 3.28084; break;
		}

		// fix values as necessary
		$days = (int)$days;
		$days = max(1, $days);
		$days = min($days, 7);

		$dateTimeZone = new DateTimeZone($timezone);
		$dateTime = new DateTime('now', $dateTimeZone);
		if (WORLDTIDES_OVERRIDE_DATE) {
			$dateTime = DateTime::createFromFormat('Y-m-d', WORLDTIDES_OVERRIDE_DATE);
		}
		$dateTime->setTime(0, 0, 0);
		$startTimestamp = $dateTime->getTimeStamp();
		$startDate = $dateTime->format('Y-m-d');

		// Get extremes (grab an extra day before and after)
		list($error, $extremesData) = WorldTides::getExtremes($key, $startTimestamp, $latitude, $longitude, $days);
		if (!$error) {
			list($error, $heightsData) = WorldTides::getHeights($key, $dateTime->getTimeStamp(), $latitude, $longitude, 1);
		}
		if (!$error) {
			// convert data so the dates are in local time and in a format we want to display
			$timeFormat = $timemode == 'ampm' ? 'g:i a' : 'H:i';
			$extremes = array();
			foreach ($extremesData['extremes'] as $v) {
				$dateTime->setTimestamp($v['dt']);
				$dateString = $dateTime->format('Y-m-d');
				if (strcmp($dateString, $startDate) >= 0) {
					$extremes[] = array(
						'height'=>$v['height']*$factor,
						'type'=>$v['type'],
						'date'=>$dateString,
						'time'=>$dateTime->format($timeFormat)
					);
				}
			}

			// The heights are used to plot a graph and to find the tide level for a particular timestamp
			$heights = array();
			$dateTime->setTimestamp($heightsData['heights'][0]['dt']);
			$lastYmd = $dateTime->format('Ymd');
			$day = 0; 
			foreach ($heightsData['heights'] as $v) {
				$dateTime->setTimestamp($v['dt']);
				$ymd = $dateTime->format('Ymd');
				if ($ymd != $lastYmd) {
					$day++;
				}
				// The clockTime isn't always the smae as the number of seconds from the beginning of the day.
				// Instead, it represents the time you would see on a clock as it it gets adjusted when 
				// daylight saving time occurs. By using the clockTime as a key to an array, it allows
				// the values to be overwritten when the clocks are turned back. It also skips a time period
				// when the clocks are turned forward. The end result is an array which either has sequential
				// seconds OR an array which has a gap of an hour. Therefore, when using this to plot a graph,
				// you will see a flat spot or an abrupt change when daylight saving time changes.
				// The dateTime format function automatically deals with the daylight saving time because
				// it knows what the xxx/xxx timezone is. The wacky $day constant is used mostly for the 
				// last point on the graph at midnight but it would also help manage the case if we were to
				// have a graph with multiple days.
				$clockTime = 86400*$day + 3600*$dateTime->format('H') + 60*$dateTime->format('i');
				if ($clockTime >= 0 && $clockTime <= 86400) {
					$heights[$clockTime] = array(
						'height'=>$v['height']*$factor,
						'clockTime'=>$clockTime, // used for drawing the graph
						'timestamp'=>$v['dt'], // used for finding the tide height based on the current timestamp
						'date'=>$dateTime->format('Y-m-d'),
						'dayOfWeek'=>$dateTime->format('D'),
						'month'=>$dateTime->format('M'),
						'day'=>$dateTime->format('j'),
						'hour'=>(int)$dateTime->format('G'),
						'minute'=>(int)$dateTime->format('i'),
						'ampm'=>$dateTime->format('a'),
						'timezone'=>$dateTime->format('T')
					);
				}
			}
			// Remove the keys since we don't need them anymore
			$heights = array_values($heights);

			// Get the max, min, and chart datums
			$maxTide = 10;
			$minTide = 0;
			foreach ($extremesData['datums'] as $datum) {
				switch ($datum['name']) {
					case 'LAT': $minTide = $datum['height']*$factor; break;
					case 'HAT': $maxTide = $datum['height']*$factor; break;
				}
			}

			// If we received a station, use that for the title. If not, use the lat/lon response coordinates.
			if (isset($extremesData['station'])) {
				$title = $extremesData['station'];
			} else {
				$title = $extremesData['responseLat'].', '.$extremesData['responseLon'];
			}

			// Using Javascript has the added benefit of allowing the graph to be redrawn as the 
			// browser is resized. It also gives us a way to draw a red dot on the graph and update the
			// page so the user sees the current tide height and time without having to refresh the page.
			// This takes advantage of HTML5 canvas routines which should be supported by all browers nowadays.
			if (WORLDTIDES_USE_HTML5_CANVAS) {
				$instance = md5(rand().time());

				$plotHtml = '';
				if ($graphmode != 'none') {
					switch ($graphmode) {
						case 'short': $graphHeight = WORLDTIDES_SHORT_PLOT_IMAGE_HEIGHT; break;
						case 'medium': $graphHeight = WORLDTIDES_MEDIUM_PLOT_IMAGE_HEIGHT; break;
						case 'tall': $graphHeight = WORLDTIDES_TALL_PLOT_IMAGE_HEIGHT; break;
						default: $graphHeight = WORLDTIDES_MEDIUM_PLOT_IMAGE_HEIGHT; break;
					}
					$plotHtml = '
							<div id=\'worldtides-plot-container-'.$instance.'\'>
								<canvas id=\'worldtides-plot-'.$instance.'\' style=\'width:100%; border-radius:10px; height:'.$graphHeight.'px\' ></canvas>
							</div>';					
				}

				$globals = array(
					'overrideDate'=>WORLDTIDES_OVERRIDE_DATE,
					'minTide'=>$minTide,
					'maxTide'=>$maxTide,
					'days'=>$days,
					'timemode'=>$timemode,
					'graphmode'=>$graphmode,
					'finegrid'=>$finegrid == 'on' ? TRUE : FALSE,
					'unitSymbol'=>$unitSymbol,
					'extremes'=>$extremes,
					'heights'=>$heights,
					'heights_count'=>count($heights),
					'dotcolor'=>$dotcolor,
				);

				$out .= '
					<div class=\'worldtides-wrapper\' id="worldtides-wrapper" style="background:'.$bgcolor.'" >
						<div class=\'worldtides-title\'>
							'.htmlspecialchars($title).'
						</div>
						<div class=\'worldtides-body\'>
							<div class="worldtides-height-and-time" id="worldtides-height-and-time-'.$instance.'">
							</div>
							'.$plotHtml.'
			              	<table class=\'worldtides-tide-forecast\' id=\'worldtides-tide-forecast-'.$instance.'\' >
			              	</table>
						</div>
					</div>

				<script>
					if (typeof worldtides_globals === "undefined") {
						worldtides_globals = [];
					}
					worldtides_globals["'.$instance.'"] = '.json_encode($globals).';
					// Wait until the document is ready before drawing the graph so we get the right size
					// Also, set up the code to refresh the data as needed
					(
						function($) {
							"use strict";
							$(document).ready(function($) {

								// Create the widget
								worldtides_refresh("'.$instance.'");

								// Re-create the widget if the window changes size
								window.addEventListener("resize", worldtides_resize_'.$instance.');

								// Update the tide info every second
								// and update the graph once a minute.
								setInterval(function() {
									var info = worldtides_getTideInfo("'.$instance.'");
									worldtides_updateTideInfo("'.$instance.'", info["icon"], info["heightText"], info["dateText"]);

									// only update the table once a minute
									if (worldtides_graphmode != "none" && info["seconds"] % 60 == 0) {
										worldtides_drawPlot("'.$instance.'", info["seconds"], info["height"]);
									}
								}, 50000);

							});
						}
					)(jQuery);

					function worldtides_resize_'.$instance.'() {
						worldtides_refresh("'.$instance.'");
					}

				</script>
				';
			} else {
				// The following is a php version of the JavaScript code. It basically does the same thing but on the server instead.
				// The one omission is the red dot on the graph and the lack of updates as time advances. This is not recommended
				// but left here as a fall back in case there is a reason to use it.
				if ($graphmode != 'none') {
					switch ($graphmode) {
						case 'short': $graphHeight = 2*WORLDTIDES_SHORT_PLOT_IMAGE_HEIGHT; break;
						case 'medium': $graphHeight = 2*WORLDTIDES_MEDIUM_PLOT_IMAGE_HEIGHT; break;
						case 'tall': $graphHeight = 2*WORLDTIDES_TALL_PLOT_IMAGE_HEIGHT; break;
					}
					$phpImage = WorldTides::createPlotImage($heights, $maxTide, $minTide, $units, $timemode, WORLDTIDES_PLOT_IMAGE_WIDTH, $graphHeight, $finegrid=='on');
					ob_start();
					imagepng($phpImage);
					$imageBinary = ob_get_clean();
					$imageHtml = '<img src="data:image/png;base64,'.base64_encode($imageBinary).'" style="border-radius:5px">';
				} else {
					$imageHtml = '';
				}

				$dateTime = new DateTime('now', $dateTimeZone);
				if (WORLDTIDES_OVERRIDE_DATE) {
					$dateTime = DateTime::createFromFormat('Y-m-d', WORLDTIDES_OVERRIDE_DATE, $dateTimeZone);
				}
				$time = $dateTime->getTimestamp();
				$startDate = $dateTime->format('Y-m-d');
				$endDateTime = new DateTime('+'.($days-1).' days', $dateTimeZone);
				$endDate = $endDateTime->format('Y-m-d');
				$dateText = $timemode == 'ampm' ? $dateTime->format('D M d g:ia T') : $dateTime->format('D M d H:i T');

				// Find the index of the samples closest the actual time of day
				$heights = $heightsData['heights'];
				$index = 0;
				for ($i=1; $i<count($heights); $i++) {
					$h = $heights[$i];
					if ($time <= $h['dt']) {
						$index = $i;
						break;
					}
				}

				// Interpolate between the sample points to get the best estimate of the tide height
				$heightText = '?';
				if ($index >= 1 && $index < count($heights)) {
					$h1 = $heights[$index-1];
					$h2 = $heights[$index];
					$height1 = $h1['height'];
					$height2 = $h2['height'];
					$time1 = $h1['dt'];
					$time2 = $h2['dt'];
					$ratio = ($time - $time2)/($time2 - $time1);
					$height = $ratio*($height2 - $height1) + $height1;
					$n = round(5 * ($height - $minTide)/($maxTide - $minTide));
					$n = min($n, 5);
					$n = max(0, $n);
					$icon = 'worldtides-tide-'.$n;
					$heightText = sprintf('%.1f', $height).$unitSymbol;
				}

				$out .= '
					<div class="worldtides-wrapper">
						<div class="worldtides-title">
							'.htmlspecialchars($title).'
						</div>
						<div class="worldtides-body">
							<div style="font-size:52px; line-height:52px"><span class="worldtides '.$icon.'">'.$heightText.'</div>
							<div style="font-size:24px">'.$dateText.'</div>
							'.$imageHtml.'
							<table class="worldtides-tide-forecast">';


				$numDays = 0;
				$lastDate = '';
				$dateTime = new DateTime();
				$dateTime->setTimezone($dateTimeZone);
				foreach ($extremes as $extreme) {
					$date = $extreme['date'];
					$time = $extreme['time'];
					$height = $extreme['height'];
					$type = $extreme['type'];
					$n = round(5 * ($height - $minTide)/($maxTide - $minTide));
					$n = min($n, 5);
					$n = max(0, $n);

					// Use the custom worldtides icon to show the relative height of the tide.
					// This makes the durnal high and low tide more meanful.
					// See https://en.wikipedia.org/wiki/Tide
					$icon = '<span class="worldtides worldtides-tide-'.$n.'"></span>';
					if ($date != $lastDate) {
						$numDays++;
						if ($numDays > $days) {
							break;
						}
						$lastDate = $date;
						$out .= '
								<tr><th colspan="3">'.$date.'</th></tr>';
					}
					$h = sprintf('%.1f%s', $height, $unitSymbol);
					$out .= '
								<tr><td>'.$icon.' '.$type.'</td><td>'.$time.'</td><td>'.$h.'</td></tr>';
				}
				$out .= '
							</table>';

				$out .= '
						</div>
					</div>';
			}
		}
		if ($error) {
			$out .= '<div>'.htmlspecialchars($error).'</div>';
		}

		return $out;
	}

	// Get the extreme tides times and values (high tides and low tides)
	static function getExtremes($key, $timestamp, $latitude, $longitude, $days) {
		$error = FALSE;
		// According to the worldtides.info terms of service, API calls may 
		// only be used "for individual spatial coordinates on behalf of an end-user."
		// Therefore, the transient must include the individual's IP address and the location.
		$transient = 'worldtides_transient_'.md5('extremes_'.$timestamp.'_'.$latitude.'_'.$longitude.'_'.$days.'_'.$_SERVER['REMOTE_ADDR']);
		$extremes = WORLDTIDES_CACHE_API_CALLS ? get_transient($transient) : FALSE;
		if (!$extremes) {
			$length = $days * 86400;

			$url = 'https://www.worldtides.info/api'.
				'?extremes'.
				'&datum=CD'.
				'&datums'.
				'&timezone'.
				'&start='.$timestamp.''.
				'&lat='.$latitude.''.
				'&lon='.$longitude.''.
				'&length='.$length.''.
				'&key='.$key;
			$response = wp_remote_get(esc_url_raw($url), array('headers' => array('Origin' => WORLDTIDES_ORIGIN)));				
			$extremes = json_decode(wp_remote_retrieve_body($response), TRUE);
			if (!$extremes || $extremes['status'] !== 200)  {
				$error = $extremes['error'];
			} else {
				set_transient($transient, $extremes, 86400);
			}
		}
		return array($error, $extremes);
	}

	// Get the hights of the tide so we graph it
	static function getHeights($key, $timestamp, $latitude, $longitude, $days) {
		$error = FALSE;
		// According to the worldtides.info terms of service, API calls may 
		// only be used "for individual spatial coordinates on behalf of an end-user."
		// Therefore, the transient must include the individual's IP address and the location.
		$transient = 'worldtides_transient_'.md5('heights_'.$timestamp.'_'.$latitude.'_'.$longitude.'_'.$days.'_'.$_SERVER['REMOTE_ADDR']);
		$heights = WORLDTIDES_CACHE_API_CALLS ? get_transient($transient) : FALSE;
		if (!$heights) {
			$length = $days * 86400 + 3600; // add one hour in case daylight saving time ended
			$url = 'https://www.worldtides.info/lib/api.php'.
				'?heights'.
				'&datum=CD'.
				'&timezone'.
				'&start='.$timestamp.''.
				'&lat='.$latitude.''.
				'&lon='.$longitude.''.
				'&length='.$length.''.
				'&step=300'. // every 5 minutes provides for accurate results
				'&key='.$key;
			$response = wp_remote_get(esc_url_raw($url), array('headers' => array('Origin' => WORLDTIDES_ORIGIN)));				
			$heights = json_decode(wp_remote_retrieve_body($response), TRUE);
			if (!$heights || $heights['status'] !== 200)  {
				$error = $heights['error'];
			} else {
				set_transient($transient, $heights, 86400);
			}
		}
		return array($error, $heights);
	}

	// Creates an image with the tide graph
	static function createPlotImage($heights, $maxTide, $minTide, $units, $timemode, $plotWidth, $plotHeight, $finegrid) {
		$hour24 = $timemode != 'ampm';

		// get the min and max values
		$min_level = $minTide;
		$max_level = $maxTide;
		$min_level = floor($min_level);
		$max_level = ceil($max_level);
		if ($max_level == $min_level)
			$max_level += 1;
		
		$hours = 24;
		$x0 = WORLDTIDES_PLOT_GRAPH_OFFSET_X;
		$y0 = WORLDTIDES_PLOT_GRAPH_OFFSET_Y;
		$w = WORLDTIDES_PLOT_IMAGE_WIDTH - 2*$x0; // padding on both sides
		$h = $plotHeight - 2*$y0; // padding on both sides
		$img = imagecreatetruecolor($plotWidth, $plotHeight);
		// imageantialias($img, TRUE);

		$white = imagecolorallocate($img, 255, 255, 255);
		imagefill($img, 0, 0, $white);

		// set scale
		$scale_y = $h / ($max_level - $min_level); // N pixels per foot

		// create graph image
		$tide_color = imagecolorallocate($img, 98, 153, 235);

		// calculate the graph points
		$points = array();
		foreach ($heights as $v) {
			$level = $v['height'];
			$t = $v['clockTime'];
			if ($t <= 86400) {
				$points[] = $x0+$t*$w/86400;
				$points[] = $h+$y0-($level-$min_level)*$scale_y;
			}
		}
		$points[] = $x0+$w;
		$points[] = $h+$y0;
		$points[] = $x0;
		$points[] = $h+$y0;

		$font = __DIR__.'/fonts/arial.ttf';
		$font_color = imagecolorallocate($img, 128, 128, 128);
		$font_size = WORLDTIDES_PLOT_FONT_SIZE;
		$numHorizontalLines = $max_level-$min_level;
		$horizontalLineVerticalOffset = ceil($numHorizontalLines / 6.0);
		switch ($units) {
			default:
			case 'meters':  $unit = 'm'; break;
			case 'feet':    $unit = '\''; break;
			case 'altFeet': $unit = 'ft'; break;
		}

		if ($finegrid) {
			// horizontal lines
			$grid_color = imagecolorallocate($img, 238, 238, 238);
			$font_offset_x = -$font_size*1.2;
			for ($i=0; $i<=$numHorizontalLines; $i++) {
				$y = floor($h + $y0 - $i*$scale_y);
				$x = $x0 - $font_offset_x;
				imageline($img, $x0, $y, $x0+$w, $y, $grid_color);
			}
			$y = floor($h + $y0 -($max_level-$min_level)*$scale_y);
			imageline($img, $x0, $y, $x0+$w, $y, $grid_color);

			// vertical lines
			for ($hour=0; $hour<=$hours; $hour++) {
				$x = floor($x0+$hour*$w/$hours);
				imageline($img, $x, $h+$y0, $x, $y0, $grid_color);
			}
		}

		// draw plot
		imagefilledpolygon($img, $points, count($points)/2, $tide_color);

		$scale_y = $h / ($max_level - $min_level); // N pixels per foot

		// graph and y-axis labels
		$font_offset_y = $font_size/4;
		for ($i=0; $i<=$numHorizontalLines; $i+=$horizontalLineVerticalOffset) {
			$level = $min_level + $i;
			$text = $level.$unit;
			$type_space = imagettfbbox($font_size, 0, $font, $text);
			$font_offset_x = 7 + $type_space[2] - $type_space[0];
			$y = floor($h + $y0 - $i*$scale_y);
			$x = $x0 - $font_offset_x;
			imagettftext($img, $font_size, 0, $x, $y+$font_offset_y, $font_color, $font, $text);
		}
		
		// x-axis labels
		$font_offset_y = $font_size*1.5;
		$font_offset_x = -$font_size*1.2;
		$y = $h+$y0+$font_offset_y;
		for ($hour=0; $hour<=$hours; $hour+=WORLDTIDES_HOURS_PER_LINE) {
			$x = floor($x0+$hour*$w/$hours);
			$text = WorldTides::formatHour($hour%24, $hour24);
			imagettftext($img, $font_size, 0, $x+$font_offset_x, $y, $font_color, $font, $text);
		}

		// horizontal lines
		$font_offset_x = -$font_size*1.2;
		$grid_color = imagecolorallocatealpha($img, 128, 128, 128, 64);
		for ($i=0; $i<=$numHorizontalLines; $i+=$horizontalLineVerticalOffset) {
			$y = floor($h + $y0 - $i*$scale_y);
			$x = $x0 - $font_offset_x;
			imageline($img, $x0, $y, $x0+$w, $y, $grid_color);
		}
		$y = floor($h + $y0 -($max_level-$min_level)*$scale_y);
		imageline($img, $x0, $y, $x0+$w, $y, $grid_color);

		// vertical lines
		for ($hour=0; $hour<=$hours; $hour+=WORLDTIDES_HOURS_PER_LINE) {
			$x = floor($x0+$hour*$w/$hours);
			imageline($img, $x, $h+$y0, $x, $y0, $grid_color);
		}
		
		// output image
		return $img;
	}

	// This function is used by the plotting routing to create the x-axis labels
	// Normally we would use the date format routine but we want a calendar clock
	// based on the time on the clock, not the number of seconds into the day.
	// We also want to keep the am/pm very close to the number. So, we just do this by hand.
	static function formatHour($time, $hour24=FALSE) {
		if ($hour24) {
			return sprintf('%02dh', $time);
		} else {
			if ($time == 0)
				$time = '12am';
			else if ($time < 12)
				$time = ' '.$time.'am';
			else if ($time == 12)
				$time = '12pm';
			else if ($time < 24)
				$time = ' '.($time-12).'pm';
			else
				$time = '12am';
			return $time;
		}
	}	

	
	private function load_dependencies() {
		require_once plugin_dir_path(dirname(__FILE__)).'includes/class-worldtides-loader.php';
		require_once plugin_dir_path(dirname(__FILE__)).'admin/class-worldtides-admin.php';
		require_once plugin_dir_path(dirname(__FILE__)).'public/class-worldtides-public.php';
		$this->loader = new WorldTides_Loader();
	}
		
	private function define_admin_hooks() {
		$plugin_admin = new WorldTides_Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
	}
	
	private function define_public_hooks() {
		$plugin_public = new WorldTides_Public($this->get_plugin_name(), $this->get_version());
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}
	
	function run() {
		$this->loader->run();
	}
	
	function get_plugin_name() {
		return $this->plugin_name;
	}
	
	function get_loader() {
		return $this->loader;
	}
	
	function get_version() {
		return $this->version;
	}
}

add_action('widgets_init', array('WorldTides', 'register_widget'));
add_shortcode('shortcode-worldtides', ['WorldTides', 'widgetHtml']);
add_filter('plugin_action_links_worldtides/worldtides.php', array('WorldTides_Admin', 'function_worldtides_plugin_action_links'));

?>