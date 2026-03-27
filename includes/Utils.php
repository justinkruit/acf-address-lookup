<?php

namespace AcfAddressNominatim;

class Utils {
  /**
   * Returns the directory of the plugin.
   *
   * @return string The directory of the plugin.
   */
  public static function pluginDir() {
    return ACF_ADDRESS_NOMINATIM_PLUGIN_DIR;
  }

  /**
   * Returns the URL of the plugin.
   *
   * @return string The URL of the plugin.
   */
  public static function pluginUrl() {
    return ACF_ADDRESS_NOMINATIM_PLUGIN_URL;
  }

  /**
   * Returns the version of the plugin.
   *
   * @return string The version of the plugin.
   */
  public static function pluginVersion() {
    return ACF_ADDRESS_NOMINATIM_VERSION;
  }
}