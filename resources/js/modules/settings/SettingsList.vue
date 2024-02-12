<template>
    <b-card>
        <h4>Settings</h4>
        <b-table ref="table" :items="items" :fields="fields">
            <template #cell(value)="data">
                <b-input-group v-if="editing === data.item.name">
                    <b-form-input v-model="newValue">
                    </b-form-input>
                    <b-button variant="danger" @click="editing = false">
                        <b-icon-x></b-icon-x>
                    </b-button>
                    <b-button variant="success" @click="update">
                        <b-icon-check/>
                    </b-button>
                </b-input-group>
                <a v-else href="#" @click.prevent="edit(data.item)">
                    {{ data.value }}
                </a>
            </template>
        </b-table>
        <p>
            Please note that changing anything that impacts shipping will
            update the estimated landed cost of all products which may take
            up to 5 minutes - please be patient and do not interrupt the
            browser.
        </p>
    </b-card>
</template>

<script>
export default {
    name: "SettingsList",
    data() {
        return {
            editing: false,
            newValue: ''
        }
    },
    computed: {
        fields() {
            return [{
                key: 'label',
                tdClass: 'align-middle',
            }, {
                key: 'value',
                thClass: 'w-30'
            }]
        }
    },
    methods: {
        edit(setting) {
            this.newValue = setting.value
            this.editing = setting.name;
        },
        items() {
            return this.$http.get('/api/admin/settings').then(response => {
                return response.data.items;
            });
        },
        update() {
            const loader = this.$loading.show();
            this.$http.post('/api/admin/settings', {
                key: this.editing,
                value: this.newValue
            },{
                timeout: 5 * 60 * 1000 // 5 minute timeout
            }).then(() => {
                loader.hide();
                this.editing = false;
                this.newValue = '';
                this.$refs['table'].refresh();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        }
    }
}
</script>

<style scoped>

</style>
