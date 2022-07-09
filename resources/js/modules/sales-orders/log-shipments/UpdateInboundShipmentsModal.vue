<template>
    <b-modal
        ref="modal"
        size="xl"
        title="Update shipment information"
        ok-title="Log Shipment"
        @ok="save($event)"
    >
        <consignments-table :sales-order="order" @selected="shipment = $event" />
        <hr />
        <b-table :items="lines" :fields="fields">
            <template #cell(qty_shipped)="data">
                <b-form-input v-model="data.item.qty_on_boat" size="sm"/>
            </template>
        </b-table>
    </b-modal>
</template>

<script>
import CurrencySelect from "../../../components/CurrencySelect";
import LandedCostCalculator from "../../../library/LandedCostCalculator";
import ConsignmentsTable from "./ConsignmentsTable";


// --------- NOT CURRENTLY USED ----------------
export default {
    name: "UpdateInboundShipmentsModal",
    components: {ConsignmentsTable, CurrencySelect},
    props: {
        order: Object,
        lines: Array
    },
    computed: {
        fields() {
            return [{
                key: 'item_number',
                label: 'Item #',
                thClass: 'w-20 text-nowrap',
                tdClass: 'align-middle'
            }, {
                key: 'article_number',
                label: 'Art #',
                thClass: 'w-10 text-nowrap',
                tdClass: 'align-middle'
            }, {
                key: 'product.name',
                label: 'Product',
                thClass: 'w-40',
                tdClass: 'align-middle'
            }, {
                key: 'units_requested',
                label: 'Qty',
                thClass: 'w-7',
                tdClass: 'align-middle'
            }, {
                key: 'received',
                label: 'Rcv\'d',
                thClass: 'w-7',
                tdClass: 'align-middle'
            }, {
                key: 'qty_shipped',
                label: 'Shipped',
                tdClass: 'align-middle w-10',
                thClass: 'w-10 text-nowrap'
            }]
        }
    },
    methods: {
        save($event) {
            $event.preventDefault();
            const loader = this.$loading.show();
            this.$http.post(`/api/sales-orders/lines/shipments`, {
                lines: this.lines,
                shipment: this.shipment
            }).then(() => {
                loader.hide();
                this.close();
                this.$emit('updated');
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
            shipment:{}
        }
    }
}
</script>

<style scoped>

</style>
