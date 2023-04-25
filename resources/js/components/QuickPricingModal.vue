<template>
    <b-modal centered ref="modal" title="Quick Price" ok-only>
        <div class="row">
            <div class="col-12 col-md-6">
                <b-form-group label="Landed Cost">
                    <b-form-input v-model="landedCost" type="number" />
                </b-form-group>
            </div>
            <div class="col-12 col-md-6">
                <b-form-group label="Referral Fee">
                    <b-form-input v-model="marketplaceCommissionPc" type="number" />
                </b-form-group>
            </div>
        </div>
        <eu-product-pricing
            country=""
            v-model="pricing"
            :marketplace-commission-pc="marketplaceCommissionPc"
            :landed-cost-per-unit-gbp="landedCost"
            fee-field="unit_fba_fee"
            sf-price-field="unit_sf_price"
            ship-field="unit_fba_shipping"
            price-field="unit_fba_price"
            vat-field="vat_rate"
            :fx-rate="1"
        />
    </b-modal>
</template>

<script>
import EuProductPricing from "../modules/products/PriceList/EuProductPricing";

export default {
    name: "QuickPricingModal",
    components: {
        EuProductPricing
    },
    methods: {
        show(){
            this.$refs['modal'].show();
        }
    },
    data() {
        return {
            pricing: {},
            marketplaceCommissionPc: 10,
            landedCost: 0,
        }
    }

}
</script>

<style scoped>

</style>