<template>
    <div>
        <b-card class="shadow-sm" header-class="justify-content-between">
            <template #header>
                <div>
                    <h4 class="m-0">
                        Order Acknowledgement: {{ salesOrder.reference }}
                    </h4>
                    <b-badge variant="success" v-if="salesOrder.is_finalised">
                        Finalised
                        {{ new Date(salesOrder.finalised_at) | date('dd/MM/yy HH:mm') }}
                    </b-badge>
                </div>
                <div>
                    <b-button variant="outline-primary" @click="close" v-if="salesOrder.is_finalised && salesOrder.status !== 'closed'">
                        Close
                    </b-button>
                    <b-button @click="unclose" variant="outline-danger" v-if="salesOrder.is_finalised && salesOrder.status === 'closed'">
                        Un-Close
                    </b-button>
                    <b-button v-if="salesOrder.is_finalised" @click="unfinalise" variant="outline-danger">
                        Un-Finalise
                    </b-button>
                    <b-button variant="outline-primary" @click="openHistoryModal()">
                        Show Changes
                    </b-button>
                </div>
            </template>
            <div class="row">
                <div class="col-12 col-md-2">
                    <b-form-group label="Supplier">
                        <supplier-select v-model="salesOrder.supplier" @selected="save()"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Our Reference">
                        <b-form-input v-model="salesOrder.reference" @change="save()"/>
                    </b-form-group>
                </div>
                <div class="col-12 col-md-2">
                    <b-form-group label="Supplier Ref">
                        <b-form-input v-model="salesOrder.supplier_reference" @change="save()"/>
                    </b-form-group>
                </div>
            </div>

            <b-table
                show-empty
                empty-text="No order acknowledgement lines to show"
                :items="salesOrder.lines"
                :fields="fields"
            >
                <template #cell(item_number)="data">
                    <div v-if="!data.item.product.id" >
                        <b-form-input size="sm" v-model="data.item.article_number" />
                        <b-badge v-if="data.item.has_duplicate_article_number" variant="danger">Duplicate Item #</b-badge>
                    </div>
                    <span v-else>{{data.item.product.article_number}}</span>
                </template>
                <template #cell(id)="data">
                    <b-icon-x class="text-danger" @click="trash(data.item)" style="cursor: pointer"/>
                </template>
                <template #cell(product_photo)="data">
                    <sales-order-line-photo-chooser
                        :media="data.item.product.media"
                        :order="salesOrder"
                        :line="data.item"
                        @updated="refresh()"
                    />
                </template>
                <template #cell(unit_weight_kg)="data">
                    <b-form-group label="Unit" label-size="sm">
                        <b-input size="sm" v-model="data.item.unit_weight_kg" @change="updateLine(data.item)" />
                    </b-form-group>
                    <b-form-group label="Carton"  label-size="sm">
                        <b-input
                            size="sm"
                            v-model="data.item.case_weight_kg" @change="updateLine(data.item)"
                        />
                    </b-form-group>
                </template>
                <template #cell(total)="data">

                    <b-input-group size="sm">
                        <b-form-input
                            readonly
                            type="number"
                            class="no-stepper"
                            :value="data.value | 2dp_value"
                        />
                    </b-input-group>
                </template>
                <template #cell(product.name)="data">
                    <!--
                        if we have a product description, and it's saved, but we don't have a product to go
                        with it then we'll offer the ability to create this as a new product
                    -->
                    <div v-if="data.item.id && data.item.product_description && !data.item.product.id">
                        <b-form-input @change="updateLine(data.item)" v-model="data.item.name" size="sm" />
                        <b-button variant="outline-success" size="sm" block class="mt-2" @click="createNewProduct(data.item)">Create</b-button>
                    </div>
                    <!-- or, if they've clicked "create new" on the drop down -->
                    <div v-else-if="showCreateProduct !== data.item.id">
                        <product-select
                            include="media,current_landed_cost_per_unit_gbp"
                            :sales-order-id="salesOrder.id"
                            size="sm"
                            :readonly="salesOrder.is_finalised"
                            v-model="data.item.product"
                            @input="selectProduct($event, data.item)"
                        >
                            <template v-slot:no-options>
                                No Products found -
                                <b-link
                                    class="font-weight-bold"
                                    @click="beginCreateProduct(data.item)"
                                >create one?
                                </b-link>
                            </template>
                        </product-select>

                        <b-badge variant="success" v-if="data.item.product.id">Product Selected</b-badge>
                        <b-button @click="createNewProduct(data.item)" block class="mt-2" size="sm" variant="outline-success"
                                 v-if="!data.item.product.id && data.item.barcode && (data.item.primary_sku || data.item.name)">
                            Create
                        </b-button>
                    </div>
                    <div v-else>
                        <b-form-input @change="updateLine(data.item)" v-model="data.item.name" size="sm" />
                        <b-button variant="outline-success" size="sm" block class="mt-2" @click="createNewProduct(data.item)">Create</b-button>
                    </div>


                </template>
                <template #cell(barcode)="data">
                    <b-form-input @change="updateLine(data.item)" size="sm" class="mb-2" v-model="data.item.barcode" />
                    <b-badge v-if="data.item.has_duplicate_barcode" variant="danger" size="sm">Duplicate Barcode</b-badge>
                </template>

                <template #cell(oa_primary_sku)="data">
                    <b-form-input @change="updateLine(data.item)" size="sm" v-model="data.item.oa_primary_sku" />
                </template>


                <template #cell(oa_reorder_number)="data">
                    <b-form-input @change="updateLine(data.item)" size="sm" v-model="data.item.oa_reorder_number" />
                </template>

                <template #cell(oa_eta)="data">
                    <b-form-datepicker
                        right
                        @input="updateLine(data.item)"
                        v-model="data.item.oa_eta"
                        placeholder="ETA"
                        size="sm"
                        :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"
                    />
                </template>

                <template #cell(units_requested)="data">
                    <b-form-group>
                        <b-form-input
                            size="sm"
                            :readonly="salesOrder.is_finalised"
                            type="number"
                            class="no-stepper"
                            v-model.number="data.item.units_requested" @change="updateLine(data.item)"
                        />
                    </b-form-group>
                    <div :class="['text-left', 'small', 'ml-1', (data.item.units_requested % data.item.units_per_case) > 0?'text-danger':'']">
                        {{ (data.item.units_requested / data.item.units_per_case).toFixed(2) }} Ctn
                    </div>
                </template>
                <template #cell(unit_price)="data">
                    <b-form-group>
                        <b-input-group prepend="£" size="sm">
                            <b-form-input
                                :readonly="salesOrder.is_finalised"
                                type="number"
                                class="no-stepper"
                                v-model="data.item.unit_price"
                                @change="updateLine(data.item)"
                            />
                        </b-input-group>
                    </b-form-group>
                </template>
                <template #cell(units_per_case)="data">
                    <b-form-group>
                        <template #label>
                        </template>
                        <b-input size="sm" v-model="data.item.units_per_case" @change="updateLine(data.item)" />
                    </b-form-group>
                </template>
                <template #cell(product_size_cm)="data">
                    <b-form-group>
                        <div class="mt-1">
                            <product-dimensions-input order-acknowledgement v-model="data.item" @updated="updateLine(data.item)"/>
                        </div>
                    </b-form-group>
                </template>
                <template #head(checked)="data">
                    <b-form-checkbox size="lg" @change="selectAllLines($event)" />
                </template>
                <template #cell(checked)="data">
                    <b-form-checkbox :rerenderCount="rerenderCount" size="lg" v-model="data.item.checked"/>
                </template>
                <template #custom-foot>
                    <b-tr>
                        <b-th :colspan="getColspan">
                        </b-th>
                        <b-td>
                            <div><strong>Pieces</strong></div>
                            {{ totalUnits }}
                        </b-td>
                        <b-td>
                            <div><strong>Cartons</strong></div>
                            {{ totalCartons }}
                        </b-td>
                        <b-td class="text-right">
                            <strong>Total</strong> <br/>
                            {{ salesOrder.currency.code }}
                            {{ salesOrder.goods_cost | 2dp_value }} <br/>
                        </b-td>
                    </b-tr>
                </template>
            </b-table>
            <div class="d-flex justify-content-between w-100">
                <div>
                    <b-button v-if="showFinaliseButton" variant="primary" size="lg"
                              @click="finalise">
                        Finalise
                    </b-button>
                    <b-button variant="secondary" size="lg" v-if="!salesOrder.finalised_at" :href="`/order-acknowledgements/${salesOrder.id}/import-lines`">
                        Import Lines
                    </b-button>
                    <b-button variant="primary" size="lg" v-if="salesOrder.finalised_at" :href="`/order-acknowledgements/${salesOrder.id}/import-invoice`">
                        Import Invoice
                    </b-button>

                    <b-button variant="danger" size="lg" v-if="salesOrder.id > 0 && !salesOrder.finalised_at" @click="deleteSalesOrder">
                        Delete
                    </b-button>
                </div>
                <div>
                    <b-button class="mr-1" size="lg" variant="success" @click="add" v-if="salesOrder.id > 0 && !salesOrder.is_finalised">
                        Add Line
                    </b-button>
                    <b-button
                        :disabled="!canCreateInvoice"
                        class="mr-1"
                        size="lg"
                        variant="outline-primary"
                        v-if="salesOrder.is_finalised && salesOrder.status !== 'closed'"
                        @click="createInvoice"
                    >
                        Create Invoice
                    </b-button>
                </div>
            </div>
        </b-card>
        <sales-order-history-modal
            :sales-order="salesOrder"
            ref="sales-order-history-modal"
        />
        <create-invoice-modal
            ref="invoice-modal"
            :sales-order="salesOrder"
        />
    </div>
