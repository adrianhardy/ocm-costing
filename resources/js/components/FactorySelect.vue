<template>
  <vue-select :multiple="multiple" :disabled="readonly" ref="select" :value="value" @input="$emit('selected', $event) && $emit('input', $event)" :options="options" :filterable="true" label="name" />
</template>

<script>
import {VueSelect} from 'vue-select';
export default {
  name: "FactorySelect",
  components: {VueSelect},
  props: {
    multiple: Boolean,
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
      this.$refs['select'].toggleLoading(true);
      this.$http.get('/api/admin/factories', {params: {all: true}}).then(response => {
        this.options = response.data.items;
        this.$refs['select'].toggleLoading(false);
      }).catch(error => {
        this.$refs['select'].toggleLoading(false);
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
