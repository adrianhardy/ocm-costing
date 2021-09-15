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
          <b-select :disabled="currentUser.id == userId" v-model="user.role">
            <b-select-option value="super">Super</b-select-option>
            <b-select-option value="pricing">Pricing</b-select-option>
            <b-select-option value="admin">Admin</b-select-option>
          </b-select>
        </b-form-group>
      </div>
      <div class="col-12 col-md-6">
          <div class="col-12 col-md-6">
            <b-form-group label="Password">
              <b-form-input v-model="user.password" type="password"/>
            </b-form-group>
          </div>
          <div class="col-12 col-md-6">
            <b-form-group label="Confirm Password">
              <b-form-input v-model="user.password_confirmation" type="password"/>
            </b-form-group>
          </div>
        </div>
          <div v-else>
            <div class="alert alert-warning">
              Please note that suppliers can <strong>only</strong> access the system
              using time limited URLs. You can provide access to specific Sales Orders
              so that suppliers can review and update them.
            </div>
          </div>
      </div>
    <b-button class="float-right" variant="primary" @click="save()">Save</b-button>
  </b-card>
</div>
</template>

<script>
export default {
  props: ['isCreating', 'user-id', 'roles', 'current-user'],
  name: "EditUser",
  mounted() {
    if (this.userId) {
      this.refresh();
    }
  },
  watch: {
    role(newValue) {
      const role = _.find(this.roles, ['name', newValue]);
      this.user.roles = [role]
    }
  },
  computed: {
    isCreating() {
      return this.userId < 1;
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
    save() {
      const loader = this.$loading.show();
      const method = this.isCreating ? 'post' : 'put'
      const payload = {...this.user};
      this.$http(this.saveUrl, {
        method: method,
        data: payload,
        params: {
          include: 'roles'
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
      this.$http.get(url,{params: {include: 'roles'}}).then(response => {
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
  data(){
    return {
      user: {}
    }
  }
}
</script>

<style scoped>

</style>
