<?php

namespace AcfAddressLookup\Providers;

class ProviderRegistry {

  /** @var AbstractProvider[] */
  private array $providers = [];

  public function register(AbstractProvider $provider): void {
    $this->providers[$provider->name()] = $provider;
  }

  public function get(string $name): AbstractProvider {
    return $this->providers[$name] ?? $this->providers['nominatim'];
  }

  /**
   * Return all providers as ['slug' => 'Label'] for use in field settings.
   *
   * @return array<string, string>
   */
  public function all(): array {
    $choices = [];

    foreach ($this->providers as $provider) {
      $choices[$provider->name()] = $provider->label();
    }

    return $choices;
  }
}
