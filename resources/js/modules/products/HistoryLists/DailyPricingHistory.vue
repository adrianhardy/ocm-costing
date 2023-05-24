<template>
    <div>
        <table-header v-model="pagination" @input="$refs['table'].refresh()"></table-header>
        <b-table ref="table" :items="items" :fields="fields">
            <template #cell(submitted_at)="data">
                <span v-if="data.item.submitted_at">
                    {{data.value | date_dmY}}
                </span>
            </template>
            <template #cell(approved_at)="data">
                <span v-if="data.item.approved_at">
                    {{data.value | date_dmY}}
                </span>
            </template>
            <template #cell(suggested_price)="data">
                <span>{{data.value | cash_value}}</span>
            </template>
            <template #cell(local_profit)="data">
                <div v-if="data.value">
                        <span v-if="data.item.country.iso_2_code !== 'SE' && data.item.country.iso_2_code !== 'PL'">
                            {{ currencySymbol(data.item.country) }}{{ data.item.local_profit }}
                        </span>
                    <span v-else>
                            £{{ data.item.profit }}
                        </span>
                </div>
            </template>
        </b-table>
    </div>
</template>

<script>
import TableHeader from "../../../components/table/TableHeader";
import getSymbolFromCurrency from "currency-symbol-map";

export default {
    name: "DailyPricingHistory",
    components: {
        TableHeader
    },
    props:{
        productId: Number
    },
    computed: {
        fields(){
            return [
                {key: 'country.iso_2_code', label: 'Country', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'approved_at', label: 'Appr.', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'approver.name', label: 'Appr. By', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'suggested_price', label: 'Sugg.', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'local_profit', label: 'Profit', tdClass: 'align-middle', thClass: 'align-middle'},
            ]
        },
        include() {
            return 'submitter,approver,country,country.currency';
        }
    },
    methods: {
        async items(ctx){
            const payload = {
                include: this.include,
                product_id: this.productId
            }
            return this.$paginatedFetch(
                '/api/daily-pricing',
                this.pagination,
                ctx, payload
            )
        },
        currencySymbol(country) {
            if (country && country.currency) {
                return getSymbolFromCurrency(country.currency.code)
            }
            return '';
        },
    },
    data(){
        return {
            search: '',
            stage: null,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
        }
    }
}
</script>

<style scoped>

</style>