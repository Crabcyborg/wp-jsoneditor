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

/**
 * @return array
 */
function jsoneditor_validate_prefix_and_key() {
	$prefix = !empty($_POST['prefix']) ? sanitize_key($_POST['prefix']) : FALSE;
	$key = !empty($_POST['key']) ? sanitize_key($_POST['key']) : FALSE;
	if(!$prefix || !$key) wp_send_json(['result' => FALSE, 'error' => 'Prefix and key must both be set and only lower alphanumeric characters, dashes, and underscores are allowed.']);
	return [$prefix, $key];
}

/**
 * @param array $data
 * @return array
 */
function jsoneditor_sanitize_json($data) {
	if(!$data) return [];
	if(!is_array($data)) wp_send_json(['result' => FALSE, 'error' => 'The JSON data being passed is not valid.']);

	$clean = [];
	foreach($data as $key => $value) {
		$clean_key = sanitize_key($key);
		$clean_key && $clean[$clean_key] = is_array($value) ? jsoneditor_sanitize_json($value) : sanitize_text_field($value);
	}
	return $clean;
}

function jsoneditor_setup_menu() {
	add_menu_page('JSON Editor', 'JSON Editor', 'manage_options', 'jsoneditor', 'jsoneditor_init');
}

function jsoneditor_init() {
	define('JSONEDITOR_BASE_PATH', plugin_dir_path(__FILE__));
	define('JSONEDITOR_BASE_URL', plugin_dir_url(__FILE__));

	wp_register_script('jsoneditor-js', JSONEDITOR_BASE_URL.'assets/jsoneditor/jsoneditor.min.js', null, null, true);
	wp_register_style('jsoneditor-css', JSONEDITOR_BASE_URL.'assets/jsoneditor/jsoneditor.min.css');
	wp_register_script('wp-jsoneditor', JSONEDITOR_BASE_URL.'assets/wp-jsoneditor.js', null, null, true);
	
	wp_enqueue_script('jsoneditor-js');
	wp_enqueue_style('jsoneditor-css');
	wp_enqueue_script('wp-jsoneditor');
?>
	<div style="padding: 20px 20px 0 0;">
		<div style="margin-bottom: 10px;">
			<input id="jsoneditor-prefix" type="text" placeholder="Prefix" />
			<input id="jsoneditor-key" type="text" placeholder="Key" />
			<button id="jsoneditor-load">Load</button> <span id="jsoneditor-load-status"></span>
		</div>
		<div id="jsoneditor"></div>
		<button id="jsoneditor-save" style="margin-top: 10px;">Save</button> <span id="jsoneditor-save-status"></span>
	</div>
<?php
}

function jsoneditor_action_load() {
	list($prefix, $key) = jsoneditor_validate_prefix_and_key();
	$option = get_option("jsoneditor_{$prefix}_{$key}");
	if(!$option || !is_array($option)) wp_send_json(['result' => FALSE, 'error' => 'No JSON data found at requested key']);
	wp_send_json(['result' => TRUE, 'payload' => $option]);
}

function jsoneditor_action_save() {	
	list($prefix, $key) = jsoneditor_validate_prefix_and_key();
	$json = jsoneditor_sanitize_json($_POST['json'] ?? FALSE);
	update_option("jsoneditor_{$prefix}_{$key}", $json, 'no');
	wp_send_json(['result' => TRUE]);
}

add_action('admin_menu', 'jsoneditor_setup_menu');
add_action('wp_ajax_jsoneditor_action_load', 'jsoneditor_action_load');
add_action('wp_ajax_jsoneditor_action_save', 'jsoneditor_action_save');