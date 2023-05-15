<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination" @input="$refs['table'].refresh()">
                    <slot>
                        <b-select v-model="stage" style="width:20% !important" @change="$refs['table'].refresh()">
                            <b-select-option :value="null">All</b-select-option>
                            <b-select-option value="approved">Approved</b-select-option>
                            <b-select-option value="approval_needed">Not Approved</b-select-option>
                            <b-select-option v-if="false" value="deleted">Deleted</b-select-option>
                        </b-select>
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
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
                                </b-badge> <br/>
                                <b-badge variant="success">
                                    Approved By: {{data.item.approver.name}}
                                </b-badge>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <b-badge variant="danger">
                            Deleted At: {{ new Date(data.item.deleted_at) | date('dd/MM/yy HH:mm')}}
                        </b-badge>
                    </div>
                    <b-badge v-if="!data.item.is_approval_required" variant="success">Manally Approved: {{ new Date(data.item.created_at) | date('dd/MM/yy HH:mm')}}</b-badge>
                </template>
                <template #cell(invoice_date)="data">
                    <span v-if="data.item.invoice_date">{{ new Date(data.value) | date('dd/MM/yy') }}</span>
                </template>
                <template #cell(sub_total)="data">
                    <span>{{data.value | cash_value}}</span>
                </template>
                <template #cell(reference)="data">
                    <b-link v-if="!data.item.deleted_at" :href="`/invoices/${data.item.id}/edit`">{{data.value}}</b-link>
                    <span v-else>{{data.value}}</span>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from "../../components/table/TableHeader";
import SearchInput from "../../components/SearchInput";
import {dateFilter} from "vue-date-fns";

export default {
    name: "ListInvoices",
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
                }, {
                    key: 'reference',
                    label: 'Invoice #'
                }, {
                    key: 'sales_order.reference',
                    label: 'OA'
                }, {
                    key: 'invoice_date',
                    label: 'Invoice Date'
                }, {
                    key: 'sub_total',
                    label: 'Sub Total'
                }, {
                    key: 'status',
                    label: 'Status'
                }
            ];
        },
        include(){
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
                    include: this.include
                }
            );
        }
    },
    data(){
        return {
            search: '',
            stage: 'approved',
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