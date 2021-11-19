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
                    <b-input-group-text class="w-100">SF Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[sfPriceField]" size="sm" />
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
                <b-form-input v-model="value[priceField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Shipping</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[shipField]" size="sm" />
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
                <b-form-input v-model="value[feeField]" size="sm" />
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100">&pound;</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Ref' Fee</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ fbaReferralFeeValue }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>

        <b-form-group class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">VAT</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">{{ vatValue }}</div>
                <b-input-group-append class="w-15">
                    <b-input-group-text class="w-100" v-html="currencySymbol" />
                </b-input-group-append>
            </b-input-group>
        </b-form-group>


        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Profit</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control bg-light">
                    {{ calculatedMargin | cash_value }}
                </div>
            </b-input-group>
        </b-form-group>
    </div>
</template>

<script>
export default {
    name: "EuProductPricing",
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
        fbaReferralFeeValue() {
            const value = this.value[this.priceField] * (1 / this.marketplaceCommissionPc);
            return value.toFixed(2);
        },
        vatValue() {
            const value = this.value[this.priceField] * (1 / this.value[this.vatField]);
            return value.toFixed(2);
        },
        calculatedMargin() {
            if (!this.fxRate) {
                return 0;
            }

            const localMargin = (this.value[this.priceField]) -
                (this.value.est_landed_cost_per_unit_gbp / this.fxRate) -
                this.vatValue -
                (this.value[this.feeField] / this.fxRate) -
                this.fbaReferralFeeValue -
                (this.value[this.shipField] / this.fxRate) ;

            return (localMargin * this.fxRate).toFixed(2);
        }
    }
}
</script>

<style scoped>
.form-control.bg-light {
    background-color: #ddd !important;
}
</style>
