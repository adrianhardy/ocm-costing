<template>
    <b-modal size="xl" ref="modal" title="Inspection Report" @ok="save">
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
            :value="product.name"
            :hide-pass="true"
            :comments-value="inspection.product_description_comments"
            comments-placeholder="Comments on product description or title"
            @pass-updated="inspection.is_packaging_ok = $event"
            @comments-updated="inspection.product_description_comments = $event"
        />
        <inspection-detail-row
            title="Packing"
            v-model="inspection.packing_instructions"
            input-type="textarea"
            comments-placeholder="Comments on packaging"
            :pass-value="inspection.is_packaging_ok === true"
            :comments-value="inspection.packaging_comments"
            @pass-updated="inspection.is_packaging_ok = $event"
            @comments-updated="inspection.packaging_comments = $event"
        />
        <inspection-detail-row
            title="Carton Gross Weight (kg)"
            input-type="text"
            v-model="inspection.case_weight_kg"
            :comments-value="inspection.case_weight_kg_comments"
            :pass-value="inspection.is_case_weight_ok === true"
            comments-placeholder="Comments on case weight"
            @pass-updated="inspection.is_case_weight_ok = $event"
            @comments-updated="inspection.case_weight_kg_comments = $event"
        />
        <inspection-detail-row
            title="Unit Weight (kg)"
            input-type="text"
            v-model="line.unit_weight_kg"
            :comments-value="inspection.unit_weight_kg_comments"
            :pass-value="inspection.is_unit_weight_ok === true"
            comments-placeholder="Comments on unit weight"
            @pass-updated="inspection.is_unit_weight_ok = $event"
            @comments-updated="inspection.unit_weight_kg_comments = $event"
        />
        <inspection-detail-row
            title="Inner Barcode"
            input-type="text"
            v-model="inspection.inner_barcode"
            :comments-value="inspection.inner_barcode_comments"
            :pass-value="inspection.is_inner_barcode_ok === true"
            comments-placeholder="Inner barcode comments"
            @pass-updated="inspection.is_inner_barcode_ok = $event"
            @comments-updated="inspection.inner_barcode_comments = $event"
        />
        <inspection-detail-row
            title="Outer Barcode"
            input-type="text"
            v-model="inspection.outer_barcode"
            :comments-value="inspection.outer_barcode_comments"
            :pass-value="inspection.is_outer_barcode_ok === true"
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
            :comments-value="inspection.case_dimensions_comments"
            :pass-value="inspection.is_case_size_ok === true"
            @pass-updated="inspection.is_case_size_ok = $event"
            @comments-updated="inspection.case_dimensions_comments = $event"
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
            :comments-value="inspection.unit_dimensions_comments"
            :pass-value="inspection.is_unit_size_ok === true"
            @pass-updated="inspection.is_unit_size_ok = $event"
            @comments-updated="inspection.unit_dimensions_comments  = $event"
        />
        <hr/>
        <inspection-detail-row
            title="Appearance"
            comments-placeholder="Appearance comments"
            v-model="inspection.appearance"
            :comments-value="inspection.appearance_comments"
            :pass-value="inspection.is_appearance_ok === true"
            @pass-updated="inspection.is_appearance_ok = $event"
            @comments-updated="inspection.appearance_comments = $event"
        />
        <inspection-detail-row
            title="Instructions"
            comments-placeholder="Comments on instructions"
            :comments-value="inspection.instructions_comments"
            :pass-value="inspection.is_instructions_ok === true"
            @pass-updated="inspection.is_instructions_ok = $event"
            @comments-updated="inspection.instructions_comments = $event"
        />
        <inspection-detail-row
            title="Product Test Run"
            comments-placeholder="Comments on test run"
            :comments-value="inspection.product_test_comments"
            :pass-value="inspection.is_product_test_run_ok === true"
            @pass-updated="inspection.is_product_test_run_ok = $event"
            @comments-updated="inspection.product_test_comments = $event"
        />
        <b-form-group label="General Comments">
            <b-form-textarea v-model="inspection.comments"></b-form-textarea>
        </b-form-group>
        <b-form-group label="Status">
            <b-form-select v-model="inspection.status">
                <b-select-option value="approved">Approved</b-select-option>
                <b-select-option value="rejected">Rejected</b-select-option>
            </b-form-select>
        </b-form-group>
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
        save($event) {
            $event.preventDefault();
            const loader = this.$loading.show();
            const url = `/api/inspections/${this.inspection.id}`;
            const include = 'sales_order_line,sales_order_line.product,sales_order_line.product.media,approver,consignment';
            this.$http.put(url, this.inspection, {params: {include}}).then(response => {
                loader.hide();
                this.close();
            }).catch(error => {
                loader.hide();
                console.log(error)
                this.$handleExceptionResponse(error, true)
            })
        },
        open(report) {
            const loader = this.$loading.show();
            const url = `/api/inspections/${report.id}`;
            const include = 'approver,sales_order_line,sales_order_line.product,sales_order_line.product.media,approver,consignment';
            this.$http.get(url, {params: {include}}).then(response => {
                loader.hide();
                this.inspection = response.data;
                this.$refs['modal'].show();
            }).catch(error => {
                loader.hide();
                console.log(error);
                this.$handleExceptionResponse(error, true)
            })
        },
        close() {
            this.$refs['modal'].hide();
            this.$emit('updated');
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
