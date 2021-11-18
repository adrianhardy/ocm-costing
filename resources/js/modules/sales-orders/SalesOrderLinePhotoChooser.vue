<template>
    <div>
        <div @click="mode = 'edit'">
            <div v-if="line.selected_media && line.selected_media.id">
                <img style="max-width: 100%; max-height: 100px" :src="line.selected_media.url" />
            </div>
            <div v-else>
                <img style="max-width: 100%; max-height: 100px" v-if="line.product.media && line.product.media[0]" :src="line.product.media[0].url" />
            </div>
        </div>
        <vue-select placeholder="..." v-if="mode === 'edit'" :options="media" @close="mode = ''" @input="updateImage($event)">
            <template slot="option" slot-scope="option">
                <img style="max-width:100px; max-height: 100px;" :src="option.url" class="img-thumbnail"/>
            </template>
        </vue-select>
    </div>
</template>

<script>
export default {
    name: "SalesOrderLinePhotoChooser",
    props: {
        order: Object,
        line: Object,
        media: Array
    },
    methods: {
        updateImage(image) {
            const loader = this.$loading.show();
            this.$http.post(`/api/sales-orders/${this.order.id}/lines/${this.line.id}/media`, {
                media_id: image.id
            }).then(response => {
                loader.hide();
                this.$emit('updated');
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })

        }
    },
    data() {
        return {
            mode: ''
        }
    }
}
</script>

<style scoped>

</style>
