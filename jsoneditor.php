<?php
/**
 * JSON Editor plugin for WordPress
 *
 * @package   jsoneditor
 * @link      https://github.com/Crabcyborg/wp-jsoneditor
 * @author    Mike Letellier <mike@crabcyb.org>
 * @copyright 2020 Mike Letellier
 * @license   Apache License 2.0
 *
 * Plugin Name:  JSON Editor
 * Description:  A bridge between https://github.com/josdejong/jsoneditor and the WordPress options table
 * Version:      0.0.1
 * Plugin URI:   https://github.com/Crabcyborg/wp-jsoneditor
 * Author:       Mike Letellier
 * Author URI:   https://crabcyb.org/
 *
 */
 
function jsoneditor_setup_menu() {
	add_menu_page('JSON Editor', 'JSON Editor', 'manage_options', 'jsoneditor', 'jsoneditor_init');
}

function jsoneditor_init() {
	define('JSONEDITOR_BASE_PATH', plugin_dir_path(__FILE__));
	define('JSONEDITOR_BASE_URL', plugin_dir_url(__FILE__));
	
	wp_register_script('jsoneditor-js', 'https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/9.0.0/jsoneditor.min.js', null, null, true);
	wp_register_style('jsoneditor-css', 'https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/9.0.0/jsoneditor.min.css');
	wp_register_script('wp-jsoneditor', JSONEDITOR_BASE_URL.'/assets/js/wp-jsoneditor.js', null, null, true);
	
	wp_enqueue_script('jsoneditor-js');
	wp_enqueue_style('jsoneditor-css');
	wp_enqueue_script('wp-jsoneditor');
?>
	<div style="padding: 20px 20px 0 0;">
		<div style="margin-bottom: 10px;">
			<input id="jsoneditor-prefix" type="text" placeholder="Prefix" />
			<input id="jsoneditor-key" type="text" placeholder="Key" />
			<button id="jsoneditor-load">Load</button>
		</div>
		<div id="jsoneditor"></div>
		<button id="jsoneditor-save" style="margin-top: 10px;">Save</button>
	</div>
<?php
}

function jsoneditor_action_load() {
	echo get_option("jsoneditor_{$_POST['prefix']}_{$_POST['key']}") ?: '';
	wp_die();
}

function jsoneditor_action_save() {
	update_option("jsoneditor_{$_POST['prefix']}_{$_POST['key']}", $_POST['json'], 'no');
	wp_die();
}

add_action('admin_menu', 'jsoneditor_setup_menu');
add_action('wp_ajax_jsoneditor_action_load', 'jsoneditor_action_load');
add_action('wp_ajax_jsoneditor_action_save', 'jsoneditor_action_save');