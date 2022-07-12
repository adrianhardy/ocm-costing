<template>
    <b-card>
        <h3>Amazon Guidance</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <h4>Small + Light</h4>
                <div class="d-flex justify-content-center">
                    <img v-if="images && images.sl" :src="images.sl" width="300" />
                </div>
                <b-form-file accept="image/*" class="mt-3" v-model="sl" />
                <div class="mt-3 d-flex justify-content-center">
                    <b-button variant="success" @click="update('sl', sl)">
                        Upload
                    </b-button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <h4>Local FBA</h4>
                <img v-if="images && images.local_fba" :src="images.local_fba" width="300"/>
                <b-form-file accept="image/*" class="mt-3" v-model="local_fba" />
                <div class="mt-3 d-flex justify-content-center">
                    <b-button variant="success" @click="update('local_fba', local_fba)">
                        Upload
                    </b-button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <h4>EFN FBA</h4>
                <div class="d-flex justify-content-center">
                    <img v-if="images && images.efn_fba" :src="images.efn_fba" width="300"/>
                </div>
                <b-form-file accept="image/*" class="mt-3" v-model="efn_fba"></b-form-file>
                <div class="mt-3 d-flex justify-content-center">
                    <b-button variant="success" @click="update('efn_fba', efn_fba)">
                        Upload
                    </b-button>
                </div>
            </div>
        </div>

    </b-card>
</template>

<script>
export default {
    name: "AmazonGuidanceUpload",
    mounted() {
        this.refresh();
    },
    methods: {
      update(collection, file){
          const formData = new FormData()
          formData.append('file', file)
          formData.append('collection', collection)

          const loader = this.$loading.show();
          const url = '/api/guidance'
          this.$http.post(url, formData).then(response => {
              loader.hide();
              this.refresh();
          }).catch(error => {
              this.$handleExceptionResponse(error, true)
              loader.hide();
          })
      },
      refresh(){
          const loading = this.$loading.show();
          const url = '/api/guidance'
          this.$http.get(url).then(response => {
              this.images = response.data
              console.log(this.data)
              loading.hide()
          }).catch(error => {
              this.$handleExceptionResponse(error, true)
              loading.hide();
          })
      }
    },
    data(){
        return {
            efn_fba:   null,
            local_fba: null,
            sl:        null,
            images: {}
        }
    }

}
</script>

<style scoped>

</style>
