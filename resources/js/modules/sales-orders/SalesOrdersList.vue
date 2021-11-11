<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-select v-model="stage" style="width:100px !important" @change="$refs['table'].refresh()">
                          <b-select-option value="">All</b-select-option>
                          <b-select-option value="quote">Quotes</b-select-option>
                          <b-select-option value="finalised">Finalised</b-select-option>
                          <b-select-option value="sent">Sent</b-select-option>
                        </b-select>
                        <search-input v-model="search" @search="$refs['table'].refresh()"/>
                        <b-button @click="$refs['create'].open()" variant="primary">Create</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table
                ref="table"
                :current-page="pagination.currentPage"
                hover
                :fields="fields"
                :items="items"
                sort-by="reference"
                show-empty
                empty-text="No Sales Orders To Show"
            >
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(reference)="data">
                    <a :href="`/sales-orders/${data.item.id}/edit`">{{ data.value }}</a>
                </template>
                <template #cell(created_at)="data">
                    <span>{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
                </template>
                <template #cell(sent_at)="data">
                    <span v-if="data.value">{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
                </template>
            </b-table>
        </b-card>
        <create-sales-order-modal ref="create"/>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import {dateFilter} from "vue-date-fns";
import CreateSalesOrderModal from "./CreateSalesOrderModal";
import SearchInput from "../../components/SearchInput";

export default {
    name: "SalesOrdersList",
    components: {SearchInput, CreateSalesOrderModal, TableHeader},
    filters: {
        date: dateFilter
    },
    methods: {
        items(ctx) {

            return this.$paginatedFetch(
                '/api/sales-orders',
                this.pagination,
                ctx, {
                    search: this.search,
                    stage: this.stage,
                    include: ''
                }
            );
        }
    },
    data() {
        return {
            search: '',
            stage: '',
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            fields: [
                {
                    key: 'reference',
                    label: 'Order #',
                    sortable: true,
                }, {
                    key: 'created_at',
                    sortable: true
                }, {
                    key: 'sent_at',
                    sortable: true
                }, {
                    key: 'status'
              }
            ]
        }
    }
}
</script>

<style scoped>

</style>
