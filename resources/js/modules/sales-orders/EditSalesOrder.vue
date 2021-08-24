<template>
    <div>
        <b-card class="shadow-sm">
            <div class="row">
                <div class="col-12 col-md-4">
                    <b-form-group label="Supplier">
                        <supplier-select v-model="salesOrder.supplier"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Reference">
                        <b-form-input v-model="salesOrder.reference"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Currency">
                        <currency-select v-model="salesOrder.currency"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="FX Rate : GBP">
                        <b-input-group :prepend="`${salesOrder.currency.code}:GBP`">
                            <b-form-input class="no-stepper" type="number" v-model.number="salesOrder.fx_rate"/>
                        </b-input-group>
                    </b-form-group>
                </div>
            </div>
            <b-table
                show-empty
                empty-text="No sales order lines to show"
                :items="salesOrder.lines"
                :fields="fields"
            >
                <template #cell(id)="data">
                    <b-icon-x class="text-danger" @click="trash(data.item)" style="cursor: pointer"/>
                </template>
                <template #cell(article_number)="data">
                    <b-form-input size="sm" v-model="data.item.article_number" />
                </template>
                <template #cell(item_number)="data">
                    <b-form-input size="sm" v-model="data.item.item_number" />
                </template>
                <template #cell(total)="data">
                    <div>{{ data.value | 2dp_value }}</div>
                    <div class="small text-right mr-1">{{ data.item.total_gbp | cash_value }}</div>
                </template>
                <template #cell(product.name)="data">
                    <product-select
                        size="sm"
                        :readonly="salesOrder.is_sent"
                        v-model="data.item.product"
                        @input="selectProduct($event, data.item)"
                    />
                    <span>{{ data.item.product_description }}</span>
                </template>
                <template #cell(packing_instructions)="data">
                    <b-textarea
                        size="sm"
                        type="number"
                        v-model="data.item.packing_instructions" @change="updateLine(data.item)"
                    />
                </template>
                <template #cell(units_requested)="data">
                    <b-form-input
                        size="sm"
                        v-if="!salesOrder.is_approved"
                        type="number"
                        class="no-stepper"
                        v-model.number="data.item.units_requested" @change="updateLine(data.item)"
                    />
                    <span v-else>{{ data.item.units_requested }}</span>
                </template>
                <template #cell(unit_price)="data">
                    <b-input-group :prepend="salesOrder.currency.code" size="sm">
                        <b-form-input
                            :readonly="salesOrder.is_approved"
                            type="number"
                            class="no-stepper"
                            v-model="data.item.unit_price"
                            @change="updateLine(data.item)"
                        />
                    </b-input-group>
                    <div class="small text-right mr-1">{{ data.item.unit_price * salesOrder.fx_rate | cash_value }}</div>
                </template>

            </b-table>
            <div class="d-flex justify-content-between w-100">
                <div>
                    <b-button v-if="salesOrder.id > 0 && !salesOrder.is_sent" variant="primary" size="lg">
                        Finalise
                    </b-button>
                    <b-button v-if="salesOrder.id > 0" variant="secondary" size="lg">
                        Send To Supplier
                    </b-button>
                </div>
                <b-button class="mr-1" size="lg" variant="success" @click="add" v-if="salesOrder.id > 0 && !salesOrder.is_sent">
                    Add Line
                </b-button>
            </div>
        </b-card>
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../components/SupplierSelect";
import CurrencySelect from "../../components/CurrencySelect";
import ProductSelect from "../../components/ProductSelect";


export default {
    name: "EditSalesOrder",
    components: {ProductSelect, CurrencySelect, SupplierSelect},
    props: {salesOrderId: Number},
    filters: {
        date: dateFilter
    },
    mounted() {
        this.refresh();
    },
    computed: {
        fields() {
            return [{
                key: 'id',
                label: '',
                thClass: 'w-1',
                tdClass: 'align-middle'
            },{
                key: 'product.name',
                label: 'Product',
                thClass: 'w-20',
                tdClass: 'align-middle'
            }, {
                key: 'article_number',
                label: 'Art #',
                thClass: 'w-10 text-nowrap'
            }, {
                key: 'item_number',
                label: 'Item #',
                thClass: 'w-10 text-nowrap'
            }, {
                key: 'packing_instructions',
                label: 'Packing Instructions',
                thClass: 'w-20'
            }, {
                key: 'unit_price',
                label: 'Unit Price',
                thClass: 'w-10'
            }, {
                key: 'units_requested',
                label: 'Qty',
                thClass: 'w-10'
            }, {
                key: 'total',
                label: 'Total',
                thClass: 'w-1 text-nowrap',
                tdClass: 'text-nowrap'
            }]
        },
        include() {
            return 'supplier,currency,lines,lines.product,lines.product.supplier';
        }
    },
    methods: {
        selectProduct(product, line) {
            this.updateLine(line);
        },
        trash(line) {
            this.$bvModal.msgBoxConfirm(
                "Are you sure you want to delete this sales order line?", {
                    centered: true,
                    title: 'Please confirm',
                    okTitle: 'Yes'
                }
            ).then(result => {
                if (!result) {
                    return;
                }
                const loader = this.$loading.show();
                this.$http.delete(
                    `/api/sales-orders/${this.salesOrder.id}/lines/${line.id}`, {
                        params: {include: this.include}
                    }
                ).then(response => {
                    this.salesOrder = response.data;
                    loader.hide();
                }).catch(error => {
                    this.$handleExceptionResponse(error);
                    loader.hide();
                });
            })
        },
        add() {
            const loader = this.$loading.show();
            const url = `/api/sales-orders/${this.salesOrder.id}/lines`;
            this.$http.post(url, {}, {params: {include: this.include}}).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            });
        },
        updateLine(line) {
            const loader = this.$loading.show();
            const url = `/api/sales-orders/${this.salesOrder.id}/lines/${line.id}`;
            this.$http.put(url, {...line}, {
                params: {include: this.include}
            }).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            });
        },
        refresh() {
            const loader = this.$loading.show();
            this.$http.get(`/api/sales-orders/${this.salesOrderId}`, {
                params: {include: this.include}
            }).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        }
    },
    data() {
        return {
            salesOrder: {},
            note: '',
        }
    }
}
</script>

<style scoped>

</style>
