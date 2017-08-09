<?php
/*
Plugin Name: Uptime Robot Widget
Plugin URI: https://beherit.pl/en/wordpress/plugins/uptime-robot-widget/
Description: Adds a widget that shows the status of the monitored services in the Uptime Robot service.
Version: 1.6.2
Author: Krzysztof Grochocki
Author URI: https://beherit.pl/
Text Domain: uptime-robot-widget
Domain Path: /languages
License: GPLv3
*/

/*
	Copyright (c) 2015-2017 Krzysztof Grochocki

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

//Define plugin basename, dir path and dir url
define('UPTIME_ROBOT_WIDGET_BASENAME', plugin_basename(__FILE__));
define('UPTIME_ROBOT_WIDGET_DIR_PATH', plugin_dir_path(__FILE__));
define('UPTIME_ROBOT_WIDGET_DIR_URL', plugin_dir_url(__FILE__));

//Define plugin version variable
define('UPTIME_ROBOT_WIDGET_VERSION', '1.6.2');

//Load plugin translations
function uptimerobot_textdomain() {
	load_plugin_textdomain('uptime-robot-widget', false, dirname(UPTIME_ROBOT_WIDGET_BASENAME).'/languages');
}
add_action('init', 'uptimerobot_textdomain');

//Include admin settings
include_once(UPTIME_ROBOT_WIDGET_DIR_PATH.'includes/admin.php');

//Include widget
include_once(UPTIME_ROBOT_WIDGET_DIR_PATH.'includes/widget.php');
