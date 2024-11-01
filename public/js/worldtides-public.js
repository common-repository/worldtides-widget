// Written by Bryan Aamot, Brainware LLC
// GNU 2.0 License
(
	function($) {
		'use strict';
		$(document).ready(function($) {
		});
	}
)(jQuery);

function worldtides_formatHour(time, hour24) {
	var result = "";
	if (hour24) {
		result = time.toString();
		if (result.length < 2) {
			result = "0"+result;
		}
	} else {
		if (time == 0)
			result = "12am";
		else if (time < 12)
			result = " "+time.toString()+"am";
		else if (time == 12)
			result = "12pm";
		else if (time < 24)
			result = " "+(time-12).toString()+"pm";
		else
			result = "12am";
	}
	return result;
}	

function worldtides_secondsToTime(seconds, ampm, suffix) {
	var out = "";
	var minutes = Math.floor(seconds/60);
	var seconds = Math.floor(seconds - minutes*60);
	seconds = seconds.toString();
	if (seconds.length == 1) {
		seconds = "0"+seconds;
	}
	var hour = Math.floor(minutes/60);
	var minute = (minutes - hour * 60).toString();
	if (minute.length == 1) {
		minute = "0"+minute;
	}
	if (ampm) {
		if (hour == 0) {
			hour = 12;
		} else if (hour > 12) {
			hour -= 12;
		}
	} else {
		if (hour.length == 1) {
			hour = "0"+hour;
		}
	}
	out += hour+":"+minute+":"+seconds;
	if (ampm) {
		out += suffix;
	}
	return out;					
}

function worldtides_updateTideTable(instance, targetDate) {
	var html = "";
	var numDays = 0;
	var lastDate = "";
	for (var i=0; i<worldtides_globals[instance].extremes.length; i++) {
		var v = worldtides_globals[instance].extremes[i];
		if (numDays == 0 && v["date"] != targetDate) {
			continue;
		}
		if (v["date"] != lastDate) {
			numDays++;
			if (numDays > worldtides_globals[instance].days) {
				break;
			}
			lastDate = v["date"];
			html += "<tr><th colspan=\'3\'>"+lastDate+"</th></tr>";
		}
		// Use the custom worldtides icon to show the relative height of the tide.
		// This makes the durnal high and low tide more meanful.
		// See https://en.wikipedia.org/wiki/Tide
		var n = Math.round(5 * (v["height"] - worldtides_globals[instance].minTide)/(worldtides_globals[instance].maxTide - worldtides_globals[instance].minTide));
		n = Math.min(n, 5);
		n = Math.max(0, n);
		var icon = "<span class=\'worldtides worldtides-tide-"+n+"\'></span>";
		html += "<tr><td>"+icon+" "+v["type"]+"</td><td>"+v["time"]+"</td><td>"+v["height"].toFixed(1)+worldtides_globals[instance].unitSymbol+"</td></tr>";
	}
	document.getElementById("worldtides-tide-forecast-"+instance).innerHTML = html;
}

var worldtides_time_blinker = false;
function worldtides_blink_colon(match, p1, p2, p3, offset, string) {
	worldtides_time_blinker = !worldtides_time_blinker;
	return worldtides_time_blinker ? p1+":"+p2 : p1+" "+p2;
}

