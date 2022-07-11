<template>
    <div>
        <b-card class="shadow-sm">
            <h2>Edit Shipment</h2>
            <div class="row">
                <div class="col-12 col-md-4">
                    <b-form-group label="Reference">
                        <b-form-input v-model="shipment.reference"/>
                    </b-form-group>
                    <b-form-group v-if="shipment.sales_order" label="Sales Order">
                        <b-form-input readonly v-model="shipment.sales_order.sales_order_number"/>
                    </b-form-group>
                    <b-form-group label="Est Cost Cuft">
                        <b-input-group prepend="£">
                            <b-form-input readonly v-model="shipment.est_cost_cuft_gbp"/>
                        </b-input-group>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Order Number">
                        <b-form-input v-model="shipment.our_order_number"/>
                    </b-form-group>
                    <b-form-group label="Vessel">
                        <b-form-input v-model="shipment.vessel_name"/>
                    </b-form-group>
                    <b-form-group label="Supplier Reference">
                        <b-form-input v-model="shipment.supplier_ref"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Container Number">
                        <b-form-input v-model="shipment.container_number"/>
                    </b-form-group>
                    <b-form-group label="BL Number">
                        <b-form-input v-model="shipment.bl_number"/>
                    </b-form-group>
                    <b-form-group label="Shipping Agent Reference">
                        <b-form-input v-model="shipment.shipping_agent_ref"/>
                    </b-form-group>
                </div>
            </div>
            <h4 class="mt-2">Dates</h4>
            <div class="row">
                <div class="col-12 col-md-3">
                    <b-form-group label="ETA">
                        <b-form-datepicker v-model="shipment.estimated_arrival_at" />
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="ETD">
                        <b-form-datepicker v-model="shipment.estimated_departure_at" />
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="Departed At">
                        <b-form-datepicker v-model="shipment.departed_at"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-3">
                    <b-form-group label="Docked At">
                        <b-form-datepicker v-model="shipment.docked_at"/>
                    </b-form-group>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <b-button @click="update" variant="primary">Update</b-button>
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
            const url = `/api/shipments/${this.shipment.id}`;
            this.$http.put(url, this.shipment).then(response => {
                this.refresh();
                loading.hide();
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
            shipment: {},
            file: null
        }
    }

}
</script>

<style scoped>

</style>
