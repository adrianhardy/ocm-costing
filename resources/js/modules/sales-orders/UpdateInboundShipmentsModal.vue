<template>
    <b-modal
        ref="modal"
        size="lg"
        title="Update shipment information"
        ok-title="Log Shipment"
        @ok="save($event)"
    >
        <p>
            Using the shipping invoice received, please enter the shipping reference
            along with the quantities on each of the lines, and the amount invoiced.
        </p>
        <div class="row">
            <div class="col-6">
                <b-form-group label="Shipping Reference">
                    <b-form-input v-model="reference"/>
                </b-form-group>
            </div>
        </div>
        <b-table :items="lines" :fields="fields">
            <template #cell(qty_shipped)="data">
                <b-form-input v-model="data.item.qty_on_boat" size="sm"/>
            </template>
            <template #cell(invoice_amt)="data">
                <b-form-input v-model="data.item.invoice_amt" size="sm"/>
            </template>
            <template #cell(est_landed_cost)="data">
                {{ calculateEstimatedLandedCost(data.item).toFixed(2) }}
            </template>
            <template #cell(est_landed_cost_gbp)="data">
                <div>
                    {{ (calculateEstimatedLandedCost(data.item) * order.fx_rate) | cash_value }}
                    <small>{{ calculateEstimatedLandedCost(data.item).toFixed(2) }} {{ order.currency.code }}</small>
                </div>
                <small v-show="false">{{ data.item.unit_price }} + ({{ data.item.invoice_amt }} - ({{ data.item.unit_price }} &times; {{ data.item.qty_on_boat }})) &times; FX</small>
            </template>
        </b-table>
    </b-modal>
</template>

<script>
import CurrencySelect from "../../components/CurrencySelect";

export default {
    name: "UpdateInboundShipmentsModal",
    components: {CurrencySelect},
    props: {
        order: Object,
        lines: Array
    },
    data() {
        return {
            reference: ''
        }
    },
    computed: {
        url() {
            return `/api/admin/sales-order/${this.order.id}/shipments`
        },
        fields() {
            return [{
                key: 'product.name',
                label: 'Product',
                thClass: 'w-30',
                tdClass: 'align-middle'
            }, {
                key: 'article_number',
                label: 'Art #',
                thClass: 'w-10 text-nowrap',
                tdClass: 'align-middle'
            }, {
                key: 'units_requested',
                label: 'Qty',
                thClass: 'w-7',
                tdClass: 'align-middle'
            }, {
                key: 'qty_shipped',
                label: 'Qty Shipped',
                tdClass: 'align-middle w-10',
                thClass: 'w-10 text-nowrap'
            }, {
                key: 'invoice_amt',
                label: 'Invoice Amt',
                tdClass: 'align-middle w-10',
                thClass: 'w-10 text-nowrap'
            }, {
                key: 'est_landed_cost_gbp',
                label: 'Est. Landed Cost',
                tdClass: 'align-middle'
            }]
        }
    },
    methods: {
        calculateEstimatedLandedCost(line) {
            if (!line.invoice_amt) {
                return 0;
            }
            const goodsCost = line.unit_price * line.qty_on_boat;
            let shipping = line.invoice_amt - goodsCost;
            if (shipping < 0) {
                return 0;
            }
            return line.unit_price + (shipping / line.qty_on_boat);
        },
        save($event) {
            $event.preventDefault();
            const loader = this.$loading.show();
            this.$http.post(`/api/sales-orders/lines/shipments`, {
                lines: this.lines,
                reference: this.reference
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
    }
}
</script>

<style scoped>

</style>
