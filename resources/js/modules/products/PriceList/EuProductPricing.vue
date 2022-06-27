<template>
    <div>
        <b-form-group :label="country" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">VAT</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[vatField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100">%</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">
                        <span>SF Price</span>
                        <a href="#" v-if="country === 'UK'" v-b-tooltip title="Translate SF Price to other currencies" @click.prevent="$emit('copy-sf-price')">
                            <b-icon-clipboard-check class="ml-1" />
                        </a>
                    </b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[sfPriceField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">SF VAT</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ sfVat }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>


        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">FBA Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[priceField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">FBA VAT</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ fbaVat }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">FBA Ship.</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[shipField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100">&pound;</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">FBA Fee</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[feeField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100">&pound;</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">FBA Ref' Fee</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ fbaReferralFeeValue }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">SF Ref' Fee</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ sfReferralFeeValue }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <pricing-profit-summary
            :fba-price="value[priceField]"
            :fba-vat="fbaVat"
            :fba-shipping="value[shipField]"
            :fba-referral-fee="fbaReferralFeeValue"
            :sf-referral-fee="sfReferralFeeValue"
            :fee="value[feeField]"
            :sf-price="value[sfPriceField]"
            :sf-vat="sfVat"
            :fx-rate="fxRate"
            :currency-symbol="currencySymbol"
            :landed-cost-per-unit-gbp="value.est_landed_cost_per_unit_gbp"
        />
    </div>
</template>

<script>
import PricingProfitSummary from "./PricingProfitSummary";

export default {
    name: "EuProductPricing",
    components: {PricingProfitSummary},
    props: {
        value: Object,
        vatField: String,
        shipField: String,
        sfPriceField: String,
        priceField: String,
        country: String,
        feeField: String,
        fxRate: Number,
        marketplaceCommissionPc: Number,
        fbaReferralFee: Number
    },
    computed: {
        currencySymbol() {
            if (this.country.toLowerCase() === 'uk') {
                return '&pound;'
            }

            return '&euro;'
        },
        sfVat() {
            return (this.value[this.sfPriceField] * (this.value[this.vatField] / 100)).toFixed(2);
        },
        fbaVat() {
            return (this.value[this.priceField] * (this.value[this.vatField] / 100)).toFixed(2);
        },
        fbaReferralFeeValue() {
            const value = this.value[this.priceField] * (this.marketplaceCommissionPc/100);
            return value.toFixed(2);
        },
        sfReferralFeeValue() {
            const value = this.value[this.sfPriceField] * (this.marketplaceCommissionPc/100);
            return value.toFixed(2);
        }

    }
}
</script>

<style scoped>
.form-control.bg-light {
    background-color: #ddd !important;
}
</style>
