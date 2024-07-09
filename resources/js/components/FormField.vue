<template>
    <DefaultField :field="field" :errors="errors">
        <template #field>
            <vue-google-autocomplete
                ref="address"
                :id="field.attribute"
                :dusk="field.attribute"
                class="w-full form-control form-input form-input-bordered"
                :class="errorClasses"
                :placeholder="field.name"
                :country="field.countries"
                @placechanged="getAddressData"
            />
            <div class="flex w-full pt-2">
                <div class="flex w-1/2">
                    <input type="checkbox" v-model="field.withMap" @change="toggleMap" class="py-2 pr-2" />
                    <label @click="toggleMap" class="inline-block text-80 pt-2 leading-tight">Show Map</label>
                </div>
                <div class="flex w-1/2">
                    <input type="checkbox" v-model="field.withLatLng" @change="toggleLatLng" class="py-2 pr-2" />
                    <label @click="toggleLatLng" class="inline-block text-80 pt-2 leading-tight">Show Coordinates</label>
                </div>
            </div>
            <div v-show="field.withLatLng" class="flex flex-wrap w-full">
                <div class="flex w-1/2">
                    <div class="w-1/5 py-3">
                        <label class="inline-block text-80 pt-2 leading-tight" for="latitude">Lat</label>
                    </div>
                    <div class="py-3 pr-2 w-4/5">
                        <input
                            id="latitude"
                            type="text"
                            class="w-full form-control form-input form-input-bordered"
                            :class="errorClasses"
                            placeholder="lat"
                            v-model="addressData.latitude"
                            @change="refreshAddressData"
                        />
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="w-1/5 py-3">
                        <label class="inline-block text-80 pt-2 leading-tight" for="longitude">Lng</label>
                    </div>
                    <div class="py-3 w-4/5">
                        <input
                            id="longitude"
                            type="text"
                            class="w-full form-control form-input form-input-bordered"
                            :class="errorClasses"
                            placeholder="lng"
                            v-model="addressData.longitude"
                            @change="refreshAddressData"
                        />
                    </div>
                </div>
            </div>

            <div class="google-map w-full" ref="mapElement" v-show="field.withMap"></div>

            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import { ref, onMounted, getCurrentInstance } from 'vue';
import VueGoogleAutocomplete from 'vue-google-autocomplete';

export default {
    mixins: [FormField, HandlesValidationErrors],
    emits: ['addressChanged'],
    components: {VueGoogleAutocomplete},
    props: {
        resourceName: String,
        resourceId: String,
        field: Object,
    },

    setup(props) {
        const { emit } = getCurrentInstance();

        let addressData = ref({
            latitude: props.field.lat || '',
            longitude: props.field.lng || '',
            address: '',
            zoom: props.field.zoom || 5,
        });

        const address = ref('');
        const mapElement = ref(null);
        const googleMap = ref(null);
        const geocoder = new google.maps.Geocoder();
        const showMap = ref(props.field.withMap || false);
        const showLngLat = ref(props.field.withLatLng || false);
        let marker;

        const getAddressData = (event, placeResultData, id) => {
            addressData.value.latitude = event.latitude;
            addressData.value.longitude = event.longitude;
            addressData.value.formatted_address = placeResultData.formatted_address;
            refreshMap();
            emit('addressChanged', addressData);
        };

        const refreshAddressData = () => {
            geocode(new google.maps.LatLng(addressData.value.latitude, addressData.value.longitude));
            refreshMap();
            fillFieldText();
        };

        const initMap = () => {
            if (!showMap.value) return;

            const element = mapElement.value;
            const center = new google.maps.LatLng(addressData.value.latitude, addressData.value.longitude);
            const options = {
                zoom: addressData.value.zoom || props.field.zoom || 5,
                center: center,
            };

            googleMap.value = new google.maps.Map(element, options);

            fillFieldText();

            if (!addressData.value.address) {
                geocode(center, true);
            }

            marker = new google.maps.Marker({
                position: center,
                map: googleMap.value,
            });

            google.maps.event.addListener(googleMap, 'zoom_changed', () => {
                addressData.value.zoom = googleMap.value.getZoom();
            });

            google.maps.event.addListener(googleMap.value, 'click', (event) => {
                if (marker) {
                    marker.setMap(null);
                }

                marker = new google.maps.Marker({
                    position: event.latLng,
                    map: googleMap.value
                });
                geocode(event.latLng);
                fillFieldText();
            });
        };

        const refreshMap = () => {
            if (!googleMap.value) return;

            const LatLng = new google.maps.LatLng(addressData.value.latitude, addressData.value.longitude);
            googleMap.value.setCenter(LatLng);

            if (marker) {
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                position: LatLng,
                map: googleMap.value
            });
        };

        const geocode = (latLng) => {
            geocoder.geocode({'location': latLng}, (results, status) => {
                if (status === 'OK') {
                    if (results[0]) {
                        addressData.value.latitude = parseFloat(latLng.lat().toFixed(6));
                        addressData.value.longitude = parseFloat(latLng.lng().toFixed(6));
                        addressData.value.formatted_address = results[0].formatted_address;
                        emit('addressChanged', addressData);
                    }
                }
            });
        };

        const setInitialValue = () => {
            const value = props.field.value || '';

            if (value) {
                addressData.value = JSON.parse(value);
            }
        };

        const fill = (formData) => {
            formData.append(props.field.attribute, JSON.stringify(addressData.value));
        };

        const fillFieldText = () => {
            if (addressData.value.formatted_address) {
                address.value.autocompleteText = addressData.value.formatted_address;
            }
        };

        const toggleMap = () => {
            props.field.withMap = !props.field.withMap;
        };

        const toggleLatLng = () => {
            props.field.withLatLng = !props.field.withLatLng;
        };

        onMounted(() => {
            if (showMap.value) {
                initMap();
            }
        });

        return {
            address,
            addressData,
            mapElement,
            googleMap,
            marker,
            geocoder,
            showMap,
            showLngLat,
            toggleMap,
            toggleLatLng,
            getAddressData,
            refreshAddressData,
            refreshMap,
            setInitialValue,
            fill,
            fillFieldText,
        };
    }
};
</script>

<style scoped>
.google-map {
    width: 720px;
    height: 300px;
    margin: 0 auto;
    background: gray;
    border: solid 1px #ccc;
}
</style>
