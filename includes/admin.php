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

//Admin init
function uptimerobot_register_settings() {
	//Register settings
	register_setting('uptimerobot_settings', 'uptimerobot_apikey', 'trim');
	register_setting('uptimerobot_settings', 'uptimerobot_custom_period', 'intval');
	register_setting('uptimerobot_settings', 'uptimerobot_api_timeout', 'intval');
	register_setting('uptimerobot_settings', 'uptimerobot_api_retry', 'intval');
	register_setting('uptimerobot_settings', 'uptimerobot_show_psp_link', 'boolval');
	register_setting('uptimerobot_settings', 'uptimerobot_psp_url', 'trim');
	//Add link to the settings on plugins page
	add_filter('plugin_action_links_'.UPTIME_ROBOT_WIDGET_BASENAME, 'uptimerobot_plugin_action_links', 10, 2);
}
add_action('admin_init', 'uptimerobot_register_settings');

//Link to the settings on plugins page
function uptimerobot_plugin_action_links($links) {
	array_unshift($links, '<a href="options-general.php?page=uptime-robot-options">'.__('Settings', 'uptime-robot-widget').'</a>');
    return $links;
}

//Create options menu
function uptimerobot_admin_menu() {
	//Global variable
	global $uptimerobot_options_page_hook;
	//Add options page
	if($uptimerobot_options_page_hook = add_options_page(__('Uptime Robot Widget', 'uptime-robot-widget'),__('Uptime Robot Widget', 'uptime-robot-widget'), 'manage_options', 'uptime-robot-options', 'uptimerobot_options')) {
		//Add metaboxes
		add_action('add_meta_boxes', 'uptimerobot_add_meta_boxes');
		//Add the needed jQuery script
		add_action('admin_footer-'.$uptimerobot_options_page_hook, 'uptimerobot_options_scripts');
	}
}
add_action('admin_menu', 'uptimerobot_admin_menu');

//Add the needed jQuery script
function uptimerobot_options_scripts() { ?>
	<style>
		.metabox-holder .postbox .hndle{
		cursor:default;
		}
		.metabox-holder .postbox.opened .hndle, .metabox-holder .postbox.closed .hndle{
		cursor:pointer;
		}
		#uptimerobot_review_meta_box{
		background-color:#E39124;
		border:1px solid transparent;
		cursor:pointer;
		}
		#uptimerobot_review_meta_box h2{
		border-bottom:none;
		color:#FAEBD7;
		cursor:pointer;
		}
		#uptimerobot_review_meta_box .inside{
		margin:0;
		}
	</style>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			//Remove toggles from postboxes
			$('div.postbox:not(.closed)').each(function() {
				$(this).children('button').remove();
			});
			//Plugin review
			$("#uptimerobot_review_meta_box").click(function() {
				window.open('https://wordpress.org/support/plugin/uptime-robot-widget/reviews/?rate=5#new-post', '_blank');
			});
		});
	</script>
<?php }

//Add metaboxes
function uptimerobot_add_meta_boxes() {
	//Get global variable
	global $uptimerobot_options_page_hook;
	//Add settings meta box
	add_meta_box(
		'uptimerobot_settings_meta_box',
		__('Settings', 'uptime-robot-widget'),
		'uptimerobot_settings_meta_box',
		$uptimerobot_options_page_hook,
		'normal',
		'default'
	);
	//Add review meta box
	add_meta_box(
		'uptimerobot_review_meta_box',
		'<span class="dashicons dashicons-star-empty"></span>&nbsp;' . __('Rate plugin', 'uptime-robot-widget'),
		'uptimerobot_review_meta_box',
		$uptimerobot_options_page_hook,
		'side',
		'default'
	);
	//Add donate meta box
	add_meta_box(
		'uptimerobot_donate_meta_box',
		__('Donations', 'uptime-robot-widget'),
		'uptimerobot_donate_meta_box',
		$uptimerobot_options_page_hook,
		'side',
		'default'
	);
}

