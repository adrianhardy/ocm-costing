<template>
    <div>

        <pricing-form-input
            :top-label="country"
            label="VAT"
            editable
            v-model.number="value[vatField]"
            currency-symbol="%"
        />

        <b-form-group class="mb-2" v-show="!quickPrice">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">
                        <span>SF Price</span>
                        <a href="#" v-if="country === 'UK'" v-b-tooltip title="Translate SF Price to other currencies" @click.prevent="$emit('copy-sf-price')">
                            <b-icon-clipboard-check class="ml-1" />
                        </a>
                    </b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[sfPriceField]" class="border-success" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <pricing-form-input
            v-show="!quickPrice"
            label="SF VAT"
            :fixed="sfVat"
            :currency-symbol="currencySymbol"
        />

        <pricing-form-input
            label="FBA Price"
            editable
            v-model.number="value[priceField]"
            :currency-symbol="currencySymbol"
        />
        <pricing-form-input
            v-show="!quickPrice"
            label="FBA VAT"
            :fixed="fbaVat"
            :currency-symbol="currencySymbol"
        />
        <pricing-form-input
            v-show="!quickPrice"
            label="FBA Ship."
            editable
            v-model.number="value[shipField]"
            :currency-symbol="currencySymbol"
        />
        <pricing-form-input
            label="FBA Fee"
            :currency-symbol="currencySymbol"
            :amz-digital-tax-value="fbaDigitalTaxOnFbaFeeValue"
            v-model.number="value[feeField]"
            editable
        />
        <pricing-form-input
            v-show="!quickPrice"
            label="FBA Ref' Fee"
            :currency-symbol="currencySymbol"
            :fixed="fbaReferralFeeValue"
            :amz-digital-tax-value="fbaDigitalTaxOnReferralFeeValue"
        />
        <pricing-form-input
            v-show="!quickPrice"
            label="SF Ref' Fee"
            :currency-symbol="currencySymbol"
            :fixed="sfReferralFeeValue"
            :amz-digital-tax-value="sfDigitalTaxOnReferralFeeValue"
        />

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
            :landed-cost-per-unit-gbp="landedCostPerUnitGbp"
            :quick-price="quickPrice"
            :amz-digital-tax-on-sf-referral-fee="sfDigitalTaxOnReferralFeeValue"
            :amz-digital-tax-on-fba-referral-fee="fbaDigitalTaxOnReferralFeeValue"
            :amz-digital-tax-on-fba-fee="fbaDigitalTaxOnFbaFeeValue"
        />
    </div>
</template>

<script>
import PricingProfitSummary from "./PricingProfitSummary";
import PricingFormInput from "./PricingFormInput.vue";

export default {
    name: "EuProductPricing",
    components: {PricingFormInput, PricingProfitSummary},
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
        fbaReferralFee: Number,
        landedCostPerUnitGbp: [Number, String],
        quickPrice: {
            type: Boolean,
            default: false
        },
        amazonDigitalTaxPc: Number
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
        fbaDigitalTaxOnReferralFeeValue() {
            return (this.fbaReferralFeeValue * (this.amazonDigitalTaxPc / 100)).toFixed(2);
        },
        fbaDigitalTaxOnFbaFeeValue() {
            return (this.value[this.feeField] * (this.amazonDigitalTaxPc / 100)).toFixed(2);
        },
        sfDigitalTaxOnReferralFeeValue() {
            return (this.sfReferralFeeValue * (this.amazonDigitalTaxPc / 100)).toFixed(2);
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
