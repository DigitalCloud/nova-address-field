<?php

namespace DigitalCloud\AddressField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event) {
            $key = env('GOOGLE_PLACES_API_KEY');
            Nova::script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$key}&libraries=places");
            Nova::script('nova-address-field', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-address-field', __DIR__.'/../dist/css/field.css');
        });
    }

    public function register(): void
    {
        //
    }
}
