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

    public function zoom($zoom)
    {
        return $this->withMeta([
            'zoom' => $zoom
        ]);
    }
}