function worldtides_getTideInfo(instance) {
	var time = Date.now() / 1000; // UTC Timestamp since 1/1/1970
	if (worldtides_globals[instance].overrideDate) {
		time = new Date(worldtides_globals[instance].overrideDate) / 1000;
	}
	var index = 0;
	for (var i=1; i<worldtides_globals[instance].heights.length; i++) {
		var h = worldtides_globals[instance].heights[i];
		if (time <= h["timestamp"]) {
			index = i;
			break;
		}
	}
	// Interpolate between samples to get a more accurate height.
	// This is a lot more work but it is worth it.
	var heightText = "";
	var dateText = "";
	var targetDate = "";
	if (index >= 1 && index < worldtides_globals[instance].heights.length) {
		var h1 = worldtides_globals[instance].heights[index-1];
		var h2 = worldtides_globals[instance].heights[index];
		targetDate = h1["date"];
		var height1 = h1["height"];
		var height2 = h2["height"];
		var time1 = h1["timestamp"];
		var time2 = h2["timestamp"];
		var ratio = (time - time1)/(time2 - time1);
		var height = ratio*(height2 - height1) + height1;
		var n = Math.round(5 * (height - worldtides_globals[instance].minTide)/(worldtides_globals[instance].maxTide - worldtides_globals[instance].minTide));
		n = Math.min(n, 5);
		n = Math.max(0, n);
		var icon = "worldtides-tide-" + n.toString();
		heightText = height.toFixed(1) + worldtides_globals[instance].unitSymbol;

		// Calculate actual number of minutes into the day for this timestamp
		var seconds = h1["clockTime"]; //Math.floor(3600*h1["hour"] + 60*h1["minute"] + (time - time1));
		var minutesText = worldtides_secondsToTime(seconds, worldtides_globals[instance].timemode == "ampm", h1["ampm"]);

		// Define the date text
		dateText = h1["dayOfWeek"] + " " + h1["month"] + " " + h1["day"] + ", " + minutesText + " " +h1["timezone"];
	}

	dateText = dateText.replace(/([\d]+):([\d]+):([\d]+)/, worldtides_blink_colon);
	return {seconds:seconds, height:height, icon:icon, heightText:heightText, dateText:dateText, targetDate:targetDate};
}

function worldtides_updateTideInfo(instance, icon, heightText, dateText) {
	var html = "<div style=\'font-size:52px; line-height:52px\'><span class=\'worldtides "+icon+"\'>"+heightText+"</div>";
	html += "<div style=\'font-size:24px\'>"+dateText+"</div>";
	document.getElementById("worldtides-height-and-time-"+instance).innerHTML = html;						
}

