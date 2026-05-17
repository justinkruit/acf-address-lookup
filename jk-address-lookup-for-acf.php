<?php

/**
 * Address Lookup field for ACF
 *
 * Plugin Name:       Address Lookup field for ACF
 * Plugin URI:        https://github.com/justinkruit/acf-address-lookup
 * Description:       ACF field integration for address lookup providers.
 * Version:           1.1.0
 * Author:            Justin Kruit
 * Author URI:        https://justinkruit.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       jk-address-lookup-for-acf
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}


use justinkruit\AddressLookupForAcf\Providers\ProviderRegistry;
use justinkruit\AddressLookupForAcf\Providers\NominatimProvider;
use justinkruit\AddressLookupForAcf\Providers\PhotonProvider;

class JK_Address_Lookup_For_Acf {

  public $plugin_name = 'jk-address-lookup-for-acf';
  public $version = '1.1.0';
  public $prefix = 'jk_address_lookup_for_acf'; // Being used for options and enqueues
  public $plugin_path;
  protected $instances = [];
  private $providerRegistry;

  /**
   * Construction of the plugin
   */
  public function __construct() {
    // Do nothing
  }

  public function initialize() {
    $this->plugin_path = plugin_dir_path(__FILE__);
    $this->define('JK_ADDRESS_LOOKUP_FOR_ACF_VERSION', $this->version);
    $this->define('JK_ADDRESS_LOOKUP_FOR_ACF_PLUGIN_DIR', $this->plugin_path);
    $this->define('JK_ADDRESS_LOOKUP_FOR_ACF_PLUGIN_URL', plugin_dir_url(__FILE__));

    spl_autoload_register(array($this, 'autoloader'));

    $this->providerRegistry = new ProviderRegistry();
    $this->providerRegistry->register(new NominatimProvider());
    $this->providerRegistry->register(new PhotonProvider());

    do_action('jk_address_lookup_for_acf/register_providers', $this->providerRegistry);

    add_action('init', [$this, 'loadField']);
    add_action('init', [$this, 'registerAcfVariations']);
  }

  public function loadField() {
    if (! function_exists('acf_register_field_type')) {
      return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/fields/jk-acf-field-address-lookup.php';
    acf_register_field_type('jk_acf_field_address_lookup');
  }

  public function autoloader($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $class = str_replace("justinkruit" . DIRECTORY_SEPARATOR . "AddressLookupForAcf", "includes", $class);
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

  public function providers(): ProviderRegistry {
    return $this->providerRegistry;
  }

  public function registerAcfVariations() {
    acf_add_filter_variations('jk_address_lookup_for_acf/nominatim_url', array( 'type', 'name', 'key' ), 1);
    acf_add_filter_variations('jk_address_lookup_for_acf/nominatim_url_vars', array( 'type', 'name', 'key' ), 1);
    acf_add_filter_variations('jk_address_lookup_for_acf/photon_url', array( 'type', 'name', 'key' ), 1);
    acf_add_filter_variations('jk_address_lookup_for_acf/photon_url_vars', array( 'type', 'name', 'key' ), 1);
  }
}

function run_jk_address_lookup_for_acf() {
  global $jk_address_lookup_for_acf;
  $jk_address_lookup_for_acf = new JK_Address_Lookup_For_Acf();
  $jk_address_lookup_for_acf->initialize();

  return $jk_address_lookup_for_acf;
}

run_jk_address_lookup_for_acf();
//add_action('init', 'run_jk_address_lookup_for_acf');

/**
 * @return JK_Address_Lookup_For_Acf
 */
function jk_address_lookup_for_acf() {
  global $jk_address_lookup_for_acf;

  return $jk_address_lookup_for_acf;
}
