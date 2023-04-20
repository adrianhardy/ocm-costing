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
            <template #cell(fba_fee)="data">
                <span>{{data.value | cash_value}}</span>
            </template>
        </b-table>
    </div>
</template>

<script>
import TableHeader from "../../../components/table/TableHeader";

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
                {key: 'country.name', label: 'Country', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'approved_at', label: 'Appr.', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'approver.name', label: 'Appr. By', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'suggested_price', label: 'Sugg.', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'fba_fee', label: 'FBA', tdClass: 'align-middle', thClass: 'align-middle'},
            ]
        },
        include() {
            return 'submitter,approver,country';
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
        }
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