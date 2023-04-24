<template>
    <div>
        <b-card class="shadow-sm">
            <div class="row">
                <div class="col-12 col-md-6">
                    <b-form-group label="Product Description">
                        <b-form-input v-model="product.name" required="required"/>
                    </b-form-group>
                    <div class="row" v-if="!isSupplier">
                        <div class="col-6">
                            <b-form-group label="Supplier">
                                <supplier-select all v-model="product.supplier"/>
                            </b-form-group>
                        </div>
                        <div class="col-6">
                            <b-form-group v-if="isChina" label="Factories">
                                <factory-select v-model="product.factories" multiple/>
                            </b-form-group>
                            <b-form-group v-if="isUk && canCustomProfit" label="Custom Minimum Profit">
                                <b-input-group prepend="£">
                                    <b-form-input type="number" v-model="product.custom_minimum_profit" />
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>
                    <div v-else>
                        <b-form-group label="Factories">
                            <factory-select v-model="product.factories" multiple/>
                        </b-form-group>
                    </div>
                    <b-form-group description="When estimating landed costs, no commission will be added"
                                  v-if="!isSupplier && showCosting && isChina">
                        <b-form-checkbox v-model="product.is_excluded_from_commission">Exclude from commission
                        </b-form-checkbox>
                    </b-form-group>

                    <div class="row">
                        <div class="col-4">
                            <b-form-group label="Item Number">
                                <b-form-input all v-model="product.item_number"/>
                                <b-badge v-if="product.has_duplicate_item_number" variant="danger" class="text-center">Duplicate Item Number</b-badge>
                            </b-form-group>
                        </div>
                        <div class="col-4" v-if="isChina">
                            <b-form-group label="Article Number">
                                <b-form-input all v-model="product.article_number"/>
                            </b-form-group>
                        </div>
                        <div class="col-4">
                            <b-form-group label="Barcode">
                                <b-form-input all v-model="product.barcode"/>
                                <b-badge v-if="product.has_duplicate_barcode" variant="danger">Duplicate Barcode</b-badge>
                            </b-form-group>
                        </div>
                        <div class="col-4" v-if="isUk">
                            <b-form-group label="Unit Price GBP">
                                <b-input-group prepend="£">
                                    <b-form-input v-model="product.unit_price_gbp"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>
                    <div class="row" v-if="isSupplier">
                        <div class="col-4">
                            <b-form-group label="Unit Price">
                                <b-input-group prepend="$">
                                    <b-form-input v-model="product.unit_price"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>
                    <div class="row" v-if="!isSupplier && isChina">
                        <div class="col-4">
                            <b-form-group label="Unit Price">
                                <b-input-group prepend="$">
                                    <b-form-input v-model="product.unit_price"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                        <div class="col-4" v-if="isChina">
                            <b-form-group label="Est. Land' Cost">
                                <b-input-group prepend="£">
                                    <b-form-input readonly v-model="product.est_landed_cost_gbp"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                        <div class="col-4" v-if="isChina">
                            <b-form-group label="Current Land' Cost" v-if="product.current_landed_cost_per_unit_gbp">
                                <b-input-group prepend="£">
                                    <b-form-input readonly v-model="product.current_landed_cost_per_unit_gbp"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>
                    <h5>Images </h5>
                    <edit-product-media :disabled="disabled" :product="product" @updated="refresh()"/>
                    <div class="text-muted" v-if="!product.id">Image upload available once product has been created
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <b-tabs v-model="tabIndex" content-class="pt-3" small>
                        <b-tab title="Description">
                            <b-form-group label="Product Details">
                                <b-textarea v-model="product.product_description" maxlength="500" rows="7"/>
                                <template #description>
                                    <span v-if="product.product_description">
                                        {{ product.product_description.length }} / 500 characters
                                    </span>
                                    <span v-else>0 / 500</span>
                                </template>
                            </b-form-group>
                            <b-form-group label="Packing Instructions" v-if="isChina">
                                <b-textarea v-model="product.packing_instructions" maxlength="500" rows="7"/>
                                <template #description>
                                  <span v-if="product.packing_instructions">
                                      {{ product.packing_instructions.length }} / 500 characters
                                  </span>
                                    <span v-else>0 / 500</span>
                                </template>
                            </b-form-group>
                        </b-tab>
                        <b-tab title="Dimensions">
                            <h4>Postage</h4>
                            <div class="row" v-if="!isSupplier">
                                <div class="col-4">
                                    <b-form-group label="UK Postage">
                                        <b-form-select class="md 1" v-model="product.uk_postage_method">
                                            <b-form-select-option value="small_letter">Small letter
                                            </b-form-select-option>
                                            <b-form-select-option value="large_letter_package">Large letter package
                                            </b-form-select-option>
                                            <b-form-select-option value="package">Package</b-form-select-option>
                                            <b-form-select-option value="dpd_small">DPD small</b-form-select-option>
                                            <b-form-select-option value="dpd_large">DPD large</b-form-select-option>
                                            <b-form-select-option value="parcel_force">Parcel force
                                            </b-form-select-option>
                                        </b-form-select>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="EU Postage">
                                        <b-form-select class="md 1" v-model="product.eu_postage_method">
                                            <b-form-select-option value="lightweight_airmail">Lightweight airmail
                                            </b-form-select-option>
                                            <b-form-select-option value="standard_airmail">Standard airmail
                                            </b-form-select-option>
                                            <b-form-select-option value="signed_for">Signed for</b-form-select-option>
                                            <b-form-select-option value="parcel_force">Parcel force
                                            </b-form-select-option>
                                        </b-form-select>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Worldwide postage">
                                        <b-form-select class="md 1" v-model="product.row_postage_method">
                                            <b-form-select-option value="lightweight_airmail">Lightweight airmail
                                            </b-form-select-option>
                                            <b-form-select-option value="standard_airmail">Standard airmail
                                            </b-form-select-option>
                                            <b-form-select-option value="signed_for">Signed for</b-form-select-option>
                                            <b-form-select-option value="parcel_force">Parcel force (check amount)
                                            </b-form-select-option>
                                        </b-form-select>
                                    </b-form-group>
                                </div>
                            </div>
                            <h5>
                                Carton
                                <small v-if="!isSupplier && product.id > 0 && isChina">
                                    <a href="#" @click.prevent="showTestCalculator = !showTestCalculator">{{
                                            showTestCalculator ? 'Hide' : 'Show'
                                        }} Test Calculator</a>
                                </small>
                            </h5>
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Units Per Case">
                                        <b-form-input v-model="product.units_per_case"/>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Inner Qty">
                                        <b-form-input v-model="product.inner_qty"/>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Weight (kg)">
                                        <b-form-input v-model="product.case_weight_kg"/>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <b-form-group label="Length (cm)">
                                        <b-form-input v-model="product.length_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Width (cm)">
                                        <b-form-input v-model="product.width_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Height (cm)">
                                        <b-form-input v-model="product.height_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Volume (ft)" class="no-input">
                                        {{ product.case_cubic_ft | 2dp_value }}
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="row" v-if="!isSupplier && showTestCalculator">
                                <div class="col-4">
                                    <b-form-group label="Exchange Rate">
                                        <b-input-group prepend="$ : £">
                                            <b-form-input v-model="test.exchange_rate_usd "/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Cost to ship / cu.ft.">
                                        <b-input-group prepend="£">
                                            <b-form-input v-model="test.shipping_cost_per_cubic_foot"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Est. Landed Cost">
                                        <b-input-group prepend="£">
                                            <div class="form-control">
                                                {{ testLandedCost | cash_value }}
                                            </div>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <h5>Unit</h5>
                            <div class="row">
                                <div class="col-12">
                                    <b-form-group label="Unit Weight (kg)">
                                        <b-form-input v-model="product.unit_weight_kg"/>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    <b-form-group label="Length (cm)">
                                        <b-form-input v-model="product.unit_length_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Width (cm)">
                                        <b-form-input v-model="product.unit_width_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Height (cm)">
                                        <b-form-input v-model="product.unit_height_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-3">
                                    <b-form-group label="Volume (ft)" class="no-input">
                                        {{ product.unit_cubic_ft | 2dp_value }}
                                    </b-form-group>
                                </div>
                            </div>
                        </b-tab>
                        <product-sku-tab v-if="!isSupplier && product.id" :product="product"/>
                        <b-tab v-if="product.id">
                            <template #title>
                                <div>
                                    Notes
                                    <b-badge variant="primary">{{ product.comments.length }} <span class="sr-only">number of notes</span>
                                    </b-badge>
                                </div>
                            </template>
                            <b-table-simple small>
                                <b-thead>
                                    <b-tr>
                                        <b-th class="w-25">Created</b-th>
                                        <b-th>Note</b-th>
                                    </b-tr>
                                </b-thead>
                                <b-tr v-if="product.comments.length < 1">
                                    <b-td colspan="2" class="text-center">No Notes Added</b-td>
                                </b-tr>
                                <b-tr v-for="note in product.comments" :key="note.id">
                                    <b-td>
                                        <div class="small">
                                            <span v-if="note.commentator && note.commentator.id">{{
                                                    note.commentator.name
                                                }}</span>
                                            <span v-else>System</span>
                                            <br/>
                                            <span>{{ new Date(note.created_at) | date('dd/MM/yy HH:mm') }}</span>
                                        </div>
                                    </b-td>
                                    <b-td class="align-top"> {{ note.comment }}</b-td>
                                </b-tr>
                            </b-table-simple>
                            <hr/>
                            <b-form-group>
                                <b-textarea v-model="note" placeholder="Add new note... "/>
                            </b-form-group>
                            <b-button variant="outline-primary float-right" @click="addNewNote">Add Note</b-button>
                        </b-tab>
                        <b-tab title="History" v-if="!isSupplier && product.id" :disabled="!showCosting">
                            <div v-if="isUk">
                                <div class="d-flex justify-content-end">
                                    <b-form-select v-model="historyType" size="sm" class="w-25 mb-2">
                                        <b-form-select-option value="changes">Changes</b-form-select-option>
                                        <b-form-select-option value="stock">Stock</b-form-select-option>
                                        <b-form-select-option value="daily_pricing">Daily Pricing</b-form-select-option>
                                        <b-form-select-option value="order_acknowledgement">OA's</b-form-select-option>
                                        <b-form-select-option value="invoices">Invoices</b-form-select-option>
                                    </b-form-select>
                                </div>
                                <changes-history v-if="product && product.id && historyType === 'changes'" :product-id="product.id" :fields="productLogFields"></changes-history>
                                <stock-history v-if="product && product.id && historyType === 'stock'" :product-id="product.id"></stock-history>
                                <daily-pricing-history v-if="product && product.id && historyType === 'daily_pricing'" :product-id="product.id"></daily-pricing-history>
                                <invoice-history v-if="product && product.id && historyType === 'invoices'" :product-id="product.id"></invoice-history>
                                <order-acknowledgement-history v-if="product && product.id && historyType === 'order_acknowledgement'" :product-id="product.id"></order-acknowledgement-history>
                            </div>
                            <changes-history v-else :product-id="product.id" :fields="productLogFields"></changes-history>
                        </b-tab>
                        <landed-cost-history-tab v-if="!isSupplier && product.id && isChina" :product="product"
                                                 :disabled="!showCosting"/>
                    </b-tabs>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <div>
                    <b-button v-if="this.productId && canDelete" variant="outline-danger" @click="deleteProduct">Delete</b-button>
                    <b-button v-if="this.productId && canCopy" variant="outline-primary" @click="copyProduct">Copy Product</b-button>

                </div>
                <div v-if="tabIndex === 0">
                    <b-button variant="outline-primary mr-2" @click="save()">
                        {{ this.productId ? 'Update' : 'Create' }} Product
                    </b-button>

                    <b-button @click="sendToPricing" v-if="this.productId"
                              variant="outline-secondary float-right" class="mr-2">
                        Send To Pricing
                    </b-button>
                </div>
                <b-button v-if="tabIndex === 1" variant="outline-primary float-right" @click="save()">
                    {{ this.productId ? 'Update' : 'Create' }} Product
                </b-button>
            </div>
        </b-card>
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../components/SupplierSelect";
import EditProductMedia from "./EditProductMedia";
import FactorySelect from "../../components/FactorySelect";
import LandedCostHistoryTab from "./LandedCostHistoryTab";
import ProductSkuTab from "./ProductSkuTab";
import LandedCostCalculator from '../../library/LandedCostCalculator';
import InvoiceHistory from "./HistoryLists/InvoiceHistory";
import OrderAcknowledgementHistory from "./HistoryLists/OrderAcknowledgementHistory";
import DailyPricingHistory from "./HistoryLists/DailyPricingHistory";
import ChangesHistory from "./HistoryLists/ChangesHistory";
import StockHistory from "./HistoryLists/StockHistory";

