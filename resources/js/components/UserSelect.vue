<template>
  <vue-select :disabled="readonly" ref="select" :value="value"
              @input="$emit('selected', $event) && $emit('input', $event)" :options="options" :filterable="true"
              label="name"
  />
</template>

<script>
import {VueSelect} from 'vue-select';

export default {
  name: "UserSelect",
  components: {VueSelect},
  props: {
    filterRole: String,
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
      this.$http.get('/api/admin/users', {
        params: {
          all: true,
          role: this.filterRole,
          orderBy: 'name'
        }
      }).then(response => {
        this.options = response.data.items;
        this.$refs['select'].toggleLoading(false);
      }).catch(error => {
        console.log(error);
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
