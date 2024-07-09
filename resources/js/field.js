import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-address-field', IndexField)
  app.component('detail-address-field', DetailField)
  app.component('form-address-field', FormField)
})
