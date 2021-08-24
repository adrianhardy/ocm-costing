<template>
  <div>
    <b-card class="shadow-sm">
      <slot name="title"/>
      {{redirectUrl}}
      <div class="row">
        <div class="col-12 col-md-6">
          <b-form-group label="Name">
            <b-form-input v-model="value.name"/>
          </b-form-group>
          <b-form-group label="Country">
            <b-form-input v-model="value.country"/>
          </b-form-group>
          <b-form-group label="Delivery Address">
            <b-form-input v-model="value.delivery_address"/>
          </b-form-group>
        </div>
        <div class="col-12 col-md-6">
          <b-form-group label="Contact Name">
            <b-form-input v-model="value.contact_name"/>
          </b-form-group>
          <b-form-group label="Contact Email">
            <b-form-input v-model="value.contact_email"/>
          </b-form-group>
          <b-form-group label="Contact Phone">
            <b-form-input v-model="value.contact_phone" type="tel"/>
          </b-form-group>
        </div>
      </div>
      <b-button class="float-right" variant="primary" @click="save()">Save</b-button>
    </b-card>
  </div>
</template>

<script>
export default {
  props: ['supplier-id', 'factory-id', 'is-factory'],
  name: "EditSupplier",
  mounted(){
    if(this.supplierId || this.factoryId){
      this.refresh();
    }
  },
  computed: {
    type(){
      if(this.isFactory){
        return 'Factory'
      } else {
        return 'Supplier'
      }
    },
    redirectUrl(){
      if(this.isFactory){
        return `/admin/factories`
      } else {
        return `/admin/suppliers`
      }
    },
    refreshSelect(){
      if(this.supplierId){
        return `/api/admin/suppliers/${this.supplierId}`
      } else if(this.factoryId){
        return `/api/admin/factories/${this.factoryId}`
      }
    },
    methodSelect(){
      if(this.supplierId || this.factoryId){
        return 'put'
      } else {
        return 'post'
      }
    },
    saveUrl() {
      if(this.isFactory){
        if(this.factoryId){
          return `/api/admin/factories/${this.factoryId}`
        } else {
          return `/api/admin/factories`
        }
      } else {
        if(this.supplierId){
          return `/api/admin/suppliers/${this.supplierId}`
        } else {
          return '/api/admin/suppliers'
        }
      }
    }
  },
  methods: {
    save(){
      const loader = this.$loading.show();
      const method = this.methodSelect;
      const payload = {...this.value};
      this.$http(this.saveUrl, {
        method: method,
        data: payload
      }).then(response => {
        this.user = response.data.data
        this.$bvToast.toast(this.type + ` has been saved`, {
          title: this.type,
          variant: 'success'
        });
        loader.hide();
        window.location.href = this.redirectUrl
      }).catch(error => {
        loader.hide();
        this.$handleExceptionResponse(error, true)
      })
    },
    async refresh(){
      const loader = this.$loading.show();
      const url = this.refreshSelect
      this.$http.get(url).then(response => {
        this.value = response.data.data;
        loader.hide();
      }).catch(error => {
        loader.hide();
        this.$handleExceptionResponse(error);
      })
    }
  },
  data(){
    return{
      value: {}
    }
  }
}
</script>

<style scoped>

</style>