function worldtides_drawPlot(instance, cursorTime, cursorHeight) {
	// Draw the plot
	var canvas = document.getElementById("worldtides-plot-"+instance);
	var div = document.getElementById("worldtides-plot-container-"+instance);
	var dpr = window.devicePixelRatio || 1; // handle high resolution displays (i.e retina)
	var rect = canvas.getBoundingClientRect();
	canvas.width = rect.width * dpr;
	canvas.height = rect.height * dpr;
	var ctx = canvas.getContext("2d");
	ctx.scale(dpr, dpr);

	// Setup defaults
	ctx.font = "14px Arial";					
	var x0 = 30;
	var y0 = 30;
	var hours = 24;
	var w = rect.width - 2*x0; // padding on both sides
	var h = rect.height - 2*y0;

	// Calculate min/max levels
	var min_level = worldtides_globals[instance].minTide; // use yearly max/mins provided by php in class-worldtides.php
	var max_level = worldtides_globals[instance].maxTide;
	min_level = Math.floor(min_level);
	max_level = Math.ceil(max_level);
	if (max_level == min_level) {
		max_level += 1;
	}

	//  Set the vertical scale based on the min/max
	var scale_y = h / (max_level - min_level);
	var numHorizontalLines = max_level - min_level;
	var horizontalLineVerticalOffset = Math.ceil(numHorizontalLines / 6.0);

	// Clear the background
	ctx.fillStyle = "white";
	ctx.fillRect(0, 0, rect.width, rect.height);

	// Add vertical lines
	if (worldtides_globals[instance].finegrid) {
		ctx.beginPath();
		ctx.lineWidth = 1;
		ctx.strokeStyle = "#F0F0F0";
		for (var hour=0; hour<=hours; hour++) {
			var x = x0+hour*w/hours;
			ctx.moveTo(x, h+y0);
			ctx.lineTo(x, y0);
		}
		ctx.stroke();

		// Add horizontal lines
		ctx.beginPath();
		ctx.lineWidth = 1;
		ctx.strokeStyle = "#F0F0F0";
		for (var i=0; i<=numHorizontalLines; i++) {
			var y = h + y0 - i*scale_y;
			var x = x0;
			ctx.moveTo(x0, y);
			ctx.lineTo(x0+w, y);
		}
		var y = h + y0 -(max_level-min_level)*scale_y;
		ctx.moveTo(x0, y);
		ctx.lineTo(x0+w, y);
		ctx.stroke();
	}


	// Draw tide graph
	ctx.beginPath();
	ctx.fillStyle = "rgba(98, 153, 235, 1)";
	ctx.lingWidth = 0;
	ctx.moveTo(x0, h+y0);
	for (var i=0; i<worldtides_globals[instance].heights.length; i++) {
		var v = worldtides_globals[instance].heights[i];
		var level = v["height"];
		var t = v["clockTime"];
		// if (t <= 86400 + 3600) {
			ctx.lineTo(x0+t*w/86400, h+y0-(level-min_level)*scale_y);
		// }
	}
	ctx.lineTo(x0+w, h+y0);
	ctx.closePath();
	ctx.fill();

	ctx.beginPath();
	ctx.lineWidth = 1;
	ctx.strokeStyle = 'rgba(128,128,128,0.3)';
	for (var hour=0; hour<=hours; hour+=4) {
		var x = x0+hour*w/hours;
		ctx.moveTo(x, h+y0);
		ctx.lineTo(x, y0);
	}
	ctx.stroke();

	// Add horizontal lines
	ctx.beginPath();
	ctx.lineWidth = 1;
	ctx.strokeStyle = 'rgba(128,128,128,0.3)';
	for (var i=0; i<=numHorizontalLines; i+=horizontalLineVerticalOffset) {
		var y = h + y0 - i*scale_y;
		var x = x0;
		ctx.moveTo(x0, y);
		ctx.lineTo(x0+w, y);
	}
	var y = h + y0 -(max_level-min_level)*scale_y;
	ctx.moveTo(x0, y);
	ctx.lineTo(x0+w, y);
	ctx.stroke();

	// Draw y-axis labels
	ctx.textAlign = "right";
	ctx.fillStyle = "rgba(140, 140, 140)";
	for (var i=0; i<=numHorizontalLines; i+=horizontalLineVerticalOffset) {
		var level = min_level + i;
		var text = level.toString()+worldtides_globals[instance].unitSymbol;
		var y = h + y0 - i*scale_y + 3;
		var x = x0 - 3;
		ctx.fillText(text, x, y);
	}

	// Draw x-axis labels
	ctx.textAlign = "center";
	var y = h+y0;
	for (var hour=0; hour<=hours; hour+=4) {
		var x = x0+hour*w/hours;
		var text = worldtides_formatHour(hour%24, worldtides_globals[instance].timemode!="ampm");
		ctx.fillText(text, x, y+20);
	}

	// draw mark on the current time on the plog (optional)
	
	if (cursorTime !== undefined && cursorHeight !== undefined) {
		var x = x0+cursorTime*w/86400;
		var y = h+y0-(cursorHeight-min_level)*scale_y;
		ctx.beginPath();
			ctx.arc(x, y, 5, 0, 2 * Math.PI, false);
			ctx.fillStyle = worldtides_globals[instance].dotcolor;
			ctx.fill();
	}
}

function worldtides_refresh(instance) {
	var info = worldtides_getTideInfo(instance);
	worldtides_updateTideInfo(instance, info["icon"], info["heightText"], info["dateText"]);
	worldtides_updateTideTable(instance, info["targetDate"]);
	if (worldtides_globals[instance].graphmode != 'none') {
		worldtides_drawPlot(instance, info["seconds"], info["height"]);
	}
}