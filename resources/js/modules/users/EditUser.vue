<template>
    <div>
        <b-card class="shadow-sm">
            <slot name="title"/>
            <div class="row">
                <div class="col-12 col-md-6">
                    <b-form-group label="Name">
                        <b-form-input v-model="user.name"/>
                    </b-form-group>
                    <b-form-group label="Email">
                        <b-form-input v-model="user.email"/>
                    </b-form-group>
                    <b-form-group label="Role">
                        <role-select v-model="user.roles" @selected="user.role = $event.name"></role-select>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-6">
                    <div v-if="user.role !== 'supplier'">
                        <b-form-group label="Password">
                            <b-form-input v-model="user.password" type="password"/>
                        </b-form-group>
                        <b-form-group label="Confirm Password">
                            <b-form-input v-model="user.password_confirmation" type="password"/>
                        </b-form-group>
                    </div>
                    <div v-else>
                        <b-form-group label="Supplier">
                            <supplier-select v-model="user.supplier"></supplier-select>
                        </b-form-group>
                        <b-form-group label="Factory">
                            <factory-select multiple v-model="user.factories" />
                        </b-form-group>
                        <div class="alert alert-warning">
                            Please note that suppliers can <strong>only</strong> access the system
                            using time limited URLs. You can provide access to specific Sales Orders
                            so that suppliers can review and update them.
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div>
                    <b-button v-if="user && user.id"  variant="danger" @click="deleteUser()">Lock</b-button>
                </div>
                <b-button variant="primary" @click="save()">Save</b-button>
            </div>
        </b-card>
    </div>
</template>

<script>
import SupplierSelect from "../../components/SupplierSelect";
import FactorySelect from "../../components/FactorySelect";
import RoleSelect from "../../components/RoleSelect";
export default {
    components: {FactorySelect, SupplierSelect, RoleSelect},
    props: ['user-id', 'roles', 'current-user'],
    name: "EditUser",
    mounted() {
        if (!this.isCreating) {
            this.refresh();
        }
    },
    computed: {
        isCreating() {
            return ! (this.userId > 0);
        },
        saveUrl() {
            if (this.isCreating) {
                return '/api/admin/users'
            } else {
                return `/api/admin/users/${this.userId}`
            }
        }
    },
    methods: {
        async deleteUser(){
            const response = await this.$bvModal.msgBoxConfirm('Are you sure you want to lock out this user?', {
                title: 'Lock User',
                centered: true,
                okTitle : 'Yes, lock user'
            })
            if(!response) {
                return;
            }
            const loading = this.$loading.show();
            this.$http.delete(this.saveUrl).then(response => {
                loading.hide();
                window.location.href = '/admin/users';
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        save() {
            const loader = this.$loading.show();
            const method = this.isCreating ? 'post' : 'put'
            const payload = {...this.user};
            this.$http(this.saveUrl, {
                method: method,
                data: payload,
                params: {
                    include: 'roles,supplier,factories'
                }
            }).then(response => {
                this.user = response.data
                if (response.data.roles && response.data.roles[0]) {
                    this.user.role = response.data.roles[0].name;
                }
                this.$bvToast.toast('User has been saved', {
                    title: 'User',
                    variant: 'success'
                });
                if (method === 'post') {
                    loader.hide()
                    window.location.href = '/admin/users'
                } else {
                    loader.hide();
                }
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true)
            })
        },
        async refresh() {
            const loader = this.$loading.show();
            const url = `/api/admin/users/${this.userId}`;
            this.$http.get(url, {params: {include: 'roles,supplier,factories'}}).then(response => {
                this.user = response.data;
                if (response.data.roles && response.data.roles[0]) {
                    this.user.role = response.data.roles[0].name;
                }
                loader.hide();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error);
            });
        }
    },
    data() {
        return {
            user: {}
        }
    }
}
</script>

<style scoped>

</style>
