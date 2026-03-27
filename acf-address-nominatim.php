<?php

/**
 * ACF Address Field
 *
 * Plugin Name:       ACF Address Field
 * Plugin URI:        https://justinkruit.com
 * Description:       ACF field integration for Nominatim-powered address lookup.
 * Version:           0.0.1-beta1
 * Author:            Justin Kruit
 * Author URI:        https://justinkruit.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       acf-address-nominatim
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}


class Acf_Address_Nominatim {

  public string $plugin_name = 'acf-address-nominatim';
  public string $version = '0.0.1-beta1';
  public string $prefix = 'acf_address_nominatim'; // Being used for options and enqueues
  public string $plugin_path;
  protected array $instances = [];

  /**
   * Construction of the plugin
   */
  public function __construct() {
    // Do nothing
  }

  public function initialize() {
    $this->plugin_path = plugin_dir_path(__FILE__);
    $this->define('ACF_ADDRESS_NOMINATIM_VERSION', $this->version);
    $this->define('ACF_ADDRESS_NOMINATIM_PLUGIN_DIR', $this->plugin_path);
    $this->define('ACF_ADDRESS_NOMINATIM_PLUGIN_URL', plugin_dir_url(__FILE__));

    spl_autoload_register(array($this, 'autoloader'));

    add_action('init', [$this, 'loadField']);
  }

  public function loadField() {
    if (! function_exists('acf_register_field_type')) {
      return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/fields/acf-field-address-nominatim.php';
    acf_register_field_type('acf_field_address_nominatim');
  }

  public function autoloader($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $class = str_replace("AcfAddressNominatim", "includes", $class);
    $file  = $this->plugin_path . $class . '.php';

    if (file_exists($file)) {
      require_once $file;
    }
  }

  public function autoRequireDir($dir) {
    $files = glob($this->plugin_path . $dir . '/*.php');
    foreach ($files as $file) {
      require_once $file;
    }
  }

  public function getInstance($class) {
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = new $class();
    }

    return $this->instances[$class];
  }

  public function newInstance($class, $initialize = null) {
    $instance = new $class();
    if ($initialize) {
      $instance->$initialize();
    }

    return $this->instances[$class] = $instance;
  }

  public function define($name, $value = true) {
    if (!defined($name)) {
      define($name, $value);
    }
  }
}

function run_acf_address_nominatim() {
  global $acf_address_nominatim;
  $acf_address_nominatim = new Acf_Address_Nominatim();
  $acf_address_nominatim->initialize();

  return $acf_address_nominatim;
}

run_acf_address_nominatim();
//add_action('init', 'run_acf_address_nominatim');

/**
 * @return Acf_Address_Nominatim
 */
function acf_address_nominatim() {
  global $acf_address_nominatim;

  return $acf_address_nominatim;
}
