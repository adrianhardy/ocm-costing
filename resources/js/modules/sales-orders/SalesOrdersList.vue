<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-select @change="$refs['table'].refresh()" style="width:15% !important" v-model="selectedYear">
                            <b-select-option v-for="year in years" :key="year" :value="year">{{year}}</b-select-option>
                        </b-select>
                        <b-select v-model="stage" style="width:20% !important" @change="$refs['table'].refresh()">
                            <b-select-option value="">All</b-select-option>
                            <b-select-option value="draft">Draft</b-select-option>
                            <b-select-option value="unconfirmed_so">Unconfirmed SO</b-select-option>
                            <b-select-option value="confirmed_so">Confirmed SO</b-select-option>
                            <b-select-option value="shipped">Shipped</b-select-option>
                        </b-select>
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
                        <b-button
                            class="ml-2"
                            @click="$refs['create'].open()"
                            v-if="!isSupplier"
                            variant="primary"
                        >
                            Create
                        </b-button>
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
                <template #cell(shipping_cost_per_cubic_foot)="data">
                    {{ data.value | cash_value }}
                </template>
                <template #cell(reference)="data">
                    <a v-if="!data.item.deleted_at" :href="`/sales-orders/${data.item.id}/edit`">{{ data.value }}</a>
                    <b-link v-else @click="restoreSalesOrder(data.item)">
                        {{ data.value }}
                    </b-link>
                </template>
                <template #cell(created_at)="data">
                    <span>{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
                </template>
                <template #cell(sent_at)="data">
                    <span v-if="data.value">{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
                </template>
            </b-table>
        </b-card>
        <create-sales-order-modal
            ref="create"
            :is-supplier="isSupplier"
        />
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
    props: {
        years: Array,
        isSupplier: Boolean,
        initStage: String,
    },
    filters: {
        date: dateFilter
    },
    computed: {
        fields() {
            const fields = [
                {
                    key: 'reference',
                    label: 'Order #',
                    sortable: true,
                }, {
                    key: 'shipping_cost_per_cubic_foot',
                    label: 'Cost / Cu.ft',
                    sortable: true
                }, {
                    key: 'created_at',
                    sortable: true
                }, {
                    key: 'sent_at',
                    sortable: true
                }, {
                    key: 'status_description'
                },
                {
                    key: 'factory.name',
                    sortable: true,
                    label: 'Factory'
                }
            ];

            if (!this.isSupplier) {
                fields.push({
                    key: 'supplier.name',
                    sortable: true,
                    label: 'Supplier'
                });
            }

            return fields;
        }
    },
    methods: {
        restoreSalesOrder(order){
            this.$bvModal.msgBoxConfirm('Are you sure you want to restore this sales order?', {
                title: 'Restore',
                okTitle: 'Restore',
                okVariant: 'primary'
            }).then(response => {
                if(!response) {
                    return;
                }
                const loading = this.$loading.show();
                const url = `/api/sales-orders/restore`
                this.$http.post(url, order).then(() => {
                    this.$refs['table'].refresh();
                    loading.hide();
                }).catch(error => {
                    this.$handleExceptionResponse(error, true);
                    loading.hide();
                })
            })
        },
        items(ctx) {
            const isSupplier = this.isSupplier;
            const url = `/api/${isSupplier ? 'supplier/' : ''}sales-orders`;
            const include = isSupplier ? '' : 'supplier,factory';
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    search: this.search,
                    stage: this.stage,
                    include,
                    year: this.selectedYear??null
                }, {
                    'supplier.name': 'suppliers|name',
                    'factory.name': 'factories|name'
                }
            );
        }
    },
    data() {
        return {
            selectedYear: 2023,
            search: '',
            stage: this.initStage,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            }
        }
    }
}
</script>

<style scoped>

</style>
