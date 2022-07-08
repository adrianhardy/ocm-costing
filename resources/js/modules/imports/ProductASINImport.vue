<template>
    <div>
        <b-card class="shadow-sm">
            <h4>Products ASIN Import</h4>
            <div class="d-flex justify-content-start">
                <b-form-group class="w-25" label="Upload">
                    <b-form-file v-model="file" accept=".csv" />
                </b-form-group>
                <b-form-group class="ml-5" label="Options">
                    <b-form-checkbox v-model="skip" :value="1" :unchecked-value="0">Skip First Row?</b-form-checkbox>
                </b-form-group>
            </div>
            <div class="d-flex justify-content-end">
                <b-button @click="upload" variant="primary">Upload</b-button>
            </div>
        </b-card>
        <b-card class="shadow-sm mt-2">
            <span>CSV Format should look like this... <br/></span>

            <ul>
                <li>article_number</li>
                <li>primary_sku</li>
                <li>primary_asin</li>
                <li>addition_skus</li>
                <li>additional_asins</li>
            </ul>

            <span>Addition fields format should look like... <br/></span>
            <b>sku_1,sku_2</b>

        </b-card>
    </div>
</template>

<script>
export default {
    name: "ProductsImport",
    methods: {
        upload(){
            const formData = new FormData()
            formData.append('file', this.file);
            formData.append('skip', this.skip);
            const loading = this.$loading.show();
            const url = '/api/admin/import/product-asin'
            this.$http.post(url, formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                this.$bvToast.toast('Import uploaded... Processing...', {
                    title: 'Product Import',
                    variant: 'Success'
                })
                loading.hide()
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        }
    },
    data(){
        return {
            skip: 0,
            file: null
        }
    }
}
</script>

<style scoped>

</style>
