<template>
    <div>
        <b-form-group>
            <b-input-group class="mt-2">
                <b-form-file :disabled="disabled" v-model="file" ref="file"/>
                <b-button size="sm" variant="success" :disabled="disabled || !file" @click="addMedia">Upload</b-button>
            </b-input-group>
        </b-form-group>
        <div class="row">
            <div class="col-4 col-sm-4" v-for="(media, index) in product.media" :key="media.id">
                <b-card no-body :img-src="media.url" class="h100">
                    <b-button class="delete-button" size="sm" block variant="danger" @click="deleteImage(media)">
                        Delete
                    </b-button>
                </b-card>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {product: Object, disabled: Boolean},
    name: "EditProductMedia",
    data() {
        return {
            file: null
        }
    },
    methods: {
        addMedia() {
            const loader = this.$loading.show();
            const formData = new FormData();
            formData.append('image', this.file);
            this.$http.post(`/api/products/${this.product.id}/media`, formData, {
                headers: {"Content-Type": "multipart/form-data"},
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

.card-img {
    margin-top: auto;
    margin-bottom: auto;
}

.delete-button {
    position: absolute;
    bottom: 0px;
    width: 100%;
}
</style>
