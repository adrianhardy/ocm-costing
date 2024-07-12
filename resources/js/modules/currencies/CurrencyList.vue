<template>
    <div>
        <b-card class="shadow-sm">
            <table-header v-model="pagination">
                <b-button href="/admin/currencies/create" >Create new Currency</b-button>
            </table-header>
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="name">
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #head(currency_fx_rate)="data">
                    <b-badge>default</b-badge><br />
                    CN Exchange Rate
                </template>
                <template #head(pricing_fx_rate)="data">
                    <b-badge>pricing</b-badge><br />
                    UK Exchange Rate
                </template>
                <template #cell(currency_fx_rate)="data">
                    <b-input-group v-if="currentEdit === data.item" >
                        <b-form-input v-model="currentEdit.currency_fx_rate" @change="$refs['check-currency-exchange-update'].show()" />
                    </b-input-group>
                    <div v-else>
                        <a href="#" @click.prevent="currentEdit = data.item">
                            {{ data.value | 2dp_value }}
                            {{ data.item.code }}
                        </a>
                    </div>
                </template>
                <template #cell(pricing_fx_rate)="data">
                    <b-input-group v-if="currentEdit === data.item" >
                        <b-form-input v-model="currentEdit.pricing_fx_rate" @change="$refs['check-currency-exchange-update'].show()" />
                    </b-input-group>
                    <div v-else>
                        <b-link v-if="data.item.pricing_fx_to_gbp === null" @click.prevent="currentEdit = data.item">
                            Set
                        </b-link>
                        <a v-else href="#" @click.prevent="currentEdit = data.item">
                            {{ data.value | 2dp_value }}
                            {{ data.item.code }}
                        </a>
                    </div>

                </template>
            </b-table>
        </b-card>
        <b-modal ref="check-currency-exchange-update" title="Please Confirm" ok-title="Continue" @ok.prevent="update()">
            <table class="table table-sm">
                <tr>
                    <th>Currency</th>
                    <th>CN Exchange Rate</th>
                    <th>UK Exchange Rate</th>
                </tr>
                <tr v-if="currentEdit">
                    <td>{{ currentEdit.name }}</td>
                    <td>{{ currentEdit.currency_fx_rate }}</td>
                    <td>{{ currentEdit.pricing_fx_rate }}</td>
                </tr>
            </table>
            <p>
                If you're changing the CN currency exchange rate, this process may
                take <strong>a few minutes to complete</strong> as we'll need to
                update <strong>every</strong> estimated landed cost. Please do not
                close the browser or navigate away while this is carried out.
            </p>
            <p>
                This should only impact the CN side of the system.
            </p>
        </b-modal>
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
        update() {
            const loader = this.$loading.show();
            this.$http.put(`/api/currencies/${this.currentEdit.id}`, {
                currency_fx_rate: this.currentEdit.currency_fx_rate,
                pricing_fx_rate: this.currentEdit.pricing_fx_rate
            }).then(response => {
                loader.hide();
                this.$bvToast.toast('Currency updated', {
                    title: 'Currency',
                    variant: 'success'
                });
                this.currentEdit = null;
		this.$refs['check-currency-exchange-update'].hide();
		this.$refs['table'].refresh();
            }).catch(error => {
		console.warn(error);
                this.$handleExceptionResponse(error, true);
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
                {key: 'currency_fx_rate', thClass:'w-15', label: 'CN Exchange Rate'},
                {key: 'pricing_fx_rate', thClass:'w-15', label: 'UK Exchange Rate'}
            ]
        }
    }
}
</script>

<style scoped>

</style>
