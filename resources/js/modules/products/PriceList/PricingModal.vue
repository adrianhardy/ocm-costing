<template>
    <b-modal
        id="pricing"
         :title="product.name"
         ref="modal"
         ok-title="Save"
         @ok="save($event)"
         cancel-title="Close"
         ok-title-html="Update"
         centered
         size="xl"
    >
        <template #modal-footer="{ok, cancel}">
            <div class="mr-auto">
                <b-button class="mr-1" @click="copyPricing">Copy Pricing</b-button>
                <b-button v-if="copiedPricing" @click="pastePricing">Paste Pricing</b-button>
            </div>
            <b-button variant="secondary" @click="cancel">Cancel</b-button>
            <b-button variant="primary" @click="submitForApproval" v-if="!value.submitted_at">
                Submit for Approval
            </b-button>
            <b-button v-if="isApprover && value.submitted_at" variant="success" @click="submitApproved">
                Approve
            </b-button>
            <b-button variant="primary" @click.prevent="save($event)">Update</b-button>
        </template>
        <div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <h5>Product</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">Item Number</th>
                            <td>{{ product.item_number }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">Primary SKU</th>
                            <td>{{ product.primary_sku }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">Primary ASIN</th>
                            <td>{{ product.primary_asin }}</td>
                        </tr>
                    </table>
                    <h5>Postage</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">UK Postage Method</th>
                            <td>{{ ukPostageMethod }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">EU Postage Method</th>
                            <td>{{ euPostageMethod }}</td>
                        </tr>
                        <tr>
                            <th class="w-50">RoW Postage Method</th>
                            <td>{{ rowPostageMethod }}</td>
                        </tr>
                    </table>
                    <h5>Carton</h5>
                    <table class="table table-sm small">
                        <tr>
                            <th class="w-50">Units per carton</th>
                            <td>{{ line.units_per_case }}</td>
                        </tr>
                        <tr>
                            <th>Inner quantity</th>
                            <td>{{ line.inner_qty }}</td>
                        </tr>
                        <tr>
                            <th>Weight (kg)</th>
                            <td>{{ line.case_weight_kg }}</td>
                        </tr>
                        <tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ line.length_cm }} x
                                {{ line.width_cm }} x
                                {{ line.height_cm }} cm
                            </td>
                        </tr>
                    </table>
                    <h5>Unit</h5>
                    <table class="table table-sm small" v-if="line">
                        <b-tr><b-td colspan="2">From Sales Order Line</b-td></b-tr>
                        <b-tr>
                            <th class="w-50">Weight (kg)</th>
                            <td>{{ line.unit_weight_kg }}</td>
                        </b-tr>
                        <b-tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ line.unit_length_cm }} x
                                {{ line.unit_width_cm }} x
                                {{ line.unit_height_cm }} cm
                            </td>
                        </b-tr>
                    </table>
                    <table class="table table-sm small" v-else>
                        <b-tr><b-td colspan="2">From Product</b-td></b-tr>
                        <b-tr>
                            <th class="w-50">Weight (kg)</th>
                            <td>{{ product.unit_weight_kg }}</td>
                        </b-tr>
                        <b-tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ product.unit_length_cm }} x
                                {{ product.unit_width_cm }} x
                                {{ product.unit_height_cm }} cm
                            </td>
                        </b-tr>
                    </table>
                    <div>
                    </div>
                    <h5>Image</h5>
                    <div class="p-1">
                        <b-img fluid style="max-height: 200px" class="img-thumnail"
                               :src="product | product_image_url"/>
                    </div>
                </div>
                <div class="col-12 col-md-9">
                    <b-tabs content-class="pt-3">
                        <b-tab title="UK/EUR Pricing">
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Est. L' Cost" label-cols="4" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input readonly v-model.number="value.est_landed_cost_per_unit_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Referral Fee" label-cols="4" label-size="sm">
                                        <b-input-group size="sm" class="w-70">
                                            <b-form-input v-model="value.marketplace_commission_pc"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100 text-center">
                                                    %
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="EU Rate" label-cols="4" label-size="sm">
                                        <b-input-group size="sm">
                                            <b-form-input v-model.number="value.current_eur_fx_rate"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100">
                                                    : £1
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="overflow-auto">
                                <div class="row flex-row flex-nowrap">
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing
                                            v-if="value && value.countries && value['countries']['gb']"
                                            v-model="value['countries']['gb']"
                                            country="UK"
                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                            :landed-cost-per-unit-gbp="value.est_landed_cost_per_unit_gbp"
                                            fee-field="unit_fba_fee"
                                            sf-price-field="unit_sf_price"
                                            ship-field="unit_fba_shipping"
                                            price-field="unit_fba_price"
                                            vat-field="vat_rate"
                                            :fx-rate="1"
                                            @copy-sf-price="copySfPrice()"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3" v-for="country in euroCountries">
                                        <eu-product-pricing
                                            v-if="value && value.countries && value.countries[country.iso_2_code.toLowerCase()]"
                                            v-model="value['countries'][country.iso_2_code.toLowerCase()]"
                                            :country="country.iso_2_code"
                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                            :landed-cost-per-unit-gbp="value.est_landed_cost_per_unit_gbp"
                                            fee-field="unit_fba_fee"
                                            sf-price-field="unit_sf_price"
                                            ship-field="unit_fba_shipping"
                                            price-field="unit_fba_price"
                                            vat-field="vat_rate"
                                            :fx-rate="value.current_eur_fx_rate"
                                        />
                                        <div v-else>
                                            <b-form-group :label="country.iso_2_code" label-size="sm" class="mb-2">
                                                Missing pricing for this country. Please resend through pricing.
                                            </b-form-group>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div v-if="value.product">
                                        <h5>Pricing Notes</h5>
                                        <b-textarea
                                            rows="4"
                                            class="mb-3"
                                            v-model="value.product.pricing_notes"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <b-form-group label="Priority">
                                        <b-select v-model="value.priority">
                                            <b-select-option value="low">Low</b-select-option>
                                            <b-select-option value="medium">Medium</b-select-option>
                                            <b-select-option value="high">High</b-select-option>
                                        </b-select>
                                    </b-form-group>
                                    <b-form-group label="Website Price">
                                        <b-input-group prepend="£">
                                            <b-form-input type="number" v-model="value.website_price" />
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <div></div>


                        </b-tab>
                        <b-tab title="EU Pricing">
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Est. L' Cost" label-cols="4" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input readonly v-model="value.est_landed_cost_per_unit_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Referral Fee" label-cols="4" label-size="sm">
                                        <b-input-group size="sm" class="w-70">
                                            <b-form-input v-model="value.marketplace_commission_pc"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100 text-center">
                                                    %
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="overflow-auto">
                                <div class="row flex-row flex-nowrap">
                                    <div class="col-12 col-md-3" v-for="country in nonEuroEuCountries" v-if="value && value['countries'] && value['countries'][country.iso_2_code.toLowerCase()]">
                                        <rest-of-world-product-pricing
                                            v-model="value['countries'][country.iso_2_code.toLowerCase()]"
                                            :currency="country.currency.code"
                                            :country="country"
                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                            :landed-cost-per-unit-gbp="value.est_landed_cost_per_unit_gbp"
                                            fee-field="unit_fba_fee"
                                            sf-price-field="unit_sf_price"
                                            ship-field="unit_fba_shipping"
                                            price-field="unit_fba_price"
                                            vat-field="vat_rate"
                                            fx-field="current_gbp_fx_rate"
                                            eu-pricing
                                        />
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="RoW Pricing">
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Est. L' Cost" label-cols="4" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input readonly v-model="value.est_landed_cost_per_unit_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Referral Fee" label-cols="4" label-size="sm">
                                        <b-input-group size="sm" class="w-70">
                                            <b-form-input v-model="value.marketplace_commission_pc"/>
                                            <b-input-group-append class="w-30">
                                                <b-input-group-text class="w-100 text-center">
                                                    %
                                                </b-input-group-text>
                                            </b-input-group-append>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="overflow-auto">
                                <div class="row flex-row flex-nowrap">
                                    <div class="col-12 col-md-3" v-for="country in rowCountries" v-if="value && value['countries'] && value['countries'][country.iso_2_code.toLowerCase()]">
                                        <rest-of-world-product-pricing
                                            v-model="value['countries'][country.iso_2_code.toLowerCase()]"
                                            :currency="country.currency.code"
                                            :country="country"
                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                            :landed-cost-per-unit-gbp="value.est_landed_cost_per_unit_gbp"
                                            fee-field="unit_fba_fee"
                                            sf-price-field="unit_sf_price"
                                            ship-field="unit_fba_shipping"
                                            price-field="unit_fba_price"
                                            vat-field="vat_rate"
                                            fx-field="current_gbp_fx_rate"
                                        />
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="History"> <!-- History Tab -->
                            <b-table size="sm" ref="table" :fields="fields" :items="items">
                                <template #cell(created_at)="data">
                                    {{ data.value | date_dmY }}
                                </template>
                                <template #cell(approved_at)="data">
                                    {{ data.value | date_dmY }}
                                </template>
                            </b-table>
                        </b-tab>
                        <b-tab title="Small & Light">
                            <div style="max-height: 650px; overflow: auto">
                                <img v-if="images" :src="images.sl" />
                            </div>
                        </b-tab>
                        <b-tab title="Local FBA">
                            <div style="max-height: 650px; overflow: auto">
                                <img v-if="images" :src="images.local_fba"/>
                            </div>
                        </b-tab>
                        <b-tab title="EFN FBA">
                            <div style="max-height: 650px; overflow: auto">
                                <img v-if="images" :src="images.efn_fba" />
                            </div>
                        </b-tab>
                    </b-tabs>
                </div>
            </div>
        </div>
    </b-modal>
