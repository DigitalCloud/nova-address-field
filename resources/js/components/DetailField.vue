<template>
    <div class="flex border-b border-40">
        <div class="w-1/4 py-4">
            <h4 class="font-normal text-80">{{field.name}}</h4>
        </div>
        <div class="w-3/4 py-4">
            <p class="text-90">{{addressData.formatted_address}} | ({{addressData.latitude.toFixed(6)}}, {{addressData.longitude.toFixed(6)}})</p>
        </div>
    </div>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    data: function () {
        return {

        }
    },

    mounted: function () {

    },

    computed: {
        addressData: function () {
            return JSON.parse(this.field.value)
        }
    }
}
</script>
