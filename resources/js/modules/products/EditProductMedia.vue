<template>
  <div>
    <b-row>
      <b-col cols="4" v-for="(media, index) in product.media" :key="media.id">
        <b-card no-body :img-src="media.url">
          <b-button size="sm" block variant="outline-danger" @click="deleteImage(media)">Delete</b-button>
        </b-card>
      </b-col>
    </b-row>
    <b-form-file @input="addMedia($event) " ref="file"/>
  </div>
</template>

<script>
export default {
  props: {product: Object},
  name: "EditProductMedia",
  methods: {
    addMedia(file) {
      const loader = this.$loading.show();
      const formData = new FormData();
      formData.append('image', file);
      this.$http.post(`/api/products/${this.product.id}/media`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      }).then(() => {
        this.$refs['file'].reset();
        this.$emit('updated');
        loader.hide();
      }).catch(error => {
        this.$handleExceptionResponse(error, true);
        loader.hide();
      });
    },
    deleteImage(media) {

      this.$bvModal.msgBoxConfirm(
          "Are you sure you want to delete this image? Click 'OK' to confirm", {
            centered: true
          }).then(value => {
            const loader = this.$loading.show();
            this.$http.delete(`/api/products/${this.product.id}/media/${media.id}`).then(response => {
              this.$emit('updated');
              loader.hide();
            }).catch(error => {
              this.$handleExceptionResponse(error, true);
              loader.hide();
            });
          });
    }
  }
}
</script>

<style scoped>

</style>
