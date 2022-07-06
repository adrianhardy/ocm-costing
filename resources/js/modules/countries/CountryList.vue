<template>
    <div>
        <b-card class="shadow-sm">
            <table-header v-model="pagination" />
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="name">
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(has_pricing)="data">
                    <b-checkbox
                        :disabled="data.item.currency === null"
                        :checked="data.value"
                        @change="updateHasPricing(data.item, !data.value)"/>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
export default {
    name: "CurrencyList",
    components: {TableHeader},
    methods: {
        items(ctx) {
            return this.$paginatedFetch(
                '/api/countries',
                this.pagination,
                ctx, {include: 'currency'}
            );
        },
        updateHasPricing(country, value) {
            const loader = this.$loading.show();
            this.$http.put(`/api/countries/${country.id}`, {
                has_pricing: value
            }).then(() => {
                loader.hide();
                this.$bvToast.toast('Country updated', {
                    title: 'Countries',
                    variant: 'success'
                });
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loader.hide();
            });
        }
    },
    data() {
        return {
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            loading: false,
            fields: [
                {key: 'name', label: 'Name'},
                {key: 'iso_2_code', label: 'Code'},
                {key: 'currency.code', label: 'Currency'},
                {key: 'has_pricing', thClass:'w-10', label: 'Pricing?'},
            ]
        }
    }
}
</script>

<style scoped>

</style>
