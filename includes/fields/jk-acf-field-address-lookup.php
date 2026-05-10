<?php

/**
 * Defines the custom field type class.
 */

use justinkruit\AddressLookupForAcf\Utils;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * jk_acf_field_address_lookup class.
 */
class jk_acf_field_address_lookup extends \acf_field {
	public $show_in_rest = true;
	private $env;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name = 'address_lookup';
		$this->label = __('Address', 'jk-address-lookup-for-acf');
		$this->category = 'advanced';
		$this->description = __('ACF field integration for address lookup providers.', 'jk-address-lookup-for-acf');
		$this->defaults = array(
			'choices'	=> array(),
			'ui'		=> 1,
			'multiple'	=> 0,
			'ajax'		=> 1,
			'placeholder' => '',
			'allow_null' => 0,
			'provider' => 'nominatim',
			'country_codes' => '',
			'language' => '',
		);

		$this->env = array(
			'url'     => plugin_dir_url(__FILE__),
			'version' => Utils::pluginVersion()
		);


		add_action('wp_ajax_acf/fields/' . $this->name . '/query', array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/' . $this->name . '/query', array($this, 'ajax_query'));

		parent::__construct();
	}

	/**
	 * Settings to display when users configure a field of this type.
	 *
	 * These settings appear on the ACF “Edit Field Group” admin page when
	 * setting up the field.
	 *
	 * @param array $field
	 * @return void
	 */
	public function render_field_settings($field) {
		// Disabled for now: want to add more providers before exposing this option.
		// acf_render_field_setting(
		// 	$field,
		// 	array(
		// 		'label'			=> __('Lookup Provider', 'jk-address-lookup-for-acf'),
		// 		'instructions'	=> __('Select the address lookup API to use.', 'jk-address-lookup-for-acf'),
		// 		'type'			=> 'select',
		// 		'name'			=> 'provider',
		// 		'choices'		=> address_lookup_for_acf()->providers()->all(),
		// 	)
		// );

		acf_render_field_setting(
			$field,
			array(
				'label'			=> __('Limit to country codes', 'jk-address-lookup-for-acf'),
				'instructions'	=> __('Limit search results to specific country codes (comma-separated)', 'jk-address-lookup-for-acf'),
				'type'			=> 'text',
				'name'			=> 'country_codes',
				'conditions'   => array(
					'field'    => 'provider',
					'operator' => '==',
					'value'    => 'nominatim',
				),
			)
		);

		acf_render_field_setting(
			$field,
			array(
				'label'			=> __('Language', 'jk-address-lookup-for-acf'),
				'instructions'	=> __('Set the language for the address lookup results.', 'jk-address-lookup-for-acf'),
				'type'			=> 'text',
				'name'			=> 'language',
				'conditions'   => array(
					'field'    => 'provider',
					'operator' => '==',
					'value'    => 'nominatim',
				),
			)
		);
	}

	/**
	 * HTML content to show when a publisher edits the field on the edit screen.
	 *
	 * @param array $field The field settings and values.
	 * @return void
	 */
	public function render_field($field) {
		$value   = acf_get_array($field['value']);
		$choices = acf_get_array($field['choices']);

		if ($field['ui'] && $field['ajax']) {
			$minimal = array();
			if (! empty($value)) {
				foreach ($value as $val) {
					$val_decoded = json_decode($val, true);
					$minimal[$val] = $val_decoded['display_name'] ?: $val;
				}
			}
			$choices = $minimal;
		}

		if (empty($field['placeholder'])) {
			$field['placeholder'] = _x('Search for an address...', 'placeholder text', 'jk-address-lookup-for-acf');
		}

		$select = array(
			'id'               => $field['id'],
			'class'            => $field['class'],
			'name'             => $field['name'],
			'data-ui'          => $field['ui'],
			'data-ajax'        => $field['ajax'],
			'data-multiple'    => $field['multiple'],
			'data-placeholder' => $field['placeholder'],
			'data-allow_null'  => $field['allow_null'],
		);

		if (! empty($field['nonce'])) {
			$select['data-nonce'] = $field['nonce'];
		}

		if ($field['ajax'] && empty($field['nonce']) && acf_is_field_key($field['key'])) {
			$select['data-nonce'] = wp_create_nonce('acf_field_' . $this->name . '_' . $field['key']);
		}

		if ($field['multiple'] || $field['ui']) {
			acf_hidden_input(
				array(
					'id'   => $field['id'] . '-input',
					'name' => $field['name'],
				)
			);
		}

		$select['value']   = $value;
		$select['choices'] = $choices;

		acf_select_input($select);
	}

	public function ajax_query() {
		$nonce = acf_request_arg('nonce', '');
		$key   = acf_request_arg('field_key', '');

		$is_field_key = acf_is_field_key($key);

		// Back-compat for field settings.
		if (! $is_field_key) {
			if (! acf_current_user_can_admin()) {
				die();
			}

			$nonce = '';
			$key   = '';
		}

		if (! acf_verify_ajax($nonce, $key, $is_field_key)) {
			die();
		}

		acf_send_ajax_results($this->get_ajax_query($_POST));
	}

	private function get_ajax_query($options) {
		$options = acf_parse_args(
			$options,
			array(
				'post_id'   => 0,
				's'         => '',
				'field_key' => '',
				'paged'     => 1,
			)
		);

		// load field.
		$field = acf_get_field($options['field_key']);
		if (! $field) {
			return false;
		}


		$provider = jk_address_lookup_for_acf()->providers()->get($field['provider'] ?? 'nominatim');
		$search_sanitized = sanitize_text_field($options['s']);

		if (empty($search_sanitized)) {
			return false;
		}

		$results  = $provider->search($search_sanitized, $field);
		if ($results === false) {
			return false;
		}

		$response = array(
			'results' => $results,
		);

		return $response;
	}

	public function format_value($value, $post_id, $field) {
		// Decode JSON string to array for easier use in templates.
		if (is_string($value)) {
			$value = json_decode($value, true);
		}

		return $value;
	}

	public function input_admin_enqueue_scripts() {
		$url     = trailingslashit($this->env['url']);
		$version = $this->env['version'];


		wp_enqueue_script(
			'acf-input-address-lookup',
			Utils::pluginUrl() . 'includes/fields/field.js',
			array('acf-input'),
			$version
		);

		// Bail early if not enqueuing select2.
		if (! acf_get_setting('enqueue_select2')) {
			return;
		}

		global $wp_scripts;

		$min   = defined('ACF_DEVELOPMENT_MODE') && ACF_DEVELOPMENT_MODE ? '' : '.min';
		$major = acf_get_setting('select2_version');

		// attempt to find 3rd party Select2 version
		// - avoid including v3 CSS when v4 JS is already enqueued.
		if (isset($wp_scripts->registered['select2'])) {
			$major = (int) $wp_scripts->registered['select2']->ver;
		}

		if ($major === 3) {
			// Use v3 if necessary.
			$version = '3.5.2';
			$script  = acf_get_url("assets/inc/select2/3/select2{$min}.js");
			$style   = acf_get_url('assets/inc/select2/3/select2.css');
		} else {
			// Default to v4.
			$version = '4.0.13';
			$script  = acf_get_url("assets/inc/select2/4/select2.full{$min}.js");
			$style   = acf_get_url("assets/inc/select2/4/select2{$min}.css");
		}

		wp_enqueue_script('select2', $script, array('jquery'), $version);
		wp_enqueue_style('select2', $style, '', $version);
	}
}
