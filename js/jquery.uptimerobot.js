/*
	Copyright (c) 2015-2016 Krzysztof Grochocki

	This file is part of Uptime Robot Widget.

	Uptime Robot Widget is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3, or
	(at your option) any later version.

	Uptime Robot Widget is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with GNU Radio. If not, see <http://www.gnu.org/licenses/>.
*/

function get_uptimerobot_widget_data() {
	jQuery.ajax({
		method: 'GET',
		url: uptimerobot.data_url,
		dataType: 'json',
		timeout: uptimerobot.timeout,
		success: function(response) {
			jQuery('#uptimerobot').html(response.data);
		},
		error: function() {
			jQuery('#uptimerobot').html(uptimerobot.error);
		}
	});
}

jQuery(document).ready(function($) {
	//Get cache data
	$.ajax({
		method: 'GET',
		url: uptimerobot.cache_data_url,
		dataType: 'json',
		timeout: uptimerobot.timeout,
		success: function(response) {
			if(response) {
				$('#uptimerobot').html(response.data);
			}
		},
		complete: function() {
			//Get fresh data
			get_uptimerobot_widget_data();
			//Set timer
			setInterval(function() {
				get_uptimerobot_widget_data();
			}, 300000);
		}
	});
});