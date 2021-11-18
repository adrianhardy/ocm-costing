<template>
    <div>
        <b-card class="shadow-sm">
            <div class="row">
                <div class="col-12 col-md-6">
                    <b-form-group label="Product Description">
                        <b-form-input v-model="product.name" required="required"/>
                    </b-form-group>
                    <div class="row">
                        <div class="col-6">
                            <b-form-group label="Supplier" v-if="!isSupplier">
                                <supplier-select all v-model="product.supplier"/>
                            </b-form-group>
                        </div>
                        <div class="col-6">
                            <b-form-group label="Factory" v-if="!isSupplier">
                                <factory-select all v-model="product.factory"/>
                            </b-form-group>
                        </div>
                    </div>
                    <b-form-group description="When estimating landed costs, no commission will be added" v-if="showCosting">
                        <b-form-checkbox v-model="product.is_excluded_from_commission">Exclude from commission
                        </b-form-checkbox>
                    </b-form-group>

                    <div class="row">
                        <div class="col-4">
                            <b-form-group label="Item Number">
                                <b-form-input all v-model="product.item_number"/>
                            </b-form-group>
                        </div>
                        <div class="col-4">
                            <b-form-group label="Article Number">
                                <b-form-input all v-model="product.article_number"/>
                            </b-form-group>
                        </div>
                        <div class="col-4">
                            <b-form-group label="Barcode">
                                <b-form-input all v-model="product.barcode"/>
                            </b-form-group>
                        </div>
                    </div>
                    <div class="row" v-if="!isSupplier && showCosting">
                        <div class="col-4">
                            <b-form-group label="Unit Price">
                                <b-input-group prepend="$">
                                    <b-form-input v-model="product.unit_price"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                        <div class="col-4">
                            <b-form-group label="Est. Land' Cost">
                                <b-input-group prepend="£">
                                    <b-form-input readonly v-model="product.est_landed_cost_gbp"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                        <div class="col-4">
                            <b-form-group label="Current Land' Cost" v-if="product.current_landed_cost_per_unit_gbp">
                                <b-input-group prepend="£">
                                    <b-form-input readonly v-model="product.current_landed_cost_per_unit_gbp"/>
                                </b-input-group>
                            </b-form-group>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div>UK postage</div>
                            <b-form-select class="md 1" v-model="product.uk_postage_method">
                                <b-form-select-option value="small_letter">Small letter</b-form-select-option>
                                <b-form-select-option value="large_letter_package">Large letter package</b-form-select-option>
                                <b-form-select-option value="package">Package</b-form-select-option>
                                <b-form-select-option value="dpd_small">DPD small</b-form-select-option>
                                <b-form-select-option value="dpd_large">DPD large</b-form-select-option>
                                <b-form-select-option value="parcel_force">Parcel force</b-form-select-option>
                            </b-form-select>
                        </div>
                        <div class="col-4">
                            <div>EU postage</div>
                            <b-form-select class="md 1" v-model="product.eu_postage_method">
                                <b-form-select-option value="lightweight_airmail">Lightweight airmail</b-form-select-option>
                                <b-form-select-option value="standard_airmail">Standard airmail</b-form-select-option>
                                <b-form-select-option value="signed_for">Signed for</b-form-select-option>
                                <b-form-select-option value="parcel_force">Parcel force</b-form-select-option>
                            </b-form-select>
                        </div>
                        <div class="col-4">
                            <div>Worldwide postage</div>
                            <b-form-select class="md 1" v-model="product.row_postage_method">
                                <b-form-select-option value="lightweight_airmail">Lightweight airmail</b-form-select-option>
                                <b-form-select-option value="standard_airmail">Standard airmail</b-form-select-option>
                                <b-form-select-option value="signed_for">Signed for</b-form-select-option>
                                <b-form-select-option value="parcel_force">Parcel force (check amount)</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                    <h5>Images </h5>
                        <edit-product-media :disabled="disabled" :product="product" @updated="refresh()"/>
                    <div class="text-muted" v-if="!product.id">Image upload available once product has been created
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <b-tabs content-class="pt-3" small>
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
                            <b-form-group label="Packing Instructions">
                                <b-textarea v-model="product.packing_instructions" maxlength="500" rows="7"/>
                                <template #description>
                                  <span v-if="product.packing_instructions">
                                      {{ product.packing_instructions.length }} / 500 characters
                                  </span>
                                    <span v-else>0 / 500</span>
                                </template>
                            </b-form-group>
                            <b-button variant="outline-primary float-right" @click="save()">
                                {{ this.productId ? 'Update' : 'Create' }} Product
                            </b-button>

                        </b-tab>
                        <b-tab title="Dimensions">
                            <h5>Carton</h5>
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
                            <b-button variant="outline-primary float-right" @click="save()">
                                {{ this.productId ? 'Update' : 'Create' }} Product
                            </b-button>
                        </b-tab>
                        <product-sku-tab v-if="!isSupplier && product.id" :product="product" />
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
                            <b-table ref="product-history" small :items="productLog" :fields="productLogFields"
                                     sticky-header="600px">
                                <template #cell(updated_at)="data">
                                    {{ new Date(data.value) | date('dd/MM/yy HH:mm') }}
                                </template>
                                <template #cell(unit_price)="data">
                                    {{ data.value | 2dp_value }}
                                </template>
                                <template #cell(landed_cost_per_unit_gbp)="data">
                                    {{ data.value | cash_value }}
                                </template>
                                <template #cell(extra)="data">
                                    <div v-if="data.item && data.item.attributes">
                                      <span v-b-popover.hover="data.item.attributes.packing_instructions"
                                            title="Packing Instructions" style="cursor:pointer">
                                        <b-icon-box-seam/>
                                      </span>
                                        <span v-b-popover.hover="data.item.attributes.product_description"
                                              title="Product Description" style="cursor:pointer">
                                        <b-icon-tag/>
                                      </span>
                                        <span style="cursor:pointer" v-b-popover.hover="data.item.attributes.barcode"
                                              title="Barcode">
                                        <b-icon-upc-scan/>
                                      </span>
                                    </div>
                                </template>
                            </b-table>
                        </b-tab>
                        <landed-cost-history-tab v-if="!isSupplier && product.id" :product="product" :disabled="!showCosting" />
                    </b-tabs>
                </div>
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

