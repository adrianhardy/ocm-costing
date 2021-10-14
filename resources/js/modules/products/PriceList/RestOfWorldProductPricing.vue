<template>
    <div>
        <b-form-group :label="currency" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-30">
                    <b-input-group-text class="w-100">FX Rate</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[fxField]" size="sm" />
                <b-input-group-append class="w-30">
                    <b-input-group-text class="w-100">to 1 GBP </b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-30">
                    <b-input-group-text class="w-100">Price</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[priceField]" size="sm" />
                <b-input-group-append class="w-30">
                    <b-input-group-text class="w-100">{{ currency }}</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-30">
                    <b-input-group-text class="w-100">Ship</b-input-group-text>
                </b-input-group-prepend>
                <b-form-input v-model.number="value[shipField]" size="sm" />
                <b-input-group-append class="w-30">
                    <b-input-group-text class="w-100">GBP</b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
        <b-form-group size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-30">
                    <b-input-group-text class="w-100">Margin</b-input-group-text>
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
    name: "RestOfWorldProductPricing",
    props: {
        value: Object,
        shipField: String,
        fxField: String,
        priceField: String,
        currency: String,
        currencySymbol: String
    },
    computed: {
        calculatedMargin() {
            if (!this.value[this.fxField]) {
                return 0;
            }

            return (this.value[this.priceField] / this.value[this.fxField]) -
                this.value.est_landed_cost_per_unit_gbp -
                this.value[this.shipField];
        }
    }
}
</script>

<style scoped>

</style>
