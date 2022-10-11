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
                    <b-input-group prepend="£">
                        <b-form-input v-model="shipCostPerCubicFt" />
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
                        <b-form-input v-model="width" :disabled="cartonTotalCubicFt"/>
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Carton Length">
                    <b-input-group prepend="cm">
                        <b-form-input v-model="length" :disabled="cartonTotalCubicFt"/>
                    </b-input-group>
                </b-form-group>
                <b-form-group label="Carton Height">
                    <b-input-group prepend="cm">
                        <b-form-input v-model="height" :disabled="cartonTotalCubicFt"/>
                    </b-input-group>
                </b-form-group>
                <b-form-group label="OR, Total Cubic Feet">
                    <b-input-group prepend="cu.ft">
                        <b-form-input v-model="cartonTotalCubicFt" />
                    </b-input-group>
                </b-form-group>
            </div>
        </div>
        <div class="small">
            <b-table-simple small>
                <b-tr>
                    <b-td>Carton Cubic Feet</b-td>
                    <b-td>{{ caseVolumeCubicFt }}</b-td>
                </b-tr>
                <b-tr>
                    <b-td>Shipping/Carton</b-td>
                    <b-td>&pound; {{ costToShipPerCartonGbp.toFixed(2) }}</b-td>
                </b-tr>
                <b-tr>
                    <b-td>Shipping/Unit</b-td>
                    <b-td>&pound; {{ shippingCostPerUnitGbp.toFixed(2) }}</b-td>
                </b-tr>
                <b-tr>
                    <b-td>Unit Cost (raw)</b-td>
                    <b-td>&pound; {{ unitCostGbp.toFixed(2) }}</b-td>
                </b-tr>
            </b-table-simple>
        </div>
        <h5>Landed Cost Per Unit</h5>
        <div class="font-weight-bold">&pound;{{ landedCostPerUnit }}</div>
    </b-modal>
</template>

<script>
export default {
    props: {defaultFXRate: Number, defaultImportDuty: Number, defaultCommissionRate: Number},
    name: "QuickCostModal",
    watch: {
        cartonTotalCubicFt(value) {
            if (!value && value !== null) {
                this.cartonTotalCubicFt = null;
            }
        }
    },
    computed: {
        landedCostPerUnit() {
            const importDutyUplift = 1 + (this.importDuty/100);
            const costAfterCommission = (this.unitCostWithCommissionGbp + this.shippingCostPerUnitGbp) * importDutyUplift;

            if (isNaN(costAfterCommission)) {
                return (0).toFixed(2);
            }

            return costAfterCommission.toFixed(2);
        },
        unitCostWithCommissionGbp() {
            const commissionUplift = 1 + (this.commissionRate/100);
            return this.unitCostGbp * commissionUplift;
        },
        unitCostGbp() {
            return this.unitPrice / this.exchangeRate;
        },
        shippingCostPerUnitGbp() {
            return (this.caseVolumeCubicFt * this.shipCostPerCubicFt) / this.unitsPerCase;
        },
        costToShipPerCartonGbp() {
            return (this.caseVolumeCubicFt * this.shipCostPerCubicFt);
        },
        caseVolumeCubicFt() {
            if (this.cartonTotalCubicFt) {
                return this.cartonTotalCubicFt;
            }

            const volume = ((this.width / 100) * (this.height/100) * (this.length/ 100));
            return (volume * 35.3147).toFixed(3);
        },
        caseVolumeCubicM() {

            if (this.cartonTotalCubicFt) {
                return this.cartonTotalCubicFt / 35.3147;
            }

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
            cartonTotalCubicFt: null,

            shipCostPerCubicFt: 5.5,
            importDuty: 4,
            commissionRate: 4.7,
            exchangeRate: 1.08,

            unitsPerCase: 10,
            width: 100.0,
            height: 100.0,
            length: 100.0,

            shippingSystem: 'imperial',
            currency: 'usd',
            shippingCurrency: 'USD',
            settings: null,
            unitPrice: 10
        }
    }
}
</script>

<style scoped>

</style>
