<template>
    <vue-select :disabled="disabled" :value="value" @input="$emit('selected', $event) && $emit('input', $event)"
                :options="roles" label="name" :multiple="multiple"
    >
        <template #selected-option="{ name }">
            <div style="display: flex; align-items: baseline">
                <span>{{ name | niceRoleName }}</span>
            </div>
        </template>
        <template #option="{ name }">
            <span>{{ name | niceRoleName }}</span>
        </template>
    </vue-select>
</template>

<script>

import VueSelect from 'vue-select';

export default {
    name: "RolesSelect",
    filters: {
        niceRoleName(value){
            value = value.toString().split('_');
            _.forEach(value, function(w, i) {
                return value[i] = value[i].charAt(0).toUpperCase() + value[i].slice(1);
            })
            value = value.join(" ");
            return value;
        },
    },
    props: [
        'value', 'disabled', 'multiple'
    ],
    components: {VueSelect},
    mounted() {
        this.refresh();
    },
    methods: {
        refresh(){
            const loading = this.$loading.show();
            const url = `/api/admin/roles?select=true`
            this.$http.get(url).then(response => {
                this.roles = response.data.roles
                loading.hide()
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        }
    },
    data(){
        return {
            roles: []
        }
    }
}
</script>

<style scoped>

</style>
