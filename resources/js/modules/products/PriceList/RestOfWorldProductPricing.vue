<template>
    <div>
        <b-form-group :label="`${country.iso_2_code} (${currency})`" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">Rate</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[fxField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100">:£1</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group class="mb-2" v-if="vatField">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">VAT</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[vatField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100">%</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">SF Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[sfPriceField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>


        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">SF VAT</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ sfVat }}</div>
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>


        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">FBA Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[priceField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">FBA VAT</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ fbaVat }}</div>
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">FBA Ship.</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[shipField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100">&pound;</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">FBA Fee</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[feeField]" size="sm" />
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100">&pound;</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">Ref' Fee</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ fbaReferralFeeValue }}</div>
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>

            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2" v-if="showReferralTaxField">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">Ref' Tax</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ referralTaxValue }}</div>
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-40">
                    <b-input-group-text class="w-100">SF Ref' Fee</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ sfReferralFeeValue }}</div>
                <b-input-group-append class="w-20">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <pricing-profit-summary
            :fba-price="value[priceField]"
            :fba-vat="0"
            :fba-shipping="value[shipField]"
            :fba-referral-fee="fbaReferralFeeValue"
            :sf-referral-fee="sfReferralFeeValue"
            :fee="value[feeField]"
            :sf-price="value[sfPriceField]"
            :sf-vat="0"
            :fx-rate="value[fxField]"
            :currency-symbol="currencySymbol"
            :landed-cost-per-unit-gbp="landedCostPerUnitGbp"
            present-profit-in-gbp
        />

    </div>
</template>

<script>
import getSymbolFromCurrency from 'currency-symbol-map';
import PricingProfitSummary from "./PricingProfitSummary";

export default {
    name: "RestOfWorldProductPricing",
    components: {PricingProfitSummary},
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
        landedCostPerUnitGbp: Number
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
            const value = this.value[this.priceField] * (1 / this.marketplaceCommissionPc);
            return value.toFixed(2);
        },
        sfReferralFeeValue() {
            const value = this.value[this.sfPriceField] * (1 / this.marketplaceCommissionPc);
            return value.toFixed(2);
        },
        currencySymbol() {
            return getSymbolFromCurrency(this.currency);
        },
        localCommission() {
          return this.value[this.priceField] * (this.marketplaceCommissionPc / 100);
        },

        calculatedMargin() {
            if (!this.value[this.fxField]) {
                return 0;
            }

            const fxRate = this.value[this.fxField];

            const localMargin = (this.value[this.priceField]) -
                (this.value.est_landed_cost_per_unit_gbp * fxRate) -
                this.fbaReferralFeeValue -
                (this.value[this.feeField] * fxRate) -
                (this.value[this.shipField] * fxRate) -
                this.referralTaxValue

            return (localMargin * fxRate).toFixed(2);
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
