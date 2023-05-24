<template>
    <vue-select :disabled="readonly" ref="select" :value="value" :placeholder="placeholder"
                @input="$emit('selected', $event) && $emit('input', $event)" :options="options" :filterable="true"
                :label="label"/>
</template>

<script>
import {VueSelect} from 'vue-select';

export default {
    name: "CountrySelect",
    components: {VueSelect},
    props: {
        'placeholder': String,
        'use-code': Boolean,
        'readonly': Boolean,
        'response-include': {
            type: String,
            default: ''
        },
        'value': [Object, String, Array],
        'amazon': {
            type: Boolean,
            default: false
        }
    },
    mounted() {
        this.refresh();
    },
    computed: {
        label() {
            if (this.useCode) {
                return 'iso_2_code';
            }
            return 'name';
        }
    },
    methods: {
        refresh() {
            const params = {
                include: this.responseInclude,
                all: true,
                amazon: this.amazon,
                orderBy: 'iso_2_code',
                sortedBy: 'asc'
            };

            this.$refs['select'].toggleLoading(true);
            this.$http.get('/api/countries', {params}).then(response => {
                this.options = response.data.items;
                this.$refs['select'].toggleLoading(false);
            }).catch(error => {
                this.$refs['select'].toggleLoading(false);
                this.$handleExceptionResponse(error, true);
            });
        }
    },
    data() {
        return {
            options: [],
        }
    }
}
</script>

<style scoped>

</style>