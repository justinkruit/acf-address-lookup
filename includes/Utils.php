<?php

namespace justinkruit\AddressLookupForAcf;

class Utils {
  /**
   * Returns the directory of the plugin.
   *
   * @return string The directory of the plugin.
   */
  public static function pluginDir() {
    return ADDRESS_LOOKUP_FOR_ACF_PLUGIN_DIR;
  }

  /**
   * Returns the URL of the plugin.
   *
   * @return string The URL of the plugin.
   */
  public static function pluginUrl() {
    return ADDRESS_LOOKUP_FOR_ACF_PLUGIN_URL;
  }

  /**
   * Returns the version of the plugin.
   *
   * @return string The version of the plugin.
   */
  public static function pluginVersion() {
    return ADDRESS_LOOKUP_FOR_ACF_VERSION;
  }
}