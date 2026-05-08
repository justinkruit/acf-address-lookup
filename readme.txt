=== JK Address Lookup field for ACF ===
Contributors: justinkruit
Tags: Advanced Custom Fields, ACF, Address, Geocoding, Nominatim
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

ACF field integration for address lookup providers.

== Description ==

Address Lookup field for ACF adds an address lookup field type to Advanced Custom Fields. Users can search for addresses directly within the editor, and the field returns structured address data including coordinates.

The plugin uses [Nominatim (OpenStreetMap)](https://nominatim.openstreetmap.org/) as the default lookup provider, and supports registering custom providers.

= Features =

* **Live address search** — AJAX-powered search as you type, using Select2.
* **Structured data** — Returns a normalized array with display name, coordinates (lat/lon), house number, road, city, state, postcode, and country.
* **Country code filtering** — Limit search results to specific countries.
* **Language support** — Set the preferred language for results.
* **Extensible provider system** — Register your own address lookup providers via the `address_lookup_for_acf/register_providers` action.
* **REST API support** — Field values are exposed in the REST API.

= Usage =

1. Install and activate the plugin.
2. Create or edit an ACF field group.
3. Add a new field and select the **Address** field type.
4. Optionally configure country code filtering and language.
5. Use `get_field()` or `the_field()` in your templates — the value is returned as an associative array.

= Template example =

`$address = get_field('my_address_field');`

The returned array has the following structure:

    [
        'display_name'  => 'Example Street 1, City, Country',
        'coordinates'   => ['lat' => '52.370216', 'lon' => '4.895168'],
        'house_number'  => '1',
        'road'          => 'Example Street',
        'city'          => 'City',
        'state'         => 'State',
        'postcode'      => '1234 AB',
        'country'       => 'Country',
    ]

= Custom providers =

You can register a custom address lookup provider by hooking into the `address_lookup_for_acf/register_providers` action:

    add_action('address_lookup_for_acf/register_providers', function ($registry) {
        $registry->register(new MyCustomProvider());
    });

Your provider must extend `justinkruit\AddressLookupForAcf\Providers\AbstractProvider` and implement the `name()`, `label()`, and `search()` methods.

= Filters =

* `address_lookup_for_acf/nominatim_url` — Override the Nominatim API base URL (e.g. to use a self-hosted instance).
* `address_lookup_for_acf/nominatim_url_vars` — Modify the query parameters sent to the Nominatim API.

Both filters also support ACF's field-specific variations by type, name, and key.

== Installation ==

1. Upload the `address-lookup-for-acf` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure Advanced Custom Fields (free or PRO) is installed and active.

== Frequently Asked Questions ==

= Does this plugin require ACF PRO? =

No. It works with both the free and PRO versions of Advanced Custom Fields.

= Can I use a self-hosted Nominatim instance? =

Yes. Use the `address_lookup_for_acf/nominatim_url` filter to point to your own instance:

    add_filter('address_lookup_for_acf/nominatim_url', function () {
        return 'https://nominatim.example.com/search';
    });

= What PHP version is required? =

PHP 7.2 or higher is required.

== Changelog ==

= 1.0.0 =
* Initial release.
* Nominatim (OpenStreetMap) provider.
* Country code filtering and language settings.
* Extensible provider architecture.