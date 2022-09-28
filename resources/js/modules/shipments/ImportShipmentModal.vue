<template>
    <b-modal title="Import Shipment" size="xl" ref="modal" :ok-title="okLabel" @ok="ok">
        <div class="row">
            <div class="col-4">
                <b-select v-model="type">
                    <b-select-option value="invoice">Invoice</b-select-option>
                    <b-select-option value="packing">Packing List</b-select-option>
                </b-select>
            </div>
            <div class="col-6">
                <b-form-group>
                    <b-form-file v-model="file" placeholder="Choose XLS,XSLX" accept=".xlsx,.xls"/>
                </b-form-group>
            </div>
            <div class="col-2">
                <b-checkbox v-model="preview" class="mt-2">Preview</b-checkbox>
            </div>
        </div>
        <b-table-simple v-if="summary && type === 'invoice'" small sticky-header="400px">
            <b-thead>
                <b-tr>
                    <b-th>SO Number</b-th>
                    <b-th>Art. Number</b-th>
                    <b-th>Name</b-th>
                    <b-th class="text-right">Inv: Qty</b-th>
                    <b-th class="text-right">SO: Qty</b-th>
                    <b-th class="text-right">Inv: Unit Price</b-th>
                    <b-th class="text-right">SO: Unit Price</b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <b-tr v-for="value in summary" :key="value.line.id">
                    <b-td>{{ value.raw.po_number }}</b-td>
                    <b-td>{{ value.raw.article_number }}</b-td>
                    <b-td>{{ value.line.product.name }}</b-td>
                    <b-td class="text-right">{{ value.raw.qty }}</b-td>
                    <b-td class="text-right">{{ value.line.units_requested }}</b-td>
                    <b-td class="text-right">USD {{ value.raw.unit_price }}</b-td>
                    <b-td class="text-right">USD {{ value.line.unit_price }}</b-td>
                </b-tr>
            </b-tbody>
        </b-table-simple>
        <b-table-simple v-if="summary && type === 'packing'" small sticky-header="400px">
            <b-thead>
            <b-tr>
                <b-th>SO Number</b-th>
                <b-th>Art. Number</b-th>
                <b-th>Product Name</b-th>
                <b-th>Qty</b-th>
                <b-th>L x W x H <small>Sales Order</small></b-th>
                <b-th>L x W x H <small>Packing List</small></b-th>
                <b-th>Qty/Ctn <small>SO</small></b-th>
                <b-th>Qty/Ctn <small>PL</small></b-th>
            </b-tr>
            </b-thead>
            <b-tbody>
                <b-tr v-for="value in summary" :key="value.line.id">
                    <b-td>{{ value.raw.po_number }}</b-td>
                    <b-td>{{ value.raw.article_number }}</b-td>
                    <b-td>{{ value.line.product.name }}</b-td>
                    <b-td>{{ value.raw.qty }}</b-td>
                    <b-td>{{ value.line.unit_length_cm }} x {{ value.line.unit_width_cm }} x {{ value.line.unit_height_cm }}</b-td>
                    <b-td>{{ value.raw.outer_dimension_l }} x {{ value.raw.outer_dimension_w }} x {{ value.raw.outer_dimension_h }}</b-td>
                    <b-td>{{ value.line.units_per_case }}</b-td>
                    <b-td>{{ value.raw.pcs_per_ctn }}</b-td>
                </b-tr>
            </b-tbody>
        </b-table-simple>
        <div v-if="shipment && summary && type === 'packing'">
            <div class="row mb-3" >
                <div class="col-2">
                    <b-form-group label="Est. Date Departure">
                        <b-form-datepicker
                            size="md"
                            v-model="shipment.estimated_date_of_departure"
                            :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"
                            placeholder="no date"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Est. Date Arrival">
                        <b-form-datepicker
                            size="md"
                            v-model="shipment.estimated_date_of_arrival"
                            :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"
                            placeholder="no date"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Vessel Name">
                        <b-form-input
                            size="md"
                            v-model="shipment.vessel_name"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Container Ref">
                        <b-form-input
                            size="md"
                            v-model="shipment.container_reference"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Agent Ref">
                        <b-form-input
                            size="md"
                            v-model="shipment.shipping_agent_ref"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="B/L Number">
                        <b-form-input
                            size="md"
                            v-model="shipment.bl_number"
                        />
                    </b-form-group>
                </div>
            </div>

            <div class="row">
                <div class="col-2 offset-6">
                    <b-form-group label="Supplier Reference">
                        <b-form-input
                            size="md"
                            v-model="shipment.supplier_reference"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Our Reference">
                        <b-form-input
                            size="md"
                            v-model="shipment.reference"
                        />
                    </b-form-group>
                </div>
                <div class="col-2">
                    <b-form-group label="Shipment Invoice Value">
                        <b-input-group prepend="USD">
                            <b-form-input
                                placeholder="REQUIRED"
                                size="md"
                                v-model="shipment.total_shipping_invoice_value"
                            />
                        </b-input-group>
                    </b-form-group>
                </div>
            </div>
        </div>

        <b-alert show v-if="summary && type === 'invoice'" variant="primary">
            Pressing 'Import' below will adjust all rows' unit prices and quantities as shown.
        </b-alert>
        <b-alert show v-if="summary && type === 'packing'" variant="primary">
            Pressing 'Import' below will create (or update) a shipment as necessary. By updating the cubic volumes
            and quantities on the shipment, the Calculated Landed Cost of the items will change.
        </b-alert>
    </b-modal>
</template>

<script>
export default {
    name: "ImportShipmentModal",
    computed: {
        okLabel() {
            if (this.preview) {
                return 'Preview';
            }
            return 'Import'
        },
        invoiceSummaryRows() {

        }
    },
    methods: {
        ok(e) {
            e.preventDefault();
            this.upload();
        },
        show() {
            this.type = 'invoice';
            this.preview = true;
            this.summary = null;
            this.file = null;
            this.$refs['modal'].show();
        },
        upload() {
            const loader = this.$loading.show();
            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('preview', this.preview?1:0);
            formData.append('type', this.type);



            if (this.type === 'packing' && !this.preview) {
                formData.append('bl_number', this.shipment.bl_number?this.shipment.bl_number:'');
                formData.append('vessel_name', this.shipment.vessel_name?this.shipment.vessel_name:'');
                formData.append('container_reference', this.shipment.container_reference?this.shipment.container_reference:'');
                formData.append('shipping_agent_ref', this.shipment.shipping_agent_ref?this.shipment.shipping_agent_ref:'');
                formData.append('reference', this.shipment.reference?this.shipment.reference:'');
                formData.append('supplier_reference', this.shipment.supplier_reference?this.shipment.supplier_reference:'');
                formData.append('total_shipping_invoice_value', this.shipment.total_shipping_invoice_value?this.shipment.total_shipping_invoice_value:'');
            }

            this.$http.post('/api/shipments/import', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                this.summary = response.data.lines;
                this.shipment = response.data.shipment;
                this.shipment.supplier_reference = response.data.shipment.invoice_number;
                delete this.shipment.invoice_number;
                loader.hide();

                if (!this.preview) {
                    this.$refs['modal'].hide();
                    this.$emit('updated');
                }

                if (this.preview) {
                    this.preview = false;
                }

            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            });
        }
    },
    data() {
        return {
            preview: true,
            type: 'invoice',
            summary: null,
            shipment: null,
            file: null
        }
    }
}
</script>

<style scoped>

</style>
