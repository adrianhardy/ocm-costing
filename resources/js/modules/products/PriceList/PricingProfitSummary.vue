<template>
    <b-form-group size="sm" class="mb-2">
        <div class="d-flex justify-content-around">
            <div class="small">FBA <span v-html="presentProfitInGbp?'&pound;':currencySymbol"></span></div>
            <div class="small">FBA %</div>
            <div class="small">SF <span v-html="presentProfitInGbp?'&pound;':currencySymbol"></span></div>
            <div class="small">SF %</div>
        </div>
        <b-input-group size="sm">
            <div class="form-control bg-light">
                {{ fbaProfit }}
            </div>
            <div class="form-control bg-light">
                {{ fbaMargin }}
            </div>
            <div class="form-control bg-light">
                {{ sfProfit }}
            </div>
            <div class="form-control bg-light">
                {{ sfMargin }}
            </div>
        </b-input-group>
    </b-form-group>
</template>
<script>
export default {
    name: 'pricing-profit-summary',
    props: {
        fxRate: [Number, String],
        landedCostPerUnitGbp: [Number, String],
        fbaVat: [Number, String],
        sfVat: [Number, String],
        fee: [Number, String],
        fbaShipping: [Number, String],
        fbaPrice: [Number, String],
        sfPrice: [Number, String],
        fbaReferralFee: [Number, String],
        sfReferralFee: [Number, String],
        currencySymbol: String,
        presentProfitInGbp: Boolean
    },
    computed: {
        sfProfit() {
            if (this.sfPrice <= 0) {
                return 0;
            }

            console.log([
                ["sfPrice", this.sfPrice],
                ["landedCost GBP", this.landedCostPerUnitGbp],
                [" - landedCost CUR", this.landedCostPerUnitGbp * this.fxRate],
                ["fx rate", this.fxRate],
                [" - sf VAT", this.sfVat],
                [" - referral fee CUR", this.sfReferralFee]
            ]);

            const profit = (this.sfPrice -
                (this.landedCostPerUnitGbp * this.fxRate) -
                this.sfVat -
                this.sfReferralFee
            );
            return (profit / (this.presentProfitInGbp?this.fxRate:1)).toFixed(2);
        },
        sfMargin() {
            if (this.sfPrice <= 0) {
                return 0;
            }
            return (100*(this.sfProfit / this.sfPrice)).toFixed(2);
        },
        fbaMargin() {
            if (this.fbaPrice <= 0) {
                return 0;
            }
            return (100*(this.fbaProfit / this.fbaPrice)).toFixed(2);
        },
        fbaProfit() {
            if (this.sfPrice <= 0) {
                return 0;
            }

            console.log([
                ["fbaPrice", this.fbaPrice],
                ["landedCost GBP", this.landedCostPerUnitGbp],
                [" - landedCost CUR", this.landedCostPerUnitGbp * this.fxRate],
                ["fx rate", this.fxRate],
                [" - fba VAT", this.fbaVat],
                [" - referral fee", this.fbaReferralFee],
                [" - fba shipping", this.fbaShipping],
                ["fee CUR", this.fee],
            ]);

            const profit = this.fbaPrice -
                (this.landedCostPerUnitGbp * this.fxRate) -
                this.fbaVat -
                this.fbaReferralFee -
                this.fbaShipping -
                this.fee;

            return (profit / (this.presentProfitInGbp?this.fxRate:1)).toFixed(2);
        }

    }
}
</script>
<style scoped>
.form-control.bg-light {
    background-color: #ddd !important;
}
</style>
