<?php
/*
	Copyright (c) 2016 Krzysztof Grochocki

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

//Die if uninstall.php is not called by WordPress
if(!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}
//Remove settings options
delete_option('uptimerobot_apikey');
delete_option('uptimerobot_custom_period');
delete_option('uptimerobot_api_timeout');
delete_option('uptimerobot_api_retry');
delete_option('uptimerobot_show_psp_link');
delete_option('uptimerobot_psp_url');
//Delete cache
delete_transient('uptimerobot_widget_cache');
