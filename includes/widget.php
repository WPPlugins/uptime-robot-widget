<?php
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

//Text status of monitor
function uptimerobot_text_status($status){
	switch($status) {
		case 0:
			$r = __('paused', 'uptime-robot-widget');
			break;
		case 1:
			$r = __('n/d', 'uptime-robot-widget');
			break;
		case 2:
			$r = __('up', 'uptime-robot-widget');
			break;
		case 8:
			$r = __('seems down', 'uptime-robot-widget');
			break;
		case 9:
			$r = __('down', 'uptime-robot-widget');
			break;
		default:
			$r = __('unk', 'uptime-robot-widget');
	}
	return $r;
}

//Return data in json via ajax
function uptimerobot_widget_data_json() {
	//Get cache data
	$data = get_transient('uptimerobot_widget_cache') ?: '<div class="error"> ' . __('Oops! Something went wrong and failed to get the status, check again soon.', 'uptime-robot-widget') . '</div>';
	//POST arguments
	$args = array(
		'body' => array(
			'api_key' => get_option('uptimerobot_apikey'),
			'format' => 'json',
			'all_time_uptime_ratio' => get_option('uptimerobot_custom_period') == 0 ? '1' : '0',
			'custom_uptime_ratios' => get_option('uptimerobot_custom_period') == 0 ? '' : get_option('uptimerobot_custom_period')
		),
		'timeout' => get_option('uptimerobot_api_timeout', 5),
		'redirection' => 0,
		'httpversion' => '1.1'
	);
	//POST data
	$retry_limit = get_option('uptimerobot_api_retry', 3);
	$retry_count = 0;
	while($retry_count <= $retry_limit) {
		$response = wp_remote_post('https://api.uptimerobot.com/v2/getMonitors', $args);
		if(is_wp_error($response)) { /* error */ }
		else if($response['response']['code'] == 200) {
			break;
		}
		$retry_count++;
	}
	//Server temporarily unavailable
	if(is_wp_error($response)) { /* error */ }
	//Verify response
	else if($response['response']['code'] == 200) {
		$json = json_decode(wp_remote_retrieve_body($response));
		//Data have monitors
		if(!empty($json->monitors)) {
			//Foreach monitors
			foreach($json->monitors as $monitor) {
				$ratio = $monitor->custom_uptime_ratio ? $monitor->custom_uptime_ratio : $monitor->all_time_uptime_ratio;
				$html .= '<div class="monitor">
					<span class="status stat'.$monitor->status.'">'.uptimerobot_text_status($monitor->status).'</span>
					<span class="name">'.$monitor->friendly_name.'</span>
					<span class="ratio">'.floatval(number_format($ratio, 2)).'%</span>
				</div>';
			}
			//Save cache
			set_transient('uptimerobot_widget_cache', $html, 0);
			//Set data
			$data = $html;
		}
	}
	//Public status page
	if(get_option('uptimerobot_show_psp_link') && !empty(get_option('uptimerobot_psp_url'))) {
		$data .= '<div class="psp"><a href="'.get_option('uptimerobot_psp_url').'" target="_blank">'.__('More details on status page', 'uptime-robot-widget').'</a></div>';
	}
	//Return response
	wp_send_json(array('data' => $data));
}
add_action('wp_ajax_nopriv_uptimerobot_data', 'uptimerobot_widget_data_json');
add_action('wp_ajax_uptimerobot_data', 'uptimerobot_widget_data_json');

//Return cache data in json via ajax
function uptimerobot_widget_cache_data_json() {
	//Get cache
	if(true == ($data = get_transient('uptimerobot_widget_cache'))) {
		if(get_option('uptimerobot_show_psp_link') && !empty(get_option('uptimerobot_psp_url'))) {
			$data .= '<div class="psp"><a href="'.get_option('uptimerobot_psp_url').'" target="_blank">'.__('More details on status page', 'uptime-robot-widget').'</a></div>';
		}
		//Return cache
		wp_send_json(array('data' => $data));
	}
	//No data
	wp_send_json();
}
add_action('wp_ajax_nopriv_uptimerobot_cache_data', 'uptimerobot_widget_cache_data_json');
add_action('wp_ajax_uptimerobot_cache_data', 'uptimerobot_widget_cache_data_json');

//Create widget instance
add_action('widgets_init', function(){
	register_widget('uptimerobot_widget');
});
class uptimerobot_widget extends WP_Widget {
	//Widget constructor
	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_uptimerobot',
			'description' => __('Status of the monitored services in the Uptime Robot service.','uptime-robot-widget'),
			'customize_selective_refresh' => false
		);
		parent::__construct('uptimerobot_widget', 'Uptime Robot', $widget_ops);
		//Enqueue styles & jQuery script if widget is active (appears in a sidebar) or if in Customizer preview
		if(is_active_widget(false, false, $this->id_base) || is_customize_preview()) {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		}
    }
	//Enqueue styles & jQuery script
	function enqueue_scripts() {
		$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
		wp_enqueue_style('uptime-robot-widget', UPTIME_ROBOT_WIDGET_DIR_URL.'css/uptime-robot'.$min.'.css', array(), UPTIME_ROBOT_WIDGET_VERSION, 'all');
		wp_enqueue_style('fontawesome', UPTIME_ROBOT_WIDGET_DIR_URL.'css/font-awesome'.$min.'.css', array(), '4.7.0', 'all');
		wp_enqueue_script('uptime-robot-widget', UPTIME_ROBOT_WIDGET_DIR_URL.'js/jquery.uptimerobot'.$min.'.js', array('jquery'), UPTIME_ROBOT_WIDGET_VERSION, true);
		if(get_option('uptimerobot_show_psp_link') && !empty(get_option('uptimerobot_psp_url'))) {
			$psp_link .= '<div class="psp"><a href="'.get_option('uptimerobot_psp_url').'" target="_blank">'.__('More details on status page', 'uptime-robot-widget').'</a></div>';
		}
		wp_localize_script('uptime-robot-widget', 'uptimerobot', array(
			'data_url' => admin_url('admin-ajax.php?action=uptimerobot_data'),
			'cache_data_url' => admin_url('admin-ajax.php?action=uptimerobot_cache_data'),
			'timeout' => (get_option('uptimerobot_api_timeout', 5)*get_option('uptimerobot_api_retry', 3)+5)*1000,
			'error' => '<div class="error">' . __('Oops! Something went wrong and failed to get the status, check again soon.', 'uptime-robot-widget') . '</div>' . $psp_link
		));
	}
	//Display function
	function widget($args, $instance) {
		//Widget title
		$instance['title'] = apply_filters('widget_title', $instance['title']);
		echo $args['before_widget'];
		if(!empty($instance['title'])) echo $args['before_title'] . $instance['title'] . $args['after_title'];
		//Widget content
		$sc = '<div id="uptimerobot" class="uptimerobot">
			<i title="'.__('Loading', 'uptime-robot-widget').'..." class="fa fa-spinner fa-pulse" style="font-size: 34px;"></i>
		</div>';
		echo $sc;
		//Widget end
		echo $args['after_widget'];
	}
	//Update function
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
	//Settings form function
	function form($instance) {
		$sc = '<p>
			<label for="'.$this->get_field_id('title').'">'.__('Title', 'uptime-robot-widget').':</label>
			<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$instance['title'].'" />
		</p>
		<p>
			'.sprintf(__('Please enter API key in <a href="%s">plugin settings</a>.', 'uptime-robot-widget'), 'options-general.php?page=uptime-robot-options').'
		</p>';
		echo $sc;
	}
}
