<template>
    <b-modal ref="modal" title="Quick Cost" ok-only>
        <div class="row">
            <div class="col-12 col-sm-6">
                <h5>Costs and Currency</h5>
                <b-form-group label="Unit Cost">
                    <b-input-group prepend="$">
                        <b-form-input v-model="unitPrice" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Exchange Rate">
                    <b-input-group prepend="$">
                        <b-form-input v-model="exchangeRate" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Shipping Cost / Cu.ft">
                    <b-input-group prepend="$">
                        <b-form-input v-model="shipCostPerUnit" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Marketplace Commission Rate">
                    <b-input-group prepend="%">
                        <b-form-input v-model="commissionRate" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Import Duty">
                    <b-input-group prepend="%">
                        <b-form-input v-model="importDuty" />
                    </b-input-group>
                </b-form-group>
            </div>
            <div class="col-12 col-sm-6">
                <h5>Carton</h5>
                <b-form-group label="Units per Carton">
                    <b-form-input v-model="unitsPerCase" />
                </b-form-group>
                <b-form-group label="Carton Width">
                    <b-input-group prepend="cm">
                        <b-form-input v-model="width" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Carton Length">
                    <b-input-group prepend="cm">
                        <b-form-input v-model="length" />
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Carton Height">
                    <b-input-group prepend="cm">
                        <b-form-input v-model="height" />
                    </b-input-group>
                </b-form-group>
            </div>
        </div>
        <h5>Landed Cost Per Unit</h5>
        <div class="font-weight-bold">&pound;{{ landedCostPerUnit }}</div>
    </b-modal>
</template>

<script>
export default {
    props: {defaultFXRate: Number, defaultImportDuty: Number, defaultCommissionRate: Number},
    name: "QuickCostModal",
    computed: {
        landedCostPerUnit() {
            const commissionUplift = 1 + (this.commissionRate/100);
            const importDutyUplift = 1 + (this.importDuty/100);

            const importCost = (this.unitPrice * importDutyUplift);
            const gbpCost = (importCost / this.exchangeRate);
            const costToShip = gbpCost + (this.caseVolumeCubicFt * this.shipCostPerUnit) / (this.unitsPerCase);

            console.log(this.caseVolumeCubicFt, this.shipCostPerUnit);

            const costAfterCommission = costToShip * commissionUplift;

            if (isNaN(costToShip)) {
                return (0).toFixed(2);
            }

            return costAfterCommission.toFixed(2);
        },
        caseVolumeCubicFt() {
            const volume = ((this.width / 100) * (this.height/100) * (this.length/ 100));
            if (this.caseSystem === 'imperial') {
                return volume.toFixed(3);
            }
            return (volume * 35.3147).toFixed(3);
        },
        caseVolumeCubicM() {
            const volume = (this.width * this.height * this.length);
            if (this.caseSystem === 'imperial') {
                return (volume / 35.3147).toFixed(3);
            }
            return volume.toFixed(3);
        },
        dimensionUnit() {
            return this.caseSystem === 'imperial' ? 'ft' : 'm';
        },
        cubicShippingUnit() {
            return this.shippingSystem === 'imperial' ? 'cu.ft' : 'cu.m';
        }
    },
    methods: {
        show() {
            this.$refs['modal'].show();
        }
    },
    data() {
        return {
            shipCostPerUnit: 2.5,
            importDuty: 4,
            commissionRate: 4.7,
            exchangeRate: 1.25,

            unitsPerCase: 0,
            width: 0.0,
            height: 0.0,
            length: 0.0,

            caseSystem: 'metric',
            shippingSystem: 'imperial',
            currency: 'usd',
            shippingCurrency: 'USD',
            settings: null,
            unitPrice: 0
        }
    }
}
</script>

<style scoped>

</style>
