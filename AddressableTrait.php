<?php

namespace App;

use DB;
use Toin0u\Geocoder\Facade\Geocoder;

/**
 * Class AddressableTrait
 * @package App
 */
trait AddressableTrait
{
    /**
     * @var array
     */
    protected $geofields = array('location');

    /**
     * {@inheritdoc}
     */
    public function formattedAddress($lfChar = '\n')
    {
        if ($lfChar == ',') {
            $lfChar = ', ';
        };

        // get address line
        $address = $this->address;

        // add address2 line
        if (!empty($this->address2)) {
            $address = $address . (!empty($address) ? $lfChar : '') . $this->address2;
        }

        // add middle (city/state/postcode) line
        $middle = $this->city;
        if (!empty($this->state_id)) {
            $middle = $middle . (!empty($middle) ? ', ' : '') . $this->state->name;
        }
        if (!empty($this->postcode)) {
            $middle = $middle . (!empty($middle) ? ' ' : '') . $this->postcode;
        }
        if (!empty($middle)) {
            $address = $address . (!empty($address) ? $lfChar : '') . $middle;
        }

        // add country line
        if (!empty($this->country_id)) {
            $address = $address . (!empty($address) ? $lfChar : '') . $this->country->name;
        }

        return $address;
    }

    /**
     * Get the fax number in a standardized US format.
     *
     * @return string
     */
    public function formattedFax()
    {
        $temp = preg_replace("/[^0-9]/", "", $this->fax);
        if (strlen($temp) == 10) {
            return '(' . substr($temp, 0, 3) . ') ' . substr($temp, 3, 3) . '-' . substr($temp, -4);
        } else {
            return $this->fax;
        }
    }

    /**
     * Get the phone number in a standardized US format.
     *
     * @return string
     */
    public function formattedPhone()
    {
        $temp = preg_replace("/[^0-9]/", "", $this->phone);
        if (strlen($temp) == 10) {
            return '(' . substr($temp, 0, 3) . ') ' . substr($temp, 3, 3) . '-' . substr($temp, -4);
        } else {
            return $this->phone;
        }
    }

    /**
     * Get the toll free number in a standardized US format.
     *
     * @return string
     */
    public function formattedTollFree()
    {
        $temp = preg_replace("/[^0-9]/", "", $this->toll_free);
        if (strlen($temp) == 10) {
            return '1-' . substr($temp, 0, 3) . '-' . substr($temp, 3, 3) . '-' . substr($temp, -4);
        } else {
            return $this->toll_free;
        }
    }

    /**
     * Get the fullname by combining the concatenating the firstname and lastname field.  If there are
     * no firstname or lastname fields, then it returns the name field or null.
     *
     * @return string|null
     */
    public function fullname()
    {
        if (!empty($this->firstname)) {
            $name = $this->firstname . (!empty($this->lastname) ? ' ' . $this->lastname : '');
        } elseif (!empty($this->lastname)) {
            $name = $this->lastname;
        } elseif (!empty($this->name)) {
            $name = $this->lastname;
        } else {
            $name = null;
        }

        return $name;
    }

    /**
     * Returns the "lastname, firstname" for the current record.  If there are no firstname or lastname fields,
     * then it returns the name field or null.
     *
     * @return string
     */
    public function reversename()
    {
        if (!empty($this->lastname)) {
            $name = $this->lastname . (!empty($this->firstname) ? ', ' . $this->firstname : '');
        } elseif (!empty($this->firstname)) {
            $name = $this->firstname;
        } elseif (!empty($this->name)) {
            $name = $this->lastname;
        } else {
            $name = null;
        }

        return $name;
    }

