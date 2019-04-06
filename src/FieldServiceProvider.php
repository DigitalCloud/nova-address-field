<?php

namespace DigitalCloud\AddressField;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//		$this->publishes([
//            $this->configPath() => config_path('nova-address-field.php'),
//        ], 'nova-address-field-config');
		
        Nova::serving(function (ServingNova $event) {
//            $key = Config::get('nova-address-field.api_key');
            $key = config('services.googleMaps.key');
            Nova::script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$key}&libraries=places");
            Nova::script('address-field', __DIR__.'/../dist/js/field.js');
            Nova::style('address-field', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
	
	/**
     * @return string
     */
    protected function configPath()
    {
        return __DIR__.'/../config/nova-address-field.php';
    }
}
