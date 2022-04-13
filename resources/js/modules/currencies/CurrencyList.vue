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
                <template #cell(currency_fx_rate)="data">
                    <b-input-group prepend="£" v-if="currentEdit === data.item" >
                        <b-form-input v-model="currentEdit.currency_fx_rate" @change="update(data.item, $event)" />
                    </b-input-group>
                    <div v-else>
                        <a href="#" @click.prevent="currentEdit = data.item">{{ data.value | cash_value }}</a>
                    </div>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import SearchInput from "../../components/common/SearchInput";
export default {
    name: "CurrencyList",
    components: {TableHeader},
    methods: {
        items(ctx) {
            const payload = {search: this.search}
            return this.$paginatedFetch(
                '/api/currencies',
                this.pagination,
                ctx, payload
            );
        },
        update(currency, value) {
            const loader = this.$loading.show();
            this.$http.put(`/api/currencies/${currency.id}`, {
                currency_fx_rate: this.currentEdit.currency_fx_rate
            }).then(response => {
                loader.hide();
                currency.default_fx_to_gbp = value;
                this.$bvToast.toast('Currency updated', {
                    title: 'Currency',
                    variant: 'success'
                });
                this.currentEdit = null;
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                this.currentEdit = null;
                loader.hide();
            });
        }
    },
    data() {
        return {
            currentEdit: null,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            loading: false,
            fields: [
                {key: 'name', label: 'Name', sortable: true},
                {key: 'code', label: 'Code'},
                {key: 'currency_fx_rate', thClass:'w-15', label: 'Exchange Rate'},
            ]
        }
    }
}
</script>

<style scoped>

</style>
