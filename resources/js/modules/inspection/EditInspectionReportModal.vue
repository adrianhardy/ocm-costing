<template>
    <b-modal ref="modal" title="Inspection Report">

    </b-modal>
</template>

<script>
export default {
    name: "EditInspectionReportModal",
    props: {
        inspectionReportId: Number,
        autoOpen: true
    },
    mounted() {
        if (this.autoOpen) {
            this.open();
            this.refresh();
        }
    },
    methods: {
        refresh() {
            const loader = this.$loading.show();
            const url = `/api/inspections/${this.inspectionReportId}`;
            const include = 'product,product.media';
            this.$http.get(url, {params:{include}}).then(response => {
                loader.hide();
                this.inspection = response.data;
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            });
        },
        open() {
            this.$refs['modal'].show();
        },
        close() {
            this.$refs['modal'].hide();
        }
    },
    data() {
        return {
            inspection: {}
        }
    }
}
</script>

<style scoped>

</style>
