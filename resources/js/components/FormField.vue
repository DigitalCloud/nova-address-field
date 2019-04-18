<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">

            <vue-google-autocomplete
                ref="address"
                :id="field.attribute"
                :dusk="field.attribute"
                class="w-full form-control form-input form-input-bordered"
                :class="errorClasses"
                :placeholder="field.name"
                :country="field.countries"
                v-on:placechanged="getAddressData">
            </vue-google-autocomplete>
            <div class="flex w-full pt-2">
                <div class="flex w-1/2">
                    <checkbox
                        :checked="field.withMap"
                        @input="toggleMap"
                        class="py-2 pr-2"

                    />
                    <label @click="toggleMap" class="inline-block text-80 pt-2 leading-tight">Show Map</label>
                </div>

                <div class="flex w-1/2">
                    <checkbox
                        :checked="field.withLatLng"
                        @input="toggleLatLng"
                        class="py-2 pr-2"
                    />
                    <label @click="toggleLatLng" class="inline-block text-80 pt-2 leading-tight">Show Coordinations</label>
                </div>
            </div>
            <div v-show="field.withLatLng" class="flex flex-wrap w-full">
                <div class="flex w-1/2">
                    <div class="w-1/5 py-3">
                        <label class="inline-block text-80 pt-2 leading-tight" for="latitude">Lat</label>
                    </div>
                    <div class="py-3 pr-2 w-4/5">
                        <input id="latitude" type="text"
                               class="w-full form-control form-input form-input-bordered"
                               :class="errorClasses"
                               placeholder="long"
                               v-model="addressData.latitude"
                               v-on:change="refreshAddressData"
                        />
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="w-1/5 py-3">
                        <label class="inline-block text-80 pt-2 leading-tight" for="longitude">Lng</label>
                    </div>
                    <div class="py-3 w-4/5">
                        <input id="longitude" type="text"
                               class="w-full form-control form-input form-input-bordered"
                               :class="errorClasses"
                               placeholder="long"
                               v-model="addressData.longitude"
                               v-on:change="refreshAddressData"
                        />
                    </div>
                </div>
            </div>

            <div class="google-map w-full" :id="mapName" v-show="field.withMap"></div>

            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import VueGoogleAutocomplete from 'vue-google-autocomplete'

export default {

    components: { VueGoogleAutocomplete },

    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data: function () {
        return {
            mapName: this.name + "-map",
            mapOptions: {
                center: new google.maps.LatLng(40.730610, -98.935242),
                zoom: 5
            },
            address: '',
            addressData: {latitude: this.field.lat || '', longitude: this.field.lng || '', address: ''},
            map: null,
            marker: null,
            geocoder: new google.maps.Geocoder,
            showMap: this.field.withMap || false,
            showLngLat: this.field.withLatLng || false,
        }
    },

    mounted: function () {
        if(this.field.withMap) {
            this.initMap()
        }
    },

    methods: {
        getAddressData: function (addressData, placeResultData, id) {
            this.addressData.latitude = addressData.latitude;
            this.addressData.longitude = addressData.longitude;
            this.addressData.formatted_address = placeResultData.formatted_address;
            this.refreshMap()
        },

        refreshAddressData() {
            this.geocode(new google.maps.LatLng(this.addressData.latitude, this.addressData.longitude))
            this.refreshMap()
        },

        toggleMap() {
            this.field.withMap = !this.field.withMap
        },
        toggleLatLng() {
            this.field.withLatLng = !this.field.withLatLng
        },

        initMap() {
            console.log('hsbfjsdbfjdbjdb')
            const element = document.getElementById(this.mapName);
            let center =  new google.maps.LatLng(this.addressData.latitude, this.addressData.longitude)

            // setup map options
            const options = {
                zoom: this.field.zoom || 5,
                center: center
            };
            // initialize the map
            this.map = new google.maps.Map(element, options);

            // get formatted address for the latitude and longitude
            if(!this.addressData.address) {
                this.geocode(center)
            }
            // adding initial marker
            this.marker = new google.maps.Marker({
                position: center,
                map: this.map
            });



            var _this = this;
            google.maps.event.addListener(this.map, 'click', function(event) {
                if (_this.marker) {
                    _this.marker.setMap(null);
                }
                _this.marker = new google.maps.Marker({
                    position: event.latLng,
                    map: _this.map
                });
                _this.geocode(event.latLng)
            });
        },

        refreshMap() {
            if(this.map === null) {
                return;
            }
            let LatLng = new google.maps.LatLng(this.addressData.latitude, this.addressData.longitude)
            this.map.setCenter(LatLng);
            if (this.marker) {
                this.marker.setMap(null)
            }

            this.marker = new google.maps.Marker({
                position: LatLng,
                map: this.map
            });
        },

        resetMarker() {
            this.marker.setMap(null)
        },

        geocode(latLng) {
            let _this = this
            this.geocoder.geocode({'location': latLng}, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        _this.addressData.latitude = latLng.lat().toFixed(6)
                        _this.addressData.longitude = latLng.lng().toFixed(6)
                        _this.addressData.formatted_address = results[0].formatted_address
                        _this.$refs.address.update(results[0].formatted_address);
                    } else {
                        //window.alert('No results found');
                    }
                } else {
                    //window.alert('Geocoder failed due to: ' + status);
                    //this.errors = false;
                }
            });
        },

        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || ''
            if(this.value) {
                this.addressData = JSON.parse(this.value)
                this.$refs.address.update(this.addressData.formatted_address);
            }
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },
    },

    computed: {
        computed() {

        }
    },

    watch: {
        'addressData' : {
            handler: function (newAddressData) {
                this.value = JSON.stringify(newAddressData)
                this.mapOptions.center = new google.maps.LatLng(newAddressData.latitude, newAddressData.longitude)
            },
            deep: true
        }
    }
}
</script>


<style scoped>
    .google-map {
        width: 720px;
        height: 300px;
        margin: 0 auto;
        background: gray;
        border:solid 1px #ccc;
    }
</style>
