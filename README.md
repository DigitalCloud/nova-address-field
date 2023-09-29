# Nova Google Address Field.

This field allows you to pick address using Google Places API by three ways:
1) using autocomplete input: user can start typing and the address will autocompleted. after the user select a place from the popup places the full real address with all the metadata (like latitude and longitude) will be filled in the form and the map will centered to the selected address.
2) using lat and long input: if you have the specific lat and lng for the place, you can put the in the form and the autocomplete input will be refreshed with the formatted address for this lat and lng, the map also will be centered to the ne address.
3) using map: you can pick the address by click on the place on the map and the real address will be filled automatically.

## Installation

You can install the package via composer:

```bash
composer require digitalcloud/nova-address-field
```

You need to provide the Google places API key, you can get one from [https://console.developers.google.com](https://console.developers.google.com)
and then add the api key to your `.env` file

```shell
GOOGLE_PLACES_API_KEY=############################
```

## Usage

```php
use DigitalCloud\AddressField\AddressField;
// ....

AddressField::make('Address'),

//You can enable lat and lng inputs:
AddressField::make('Address')
          ->withLatLng(),

//You can enable map picking address:
AddressField::make('Address')
        ->withMap(),

//You can set the init location and zoom for the map:
AddressField::make('Address')
        ->withMap()->initLocation('24.6', '46.7')->zoom(5),

```

## Images
![addressfield2](https://user-images.githubusercontent.com/41853913/49798944-444c6800-fd4c-11e8-9299-0732960e8469.PNG)
