<template>
    <div>
        <b-card class="shadow-sm">
            <h2>Edit Shipment</h2>
            <div class="row">
                <div class="col-12 col-md-4">
                    <b-form-group label="Reference">
                        <b-form-input v-model="shipment.reference"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Order Number">
                        <b-form-input v-model="shipment.our_order_number"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group v-if="shipment.sales_order" label="Sales Order">
                        <b-form-input readonly v-model="shipment.sales_order.sales_order_number"/>
                    </b-form-group>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    <b-form-group label="Vessel">
                        <b-form-input v-model="shipment.vessel_name"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Container Number">
                        <b-form-input v-model="shipment.container_number"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="BL Number">
                        <b-form-input v-model="shipment.bl_number"/>
                    </b-form-group>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <b-form-group label="Est Cost Cuft">
                        <b-input-group prepend="£">
                            <b-form-input readonly :value="shipment.est_cost_cuft_gbp.toFixed(2)"/>
                        </b-input-group>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="Supplier Reference">
                        <b-form-input v-model="shipment.supplier_ref"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="Shipping Agent Reference">
                        <b-form-input v-model="shipment.shipping_agent_ref"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="Shipping Invoice Amt">
                        <b-input-group prepend="£">
                            <b-form-input v-model="shipment.invoice_amt"/>
                        </b-input-group>
                    </b-form-group>
                </div>
            </div>
            <h4 class="mt-2">Dates</h4>
            <div class="row">
                <div class="col-12 col-md-2 offset-md-1">
                    <b-form-group label="ETA">
                        <b-form-datepicker v-model="shipment.estimated_arrival_at" label-no-date-selected="None Set" :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}" />
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="ETD">
                        <b-form-datepicker v-model="shipment.estimated_departure_at" label-no-date-selected="None Set" :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}" />
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Shipped At">
                        <b-form-datepicker v-model="shipment.departed_at" label-no-date-selected="None Set" :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}" />
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Booked In">
                        <b-form-datepicker v-model="shipment.booked_in_at" label-no-date-selected="None Set" :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Delivered At">
                        <b-form-datepicker v-model="shipment.docked_at" label-no-date-selected="None Set" :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"/>
                    </b-form-group>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between">
                <div>
                    <b-button :href="`/shipments/${shipment.id}/generate-inspection-report`">Generate Inspection Report</b-button>
                </div>
                <div>
                    <b-input-group>
                        <b-input-group-addon is-text>
                            <b-checkbox v-model="updateLandedCosts">Calculate Landed Costs</b-checkbox>
                        </b-input-group-addon>
                        <b-input-group-addon is-text>
                            <b-checkbox v-model="sendToPricing" :disabled="!updateLandedCosts">Send to pricing</b-checkbox>
                        </b-input-group-addon>
                        <b-button @click="update" variant="primary">Update</b-button>
                    </b-input-group>
                </div>
            </div>
            <h4 class="mt-2">Sales Order Lines</h4>
            <div>
                <b-table :fields="fields" :items="shipment.sales_order_lines" small>
                    <template #cell(sales_order.sales_order_number)="data">
                        <a :href="`/sales-orders/${data.item.sales_order.id}/edit`">
                            {{ data.value }}
                        </a>
                    </template>
                    <template #cell(landed_cost.shipping_cost_gbp_per_unit)="data">
                        <b-input v-model="data.value"/>
                    </template>
                    <template #cell(landed_cost.est_landed_cost_per_unit_gbp)="data">
                        <b-input v-model="data.value"/>
                    </template>
                </b-table>
            </div>
            <h4 class="mt-2">Files</h4>
            <b-tabs>
                <b-tab title="Upload">
                    <div class="d-flex justify-content-between">
                        <b-form-file class="w-50 mt-3" v-model="file" />
                        <b-button class="mt-3 mr-3" @click="uploadFile">Upload</b-button>
                    </div>
                </b-tab>
                <b-tab title="Existing">
                    <b-table
                        :items="shipment.media"
                        :fields="[{key: 'file_name', label: 'File'}, {key: 'delete', label: ''}]"
                    >
                        <template #cell(file_name)="data">
                            <b-link :href="data.item.url">
                                {{data.value}}
                            </b-link>
                        </template>
                        <template #cell(delete)="data">
                            <b-button size="sm" @click="deleteFile(data.item)" variant="danger">Delete</b-button>
                        </template>
                    </b-table>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "EditShipment",
    props: {
        shipmentId: Number
    },
    mounted() {
        this.refresh();
    },
    computed: {
        include() {
            return 'sales_order,sales_order_lines,sales_order_lines.received,sales_order_lines.product,sales_order_lines.landed_cost,sales_order_lines.sales_order,media'
        },
        fields() {
            return [
                {key: 'sales_order.sales_order_number', label: 'Sales Order', tdClass: 'align-middle'},
                {key: 'article_number', label: 'Product', tdClass: 'align-middle'},
                {key: 'product.name', label: 'Name', tdClass: 'align-middle'},
                {key: 'landed_cost.shipping_cost_gbp_per_unit', label: 'Shipping Cost/Unit £', tdClass: 'w-15 align-middle'},
                {key: 'landed_cost.est_landed_cost_per_unit_gbp', label: 'Landed Cost/Unit £', tdClass: 'w-15 align-middle'},
                {key: 'received', label: 'Quantity', tdClass: 'align-middle'}
            ]
        }
    },
    methods: {
        update() {
            const loading = this.$loading.show();
            let xhrs = [
                this.$http.put(`/api/shipments/${this.shipment.id}`, this.shipment),
                this.$http.post(`/api/shipments/${this.shipment.id}/landed-costs`, {
                    'send_to_pricing': this.send_to_pricing
                })
            ];
            this.$http.all(xhrs).then(() => {
                loading.hide();
                this.refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        uploadFile(){
            const formData = new FormData()
            formData.append('file', this.file);

            const loader = this.$loading.show();
            this.$http.post(`/api/shipments/${this.shipment.id}/media`, formData, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data'
                }
            }).then(() => {
                this.refresh();
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            })
        },
        deleteFile(media){
            const loading = this.$loading.show();
            const url = `/api/shipments/${this.shipment.id}/media/${media.id}`
            this.$http.delete(url).then(() => {
                this.refresh();
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        refresh() {
            const loading = this.$loading.show()
            const url = `/api/shipments/${this.shipmentId}`
            this.$http.get(url, {params: {include: this.include}}).then(response => {
                this.shipment = response.data
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        }
    },
    data() {
        return {
            sendToPricing: false,
            updateLandedCosts: false,
            shipment: {},
            file: null
        }
    }

}
</script>

<style scoped>

</style>
