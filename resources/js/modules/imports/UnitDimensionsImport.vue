<template>
    <div>
        <b-card class="shadow-sm">
            <h4>Unit Dimensions Importer</h4>
            <div class="d-flex justify-content-start">
                <b-form-group class="w-25" label="Upload">
                    <b-form-file v-model="file" accept=".csv"></b-form-file>
                </b-form-group>
                <b-form-group class="ml-5" label="Options">
                    <b-form-checkbox v-model="skip" :value="1" :unchecked-value="0">Skip First Row</b-form-checkbox>
                </b-form-group>
            </div>
            <div class="d-flex justify-content-end">
                <b-button variant="primary" @click="upload">Upload</b-button>
            </div>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "UnitDimensionsImport",
    methods: {
        upload(){
            const formData = new FormData()
            formData.append('file', this.file)
            formData.append('skip', this.skip)
            const loading = this.$loading.show();
            const url = '/api/admin/import/unit-dimensions'
            this.$http.post(url, formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                this.$bvToast.toast('Import Created... Processing', {
                    title: 'Unit Dimensions Import',
                    variant: 'success'
                })
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
    },
    data(){
        return {
            file: null,
            skip: 0
        }
    }
}
</script>

<style scoped>

</style>
