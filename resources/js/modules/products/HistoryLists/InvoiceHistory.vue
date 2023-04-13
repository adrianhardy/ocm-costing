<template>
    <div>
        <table-header v-model="pagination" @input="$refs['table'].refresh()">
        </table-header>
        <b-table
            ref="table"
            :current-page="pagination.currentPage"
            hover
            :fields="fields"
            :items="items"
            sort-by="reference"
            show-empty
            empty-text="No Invoices To Show"
        >
            <template #table-busy>
                <div class="text-center my-2">
                    <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                </div>
            </template>
            <template #cell(status)="data">
                <div v-if="!data.item.deleted_at">
                    <div v-if="data.item.is_approval_required">
                        <b-badge v-if="!data.item.approved_at" variant="danger">Approval Required</b-badge>
                        <div v-else>
                            <b-badge variant="success">
                                Approved At: {{ new Date(data.item.approved_at) | date('dd/MM/yy HH:mm') }}
                            </b-badge>
                            <br/>
                            <b-badge variant="success">
                                Approved By: {{ data.item.approver.name }}
                            </b-badge>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <b-badge variant="danger">
                        Deleted At: {{ new Date(data.item.deleted_at) | date('dd/MM/yy HH:mm') }}
                    </b-badge>
                </div>
                <b-badge v-if="!data.item.is_approval_required" variant="success">Manally Approved:
                    {{ new Date(data.item.created_at) | date('dd/MM/yy HH:mm') }}
                </b-badge>
            </template>
            <template #cell(created_at)="data">
                <span>{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
            </template>
            <template #cell(sub_total)="data">
                <span>{{ data.value | cash_value }}</span>
            </template>
            <template #cell(reference)="data">
                <b-link v-if="!data.item.deleted_at" :href="`/invoices/${data.item.id}/edit`">{{ data.value }}</b-link>
                <span v-else>{{ data.value }}</span>
            </template>
        </b-table>
    </div>
</template>

<script>
import TableHeader from "../../../components/table/TableHeader";
import SearchInput from "../../../components/SearchInput";
import {dateFilter} from "vue-date-fns";

export default {
    name: "InvoiceHistory",
    props: {
        productId: String
    },
    components: {
        TableHeader, SearchInput
    },
    filters: {
        date: dateFilter
    },
    computed: {
        fields() {
            return [
                {
                    key: 'sales_order.supplier.name',
                    label: 'Supplier',
                    tdClass: 'w-1'
                }, {
                    key: 'reference',
                    label: 'Invoice #',
                    tdClass: 'w-1'
                }, {
                    key: 'created_at',
                    label: 'Date',
                    tdClass: 'w-1'
                }, {
                    key: 'sub_total',
                    label: 'Sub Total',
                    tdClass: 'w-1'
                }, {
                    key: 'status',
                    label: 'Status',
                    tdClass: 'w-25'
                }
            ];
        },
        include() {
            return 'creator,approver,sales_order,sales_order.supplier';
        }
    },
    methods: {
        items(ctx) {
            const url = `/api/invoices`;
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    search: this.search,
                    stage: this.stage,
                    include: this.include,
                    product_id: this.productId
                }
            );
        }
    },
    data() {
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