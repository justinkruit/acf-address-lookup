<?php

namespace justinkruit\AddressLookupForAcf\Providers;

class PhotonProvider extends AbstractProvider {

  public function name(): string {
    return 'photon';
  }

  public function label(): string {
    return __('Photon', 'jk-address-lookup-for-acf');
  }

  public function search(string $query, array $field) {
    $url_vars = [
      'q'              => $query,
    ];

    if (! empty($field['country_codes'])) {
      $countries = array_map('trim', explode(',', $field['country_codes']));
      $countries = array_filter($countries, static function($country) {
        return $country !== '';
      });

      if (! empty($countries)) {
        $url_vars['countrycode'] = array_values($countries);
      }
    }

    if (! empty($field['language'])) {
      $url_vars['lang'] = $field['language'];
    }

    $base_url = apply_filters('jk_address_lookup_for_acf/photon_url', 'https://photon.komoot.io/api/', $field);
    $url_vars = apply_filters('jk_address_lookup_for_acf/photon_url_vars', $url_vars, $field);

    $response = wp_remote_get($base_url . '?' . $this->buildQueryString($url_vars));

    if (! is_array($response) || is_wp_error($response)) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (! is_array($data)) {
      return false;
    }

    $results = [];

    foreach ($data['features'] as $item) {
      $properties = $item['properties'];
      $geometry = $item['geometry'];
      $name = [
        $properties['name'] ?? '',
        $properties['housenumber'] ?? '',
        $properties['street'] ?? '',
        $properties['city'] ?? $properties['town'] ?? $properties['village'] ?? '',
        $properties['postcode'] ?? '',
        $properties['country'] ?? '',
      ];
      $normalized = [
        'display_name' => implode(', ', array_filter($name)),
        'coordinates'  => [
          'lat' => $geometry['coordinates'][1],
          'lon' => $geometry['coordinates'][0],
        ],
        'house_number' => $properties['housenumber'] ?? '',
        'street'       => $properties['street'] ?? '',
        'city'         => $properties['city'] ?? $properties['town'] ?? $properties['village'] ?? '',
        'state'        => $properties['state'] ?? '',
        'postcode'     => $properties['postcode'] ?? '',
        'country'      => $properties['country'] ?? '',
      ];

      $results[] = $this->formatResult($normalized);
    }

    return $results;
  }
}