export default {
    name: "EditProduct",
    components: {
        StockHistory,
        ChangesHistory,
        DailyPricingHistory,
        OrderAcknowledgementHistory,
        InvoiceHistory,
        ProductSkuTab,
        LandedCostHistoryTab,
        FactorySelect,
        EditProductMedia,
        SupplierSelect
    },
    props: {
        importDutyPc: Number,
        commissionRatePc: Number,
        exchangeRateUsd: Number,
        shippingCostPerCubicFoot: Number,
        showCosting: Boolean,
        productId: Number,
        isSupplier: Boolean,
        cubicMetresToFeet: Number,
        isUk: Boolean,
        isChina: Boolean,
        canDelete: Boolean,
        canCopy: Boolean,
        canCustomProfit: Boolean
    },
    filters: {
        date: dateFilter
    },
    mounted() {
        if (this.productId) {
            this.refresh();
        }
        this.test = {
            shipping_cost_per_cubic_foot: this.shippingCostPerCubicFoot,
            exchange_rate_usd: this.exchangeRateUsd
        }
    },
    computed: {
        testLandedCost() {
            const caseVolumeM = (this.product.height_cm/100) * (this.product.length_cm/100) * (this.product.width_cm/100);
            const landedCost = LandedCostCalculator.calculate(
                caseVolumeM * this.cubicMetresToFeet,
                this.product.unit_price,
                this.test.shipping_cost_per_cubic_foot,
                this.test.exchange_rate_usd,
                this.commissionRatePc,
                this.importDutyPc,
                this.product.is_excluded_from_commission,
                this.product.units_per_case
            );
            return landedCost;
        },
        disabled() {
            return !(this.productId > 0);
        },
        include() {
            return 'supplier,comments,comments.commentator,media,factories,current_landed_cost_per_unit_gbp';
        },
        productLogFields() {
            return [{
                key: 'updated_at',
                label: 'When',
            }, {
                key: 'attributes.case_weight_kg',
                label: 'KG/Ctn'
            }, {
                key: 'attributes.est_landed_cost_gbp',
                label: '£ELC'
            }, {
                key: 'attributes.units_per_case',
                label: 'QTY/Ctn'
            }, {
                key: 'attributes.case_cubic_ft',
                label: 'Cuft/Ctn'
            }, {
                key: 'attributes.unit_price',
                label: 'U/Price'
            }, {
                key: 'extra',
                label: 'Desc',
                tdClass: 'w-25'
            }, {
                key: 'details',
                label: '',
                tdClass: 'w-1'
            }]
        }
    },
    methods: {
        async deleteProduct(){
            const result = await this.$bvModal.msgBoxConfirm('Are you sure you want to delete this product?', {
                centered: true,
                okTitle: 'Confirm',
                title: 'Please Confirm'
            })
            if(!result) {
                return;
            }
            const loading = this.$loading.show();
            this.$http.delete(`/api/products/${this.product.id}`).then(response => {
                loading.hide();
                window.location.href = '/products'
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        async copyProduct(){
            const result = await this.$bvModal.msgBoxConfirm('Are you sure you want to copy this product?', {
                centered: true,
                okTitle: 'Confirm',
                title: 'Please Confirm'
            })
            if(!result) {
                return;
            }
            const loading = this.$loading.show();
            this.$http.post(`/api/products/${this.product.id}/copy`).then(response => {
                loading.hide();
                window.location.href = `/products/${response.data.id}/edit`
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        sendToPricing() {
            this.$bvModal.msgBoxConfirm('Are you sure you want to send this product to pricing?', {
                title: 'Send to pricing',
                okTitle: 'Yes',
            }).then(response => {
                if (!response) {
                    return;
                }
                const loading = this.$loading.show();
                const url = `/api/products/${this.product.id}/pricing`
                this.$http.post(url).then(() => {
                    this.refresh();
                    loading.hide();
                }).catch(error => {
                    this.$handleExceptionResponse(error, true);
                    loading.hide();
                })
            })

        },
        save() {
            const method = this.product.id ? 'PUT' : 'POST';
            const url = `/api/products/` + (method === 'PUT' ? this.product.id : '');
            const loader = this.$loading.show();
            this.$http({
                method: method,
                url: url,
                data: {...this.product},
                params: {include: this.include}
            }).then(response => {

                if (method === 'POST') {
                    window.location.href = `/products/${response.data.id}/edit`
                    return;
                }

                this.product = response.data
                loader.hide();
                this.refresh();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        },
        addNewNote() {
            const url = `/api/products/${this.product.id}/comments`;
            const loader = this.$loading.show();
            this.$http.post(url, {comment: this.note}).then(() => {
                this.note = '';
                loader.hide();
                this.refresh();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        },
        refresh() {
            const loader = this.$loading.show();
            this.$http.get(`/api/products/${this.productId}`, {
                params: {include: this.include}
            }).then(response => {
                this.product = response.data;
                this.test.unit_price = this.product.unit_price;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        }
    },
    data() {
        return {
            showTestCalculator: false,
            test: {
                exchange_rate_usd: this.exchangeRateUsd
            },
            recalculate_elc: false,
            note: '',
            product: {
                product_description: '',
                packing_instructions: '',
                comments: [],
                supplier: {}
            },
            tabIndex: 0,
            historyType: 'changes',
        }
    }
}
</script>

<style scoped>

</style>
