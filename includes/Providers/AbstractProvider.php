<?php

namespace AcfAddressLookup\Providers;

abstract class AbstractProvider {

  abstract public function name(): string;

  abstract public function label(): string;

  /**
   * Search for addresses using the provider's API.
   *
   * Must return an array of normalized address arrays, or false on failure.
   * Each normalized address array must have the following shape:
   *
   *   [
   *       'display_name'  => string,
   *       'coordinates'   => ['lat' => string, 'lon' => string],
   *       'house_number'  => string,
   *       'road'          => string,
   *       'city'          => string,
   *       'state'         => string,
   *       'postcode'      => string,
   *       'country'       => string,
   *   ]
   *
   * @param string $query The search query.
   * @param array  $field The ACF field settings.
   * @return array|false Array of normalized address arrays, or false on failure.
   */
  abstract public function search(string $query, array $field): array|false;

  /**
   * Wrap a normalized address array into the format expected by ACF select results.
   *
   * @param array $normalized A single normalized address array.
   * @return array ['id' => json string, 'text' => display name]
   */
  protected function formatResult(array $normalized): array {
    return [
      'id'   => json_encode($normalized),
      'text' => $normalized['display_name'],
    ];
  }
}
