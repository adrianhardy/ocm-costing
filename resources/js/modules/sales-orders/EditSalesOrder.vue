<template>
    <div>
        <b-card class="shadow-sm" header-class="justify-content-between">
            <template #header>
                <div>
                    <h4 class="m-0">
                        Sales Order {{ salesOrder.sales_order_number }}
                    </h4>
                    <b-badge variant="success" v-if="salesOrder.is_finalised">
                        Finalised
                        {{ new Date(salesOrder.finalised_at) | date('dd/MM/yy HH:mm') }}
                    </b-badge>
                </div>
                <div v-if="!isSupplier">
                    <b-button variant="outline-primary" @click="openSupplierAccessModal()">
                        Supplier Access
                    </b-button>
                    <b-button v-if="salesOrder.is_finalised && isSuper" @click="unfinalise" variant="outline-danger">
                        Un-Finalise
                    </b-button>
                    <b-button variant="outline-primary" @click="openHistoryModal()">
                        Show Changes
                    </b-button>
                    <b-button variant="outline-primary" v-if="isSuper" :href="`/sales-orders/${salesOrderId}/consignments`">
                        Shipping History
                    </b-button>
                </div>
            </template>
            <div class="row" v-if="!isSupplier">
                <div class="col-12 col-md-4">
                    <b-form-group label="Supplier">
                        <supplier-select v-model="salesOrder.supplier" @select="save()"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-4">
                    <b-form-group label="Reference">
                        <b-form-input v-model="salesOrder.reference" @change="save()"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Currency">
                        <currency-select v-model="salesOrder.currency" @select="save()"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="FX Rate : GBP">
                        <b-input-group :prepend="`${salesOrder.currency.code}:GBP`">
                            <b-form-input class="no-stepper" type="number" v-model.number="salesOrder.fx_rate"
                                          @change="save()"/>
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
                    <b-icon-x v-if="!isSupplier" class="text-danger" @click="trash(data.item)" style="cursor: pointer"/>
                </template>
                <template #cell(article_number)="data">
                    <b-form-input :readonly="salesOrder.is_finalised" size="sm" v-model="data.item.article_number"/>
                    <b-form-input :readonly="salesOrder.is_finalised" size="sm" v-model="data.item.item_number"/>
                </template>
                <template #cell(total)="data">

                    <b-input-group :prepend="salesOrder.currency.code" size="sm">
                        <b-form-input
                            readonly
                            type="number"
                            class="no-stepper"
                            :value="data.value | 2dp_value"
                        />
                    </b-input-group>
                    <b-input-group prepend="GBP" size="sm">
                        <b-form-input
                            readonly
                            class="no-stepper"
                            :value="data.item.total_gbp | 2dp_value"
                        />
                    </b-input-group>
                </template>
                <template #cell(product.name)="data">
                    <product-select
                        v-if="!salesOrder.finalised_at"
                        size="sm"
                        :readonly="salesOrder.is_finalised"
                        v-model="data.item.product"
                        @input="selectProduct($event, data.item)"
                    />
                    <div v-else>
                        <div>{{ data.item.product.name }}</div>
                        <b-badge v-b-tooltip.hover.right title="Estimated Landed Cost" variant="primary"
                                 v-if="data.item.landed_cost && data.item.landed_cost.consignment_id">
                            {{ data.item.landed_cost.est_landed_cost_per_unit_gbp | cash_value }}
                        </b-badge>
                    </div>
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
                        :readonly="salesOrder.is_finalised"
                        type="number"
                        class="no-stepper"
                        v-model.number="data.item.units_requested" @change="updateLine(data.item)"
                    />
                    <b-form-input
                        size="sm"
                        type="number"
                        v-model.number="data.item.total_cubic_ft" @change="updateLine(data.item)"
                        class="no-stepper"
                    />
                </template>
                <template #cell(unit_price)="data">
                    <b-input-group :prepend="salesOrder.currency.code" size="sm">
                        <b-form-input
                            :readonly="salesOrder.is_finalised"
                            type="number"
                            class="no-stepper"
                            v-model="data.item.unit_price"
                            @change="updateLine(data.item)"
                        />
                    </b-input-group>
                    <b-input-group prepend="GBP" size="sm">
                        <b-form-input
                            readonly
                            class="no-stepper"
                            :value="data.item.unit_price * salesOrder.fx_rate | cash_value"
                        />
                    </b-input-group>
                </template>
                <template #cell(checked)="data">
                    <b-form-checkbox size="lg" v-model="data.item.checked"/>
                </template>
                <template #custom-foot>
                    <b-tr>
                        <b-th colspan="3"></b-th>
                        <b-th class="text-right">Total</b-th>
                        <b-th></b-th>
                        <b-td></b-td>
                        <b-td>
                            <strong>Goods</strong> <br/>
                            {{ salesOrder.currency.code }}
                            {{ salesOrder.goods_cost }} <br/>
                            GBP {{ salesOrder.goods_cost_gbp }}
                        </b-td>
                    </b-tr>
                </template>
            </b-table>
          <div class="d-flex justify-content-between w-100">
                <div>
                    <b-button v-if="salesOrder.id > 0 && !salesOrder.is_finalised" variant="primary" size="lg"
                              @click="finalise">
                        Finalise
                    </b-button>
                    <b-button v-show="false" v-if="salesOrder.id > 0" variant="secondary" size="lg">
                        Export for Supplier
                    </b-button>
                </div>
                <div>
                  <b-button class="mr-1" size="lg" variant="success" @click="add"
                            v-if="salesOrder.id > 0 && !salesOrder.is_finalised">
                    Add Line
                  </b-button>

                  <b-button
                      class="mr-1"
                      size="lg"
                      variant="primary"
                      v-if="salesOrder.is_finalised && hasSelectedRows"
                      @click="confirmSendToPricing()"
                  >
                    Send To Pricing
                  </b-button>
                  <b-button
                      class="mr-1"
                      size="lg"
                      variant="success"
                      v-if="salesOrder.is_finalised && hasSelectedRows"
                      @click="$refs['update-inbound-shipments-modal'].open()"
                  >
                    Update Inbound Shipments
                  </b-button>
                </div>
            </div>
        </b-card>
        <sales-order-history-modal
            :sales-order="salesOrder"
            ref="sales-order-history-modal"
        />
        <supplier-access-modal
            :sales-order="salesOrder"
            ref="supplier-access-modal"
        />
        <update-inbound-shipments-modal
            @updated="refresh()"
            :order="salesOrder"
            :lines="selectedRows"
            ref="update-inbound-shipments-modal"
        />
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../components/SupplierSelect";
import CurrencySelect from "../../components/CurrencySelect";
import ProductSelect from "../../components/ProductSelect";
import UpdateInboundShipmentsModal from "./UpdateInboundShipmentsModal";
import SalesOrderHistoryModal from "./SalesOrderHistoryModal";
import SupplierAccessModal from "./SupplierAccessModal";