</template>

<script>
import FactorySelect from "../../components/FactorySelect";
import SalesOrderLinePhotoChooser from "../sales-orders/SalesOrderLinePhotoChooser";
import ProductDimensionsInput from "../../components/ProductDimensionsInput";
import SupplierAccessModal from "../sales-orders/SupplierAccessModal";
import SalesOrderHistoryModal from "../sales-orders/SalesOrderHistoryModal";
import UpdateInboundShipmentsModal from "../sales-orders/log-shipments/UpdateInboundShipmentsModal";
import ProductSelect from "../../components/ProductSelect";
import CurrencySelect from "../../components/CurrencySelect";
import SupplierSelect from "../../components/SupplierSelect";
import {dateFilter} from "vue-date-fns";
import CreateInvoiceModal from "./modals/CreateInvoiceModal";

export default {
    name: "EditOrderAcknowledgements",
    components: {
        FactorySelect,
        SalesOrderLinePhotoChooser,
        ProductDimensionsInput,
        SupplierAccessModal,
        SalesOrderHistoryModal,
        UpdateInboundShipmentsModal,
        ProductSelect,
        CurrencySelect,
        SupplierSelect,
        CreateInvoiceModal
    },
    props: {
        acknowledgementId: {
            type: String
        }
    },
    filters: {
        date: dateFilter
    },
    mounted() {
        this.refresh();
    },
    computed: {
        getColspan(){
            return this.salesOrder.is_finalised?12:10;
        },
        state() {
            return window.__state;
        },
        showFinaliseButton() {
            return this.salesOrder.id > 0 && !this.salesOrder.is_finalised && this.salesOrder.status !== 'unmatched_products';
        },
        totalCartons() {
            let count = 0;
            for (let i in this.salesOrder.lines) {
                const line = this.salesOrder.lines[i];
                count += line.units_requested / line.units_per_case;
            }
            return Math.round(count);
        },
        totalUnits() {
            let count = 0;
            for (let i in this.salesOrder.lines) {
                const line = this.salesOrder.lines[i];
                count += line.units_requested;
            }
            return count;
        },
        urlPrefix() {
            return `/api/order-acknowledgements`
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
                key: 'item_number',
                label: 'Item #',
                thClass: 'w-5 text-nowrap',
                tdClass: 'small'
            }, {
                key: 'product_photo',
                label: 'photo',
                thClass: 'w-5 text-nowrap'
            }, {
                key: 'product.name',
                label: 'Product Desc.',
                thClass: 'w-12',
            }, {
                key: 'oa_primary_sku',
                label: 'Primary Sku',
                thClass: 'w-8'
            }, {
                key: 'barcode',
                label :'Barcode',
                thClass: 'w-10'
            }, {
                key: 'oa_reorder_number',
                label :'Re-order',
                thClass: 'w-5'
            }, {
                key: 'product_size_cm',
                label: 'dimensions ',
                thClass: 'w-12',
            }, {
                key: 'unit_price',
                label: 'Unit Price',
                thClass: 'w-8'
            }, {
                key: 'qty_received',
                label: "Rcv'",
                thClass: 'w-5'
            },  {
                key: 'qty_remaining',
                label: "Rem'",
                thClass: 'w-5'
            }, {
                key: 'units_per_case',
                label: 'units/case',
                thClass: 'w-5'
            }, {
                key: 'units_requested',
                label: 'Units',
                thClass: 'w-5'
            }, {
                key: 'total',
                label: 'Sub-Total',
                thClass: 'w-6 text-nowrap',
                tdClass: 'text-nowrap'
            }, {
                key: 'oa_eta',
                label: 'ETA',
                thClass: 'w-10'
            }];
            if (this.salesOrder.is_finalised) {
                return _.filter(fields, o => o.key !== 'id')
            } else {
                return _.filter(fields, o => o.key !== 'checked' && o.key !== 'qty_received' && o.key !== 'qty_remaining')
            }
        },
        include() {
            return 'supplier,factory,currency,lines,lines.selected_media,lines.product,lines.product.media,lines.product.current_landed_cost_per_unit_gbp,lines.product.supplier,lines.landed_cost,lines.received,lines.live_estimated_landed_cost';
        },
        canCreateInvoice(){
            if(!this.salesOrder) {
                return false;
            }
            return _.filter(this.salesOrder.lines, function(line) {
                return line.checked === true;
            }).length > 0


        }
    },
    methods: {
        selectAllLines(event){
            _.forEach(this.salesOrder.lines, function(line) {
                line.checked = event
            });
            this.rerenderCount++;
        },
        createInvoice() {
            this.$refs['invoice-modal'].open();
        },
        beginCreateProduct(line) {
            this.showCreateProduct = line.id;
        },
        createNewProduct(line){
            const loading = this.$loading.show();
            const url = `/api/order-acknowledgements/${this.salesOrder.id}/lines/${line.id}/product`
            this.$http.post(url, {...line}, {
                params: {include: this.include}
            }).then(response => {
                this.refresh();
                this.showCreateProduct = null;
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        openSupplierAccessModal() {
            this.$refs['supplier-access-modal'].open();
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
            if (result) {
                this.restore();
            }
        },
        async finalise() {
            let result = await this.$bvModal.msgBoxConfirm(
                "Are you sure you want to finalise this order acknowledgement?", {
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
        async close(){
            let result = await this.$bvModal.msgBoxConfirm(
                "Are you sure you want to close this order acknowledgement?", {
                    centered: true,
                    title: 'Please confirm',
                    okTitle: 'Yes'
                }
            );

            if (!result) {
                return;
            }

            this.save({closed: true})
        },
        async unclose(){
            let result = await this.$bvModal.msgBoxConfirm(
                "Are you sure you want to reopen this order acknowledgement?", {
                    centered: true,
                    title: 'Please confirm',
                    okTitle: 'Yes'
                }
            );

            if (!result) {
                return;
            }

            this.save({reopen: true})
        },
        restore() {
            const loader = this.$loading.show
            this.salesOrder.restore = true
            this.$http.put(`/api/order-acknowledgements/${this.salesOrder.id}`, this.salesOrder, {params: {include: this.include}}
            ).then(response => {
                this.salesOrder = response.data;
                loader.hide
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            });

        },
        save(model = false) {
            const loader = this.$loading.show();
            this.$http.put(
                `${this.urlPrefix}/${this.salesOrder.id}`, (model) ? model : this.salesOrder, {params: {include: this.include}}
            ).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
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
                    `${this.urlPrefix}/${this.salesOrder.id}/lines/${line.id}`, {
                        params: {include: this.include}
                    }
                ).then(response => {
                    this.salesOrder = response.data;
                    loader.hide();
                }).catch(error => {
                    this.$handleExceptionResponse(error, true);
                    loader.hide();
                });
            })
        },
        add() {
            const loader = this.$loading.show();
            const url = `${this.urlPrefix}/${this.salesOrder.id}/lines`;
            this.$http.post(url, {}, {params: {include: this.include}}).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            });
        },
        updateLine(line) {


            if(!line.unit_price) {
                line.unit_price = line.current_landed_cost_per_unit_gbp;
            }



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
            this.$http.get(`/api/order-acknowledgements/${this.acknowledgementId}`, {
                params: {include: this.include}
            }).then(response => {
                this.salesOrder = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        },
        deleteSalesOrder() {
            this.$bvModal.msgBoxConfirm('Are you sure that you want to delete this Order Acknowledgement', {
                title: 'Delete',
                okTitle: 'Yes',
                okVariant: 'danger'
            }).then(response => {
                if (!response) {
                    return;
                }
                const loader = this.$loading.show();
                this.$http.delete(`/api/order-acknowledgements/${this.salesOrder.id}`).then(() => {
                    loader.hide();
                    window.location.href = "/order-acknowledgements"
                }).catch(error => {
                    this.$handleExceptionResponse(error, true)
                    loader.hide()
                })
            })
        }
    },
    data() {
        return {
            showCreateProduct: null,
            salesOrder: {
                currency: {}
            },
            note: '',
            rerenderCount: 0,
        }
    }
}
</script>

<style scoped>

</style>