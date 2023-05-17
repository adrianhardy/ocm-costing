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
            show-empty
            empty-text="No Invoices To Show"
        >
            <template #table-busy>
                <div class="text-center my-2">
                    <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                </div>
            </template>
            <template #cell(invoice.status)="data">
                <div v-if="!data.item.invoice.deleted_at">
                    <div v-if="data.item.invoice.is_approval_required">
                        <b-badge :id="`approval-required-${data.item.invoice.id}`" v-if="!data.item.invoice.approved_at" variant="danger">X</b-badge>
                        <div v-else>
                            <b-badge :id="`approved-${data.item.invoice.id}`" variant="warning">
                                <b-icon-check></b-icon-check>
                            </b-badge>
                            <br/>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <b-badge variant="danger">
                        Deleted At: {{ new Date(data.item.invoice.deleted_at) | date('dd/MM/yy HH:mm') }}
                    </b-badge>
                </div>
                <b-badge :id="`auto-approved-${data.item.invoice.id}`" v-if="!data.item.invoice.is_approval_required" variant="success">
                    <b-icon-check></b-icon-check>
                </b-badge>

                <b-tooltip triggers="hover" :target="`approved-${data.item.invoice.id}`">
                    <span>Approved: {{new Date(data.item.invoice.approved_at) | date('dd/MM/yy HH:mm')}}</span>
                </b-tooltip>
                <b-tooltip triggers="hover" :target="`auto-approved-${data.item.invoice.id}`">
                    <span>Auto Approved: {{new Date(data.item.invoice.created_at) | date('dd/MM/yy HH:mm')}}</span>
                </b-tooltip>
                <b-tooltip triggers="hover" :target="`approval-required-${data.item.invoice.id}`">
                    <span>Approval Required</span>
                </b-tooltip>
            </template>
            <template #cell(invoice.created_at)="data">
                <span>{{ new Date(data.value) | date('dd/MM/yy HH:mm') }}</span>
            </template>
            <template #cell(invoice.sub_total)="data">
                <span>{{ data.value | cash_value }}</span>
            </template>
            <template #cell(invoice.reference)="data">
                <b-link v-if="!data.item.invoice.deleted_at" :href="`/invoices/${data.item.invoice.id}/edit`">{{ data.value }}</b-link>
                <span v-else>{{ data.value }}</span>
            </template>
            <template #cell(unit_price)="data">
                <span v-if="data.item.unit_price">£{{data.item.unit_price | 2dp_value}}</span>
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
                    key: 'invoice.sales_order.supplier.name',
                    label: 'Supplier',
                    tdClass: 'align-middle text-center',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'invoice.reference',
                    label: 'Invoice #',
                    tdClass: 'text-center align-middle',
                    thClass: 'align-middle text-center'

                }, {
                    key: 'invoice.created_at',
                    label: 'Date',
                    tdClass: 'text-center align-middle',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'qty',
                    label: 'QTY',
                    tdClass: 'text-center align-middle',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'unit_price',
                    label: 'Price',
                    tdClass: 'text-center align-middle',
                    thClass: 'align-middle text-center'
                }, {
                    key: 'invoice.status',
                    label: 'Status',
                    tdClass: 'text-center align-middle',
                    thClass: 'align-middle text-center'

                }
            ];
        },
        include() {
            return 'invoice.creator,invoice.approver,invoice.sales_order,invoice.sales_order.supplier';
        }
    },
    methods: {
        items(ctx) {
            const url = `/api/invoice-lines`;
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