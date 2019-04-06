<?php

namespace DigitalCloud\AddressField;

use Laravel\Nova\Fields\Field;

class AddressField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'address-field';

    public function withMap(){
        return $this->withMeta([
            'withMap' => true
        ]);
    }

    public function withLatLng(){
        return $this->withMeta([
            'withLatLng' => true
        ]);
    }
    
    public function hideToggles(){
        return $this->withMeta([
            'hideToggles' => true
        ]);
    }
    
    public function countryCode($field)
    {
        return $this->withMeta([
            'countryCode' => $field
        ]);
    }
    
    public function country($field)
    {
        return $this->withMeta([
            'country' => $field
        ]);
    }
    
    /**
     * City
     * @param $field
     * @return AddressField
     */
    public function locality($field)
    {
        return $this->withMeta([
            'locality' => $field
        ]);
    }
    
    public function administrativeArea($field)
    {
        return $this->withMeta([
            'administrative_area_level_1' => $field
        ]);
    }
    
    public function postalCode($field)
    {
        return $this->withMeta([
            'postal_code' => $field
        ]);
    }
    
    public function address($field, $inArrayKey=null)
    {
        return $this->withMeta([
            'address_field' => $field,
            'address_field_array_key' => $inArrayKey,
        ]);
    }
    
    public function latitude($field)
    {
        return $this->withMeta([
            'latitude_field' => $field
        ]);
    }
    
    public function longitude($field)
    {
        return $this->withMeta([
            'longitude_field' => $field
        ]);
    }

    public function countries($list){
        return $this->withMeta([
            'countries' => $list
        ]);
    }
    

    public function initLocation($latitude, $longitude){
        return $this->withMeta([
            'lat' => $latitude,
            'lng' => $longitude,
        ]);
    }
    
    public function doNotStore(){
        $this->fillUsing(function(){});
        $this->withMeta(['do_not_store' => true]);
        return $this;
    }
    
    public function manualFill($label){
        return $this->withMeta(['manual_fill' => $label]);
    }

    public function zoom($zoom)
    {
        return $this->withMeta([
            'zoom' => $zoom
        ]);
    }
}