export default {
    name: "EditProduct",
    components: {ProductSkuTab, LandedCostHistoryTab, FactorySelect, EditProductMedia, SupplierSelect},
    props: {
        showCosting: Boolean,
        productId: Number,
        isSupplier: Boolean
    },
    filters: {
        date: dateFilter
    },
    mounted() {
        if (this.productId) {
            this.refresh();
        }
    },
    computed: {
        disabled() {
            return !(this.productId > 0);
        },
        include() {
            return 'supplier,comments,comments.commentator,media,factory,current_landed_cost_per_unit_gbp';
        },
        productLogFields() {
            return [{
                key: 'updated_at',
                label: 'When',
            }, {
                key: 'attributes.case_weight_kg',
                label: 'Ctn Kg'
            }, {
                key: 'attributes.est_landed_cost_gbp',
                label: 'ELC'
            }, {
                key: 'attributes.units_per_case',
                label: 'Qty/Ctn'
            }, {
                key: 'attributes.length_cm',
                label: 'L'
            }, {
                key: 'attributes.width_cm',
                label: 'W'
            }, {
                key: 'attributes.height_cm',
                label: 'H'
            }, {
                key: 'attributes.unit_price',
                label: 'U/Price'
            }, {
                key: 'extra',
                label: 'Desc'
            }]
        }
    },
    methods: {
        productLog() {
            const url = `/api/products/${this.productId}/history`;
            return this.$http.get(url).then(response => {
                return _.map(response.data.items, row => {
                    return {
                        updated_at: row.updated_at,
                        attributes: row.properties.attributes
                    }
                });
            });
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
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        }
    },
    data() {
        return {
            note: '',
            product: {
                product_description: '',
                packing_instructions: '',
                comments: [],
                supplier: {},
                factory: {}
            }
        }
    }
}
</script>

<style scoped>

</style>
