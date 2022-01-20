<template>
    <b-table ref="table" :items="items" :fields="fields" small show-empty empty-text="No shipments set up - create the first one below">
        <template #table-busy>
            <div class="text-center my-2">
                <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
            </div>
        </template>
        <template #cell(invoice_amt)="data">
            {{ data.value | cash_value }}
        </template>
        <template #cell(id)="data">
            <b-form-radio size="lg" name="selected_shipment_id" @change="$emit('selected', data.item)" />
        </template>
        <template #custom-foot>
            <b-tr class="footer">
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="reference" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="supplierRef" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="ourOrderNumber" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="vesselName" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="containerNumber" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="blNumber" />
                </b-td>
                <b-td>
                    <b-form-input size="sm" placeholder="" v-model="shippingAgentRef" />
                </b-td>
                <b-td>
                    <b-input-group prepend="£" size="sm">
                        <b-form-input type="number" size="sm" placeholder="Shipment invoice" v-model="invoiceAmt" />
                    </b-input-group>
                </b-td>
                <b-td>
                    <b-button size="sm" variant="primary" class="text-nowrap" @click="saveNew()">
                        <b-icon-plus />
                    </b-button>
                </b-td>
            </b-tr>
        </template>
    </b-table>
</template>

<script>
export default {
    name: "ConsignmentsTable",
    props: {
        salesOrder: Object,
        value: Object
    },
    computed: {
        url() {
            return `/api/sales-orders/${this.salesOrder.id}/consignments`
        },
        fields() {
            return [{
                key: 'reference',
                label: 'Reference',
                thClass: 'text-nowrap'
            },{
                key: 'supplier_ref',
                label: 'Supplier Ref.',
                thClass: 'text-nowrap'
            },{
                key: 'our_order_number',
                label: 'Our Order #',
                thClass: 'text-nowrap'
            },{
                key: 'vessel_name',
                label: 'Vessel Name',
                thClass: 'text-nowrap'
            },{
                key: 'container_number',
                label: 'Container #',
                thClass: 'text-nowrap'
            },{
                key: 'bl_number',
                label: 'B/L Number',
                thClass: 'text-nowrap'
            },{
                key: 'shipping_agent_ref',
                label: 'Ship/Agent Ref.',
                thClass: 'w-10 text-nowrap'
            },{
                key: 'invoice_amt',
                label: 'Invoice Amt.',
                thClass: 'text-nowrap'
            },{
                key: 'id',
                label: '',
                thClass: 'w-1',
                tdClass: 'text-center'
            }]
        }
    },
    methods: {
        saveNew() {
            this.$http.post(this.url, {
                reference: this.reference,
                invoice_amt: this.invoiceAmt,
                supplier_ref: this.supplierRef,
                shipping_agent_ref: this.shippingAgentRef,
                bl_number: this.blNumber,
                container_number: this.containerNumber,
                our_order_number: this.ourOrderNumber,
                vessel_name: this.vesselName
            }).then(() => {
                this.$refs['table'].refresh();
                this.reference = '';
                this.invoiceAmt = 0;
                this.supplierRef = '';
                this.shippingAgentRef = '';
                this.blNumber = '';
                this.containerNumber = '';
                this.ourOrderNumber = '';
                this.vesselName = '';
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            });
        },
        items() {
            return this.$http.get(this.url).then(response => {
                return response.data.items;
            });
        }
    },
    data() {
        return {
            reference: '',
            invoiceAmt: 0,
            supplierRef: '',
            blNumber: '',
            shippingAgentRef: '',
            vesselNumber: '',
            ourOrderNumber: '',
            containerNumber: '',
            pagination: {},
            value: {}
        }
    }
}
</script>

<style scoped>
    .b-table {
        font-size: 90%;
    }
    .b-table tr.footer {
        font-size: 100%;
    }
</style>