</template>

<script>
import RestOfWorldProductPricing from "./RestOfWorldProductPricing";
import EuProductPricing from "./EuProductPricing";
import SmallLight from "./AmazonShippingRates/SmallLight";
import LocalFBA from "./AmazonShippingRates/LocalFBA";
import EFNFBA from "./AmazonShippingRates/EFNFBA";

export default {
    name: "PricingModal",
    components: {EFNFBA, LocalFBA, SmallLight, EuProductPricing, RestOfWorldProductPricing},
    props: {
        uk: Object,
        rowCountries: Array,
        euroCountries: Array,
        nonEuroEuCountries: Array,
        isApprover: Boolean
    },
    created() {
        this.$on('updated', e => {
            if (e.submitted) {
                this.close();
            }
            if (e.approved) {
                this.close();
            }
        })
    },
    mounted() {
        this.getGuidance();
        console.log("****************", this.nonEuroEuCountries, "****************");
    },
    computed: {
        ukPostageMethod() {
            if (this.product.uk_postage_method) {
                return this.product.uk_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        euPostageMethod() {
            if (this.product.eu_postage_method) {
                return this.product.eu_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        rowPostageMethod() {
            if (this.product.row_postage_method) {
                return this.product.row_postage_method.toUpperCase().replaceAll('_', ' ');
            }
            return 'NOT SET';
        },
        product() {
            if (this.value && this.value.product) {
                return this.value.product;
            }
            return {};
        },
        line() {
            if (this.value.sales_order_line) {
                return this.value.sales_order_line;
            }
            return {};
        }
    },
    methods: {
        submitForApproval() {
            this.value.submitted = true;
            this.update();
        },
        submitApproved() {
            this.value.approved = true;
            this.update();
        },
        copyPricing(){
            this.$emit('copy', this.value)
            // this is more of a visual thing of saying
            // "hey, you can paste this somewhere now", and
            // pasting to the same place shouldn't do anything anyway
            this.copiedPricing = this.value
        },
        pastePricing(){
            const copied = this.copiedPricing

            let newPricing = {}
            _.forEach(this.value.countries, function(country, key) {
                newPricing[key] = copied.countries[key]
            })

            this.value.countries = newPricing
        },
        getGuidance(){
            const loading = this.$loading.show();
            const url = '/api/guidance'
            this.$http.get(url).then(response => {
                this.images = response.data
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        copySfPrice() {
            const euPrice = (this.value['unit_sf_uk_price'] / this.value['eur_fx_rate']).toFixed(2);
            this.value['unit_sf_fr_price'] = euPrice;
            this.value['unit_sf_es_price'] = euPrice;
            this.value['unit_sf_it_price'] = euPrice;
            this.value['unit_sf_de_price'] = euPrice;
            this.value['unit_sf_nl_price'] = euPrice;

            this.value['unit_sf_se_price'] = (this.value['unit_sf_uk_price'] / this.value['sek_fx_rate']).toFixed(2);
            this.value['unit_sf_pl_price'] = (this.value['unit_sf_uk_price'] / this.value['pln_fx_rate']).toFixed(2);
            this.value['unit_sf_au_price'] = (this.value['unit_sf_uk_price'] / this.value['aud_fx_rate']).toFixed(2);
            this.value['unit_sf_mx_price'] = (this.value['unit_sf_uk_price'] / this.value['mxn_fx_rate']).toFixed(2);
            this.value['unit_sf_us_price'] = (this.value['unit_sf_uk_price'] / this.value['usd_fx_rate']).toFixed(2);
            this.value['unit_sf_ca_price'] = (this.value['unit_sf_uk_price'] / this.value['cad_fx_rate']).toFixed(2);

        },
        save(event) {
            event.preventDefault();
            this.update()
        },
        update() {
            this.$http.put(`/api/pricing/${this.value.id}`, {...this.value, pricing_notes: this.notes}, {
                params: {
                    include: `product,product.media,sales_order_line,countries`
                }
            }).then(response => {
                // give a clue to anything watching as to what happened
                this.$emit('updated', {submitted: this.value.submitted, approved: this.value.approved});
                this.value = response.data;
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
            })
        },
        open(line, copiedPricing = null) {
            this.value = line;
            this.copiedPricing = copiedPricing;
            this.$http.get(`/api/pricing/${this.value.id}`, {
                params: {
                    include: `product,product.media,sales_order_line,countries`
                }
            }).then(response => {
                this.value = response.data
                this.$refs['modal'].show();
            }).catch(error => {
                console.log(error);
                this.$handleExceptionResponse(error, true)
            })
        },
        items(ctx) {
            return this.$paginatedFetch(
                `api/pricing/${this.value.id}/history`,
                this.pagination,
                ctx, {
                    include: 'approver'
                });
        },
        close() {
            this.$refs['modal'].hide();
        }
    },
    data() {
        return {
            value: {},
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'approved_at'
            },
            images: {},
            fields: [
                {key: 'approved_at', label: 'Approved At', sortable: true},
                {key: 'approver.name', label: 'Approved By', sortable: true},
                {key: 'unit_price_gbp', label: 'Unit Price'},
                {key: 'margin_pc', label: 'Margin'},
            ],
            copiedPricing: null
        }
    }
}
</script>

<style scoped>
::v-deep .modal-xlg {
    max-width: 1200px !important;
}
</style>
