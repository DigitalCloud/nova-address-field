<template>
    <span class="text-90">{{addressData.formatted_address}} <br/> ({{addressData.latitude.toFixed(6)}}, {{addressData.longitude.toFixed(6)}})</span>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    computed: {
        addressData: function () {
            return JSON.parse(this.field.value)
        }
    }
}
</script>
