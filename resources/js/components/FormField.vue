<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div class="flex" v-if="manualFill">
                <div class="w-4/5 p-1">
                    <vue-google-autocomplete
                        ref="address"
                        :id="field.attribute"
                        :dusk="field.attribute"
                        class="w-full form-control form-input form-input-bordered"
                        :class="errorClasses"
                        :placeholder="field.name"
                        :country="field.countries"
                        :types="types"
                        v-on:placechanged="getAddressData">
                    </vue-google-autocomplete>
                </div>
                <div class="w-1/5 p-1">

                    <button type="button" v-if="manualFill" v-on:click="fillFields" :disabled="!hasUnfilledChanges"
                            class="btn btn-default btn-primary hover:bg-primary-dark w-full">{{manualFill}}
                    </button>
                </div>
            </div>
            <vue-google-autocomplete
                v-else
                ref="address"
                :id="field.attribute"
                :dusk="field.attribute"
                class="w-full p-1 form-control form-input form-input-bordered"
                :class="errorClasses"
                :placeholder="field.name"
                :country="field.countries"
                :types="types"
                v-on:placechanged="getAddressData">
            </vue-google-autocomplete>

            <div v-if="!field.hideToggles" class="flex w-full pt-2">
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
            addressData: {
                latitude: this.field.lat || '',
                longitude: this.field.lng || '',
                address: ''
            },
            map: null,
            marker: null,
            geocoder: new google.maps.Geocoder,
            showMap: this.field.withMap || false,
            showLngLat: this.field.withLatLng || false,
            hideToggles: this.field.hideToggles || false,
            countryCode: this.field.countryCode || false,
            country: this.field.country || false,
            administrative_area_level_1: this.field.administrative_area_level_1 || false,
            locality: this.field.locality || false,
            postal_code: this.field.postal_code || false,
            timezone: this.field.timezone || false,
            address_field: this.field.address_field || false,
            latitude_field: this.field.latitude_field || false,
            longitude_field: this.field.longitude_field || false,
            relatedValues: {},
            relatedWatchers: [],
            addressIsInitializing: this.field.do_not_store ? true : false,
            manualFill: this.field.manual_fill || false,
            hasUnfilledChanges: false,
            types: ['geocode', 'establishment']
        }
    },

    mounted: function () {
        if (this.field.withMap) {
            this.initMap()
        }

        if (this.field.do_not_store){
            this.$parent.$children.forEach(component => {
                if (component.field && [this.field.latitude_field, this.field.longitude_field, this.field.address_field].includes(component.field.attribute)){
                    const comp = component;
                    const watcher = component.$watch('value', value => {
                        this.relatedValues[comp.field.attribute] = value;
                        if (this.addressIsInitializing){
                            this.initializeAddress();
                        }
                    });
                    if (component.field.attribute === this.field.address_field){
                        this.relatedWatchers.push( watcher );
                    }
                }
            })

        }
    },

    methods: {
        getAddressComponent: function (address_components, component, short=false){
            const res = address_components.find(function (comp) {
                return comp.types.includes(component)
            })
            if (!res){
                return;
            }
            if (short){
                return res.short_name;
            }

            return res.long_name;
        },
        getFirstOccurenceOfComponent: function (results, component) {
            const address_components = results.map(e => e.address_components);

            let foundComponent = null;

            for (let i = 0; i < address_components.length; i++) {
                if (foundComponent) {
                    return foundComponent.long_name;
                }

                foundComponent = address_components[i].find(function (comp) {
                    return comp.types.includes(component)
                })
            }

            return null;
        },
        getAddressData: function (addressData, placeResultData, id) {
            this.forgetRelatedWatchers()
            this.addressData.latitude = addressData.latitude;
            this.addressData.longitude = addressData.longitude;
            this.addressData.formatted_address = placeResultData.formatted_address;

            this.addressData.countryCode = this.getAddressComponent(placeResultData.address_components, 'country', true);
            this.addressData.country = addressData.country;
            this.addressData.administrative_area_level_1 = addressData.administrative_area_level_1;
            this.addressData.locality = addressData.locality || addressData.administrative_area_level_1;
            this.addressData.postal_code = addressData.postal_code;

            this.hasUnfilledChanges = true;
            this.refreshMap()
        },
        forgetRelatedWatchers: function(){
            this.addressIsInitializing = false;
            this.relatedWatchers.forEach(watcher => watcher());
        },
        relatedAreEmpty: function(){
            if (this.field.address_field && this.relatedValues[this.field.address_field] ) {
                if (this.field.address_field_array_key){
                    if (this.relatedValues[this.field.address_field][this.field.address_field_array_key]){
                        return false;
                    }
                } else {
                    return false;
                }
            }
            if (this.field.latitude_field && this.relatedValues[this.field.latitude_field]) {
                return false;
            }
            if (this.field.longitude_field && this.relatedValues[this.field.longitude_field]) {
                return false;
            }
            return true;
        },
        initializeAddress: function(){
            if (this.field.address_field && this.relatedValues[this.field.address_field]){
                let v = this.relatedValues[this.field.address_field];
                if (this.field.address_field_array_key){
                    v = v[this.field.address_field_array_key]
                }
                this.addressData.formatted_address = v;
                this.$refs.address.update(this.addressData.formatted_address);
            }
            if (this.field.latitude_field && this.relatedValues[this.field.latitude_field]) {
                this.addressData.latitude = this.relatedValues[this.field.latitude_field];
                this.refreshMap()
            }
            if (this.field.longitude_field && this.relatedValues[this.field.longitude_field]) {
                this.addressData.longitude = this.relatedValues[this.field.longitude_field];
                this.refreshMap()
            }
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
            // if(!this.addressData.address) {
            //     this.geocode(center)
            // }
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
                        _this.addressData.latitude = latLng.lat()
                        _this.addressData.longitude = latLng.lng()
                        _this.addressData.formatted_address = results[0].formatted_address

                        _this.addressData.countryCode = _this.getAddressComponent(results[0].address_components, 'country', true);
                        _this.addressData.country = _this.getAddressComponent(results[0].address_components, 'country');
                        _this.addressData.administrative_area_level_1 = _this.getAddressComponent(results[0].address_components, 'administrative_area_level_1');
                        _this.addressData.locality = _this.getFirstOccurenceOfComponent(results, 'locality') || _this.addressData.administrative_area_level_1;
                        _this.addressData.postal_code = _this.getAddressComponent(results[0].address_components, 'postal_code');
                        _this.forgetRelatedWatchers()
                        _this.hasUnfilledChanges = true;
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
            if (this.field.do_not_store) {

            } else {
                if(this.value) {
                    this.addressData = JSON.parse(this.value)
                    this.$refs.address.update(this.addressData.formatted_address);
                }
            }
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            if (this.field.do_not_store){
                return;
            }
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },
        fillFields(){
            this.updateFields(this.addressData)
        },

        updateTimeZoneField() {
            this.$nextTick(() => {
                for(const [id, name] of Object.entries(this.field.all_time_zones)) {
                    if (this.addressData.timeZoneName === name) {
                        Nova.$emit(this.field.timezone + '-value', id);
                        break;
                    }
                }
            });
        },

        updateFields(addressData){
            this.hasUnfilledChanges = false;
            this.$nextTick(() => {
                Nova.$emit(this.field.countryCode + '-value', addressData.countryCode);
                Nova.$emit(this.field.country + '-value', addressData.country);
                Nova.$emit(this.field.locality + '-value', addressData.locality);
                Nova.$emit(this.field.administrative_area_level_1 + '-value', addressData.administrative_area_level_1);
                var name = addressData.formatted_address;
                if (this.field.address_field_array_key){
                    name = {};
                    name[this.field.address_field_array_key] = addressData.formatted_address;
                }

                Nova.$emit(this.field.address_field + '-value', name);
                Nova.$emit(this.field.postal_code + '-value', addressData.postal_code);
            });
        },
        updateGeoLocationFields(addressData) {
            this.$nextTick(() => {
                Nova.$emit(this.field.latitude_field + '-value', addressData.latitude);
                Nova.$emit(this.field.longitude_field + '-value', addressData.longitude);
            });
        }
    },

    computed: {
        computed() {

        }
    },

    watch: {
        'addressData' : {
            handler: async function (newAddressData) {
                this.value = JSON.stringify(newAddressData)
                this.mapOptions.center = new google.maps.LatLng(newAddressData.latitude, newAddressData.longitude)

                if (!this.addressIsInitializing && (!this.manualFill || this.relatedAreEmpty())){
                    this.updateFields(newAddressData)
                }
                if (!this.addressIsInitializing){
                    this.updateGeoLocationFields(newAddressData);
                }

                if (this.field.timezone && !this.addressIsInitializing) {
                    const baseTimeZoneUrl = 'https://maps.googleapis.com/maps/api/timezone/json?';
                    const timezoneParams = new URLSearchParams({
                        location: `${newAddressData.latitude},${newAddressData.longitude}`,
                        timestamp: Date.now() / 1000,
                        key: Nova.config.googleTimezoneApiKey
                    });
                    const response = await axios.get(baseTimeZoneUrl + timezoneParams.toString());
                    this.addressData.timeZoneName = response.data.timeZoneId;
                    this.updateTimeZoneField();
                }
            },
            deep: true
        },
    }
}
</script>


<style scoped>
.google-map {
    height: 300px;
    margin: 0 auto;
    background: gray;
    border:solid 1px #ccc;
}
</style>
