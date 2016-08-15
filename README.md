AddressableModel for Laravel 5
==============================

This adds methods to format addresses and phone numbers to an Eloquent model in [**Laravel 5**](http://laravel.com/).  It also adds geocoding functionality using [GeocoderLaravel](https://github.com/geocoder-php/GeocoderLaravel).

The AddressableTrait file is pretty simple and easy to understand so modify it to suit your needs.  If you have additional functionality that you would like to see added please let me know.

Composer Install
----------------

It can be found on [Packagist](https://packagist.org/packages/craigzeaross/addressable-model).
The recommended way is through [composer](http://getcomposer.org).

Edit `composer.json` and add:

```json
{
    "require": {
        "craigzearfoss/addressable-model": "dev-master"
    }
}
```

And install dependencies:
```bash
$ composer update
```

If you do not have [**Composer**](https://getcomposer.org) installed, run these two commands:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```


Configuration
-------------

In your model add the AddressableTrait.

```php
<?php

// ...
use Craigzearfoss\AddressableModel\AddressableTrait;

class MyModel extends Model
{
    use AddressableTrait;
```


Usage
-----

The AddressableTrait assumes that your model has the following fields.  You are not required to have all of the fields, only the fields that that are needed for the methods you want to use.

* `firstname` (string) - used by `fullname` and `reversename` methods 
* `lastname` (string) - used by `fullname` and `reversename` methods
* `address` (string)
* `address2` (string)
* `city` (string)
* `state_id` (integer) - references the field `abbrev` in a `states` table
* `postcode` (string)
* `country_id` (integer) - references the field `abbrev` in a `countries` table
* `lat` (float) nullable - stores the latitude retrieved from GeocoderLaravel
* `lng` (float) nullable - stores the longitude retrieved from GeocoderLaravel
* `phone` (string)
* `fax` (string)
    
Formatting Methods:

* `fullname`
* `reversename`
* `formattedAddress`
* `formattedPhone`
* `formattedFax`

Geocoder Methods

* `fetchGeocode` - returns the geocode array  for the current record
* `fetchCoordinates` - returns an array with the latitude and longitude for the current record
* `updateGeocode` - updates the `lat` and `lng` fields for the current model 
* `lookupGeocode` - makes a call to Google maps for the specified address and return the geocode array
* `loopupCoordinates` - makes a call to Google maps for the specified address and returns an array with the latitude and longitude
* `distance` - calculates the distance between the current record and the specified latitude and longitude
* `scopeDistance` - adds a location coordinate and distance to a query

Changelog
---------

[See the CHANGELOG file](https://github.com/craigzearfoss/bullets/blob/master/CHANGELOG.md)


Support
-------

[Please open an issue on GitHub](https://github.com/craigzearfoss/bullets/issues)


Contributor Code of Conduct
---------------------------

Please note that this project is released with a Contributor Code of Conduct.
By participating in this project you agree to abide by its terms.


License
-------

AddressableModel is released under the MIT License. See the bundled
[LICENSE](https://github.com/craigzearfoss/bullets/blob/master/LICENSE)
file for details.