//Settings meta box
function uptimerobot_settings_meta_box() { ?>
	</div>
	<form id="uptimerobot-form" method="post" action="options.php">
		<?php settings_fields('uptimerobot_settings'); ?>
		<div class="inside" style="margin-top:-18px;">
			<ul>
				<li>
					<strong><?php _e('API settings', 'uptime-robot-widget'); ?></strong>
				</li>
				<li>
					<label for="uptimerobot_apikey"><?php _e('API key', 'uptime-robot-widget'); ?>:&nbsp;<input type="text" size="40" name="uptimerobot_apikey" id="uptimerobot_apikey" value="<?php echo get_option('uptimerobot_apikey') ?>" /></label>
					</br><small><?php printf(__('To get your API key visit <a target="_blank" href="%s">Uptime Robot webpage</a>.', 'uptime-robot-widget'), 'https://uptimerobot.com/dashboard#mySettings'); ?></small>
				</li>
			</ul>
			<ul>
				<li>
					<strong><?php _e('Advanced', 'uptime-robot-widget'); ?></strong>
				</li>
				<li>
					<label for="uptimerobot_custom_period"><?php _e('Uptime ratio in a custom period', 'uptime-robot-widget'); ?>:&nbsp;<input type="number" min="0" size="4" name="uptimerobot_custom_period" id="uptimerobot_custom_period" value="<?php echo get_option('uptimerobot_custom_period'); ?>" />&nbsp;<?php _e('days', 'uptime-robot-widget'); ?></label>
					</br><small><?php _e('Shows uptime ratio in a custom period instead of the whole period. Leave field empty if disabled.', 'uptime-robot-widget'); ?></small>
				</li>
				<li>
					<label for="uptimerobot_api_timeout"><?php _e('Connection timeout with API', 'uptime-robot-widget'); ?>:&nbsp;<input type="number" min="5" size="4" name="uptimerobot_api_timeout" id="uptimerobot_api_timeout" value="<?php echo get_option('uptimerobot_api_timeout', 5); ?>" />&nbsp;s</label>
				</li>
				<li>
					<label for="uptimerobot_api_retry"><?php _e('Connection retry limit with API', 'uptime-robot-widget'); ?>:&nbsp;<input type="number" min="3" size="4" name="uptimerobot_api_retry" id="uptimerobot_api_retry" value="<?php echo get_option('uptimerobot_api_retry', 3); ?>" /></label>
				</li>
			</ul>
			<ul>
				<li>
					<strong><?php _e('Others', 'uptime-robot-widget'); ?></strong>
				</li>
				<li>
					<label for="uptimerobot_show_psp_link"><input name="uptimerobot_show_psp_link" id="uptimerobot_show_psp_link" type="checkbox" value="1" <?php checked(1, get_option('uptimerobot_show_psp_link', false)); ?> /><?php _e('Show link to the public status page', 'uptime-robot-widget'); ?></label>
					</br><label for="uptimerobot_psp_url"><?php _e('Page URL', 'uptime-robot-widget'); ?>:&nbsp;<input type="text" size="40" name="uptimerobot_psp_url" id="uptimerobot_psp_url" value="<?php echo get_option('uptimerobot_psp_url') ?>" /></label>
				</li>
			</ul>
		</div>
		<div id="major-publishing-actions">
			<div id="publishing-action">
				<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save settings', 'uptime-robot-widget'); ?>" />
			</div>
			<div class="clear"></div>
		</div>
	</form>
	<div>
<?php }

//Review meta box
function uptimerobot_review_meta_box() { ?>
	<?php _e('If you like this plugin, please give it a nice review', 'uptime-robot-widget'); ?>
<?php }

//Donate meta box
function uptimerobot_donate_meta_box() { ?>
	<p><?php _e('If you like this plugin, please send a donation to support its development and maintenance', 'uptime-robot-widget'); ?></p>
	<p style="text-align:center; height:50px;"><a href="https://beherit.pl/donate/uptime-robot-widget/" style="display: inline-block;"><img src="<?php echo UPTIME_ROBOT_WIDGET_DIR_URL; ?>img/paypal.png" style="height:50px;"></a></p>
<?php }

//Display options page
function uptimerobot_options() {
	//Global variable
	global $uptimerobot_options_page_hook;
	//Enable add_meta_boxes function
	do_action('add_meta_boxes', $uptimerobot_options_page_hook); ?>
	<div class="wrap">
		<h2><?php _e('Uptime Robot Widget', 'uptime-robot-widget'); ?></h2>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes($uptimerobot_options_page_hook, 'normal', null); ?>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes($uptimerobot_options_page_hook, 'side', null); ?>
				</div>
			</div>
		</div>
	</div>
<?php }
