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

    public function withMap(): static
    {
        return $this->withMeta([
            'withMap' => true,
        ]);
    }

    public function withLatLng(): static
    {
        return $this->withMeta([
            'withLatLng' => true,
        ]);
    }

    public function countries(array $list): static
    {
        return $this->withMeta([
            'countries' => $list,
        ]);
    }

    public function initLocation(string $lat, string $lng): static
    {
        return $this->withMeta([
            'lat' => $lat,
            'lng' => $lng
        ]);
    }

    public function zoom(int $zoom): static
    {
        return $this->withMeta([
            'zoom' => $zoom,
        ]);
    }
}
