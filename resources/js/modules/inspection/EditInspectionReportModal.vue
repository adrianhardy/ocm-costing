<template>
    <b-modal size="xl" ref="modal" title="Inspection Report">
        <div class="row">
            <div class="col-6 text-center">
                <h4>Item: {{ line.item_number }}</h4>
            </div>
            <div class="col-6 text-center">
                <h5>Article: {{ line.article_number }}</h5>
            </div>
        </div>
        <inspection-detail-row
            title="Name"
            :value="line.name"
            comments-placeholder="Comments on product description or title"
        />
        <inspection-detail-row
            title="Packing"
            :value="line.packing_instructions"
            input-type="textarea"
            comments-placeholder="Comments on packaging"
            @pass-updated="inspection.is_packaging_ok = $event"
            @comments-updated="inspection.packaging_comments = $event"
        />
        <inspection-detail-row
            title="Carton Gross Weight (kg)"
            :value="line.case_weight_kg"
            input-type="text"
            comments-placeholder="Comments on case weight"
            @pass-updated="inspection.is_case_weight_ok = $event"
            @comments-updated="inspection.case_weight_comments = $event"
        />
        <inspection-detail-row
            title="Unit Weight (kg)"
            :value="line.unit_weight_kg"
            input-type="text"
            comments-placeholder="Comments on unit weight"
            @pass-updated="inspection.is_unit_weight_ok = $event"
            @comments-updated="inspection.unit_weight_comments = $event"
        />
        <inspection-detail-row
            title="Inner Barcode"
            v-model="inspection.inner_barcode"
            input-type="text"
            comments-placeholder="Inner barcode comments"
            @pass-updated="inspection.is_inner_barcode_ok = $event"
            @comments-updated="inspection.inner_barcode_comments = $event"
        />
        <inspection-detail-row
            title="Outer Barcode"
            v-model="inspection.outer_barcode"
            input-type="text"
            comments-placeholder="Outer barcode comments"
            @pass-updated="inspection.is_outer_barcode_ok = $event"
            @comments-updated="inspection.outer_barcode_comments = $event"
        />
        <inspection-dimensions-row
            title="Carton Dimensions (LxWxH cm)"
            comments-placeholder="Comments on carton dimensions"
            :length="inspection.case_length_cm"
            :width="inspection.case_width_cm"
            :height="inspection.case_height_cm"
            @length-updated="inspection.case_length_cm = $event"
            @width-updated="inspection.case_width_cm = $event"
            @height-updated="inspection.case_height_cm = $event"
            @pass-updated="inspection.is_case_size_ok = $event"
            @comments-updated="inspection.product_test_comments = $event"
        />
        <inspection-dimensions-row
            title="Unit Dimensions (LxWxH cm)"
            comments-placeholder="Comments on unit dimensions"
            :length="inspection.unit_length_cm"
            :width="inspection.unit_width_cm"
            :height="inspection.unit_height_cm"
            @length-updated="inspection.unit_length_cm = $event"
            @width-updated="inspection.unit_width_cm = $event"
            @height-updated="inspection.unit_height_cm = $event"
            @pass-updated="inspection.is_unit_size_ok = $event"
            @comments-updated="inspection.product_test_comments = $event"
        />
        <hr />
        <inspection-detail-row
            title="Appearance"
            comments-placeholder="Appearance comments"
            @pass-updated="inspection.is_appearance = $event"
            @comments-updated="inspection.apperance_comments = $event"
        />
        <inspection-detail-row
            title="Instructions"
            comments-placeholder="Comments on instructions"
            @pass-updated="inspection.is_instructions_ok = $event"
            @comments-updated="inspection.instructions_comments = $event"
        />
        <inspection-detail-row
            title="Product Test Run"
            comments-placeholder="Comments on test run"
            @pass-updated="inspection.is_product_test_run_ok = $event"
            @comments-updated="inspection.product_test_comments = $event"
        />
    </b-modal>
</template>

<script>
import InspectionDetailRow from "./InspectionDetailRow";
import InspectionDimensionsRow from "./InspectionDimensionsRow";
export default {
    name: "EditInspectionReportModal",
    components: {InspectionDimensionsRow, InspectionDetailRow},
    props: {
        inspectionReportId: Number,
        // autoOpen is here during development only and should be removed
        autoOpen: true
    },
    computed: {
        line() {
            if (!this.inspection.id) {
                return {};
            }
            return this.inspection.sales_order_line;
        },
        product() {
            if (!this.inspection.id) {
                return {};
            }
            return this.inspection?.sales_order_line.product;
        }
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
            const include = 'sales_order_line,sales_order_line.product,sales_order_line.product.media,approver,consignment';
            this.$http.get(url, {params:{include}}).then(response => {
                loader.hide();
                this.inspection = response.data;
            }).catch(error => {
                loader.hide();
                console.log(error);
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
