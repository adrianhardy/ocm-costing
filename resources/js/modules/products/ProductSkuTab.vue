<template>
    <b-tab title="SKUs">
        <b-form-group label="Primary ASIN" label-cols="4">
            <b-form-input v-model="primary_asin" @change="update()" />
        </b-form-group>
        <b-form-group label="Primary SKU" label-cols="4">
            <b-form-input v-model="primary_sku" @change="update()" />
        </b-form-group>
        <b-form-group label="Additional ASINs">
            <b-tags placeholder="Add ASIN" v-model="additional_asins" @input="startUpdate()" />
        </b-form-group>
        <b-form-group label="Additional SKUs">
            <b-tags placeholder="Add SKU" v-model="additional_skus" @input="startUpdate()" />
        </b-form-group>
    </b-tab>
</template>

<script>
export default {
    name: "ProductSkuTab",
    props: {
        product: Object
    },
    mounted() {
        this.setup(this.product);
    },
    methods: {
        // There was an infinite loop of update being called on
        // products where either addition skus, or additional asins
        // were null in the database, this is a fix for that
        startUpdate(){
            if(Array.isArray(this.additional_asins) && Array.isArray(this.additional_skus)) {
                this.update();
            }
        },
        update() {
            const loader = this.$loading.show();
            this.$http.post(`/api/products/${this.product.id}/skus`, {
                primary_asin: this.primary_asin,
                primary_sku: this.primary_sku,
                additional_asins: this.additional_asins,
                additional_skus: this.additional_skus
            }).then(response => {
                loader.hide();
                this.$bvToast.toast('SKU updated', {
                    variant: 'success'
                });
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                this.setup(this.product);
                loader.hide();
            });
        },
        setup(product) {
            this.additional_asins = product.additional_asins;
            this.additional_skus = product.additional_skus;
            this.primary_asin = product.primary_asin;
            this.primary_sku = product.primary_sku;
        }
    },
    data() {
        return {
            additional_skus: [],
            additional_asins: [],
            primary_sku: '',
            primary_asin: ''
        }
    }
}
</script>

<style scoped>
    ::v-deep .b-form-tags-button {
        min-width: 5em;
        min-height: 1.5em;
    }
</style>
