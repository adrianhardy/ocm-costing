<template>
    <b-tab title="Landed Costs" :disabled="disabled">
        <div class="mb-2 d-flex justify-content-end" v-if="mode !== 'add'">
            <b-button size="sm" @click="mode = 'add'">Add Manual Landed Cost</b-button>
        </div>
        <b-form v-if="mode === 'add'">
            <h5>New Landed Cost</h5>
            <p>All fields are required</p>
            <b-form-group label="When" label-cols-md="4">
                <b-datepicker v-model="cost.date" />
            </b-form-group>
            <b-form-group label="Shipping Rate / Cuft" label-cols-md="4">
                <b-input-group prepend="$">
                    <b-input v-model="cost.shipping_cost_per_cuft" />
                </b-input-group>
            </b-form-group>
            <b-form-group label="Purchase Currency" label-cols-md="4">
                <currency-select v-model="cost.currency" />
            </b-form-group>
            <b-form-group label="Unit Price" label-cols-md="4">
                <b-input-group :prepend="cost.currency.code">
                    <b-input v-model="cost.unit_price" />
                </b-input-group>
            </b-form-group>
            <b-form-group label="Exchange Rate" label-cols-md="4">
                <b-input-group :prepend="cost.currency.code">
                    <b-input v-model="cost.fx_rate" />
                    <b-input-group-append is-text>: 1.00 GBP</b-input-group-append>
                </b-input-group>
            </b-form-group>
            <b-form-group label="Qty / Carton" label-cols-md="4">
                <b-input v-model="cost.units_per_case" />
            </b-form-group>
            <b-form-group label="Carton Dim's (cm)" label-cols-md="4">
                <b-input-group>
                    <b-input-group-prepend is-text>L</b-input-group-prepend>
                    <b-input v-model="cost.length_cm" />
                    <b-input-group-prepend is-text>W</b-input-group-prepend>
                    <b-input v-model="cost.width_cm" />
                    <b-input-group-prepend is-text>H</b-input-group-prepend>
                    <b-input v-model="cost.height_cm" />
                </b-input-group>
            </b-form-group>
            <b-form-group label="Commission Rate %" label-cols-md="4">
                <b-input-group prepend="%">
                    <b-input v-model="cost.commission_rate_pc" />
                </b-input-group>
            </b-form-group>
            <b-form-group label="Import Duty %" label-cols-md="4">
                <b-input-group prepend="%">
                    <b-input v-model="cost.import_duty_pc" />
                </b-input-group>
            </b-form-group>
            <b-form-group label-class="font-weight-bold" label="Landed Cost / Unit" label-cols-md="4">
                <b-input-group prepend="£">
                    <b-input v-model="cost.landed_cost_per_unit_gbp" />
                </b-input-group>
            </b-form-group>
            <div class="d-flex justify-content-end">
                <b-button variant="secondary" @click="mode = ''">Cancel</b-button>
                <b-button variant="primary" class="ml-2" @click="save">Create</b-button>
            </div>
        </b-form>
        <b-table v-else ref="table" show-empty empty-text="No landed cost history" small :items="landedCostsLog" :fields="landedCostsFields" sticky-header="600px">
            <template #cell(id)="data">

            </template>
            <template #cell(date)="data">
                {{ new Date(data.value) | date('dd/MM/yy') }}
            </template>
            <template #cell(length_cm)="data">
                {{ data.item.length_cm }}x{{ data.item.width_cm }}x{{ data.item.height_cm }}
            </template>
            <template #cell(shipping_cost_per_cuft)="data">
                {{ data.value * data.item.fx_rate | cash_value }}
            </template>
            <template #cell(landed_cost_per_unit_gbp)="data">
                {{ data.value | cash_value }}
            </template>
            <template #cell(fx_rate)="data">
                {{ data.value | cash_value }}
            </template>
            <template #cell(fx_rate)="data">
                {{ data.value | cash_value }}
            </template>
            <template #cell(import_duty_pc)="data">
                {{ data.value | 2dp_value }}%
            </template>
        </b-table>

    </b-tab>
</template>

<script>
import CurrencySelect from "../../components/CurrencySelect";
export default {
    name: "LandedCostHistoryTab",
    components: {CurrencySelect},
    props: {
        disabled: Boolean,
        product: Object
    },
    data() {
        return {
            mode: '',
            cost: {
                currency: {},
                date: null,
                length_cm: null,
                width_cm: null,
                height_cm: null,
                fx_rate: null,
                shipping_cost_per_cuft: null,
            }
        }
    },
    computed: {
        landedCostsFields() {
            return [{
                key: 'id',
                label: ''
            },{
                key: 'date',
                label: 'When',
            }, {
                key: 'fx_rate',
                label: 'FX'
            }, {
                key: 'shipping_cost_per_cuft',
                label: 'GBP/cuft'
            }, {
                key: 'case_cubic_ft',
                label: 'Case cuft'
            }, {
                key: 'units_per_case',
                label: 'Qty/Ctn'
            }, {
                key: 'unit_price',
                label: 'U/Price'
            }, {
                key: 'landed_cost_per_unit_gbp',
                label: '£LC/Unit'
            }]
        },
    },
    methods: {
        save() {
            const loader = this.$loading.show();
            this.$http.post(`/api/products/${this.product.id}/landed-costs`, this.cost).then(response => {
                loader.hide();
                this.mode = '';
            }).catch(error => {
                console.log(error);
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        },
        landedCostsLog() {
            const url = `/api/products/${this.product.id}/landed-costs`;
            return this.$http.get(url).then(response => {
                return response.data.items;
            });
        },
    }
}
</script>

<style scoped>

</style>