export default {
    name: "EditSalesOrder",
    components: {
        SupplierAccessModal,
        SalesOrderHistoryModal,
        UpdateInboundShipmentsModal,
        ProductSelect,
        CurrencySelect,
        SupplierSelect
    },
    props: {
      salesOrderId: Number,
      isSupplier: Boolean
    },
    filters: {
        date: dateFilter
    },
    mounted() {
        this.refresh();
    },
    computed: {
        urlPrefix() {
          return `/api/${this.isSupplier?'supplier/':''}sales-orders`
        },
        selectedRows() {
            return _.filter(this.salesOrder.lines, line => {
                return line.checked;
            })
        },
        hasSelectedRows() {
            return this.selectedRows.length > 0;
        },
        fields() {
            const fields = [{
                key: 'id',
                label: '',
                thClass: 'w-1',
                tdClass: 'align-middle'
            }, {
                key: 'checked',
                label: '',
                thClass: 'w-1',
                tdClass: 'align-middle'
            }, {
                key: 'product.name',
                label: 'Product',
                thClass: 'w-20',
                tdClass: 'align-middle'
            }, {
                key: 'article_number',
                label: 'Art # / Item #',
                thClass: 'w-10 text-nowrap'
            }, {
                key: 'packing_instructions',
                label: 'Packing Instructions',
                thClass: 'w-15'
            }, {
                key: 'unit_price',
                label: 'Unit Price',
                thClass: 'w-10'
            }, {
                key: 'units_requested',
                label: 'Units / Cu.ft',
                thClass: 'w-8'
            }, {
                key: 'total',
                label: 'Total',
                thClass: 'w-10 text-nowrap',
                tdClass: 'text-nowrap'
            }];
            if (this.salesOrder.is_finalised) {
                return _.filter(fields, o => o.key !== 'id')
            } else {
                return _.filter(fields, o => o.key !== 'checked')
            }
        },
        include() {
            return 'supplier,currency,lines,lines.product,lines.product.supplier,lines.landed_cost';
        }
    },
    methods: {
        openSupplierAccessModal() {
            this.$refs['supplier-access-modal'].open();
        },
        async confirmSendToPricing() {
            let result = await this.$bvModal.msgBoxConfirm(
                'Are you sure you want to send the selected lines to pricing? This will invalidate any current pricing', {
                  okTitle: 'Yes, Continue',
                  title: 'Please confirm',
                  centered: true
                }
            );

            if (!result) {
                return;
            }

            const loader = this.$loading.show();
            this.$http.post('/api/sales-orders/lines/pricing', {
                lines: this.selectedRows
            }).then(() => {
                loader.hide();
                this.$bvModal.msgBoxOk(
                    'The selected products have been sent to pricing', {
                      centered: true,
                      title: 'Confirmation'
                    }
                );
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            });
        },
        openHistoryModal() {
            this.$refs['sales-order-history-modal'].open();
        },
        async unfinalise() {
          let result = await this.$bvModal.msgBoxConfirm('Are you sure you want to restore this sales order?', {
            centered: true,
            title: 'Please confirm',
            okTitle: 'Yes'
          });

          this.restore();
        },
        async finalise() {
            let result = await this.$bvModal.msgBoxConfirm(
                "Are you sure you want to finalise this into a sales order?", {
                    centered: true,
                    title: 'Please confirm',
                    okTitle: 'Yes'
                }
            );

            if (!result) {
                return;
            }

            this.save({finalised: true})
        },
        restore(){
          const loader = this.$loading.show
          this.salesOrder.restore = true
          this.$http.put(`/api/sales-orders/${this.salesOrder.id}`, this.salesOrder, {params: {include: this.include}}
          ).then(response => {
            this.salesOrder = response.data;
            loader.hide
          }).catch(error => {
            this.$handleExceptionResponse(error);
            loader.hide();
          });

        },

        save(model = false) {
            console.log(this.salesOrder.reference);
            const loader = this.$loading.show();
            this.$http.put(
                `${this.urlPrefix}/${this.salesOrder.id}`, (model) ? model : this.salesOrder, {params: {include: this.include}}
            ).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error);
                loader.hide();
            });
        },
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
            const url = `${this.urlPrefix}/${this.salesOrder.id}/lines/${line.id}`;
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
            this.$http.get(`${this.urlPrefix}/${this.salesOrderId}`, {
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
