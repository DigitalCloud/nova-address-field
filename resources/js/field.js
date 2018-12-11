Nova.booting((Vue, router) => {
    Vue.component('index-address-field', require('./components/IndexField'));
    Vue.component('detail-address-field', require('./components/DetailField'));
    Vue.component('form-address-field', require('./components/FormField'));
})
