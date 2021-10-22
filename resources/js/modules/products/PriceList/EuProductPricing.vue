<template>
    <div>
        <b-form-group :label="country" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">VAT %</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[vatField]" size="sm" />
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[priceField]" size="sm" />
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">Ship</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model="value[shipField]" size="sm" />
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-50">
                    <b-input-group-text class="w-100">M'gn</b-input-group-text>
                </b-input-group-prepend>
                <div class="form-control">
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
        priceField: String,
        country: String,
        fxRate: Number,
        marketplaceCommissionPc: Number
    },
    computed: {
        calculatedMargin() {
            if (!this.fxRate) {
                return 0;
            }

            const fxRate = this.fxRate;
            const commission = this.value[this.priceField] * (this.marketplaceCommissionPc / 100);
            const vat = this.value[this.priceField] * (this.value[this.vatField] / 100);
            const margin = (this.value[this.priceField]) -
                (this.value.est_landed_cost_per_unit_gbp / fxRate) -
                vat -
                commission -
                (this.value[this.shipField] / fxRate) ;

            return (margin * fxRate).toFixed(2);
        }
    }
}
</script>

<style scoped>

</style>
