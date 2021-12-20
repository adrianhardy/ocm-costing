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
            <b-tr>
                <b-td>
                    <b-form-input size="sm" placeholder="New shipment reference" v-model="reference" />
                </b-td>
                <b-td>
                    <b-input-group prepend="£" size="sm">
                        <b-form-input type="number" size="sm" placeholder="Shipment invoice" v-model="invoiceAmt" />
                    </b-input-group>
                </b-td>
                <b-td>
                    <b-button size="sm" variant="primary" class="text-nowrap" @click="saveNew()">Create New</b-button>
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
                label: 'Reference'
            },{
                key: 'invoice_amt',
                label: 'Invoice Amt'
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
                invoice_amt: this.invoiceAmt
            }).then(() => {
                this.$refs['table'].refresh();
                this.reference = '';
                this.invoiceAmt = false;
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
            pagination: {},
            value: {}
        }
    }
}
</script>

<style scoped>

</style>
