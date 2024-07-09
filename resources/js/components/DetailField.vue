<template>
    <PanelItem :index="index" :field="field">
        <template v-slot:value>
            <p class="text-90">{{ addressData.formatted_address }} | ({{ addressData.latitude.toFixed(6) }},
                {{ addressData.longitude.toFixed(6) }})</p>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        addressData() {
            return JSON.parse(this.field.value);
        }
    }
}
</script>
