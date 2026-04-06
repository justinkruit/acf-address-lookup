<?php

namespace AcfAddressLookup\Providers;

class NominatimProvider extends AbstractProvider {

  public function name(): string {
    return 'nominatim';
  }

  public function label(): string {
    return 'Nominatim (OpenStreetMap)';
  }

  public function search(string $query, array $field): array|false {
    $url_vars = [
      'q'              => $query,
      'format'         => 'json',
      'addressdetails' => 1,
    ];

    if (! empty($field['country_codes'])) {
      $url_vars['countrycodes'] = $field['country_codes'];
    }

    if (! empty($field['language'])) {
      $url_vars['accept-language'] = $field['language'];
    }

    $base_url = apply_filters('acf_address_lookup/nominatim_url', 'https://nominatim.openstreetmap.org/search', $field);
    $url_vars = apply_filters('acf_address_lookup/nominatim_url_vars', $url_vars, $field);

    $response = wp_remote_get($base_url . '?' . http_build_query($url_vars));

    if (! is_array($response) || is_wp_error($response)) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (! is_array($data)) {
      return false;
    }

    $results = [];

    foreach ($data as $item) {
      $normalized = [
        'display_name' => $item['display_name'],
        'coordinates'  => [
          'lat' => $item['lat'],
          'lon' => $item['lon'],
        ],
        'house_number' => $item['address']['house_number'] ?? '',
        'road'         => $item['address']['road'] ?? '',
        'city'         => $item['address']['city'] ?? $item['address']['town'] ?? $item['address']['village'] ?? '',
        'state'        => $item['address']['state'] ?? '',
        'postcode'     => $item['address']['postcode'] ?? '',
        'country'      => $item['address']['country'] ?? '',
      ];

      $results[] = $this->formatResult($normalized);
    }

    return $results;
  }
}