    /**
     * Returns the geocode array for the the current record or the address that is passed as a parameter.
     *
     * @param null|string $address
     * @return array
     */
    public function fetchGeocode($address = null)
    {
        if (empty($address)) {
            $address = $this->formattedAddress(', ');
        }

        if (empty($address)) {
            return [];
        }

        try {
            $geocode = Geocoder::geocode($address);
            // The GoogleMapsProvider will return a result
            return $geocode;
        } catch (\Exception $e) {
            // No exception will be thrown here
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Returns the [latitude, longitude] array for the current record or the address that is passed as a parameter.
     *
     * @param null|string $address
     * @return array
     */
    public function fetchCoordinates($address = null)
    {
        $geocode = self::lookupGeocode($address);

        if (empty($geocode)) {
            return [];
        } elseif (!isset($geocode['latitude']) || !isset($geocode['longitude'])) {
            return [];
        } else {
            return [$geocode['latitude'], $geocode['longitude']];
        }
    }

    /**
     * Makes a call to to Google maps to get the geocode array for the specified address.
     *
     * @param $address
     * @return array
     */
    public static function lookupGeocode($address)
    {
        if (empty($address)) {
            return [];
        }

        try {
            $geocode = Geocoder::geocode($address);
            // The GoogleMapsProvider will return a result
            return $geocode;
        } catch (\Exception $e) {
            // No exception will be thrown here
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Makes a call to to Google maps to get the [latitude, longitude] array for the specified address.
     *
     * @param $address
     * @return array
     */
    public static function lookupCoordinates($address)
    {
        $geocode = self::lookupGeocode($address);

        if (empty($geocode)) {
            return [];
        } elseif (!isset($geocode['latitude']) || !isset($geocode['longitude'])) {
            return [];
        } else {
            return [$geocode['latitude'], $geocode['longitude']];
        }
    }

    /**
     * Updates the location field in the database for the current record.
     *
     * @return bool
     */
    public function updateGeocode()
    {
        if ($coordinates = $this->fetchCoordinates()) {
            $this->lat = $coordinates[0];
            $this->lon = $coordinates[1];
            $this->setLocationAttribute("{$this->lat}, {$this->lon}");

            $this->update();

            return true;
        }

        return false;
    }

    /**
     * Sets the location attribute where the coordinate is specified by a string formatted as "latitude,longitude"
     *
     * @param $value
     */
    public function setLocationAttribute($value) {
        $this->attributes['location'] = DB::raw("POINT({$value})");
    }

    /**
     * Gets the location attribute for the current record specified by a string formatted as "latitude,longitude"
     *
     * @param $value
     * @return string
     */
    public function getLocationAttribute($value){

        $loc =  substr($value, 6);
        $loc = preg_replace('/[ ,]+/', ',', $loc, 1);

        return substr($loc,0,-1);
    }

    /**
     * Returns a query with the geofield fields formatted as strings.
     *
     * @param bool $excludeDeleted
     * @return mixed
     */
    public function newQuery($excludeDeleted = true)
    {
        $raw='';
        foreach($this->geofields as $column){
            $raw .= " ASTEXT({$column}) AS {$column} ";
        }

        return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
    }

    /**
     * Adds a where clause to a query that specifies the distance from a location. The location is a string in the
     * format "latitude,longitude".
     *
     * @param $query
     * @param $dist
     * @param $location
     * @return mixed
     */
    public function scopeDistance($query, $dist, $location)
    {
        return $query->whereRaw("ST_DISTANCE(location, POINT({$location})) < {$dist}");

    }

    /**
     * Returns the distance from the current record to the specifed latitude, longitude.
     * source: http://www.geodatasource.com/developers/php
     *
     * @param float $lat2
     * @param float $lon2
     * @param string $unit
     * @return float|null
     */
    public function distance($lat2, $lon2, $unit = "M") {

        if (!empty(!$this->location)) {
            $point = explode(',' , $this->location);
            if (count($point) != 2) {
                return null;
            }
            $lat1 = $point[0];
            $lon1 = $point[1];
        } else {
            if (!empty($this->lat) && !empty($this->lon)) {
                $lat1 = $this->lat;
                $lon1 = $this->lon;
            } else {
                return null;
            }
        }

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

}
