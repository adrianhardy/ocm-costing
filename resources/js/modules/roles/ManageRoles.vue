<template>
    <div>
        <b-card class="shadow-sm">
            <div class="d-flex justify-content-between">
                <h4 class="align-middle">Permissions and Roles</h4>
            </div>

            <b-table-simple borderless>
                <tr>
                    <th class="w-5"></th>
                    <th
                        style="width: 200px"
                        class="text-center align-middle text-nowrap"
                        v-if="roles.length >= 1" v-for="role in roles"
                        :key="role.id"
                    >
                        {{ role.name | niceRoleName }}
                    </th>
                </tr>
                <tr v-if="permissions.length >= 1"
                    v-for="permission in permissions"
                    :key="permission.id">
                    <td>{{ permission.name | nicePermName | niceRoleName }}</td>
                    <td class="align-middle" v-for="role in roles" :key="role.id">
                        <div v-for="rolePermission in role.permissions">
                            <b-form-checkbox class="text-center" switch v-if="rolePermission.id === permission.id"
                                             v-model="rolePermission.has_permission"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td v-if="roles.length >= 1" v-for="role in roles" :key="role.id">
                        <div class="text-center">
                            <b-button size="sm" variant="outline-danger" v-if="!role.is_protected"
                                      @click="trashRole(role.id)">Delete
                            </b-button>
                        </div>
                    </td>
                </tr>
            </b-table-simple>


            <b-modal ref="modal" title="Create Role" @ok.prevent="createNewRole" ok-title="Create Role">
                <b-form-group label="Name"
                              description="This is the name for your new role, please make sure you save any changes before creating this role">
                    <b-form-input v-model="roleName"/>
                </b-form-group>
            </b-modal>

            <div class="d-flex justify-content-end mt-4">
                <b-button @click="open()" variant="primary" class="mr-2">Create Role</b-button>
                <b-button variant="success" @click="save()">
                    Save
                </b-button>
            </div>
        </b-card>

    </div>
</template>

<script>
export default {
    name: "ManageRoles",
    filters: {
        niceRoleName(value) {
            value = value.toString().split('_');
            _.forEach(value, function (w, i) {
                return value[i] = value[i].charAt(0).toUpperCase() + value[i].slice(1);
            })
            value = value.join(" ");
            return value;
        },
        nicePermName(value) {
            value = value.toString().split('.');
            _.forEach(value, function (w, i) {
                return value[i] = value[i].charAt(0).toUpperCase() + value[i].slice(1);
            })
            value = value.join(" ");
            return value;
        }
    },
    mounted() {
        this.refresh();
    },
    methods: {
        async trashRole(id) {
            const result = await this.$bvModal.msgBoxConfirm(
                'Are you sure you want to delete this role?', {centered: true});

            if (!result) {
                return;
            }

            const loading = this.$loading.show();
            const url = `/api/admin/roles/${id}`
            this.$http.delete(url).then(response => {
                loading.hide();
                this.refresh();
            }).catch(error => {
                loading.hide();
                this.$handleExceptionResponse(error, true);
            });
        },
        open() {
            this.roleName = null;
            this.$refs['modal'].show()
        },
        createNewRole() {
            const loading = this.$loading.show();
            this.$http.post(this.url, {name: this.roleName, type: this.roleType}).then(response => {
                loading.hide();
                this.$refs['modal'].hide();
                this.refresh();
            }).catch(error => {
                loading.hide();
                this.$handleExceptionResponse(error, true);
            });
        },
        save() {
            const loading = this.$loading.show();
            this.$http.put(this.url, this.roles).then(response => {
                loading.hide();
                this.refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        refresh() {
            const loading = this.$loading.show();
            this.$http.get(this.url).then(response => {
                this.permissions = response.data.permissions;
                this.roles = response.data.roles;
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        }
    },
    data() {
        return {
            url: '/api/admin/roles',
            roleName: null,
            roleType: null,
            isCustomer: false,
            permissions: [],
            roles: []
        }
    }
}
</script>

<style scoped>

</style>
