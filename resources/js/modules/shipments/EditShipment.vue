<template>
    <div>
        <b-card class="shadow-sm">
            <h2>Edit Shipment</h2>
            <div class="row">
                <div class="col-12 col-md-4">
                    <b-form-group label="Reference">
                        <b-form-input v-model="shipment.reference"/>
                    </b-form-group>
                    <!-- @todo make a new 'sales order select' for this, readonly for now -->
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
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <b-form-group label="Departed At">
                                <b-form-datepicker v-model="shipment.departed_at"/>
                            </b-form-group>
                        </div>
                        <div class="col-12 col-md-6">
                            <b-form-group label="Docked At">
                                <b-form-datepicker v-model="shipment.docked_at"/>
                            </b-form-group>
                        </div>
                    </div>
                </div>
            </div>
            <h4>
                Sales Order Lines
            </h4>
            <div>
                <b-table :fields="fields" :items="shipment.sales_order_lines">
                    <template #cell(landed_cost.shipping_cost_gbp)="data">
                        <b-input readonly v-model="data.value"/>
                    </template>
                    <template #cell(landed_cost.shipping_cost_gbp_per_unit)="data">
                        <b-input readonly v-model="data.value"/>
                    </template>
                    <template #cell(landed_cost.est_landed_cost_per_unit)="data">
                        <b-input readonly v-model="data.value"/>
                    </template>
                    <template #cell(landed_cost.est_landed_cost_per_unit_gbp)="data">
                        <b-input readonly v-model="data.value"/>
                    </template>
                </b-table>
            </div>
            <div class="d-flex justify-content-end">
                <b-button @click="update" variant="primary">Update</b-button>
            </div>
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
            return 'sales_order,sales_order_lines,sales_order_lines.landed_cost'
        },
        fields() {
            return [
                {key: 'id', label: 'ID'},
                {key: 'landed_cost.shipping_cost_gbp', label: 'Shipping Cost £'},
                {key: 'landed_cost.shipping_cost_gbp_per_unit', label: 'Shipping Cost per Unit £'},
                {key: 'landed_cost.est_landed_cost_per_unit', label: 'Landed Cost per Unit'},
                {key: 'landed_cost.est_landed_cost_per_unit_gbp', label: 'Landed Cost per Unit £'},
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
            shipment: {}
        }
    }

}
</script>

<style scoped>

</style>
