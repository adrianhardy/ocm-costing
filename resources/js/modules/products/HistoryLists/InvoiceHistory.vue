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
                        <b-badge :id="`approval-required-${data.item.id}`" v-if="!data.item.approved_at" variant="danger">X</b-badge>
                        <div v-else>
                            <b-badge :id="`approved-${data.item.id}`" variant="warning">
                                <b-icon-check></b-icon-check>
                            </b-badge>
                            <br/>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <b-badge variant="danger">
                        Deleted At: {{ new Date(data.item.deleted_at) | date('dd/MM/yy HH:mm') }}
                    </b-badge>
                </div>
                <b-badge :id="`auto-approved-${data.item.id}`" v-if="!data.item.is_approval_required" variant="success">
                    <b-icon-check></b-icon-check>
                </b-badge>

                <b-tooltip triggers="hover" :target="`approved-${data.item.id}`">
                    <span>Approved: {{new Date(data.item.approved_at) | date('dd/MM/yy HH:mm')}}</span>
                </b-tooltip>
                <b-tooltip triggers="hover" :target="`auto-approved-${data.item.id}`">
                    <span>Auto Approved: {{new Date(data.item.created_at) | date('dd/MM/yy HH:mm')}}</span>
                </b-tooltip>
                <b-tooltip triggers="hover" :target="`approval-required-${data.item.id}`">
                    <span>Approval Required</span>
                </b-tooltip>
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
        productId: Number
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
                    tdClass: 'w-1 align-middle text-center',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'reference',
                    label: 'Invoice #',
                    tdClass: 'w-1 text-center',
                    thClass: 'align-middle text-center'

                }, {
                    key: 'created_at',
                    label: 'Date',
                    tdClass: 'w-5 text-center',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'sub_total',
                    label: 'Sub Total',
                    tdClass: 'w-1 text-center',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'status',
                    label: 'Status',
                    tdClass: 'w-1 text-center align-middle',
                    thClass: 'align-middle text-center'

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