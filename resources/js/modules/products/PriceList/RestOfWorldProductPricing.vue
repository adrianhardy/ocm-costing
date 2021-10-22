<template>
    <div>
        <b-form-group :label="currency" label-size="sm" class="mb-2">
            <b-input-group size="sm">
                <b-input-group-prepend class="w-30">
                    <b-input-group-text class="w-100">FX {{ currencySymbol }}</b-input-group-text>
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
        currencySymbol: String,
        marketplaceCommissionPc: Number
    },
    computed: {

        localCommission() {
          return this.value[this.priceField] * (this.marketplaceCommissionPc / 100);
        },

        calculatedMargin() {
            if (!this.value[this.fxField]) {
                return 0;
            }

            const fxRate = this.value[this.fxField];
            const commission = this.value[this.priceField] * (this.marketplaceCommissionPc / 100);
            const localPrice = (this.value[this.priceField]);
            const localLandedCost = (this.value.est_landed_cost_per_unit_gbp / fxRate);
            const localShipping = (this.value[this.shipField] / fxRate);

            const margin = localPrice - commission - localLandedCost - localShipping;

            return (margin * fxRate);
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

</style>
