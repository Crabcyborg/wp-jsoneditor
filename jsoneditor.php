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
 */

/**
 * Validate prefix and key $_POST values
 *
 * @return array
 */
function jsoneditor_validate_prefix_and_key() {
	if ( ! check_admin_referer( 'jsoneditor_ajax', 'nonce' ) ) {
		wp_send_json( array( 'result' => false ) );
	}
	$prefix = isset( $_POST['prefix'] ) ? sanitize_key( wp_unslash( $_POST['prefix'] ) ) : '';
	$key    = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
	if ( ! $prefix || ! $key ) {
		wp_send_json(
			array(
				'result' => false,
				'error'  => 'Prefix and key must both be set and only lower alphanumeric characters, dashes, and underscores are allowed.',
			)
		);
	}
	return array( $prefix, $key );
}

/**
 * Sanitize JSON data
 *
 * @param array $data raw unsanitized data.
 * @return array
 */
function jsoneditor_sanitize_json( $data ) {
	if ( ! $data ) {
		return array();
	}
	if ( ! is_array( $data ) ) {
		wp_send_json(
			array(
				'result' => false,
				'error'  => 'The JSON data being passed is not valid.',
			)
		);
	}

	$clean = array();
	foreach ( $data as $key => $value ) {
		$clean_key = sanitize_key( $key );
		if ( $clean_key ) {
			$clean[ $clean_key ] = is_array( $value ) ? jsoneditor_sanitize_json( $value ) : sanitize_text_field( $value );
		}
	}
	return $clean;
}

/**
 * Set Up Menu
 */
function jsoneditor_setup_menu() {
	add_menu_page( 'JSON Editor', 'JSON Editor', 'manage_options', 'jsoneditor', 'jsoneditor_init' );
}

/**
 * Initialize JSON Editor
 */
function jsoneditor_init() {
	define( 'JSONEDITOR_BASE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'JSONEDITOR_BASE_URL', plugin_dir_url( __FILE__ ) );

	$version = '0.0.1';
	wp_register_script( 'jsoneditor-js', JSONEDITOR_BASE_URL . 'assets/jsoneditor/jsoneditor.min.js', null, $version, true );
	wp_register_style( 'jsoneditor-css', JSONEDITOR_BASE_URL . 'assets/jsoneditor/jsoneditor.min.css', null, $version );
	wp_register_script( 'wp-jsoneditor', JSONEDITOR_BASE_URL . 'assets/wp-jsoneditor.js', null, $version, true );

	wp_enqueue_script( 'jsoneditor-js' );
	wp_enqueue_style( 'jsoneditor-css' );
	wp_enqueue_script( 'wp-jsoneditor' );
	?>
	<div style="padding: 20px 20px 0 0;">
		<div style="margin-bottom: 10px;">
			<input id="jsoneditor-prefix" type="text" placeholder="Prefix" />
			<input id="jsoneditor-key" type="text" placeholder="Key" />
			<?php wp_nonce_field( 'jsoneditor_ajax', 'jsoneditor-nonce' ); ?>
			<button id="jsoneditor-load">Load</button> <span id="jsoneditor-load-status"></span>
		</div>
		<div id="jsoneditor"></div>
		<button id="jsoneditor-save" style="margin-top: 10px;">Save</button> <span id="jsoneditor-save-status"></span>
	</div>
	<?php
}

/**
 * Handle load AJAX action
 */
function jsoneditor_action_load() {
	list( $prefix, $key ) = jsoneditor_validate_prefix_and_key();
	$option               = get_option( "jsoneditor_{$prefix}_{$key}" );
	if ( ! $option || ! is_array( $option ) ) {
		wp_send_json(
			array(
				'result' => false,
				'error'  => 'No JSON data found at requested key',
			)
		);
	}
	wp_send_json(
		array(
			'result'  => true,
			'payload' => $option,
		)
	);
}

/**
 * Handle save AJAX action
 */
function jsoneditor_action_save() {
	if ( ! check_admin_referer( 'jsoneditor_ajax', 'jsoneditor-nonce' ) ) {
		wp_send_json( array( 'result' => false ) );
	}
	list( $prefix, $key ) = jsoneditor_validate_prefix_and_key();
	if ( ! isset( $_POST['json'] ) ) {
		wp_send_json( array( 'result' => false ) );
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$json = jsoneditor_sanitize_json( wp_unslash( $_POST['json'] ) );
	update_option( "jsoneditor_{$prefix}_{$key}", $json, 'no' );
	wp_send_json( array( 'result' => true ) );
}

add_action( 'admin_menu', 'jsoneditor_setup_menu' );
add_action( 'wp_ajax_jsoneditor_action_load', 'jsoneditor_action_load' );
add_action( 'wp_ajax_jsoneditor_action_save', 'jsoneditor_action_save' );
