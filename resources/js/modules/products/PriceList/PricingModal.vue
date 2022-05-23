<template>
    <b-modal id="pricing"
             :title="product.name"
             ref="modal"
             ok-title="Save"
             @ok="save($event)"
             cancel-title="Close"
             ok-title-html="Update"
             centered
             size="xl"
    >
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
                    <table class="table table-sm small">
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
                        <b-tab title="UK/EU Pricing">
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
                                        <eu-product-pricing v-model="value" country="UK"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_uk_fba_fee"
                                                            sf-price-field="unit_sf_uk_price"
                                                            ship-field="unit_shipping_gbp" price-field="unit_price_gbp"
                                                            vat-field="uk_vat"
                                                            :fx-rate="1"
                                                            @copy-sf-price="copySfPrice()"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="FR"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_fr_price"
                                                            fee-field="unit_fr_fba_fee"
                                                            ship-field="unit_fr_shipping" price-field="unit_fr_price"
                                                            vat-field="fr_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="DE"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_de_price"
                                                            fee-field="unit_de_fba_fee"
                                                            ship-field="unit_de_shipping" price-field="unit_de_price"
                                                            vat-field="de_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="IT"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_it_fba_fee"
                                                            sf-price-field="unit_sf_it_price"
                                                            ship-field="unit_it_shipping" price-field="unit_it_price"
                                                            vat-field="it_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="ES"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            fee-field="unit_es_fba_fee"
                                                            sf-price-field="unit_sf_es_price"
                                                            ship-field="unit_es_shipping" price-field="unit_es_price"
                                                            vat-field="es_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <eu-product-pricing v-model="value" country="NL"
                                                            :marketplace-commission-pc="value.marketplace_commission_pc"
                                                            sf-price-field="unit_sf_nl_price"
                                                            fee-field="unit_nl_fba_fee"
                                                            ship-field="unit_nl_shipping" price-field="unit_nl_price"
                                                            vat-field="nl_vat" :fx-rate="value.eur_fx_rate"/>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <rest-of-world-product-pricing v-model="value" currency="SEK"
                                           :marketplace-commission-pc="value.marketplace_commission_pc"
                                           sf-price-field="unit_sf_se_price"
                                           ship-field="unit_se_shipping"
                                           fee-field="unit_se_fba_fee"
                                           price-field="unit_se_price"
                                           fx-field="current_sek_fx_rate"
                                           vat-field="se_vat"
                                        />
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <rest-of-world-product-pricing v-model="value" currency="PLN"
                                                                       :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                       sf-price-field="unit_sf_pln_price"
                                                                       ship-field="unit_pln_shipping"
                                                                       fee-field="unit_pln_fba_fee"
                                                                       price-field="unit_pln_price"
                                                                       fx-field="current_pln_fx_rate"
                                                                       vat-field="pl_vat"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div v-if="value.product">
                                <h5>Pricing Notes</h5>
                                <b-textarea
                                    class="mb-3"
                                    v-model="value.product.pricing_notes"
                                />
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
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="USD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_usd_price"
                                                                   ship-field="unit_usd_shipping"
                                                                   fee-field="unit_usd_fba_fee"
                                                                   price-field="unit_usd_price" fx-field="current_usd_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="CAD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_cad_price"
                                                                   fee-field="unit_cad_fba_fee"
                                                                   ship-field="unit_cad_shipping"
                                                                   price-field="unit_cad_price" fx-field="current_cad_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="MXN"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_mxn_price"
                                                                   fee-field="unit_mxn_fba_fee"
                                                                   ship-field="unit_mxn_shipping"
                                                                   price-field="unit_mxn_price" fx-field="current_mxn_fx_rate"/>
                                </div>
                                <div class="col-12 col-md-3">
                                    <rest-of-world-product-pricing v-model="value" currency="AUD"
                                                                   :marketplace-commission-pc="value.marketplace_commission_pc"
                                                                   sf-price-field="unit_sf_aud_price"
                                                                   ship-field="unit_aud_shipping"
                                                                   fee-field="unit_aud_fba_fee"
                                                                   price-field="unit_aud_price" fx-field="current_aud_fx_rate"/>
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
                                <small-light />
                            </div>
                        </b-tab>
                        <b-tab title="Local FBA">
                            <div style="max-height: 650px; overflow: auto">
                                <local-f-b-a />
                            </div>
                        </b-tab>
                        <b-tab title="EFN FBA">
                            <div style="max-height: 650px; overflow: auto">
                                <e-f-n-f-b-a />
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
import EuOfWorldProductPricing from "./EuProductPricing";
import EuProductPricing from "./EuProductPricing";
import SmallLight from "./AmazonShippingRates/SmallLight";
import LocalFBA from "./AmazonShippingRates/LocalFBA";
import EFNFBA from "./AmazonShippingRates/EFNFBA";

export default {
    name: "PricingModal",
    components: {EFNFBA, LocalFBA, SmallLight, EuProductPricing, EuOfWorldProductPricing, RestOfWorldProductPricing},
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
        copySfPrice() {
            const euPrice = (this.value['unit_sf_uk_price'] / this.value['eur_fx_rate']).toFixed(2);
            this.value['unit_sf_fr_price'] = euPrice;
            this.value['unit_sf_es_price'] = euPrice;
            this.value['unit_sf_it_price'] = euPrice;
            this.value['unit_sf_de_price'] = euPrice;
            this.value['unit_sf_nl_price'] = euPrice;

            this.value['unit_sf_se_price'] = (this.value['unit_sf_uk_price'] / this.value['sek_fx_rate']).toFixed(2);
            this.value['unit_sf_pln_price'] = (this.value['unit_sf_uk_price'] / this.value['pln_fx_rate']).toFixed(2);
            this.value['unit_sf_aud_price'] = (this.value['unit_sf_uk_price'] / this.value['aud_fx_rate']).toFixed(2);
            this.value['unit_sf_mxn_price'] = (this.value['unit_sf_uk_price'] / this.value['mxn_fx_rate']).toFixed(2);
            this.value['unit_sf_usd_price'] = (this.value['unit_sf_uk_price'] / this.value['usd_fx_rate']).toFixed(2);
            this.value['unit_sf_cad_price'] = (this.value['unit_sf_uk_price'] / this.value['cad_fx_rate']).toFixed(2);

        },
        save(event) {
            event.preventDefault();
            this.update()
        },
        update() {
            this.$http.put(`/api/pricing/${this.value.id}`, {...this.value, pricing_notes: this.notes}, {
                params: {
                    include: `product,product.media,sales_order_line`
                }
            }).then(response => {
                this.value = response.data;
                this.$emit('updated');
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
            })
        },
        open(line) {
            this.value = line;
            this.$http.get(`/api/pricing/${this.value.id}`, {
                params: {
                    include: `product,product.media,sales_order_line`
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
            fields: [
                {key: 'approved_at', label: 'Approved At', sortable: true},
                {key: 'approver.name', label: 'Approved By', sortable: true},
                {key: 'unit_price_gbp', label: 'Unit Price'},
                {key: 'margin_pc', label: 'Margin'},
            ]
        }
    }
}
</script>

<style scoped>
::v-deep .modal-xlg {
    max-width: 1200px !important;
}
</style>
