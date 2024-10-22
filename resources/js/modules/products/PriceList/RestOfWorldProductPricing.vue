<template>
    <div>
        <b-form-group :label="`${country.iso_2_code} (${currency})`" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Rate</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[fxField]" class="border-success" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100">:£1</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group class="mb-2" v-if="vatField">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">VAT</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[vatField]" class="border-success" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100">%</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">SF Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[sfPriceField]" class="border-success" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <pricing-form-input
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
            label="FBA VAT"
            :fixed="fbaVat"
            :currency-symbol="currencySymbol"
        />

        <pricing-form-input
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
            label="Ref' fee"
            :fixed="fbaReferralFeeValue"
            :currency-symbol="currencySymbol"
            :amz-digital-tax-value="fbaDigitalTaxOnReferralFeeValue"
        />
        <pricing-form-input
            v-if="showReferralTaxField"
            label="Ref' Tax"
            :fixed="referralTaxValue"
            :currency-symbol="currencySymbol"
        />

        <pricing-form-input
            label="SF Ref' Fee"
            :fixed="sfReferralFeeValue"
            :currency-symbol="currencySymbol"
            :amz-digital-tax-value="sfDigitalTaxOnReferralFeeValue"
        />

        <pricing-profit-summary
            :fba-price="value[priceField]"
            :fba-vat="euPricing?fbaVat:0"
            :fba-shipping="value[shipField]"
            :fba-referral-fee="fbaReferralFeeValue"
            :sf-referral-fee="sfReferralFeeValue"
            :fee="value[feeField]"
            :sf-price="value[sfPriceField]"
            :sf-vat="euPricing?sfVat:0"
            :fx-rate="value[fxField]"
            :currency-symbol="currencySymbol"
            :landed-cost-per-unit-gbp="landedCostPerUnitGbp"
            :amz-digital-tax-on-sf-referral-fee="sfDigitalTaxOnReferralFeeValue"
            :amz-digital-tax-on-fba-referral-fee="fbaDigitalTaxOnReferralFeeValue"
            :amz-digital-tax-on-fba-fee="fbaDigitalTaxOnFbaFeeValue"
            present-profit-in-gbp
        />

    </div>
</template>

<script>
import getSymbolFromCurrency from 'currency-symbol-map';
import PricingProfitSummary from "./PricingProfitSummary";
import PricingFormInput from "./PricingFormInput.vue";

export default {
    name: "RestOfWorldProductPricing",
    components: {PricingFormInput, PricingProfitSummary},
    props: {
        value: Object,
        shipField: String,
        sfPriceField: String,
        fxField: String,
        priceField: String,
        feeField: String,
        currency: String,
        country: Object,
        vatField: String,
        marketplaceCommissionPc: Number,
        landedCostPerUnitGbp: Number,
        euPricing: {
            type: Boolean,
            default: false
        },
        amazonDigitalTaxPc: Number
    },
    computed: {
        sfVat() {
            if (this.country.is_row) {
                return 0.00;
            }
            return (this.value[this.sfPriceField] * (this.value[this.vatField] / 100)).toFixed(2);
        },
        fbaVat() {
            if (this.country.is_row) {
                return 0.00;
            }
            return (this.value[this.priceField] * (this.value[this.vatField] / 100)).toFixed(2);
        },
        showReferralTaxField() {
            return this.country.is_row;
        },
        referralTaxValue() {
            // if it's a EU, non EURO country
            if (!this.country.is_row) {
                return '0.00';
            }
            return (this.fbaReferralFeeValue * 0.2).toFixed(2);
        },
        fbaReferralFeeValue() {
            const value = this.value[this.priceField] * (this.marketplaceCommissionPc/100);
            return value.toFixed(2);
        },
        sfReferralFeeValue() {
            const value = this.value[this.sfPriceField] * (this.marketplaceCommissionPc/100);
            return value.toFixed(2);
        },
        fbaDigitalTaxOnReferralFeeValue() {
            return (this.fbaReferralFeeValue * (2.0 / 100)).toFixed(2);
        },
        fbaDigitalTaxOnFbaFeeValue() {
            return (this.value[this.feeField] * (2.0 / 100)).toFixed(2);
        },
        sfDigitalTaxOnReferralFeeValue() {
            return (this.sfReferralFeeValue * (2.0 / 100)).toFixed(2);
        },
        currencySymbol() {
            return getSymbolFromCurrency(this.currency);
        },
        localCommission() {
          return this.value[this.priceField] * (this.marketplaceCommissionPc / 100);
        }
    },
    methods: {
        showMyWorking() {
            const fxRate = this.value[this.fxField];
            this.$bvModal.msgBoxOk(
                `Commission: ${this.value[this.priceField] * (this.marketplaceCommissionPc / 100)}, Local Shipping ${(this.value[this.shipField] / fxRate)}, Local Landed Cost ${this.value.est_landed_cost_per_unit_gbp / fxRate}`,
                {
                  title: 'Calculation',
                });
        }
    }
}
</script>

<style scoped>
    .form-control.bg-light {
        background-color: #ddd !important;
    }
</style>
