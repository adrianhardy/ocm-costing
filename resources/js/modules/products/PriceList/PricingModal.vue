<template>
    <b-modal id="pricing" :title="product.name" ref="modal" ok-title="Save" @ok="save($event)" cancel-title="Close"
             ok-title-html="Update" centered size="xl">
        <div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <h5>Carton</h5>
                    <table class="table">
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
                    <table class="table">
                        <tr>
                            <th class="w-50">Weight (kg)</th>
                            <td>{{ line.unit_weight_kg }}</td>
                        </tr>
                        <tr>
                            <th>Dimensions (LxWxH)</th>
                            <td>{{ line.unit_length_cm }} x
                                {{ line.unit_width_cm }} x
                                {{ line.unit_height_cm }} cm
                            </td>
                        </tr>
                    </table>
                    <div>
                    </div>
                    <h5>Image</h5>
                    <div class="p-1">
                        <b-img fluid style="max-height: 200px" class="img-thumnail"
                               :src="value.product | product_image_url"/>
                    </div>
                </div>
                <div class="col-12 col-md-9">
                    <b-tabs content-class="pt-3">
                        <b-tab title="Pricing">
                            <h5>Pricing</h5>
                            <div class="row">
                                <div class="col-6">
                                    <b-form-group label="Our Unit Price" label-cols="6" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input v-model="value.unit_price_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                    <b-form-group label="Est. Landed Cost" label-cols="6" label-size="sm">
                                        <b-input-group prepend="£" size="sm">
                                            <b-form-input readonly v-model="value.est_landed_cost_per_unit_gbp"/>
                                        </b-input-group>
                                    </b-form-group>
                                </div>
                                <div class="col-6">
                                  <b-form-group label="Marketplace Comm." label-cols="6" label-size="sm" >
                                    <b-input-group prepend="%" size="sm">
                                      <b-form-input v-model="value.marketplace_commission_pc"/>
                                      <b-input-group-append class="w-40">
                                        <b-input-group-text class="w-100">
                                          {{ calculatedMarketplaceComm | cash_value }}
                                        </b-input-group-text>
                                      </b-input-group-append>
                                    </b-input-group>
                                  </b-form-group>
                                  <b-form-group label="EUR FX Rate" label-cols="6" label-size="sm" >
                                    <b-input-group size="sm">
                                      <b-form-input v-model="value.eur_fx_rate"/>
                                      <b-input-group-append class="w-40">
                                        <b-input-group-text class="w-100">
                                          to 1 GBP
                                        </b-input-group-text>
                                      </b-input-group-append>
                                    </b-input-group>
                                  </b-form-group>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-2">
                                  <eu-product-pricing v-model="value" country="UK" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_shipping_gbp" price-field="unit_price_gbp" vat-field="uk_vat" :fx-rate="1"  />
                                </div>
                                <div class="col-12 col-md-2">
                                    <eu-product-pricing v-model="value" country="FR" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_fr_shipping" price-field="unit_fr_price" vat-field="fr_vat" :fx-rate="value.eur_fx_rate"  />
                                </div>
                                <div class="col-12 col-md-2">
                                    <eu-product-pricing v-model="value" country="DE" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_de_shipping" price-field="unit_de_price" vat-field="de_vat" :fx-rate="value.eur_fx_rate"  />
                                </div>
                                <div class="col-12 col-md-2">
                                    <eu-product-pricing v-model="value" country="IT" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_it_shipping" price-field="unit_it_price" vat-field="it_vat" :fx-rate="value.eur_fx_rate"  />
                                </div>
                                <div class="col-12 col-md-2">
                                    <eu-product-pricing v-model="value" country="ES" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_es_shipping" price-field="unit_es_price" vat-field="es_vat" :fx-rate="value.eur_fx_rate"  />
                                </div>
                                <div class="col-12 col-md-2">
                                    <eu-product-pricing v-model="value" country="NL" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_nl_shipping" price-field="unit_nl_price" vat-field="nl_vat" :fx-rate="value.eur_fx_rate"  />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <rest-of-world-product-pricing v-model="value" currency="USD" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_usd_shipping" price-field="unit_usd_price" fx-field="usd_fx_rate" />
                                </div>
                                <div class="col-12 col-md-4">
                                    <rest-of-world-product-pricing v-model="value" currency="CAD" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_cad_shipping" price-field="unit_cad_price" fx-field="cad_fx_rate" />
                                </div>
                                <div class="col-12 col-md-4">
                                    <rest-of-world-product-pricing v-model="value" currency="MXN" :marketplace-commission-pc="value.marketplace_commission_pc" ship-field="unit_mxn_shipping" price-field="unit_mxn_price" fx-field="mxn_fx_rate" />
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
                        <b-tab title="Amazon shipping rates">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">UK (£)</th>
                                    <th scope="col">DE/PL/CZ(&#128;)</th>
                                    <th scope="col">FR (&#128;)</th>
                                    <th scope="col">IT (&#128;)</th>
                                    <th scope="col">ES (&#128;) </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th colspan="6">Small Envelope, Dimensions &#8804; 20 x 15 x 1cm</th>
                                </tr>
                                <tr>
                                    <th scope="row">&#8804; 80g</th>
                                    <td>£0.60</td>
                                    <td>&#128;1</td>
                                    <td>&#128;1.40</td>
                                    <td>&#128;1.45</td>
                                    <td>&#128;1.35</td>
                                </tr>
                                <tr>
                                    <td colspan="6">Standard Envelope, Dimensions &#8804; 33 x 23 x 2.5cm </td>
                                </tr>
                                <tr>
                                    <th scope="row">&#8804; 60g</th>
                                    <td>£0.80</td>
                                    <td>&#128;1.15</td>
                                    <td>&#128;1.60</td>
                                    <td>&#128;1.60</td>
                                    <td>&#128;1.52</td>
                                </tr>
                                <tr>
                                    <th scope="row">&#8804; 210g</th>
                                    <td>£0.85</td>
                                    <td>&#128;1.32</td>
                                    <td>&#128;2.10</td>
                                    <td>&#128;1.85</td>
                                    <td>&#128;1.65</td>
                                </tr>
                                <tr>
                                    <td colspan="6">Standard Envelope, Dimensions &#8804; 33 x 23 x 5cm </td>
                                </tr>
                                <tr>
                                    <th scope="row">&#8804; 225g</th>
                                    <td>£1.42</td>
                                    <td>&#128;1.95</td>
                                    <td>&#128;3.45</td>
                                    <td>&#128;2.60</td>
                                    <td>&#128;2.18</td>
                                </tr>
                                </tbody>
                            </table>
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
export default {
    name: "PricingModal",
    components: {EuProductPricing, EuOfWorldProductPricing, RestOfWorldProductPricing},
    computed: {
        calculatedMarketplaceComm() {
            return this.value.unit_price_gbp * (this.value.marketplace_commission_pc/100);
        },
        calculatedProfit() {
            return this.value.unit_price_gbp - this.vatValue - this.calculatedMarketplaceComm - this.value.est_landed_cost_per_unit_gbp - this.value.unit_shipping_gbp
        },
        calculatedMarginPc() {
            return this.calculatedProfit / this.value.unit_price_gbp;
        },
        product() {
            if (this.value.product) {
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
        save(event) {
            event.preventDefault();
            this.update()
        },
        update() {
            this.$http.put(`/api/pricing/${this.value.id}`, {...this.value}, {
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
            this.$http.get(`api/pricing/${this.value.id}`, {
                params: {
                    include: `product,product.media,sales_order_line`
                }
            }).then(response => {
                this.value = response.data
                this.$refs['modal'].show();
            }).catch(error => {
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
