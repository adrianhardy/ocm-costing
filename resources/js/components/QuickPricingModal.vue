<template>
    <b-modal :size="size" centered ref="modal" title="Quick Price" ok-only>
        <b-tabs v-model="tab">
            <b-tab title="Pricing">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <b-form-group label="Landed Cost">
                            <b-form-input v-model="landedCost" type="number" />
                        </b-form-group>
                    </div>
                    <div class="col-12 col-md-6">
                        <b-form-group label="Referral Fee">
                            <b-form-input disabled v-model="marketplaceCommissionPc" type="number" />
                        </b-form-group>
                    </div>
                </div>
                <eu-product-pricing
                    country=""
                    v-model="pricing"
                    :marketplace-commission-pc="marketplaceCommissionPc"
                    :landed-cost-per-unit-gbp="landedCost"
                    quick-price
                    fee-field="unit_fba_fee"
                    sf-price-field="unit_sf_price"
                    ship-field="unit_fba_shipping"
                    price-field="unit_fba_price"
                    vat-field="vat_rate"
                    :fx-rate="1"
                />
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
    </b-modal>
</template>

<script>
import EuProductPricing from "../modules/products/PriceList/EuProductPricing";

export default {
    name: "QuickPricingModal",
    components: {
        EuProductPricing
    },
    mounted() {
        this.getGuidance();
    },
    computed: {
        size(){
            if(this.tab > 0) {
                return 'lg'
            } else {
                return 'md'
            }
        }
    },
    methods: {
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
        show(){
            this.$refs['modal'].show();
        }
    },
    data() {
        return {
            tab: 0,
            pricing: {},
            marketplaceCommissionPc: 15,
            landedCost: 0,
            images: {},
        }
    }

}
</script>

<style scoped>

</style>