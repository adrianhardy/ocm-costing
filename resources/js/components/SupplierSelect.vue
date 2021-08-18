<template>
  <vue-select :disabled="readonly" ref="select" :value="value" @input="$emit('selected', $event) && $emit('input', $event)" :options="options" :filterable="true" label="name" />
</template>

<script>
import {VueSelect} from 'vue-select';
export default {
  name: "SupplierSelect",
  components: {VueSelect},
  props: {
    'readonly': Boolean,
    'response-include': {
      type: String,
      default: ''
    },
    'value': [Object, String, Array],
  },
  mounted() {
    this.refresh();
  },
  methods: {
    refresh() {
      const loader = this.$loading.show();
      this.$http.get('/api/suppliers', {params: {all: true}}).then(response => {
        this.options = response.data.items;
        loader.hide();
      }).catch(error => {
        loader.hide();
        this.$handleExceptionResponse(error, true);
      })
    }
  },
  data() {
    return {
      options: [],
    }
  }
}
</script>
