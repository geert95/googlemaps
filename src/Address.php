<?php

namespace GeertBoetzkes\Gmaps;

use Httpful\Request;

class Address
{
    public $input; //the user input

    public $city;
    public $street;
    public $housenumber;
    public $region;
    public $postalcode;
    public $country;
    public $countryCode;
    public $lat;
    public $lon;

    public $output;
    private $key;

    public function __construct($key)
    {
        // $this->input = $input;
        $this->key = $key;
        return $this;
    }


    public function search($input){
      $this->input = $input;
      return $this->locate();
    }
    
    public function locate()
    {
        $request = Request::get(self::url($this->input))->send();
        if(property_exists($request->body, "results") && $request->body->status == "OK")
        {
            $collect = collect($request->body->results);
            if($collect->count() == 1){
                $result = $collect->first();

                $address = self::mapGoolgeArray($result->address_components);

                $this->city         = $address['locality']->long_name;
                $this->street       = $address['route']->long_name;
                $this->housenumber  = $address['street_number']->long_name;
                $this->region       = $address['administrative_area_level_1']->long_name;
                $this->postalcode   = $address['postal_code']->long_name;

                $this->country      = $address['country']->long_name;
                $this->countryCode  = $address['country']->short_name;
                $this->output       = $result->formatted_address;
                $this->lat          = $result->geometry->location->lat;
                $this->lon          = $result->geometry->location->lng;


                return $this;
            }
        }

        //oops
        return false;
    }

    private function url($address){
        $address = urlencode($address);
        return "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=".$this->key;
    }

    private function mapGoolgeArray($array){
        foreach($array as $key => $value) {
            $type = $value->types[0];
            $array[$type] = $value;
            unset($array[$key]);
        }

        return $array;
    }
}
