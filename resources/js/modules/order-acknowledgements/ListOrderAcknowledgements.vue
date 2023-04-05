<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-select v-model="stage" style="width:20% !important" @change="$refs['table'].refresh()">
                            <b-select-option value="">All</b-select-option>
                            <b-select-option value="quote">Quotes</b-select-option>
                            <b-select-option value="order">Orders</b-select-option>
                            <b-select-option value="deleted">Deleted</b-select-option>
                        </b-select>
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
                        <b-button
                            class="ml-2"
                            @click="openModal()"
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
                empty-text="No Order Acknowledgements To Show"
            >
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(reference)="data">
                    <a v-if="!data.item.deleted_at" :href="`/order-acknowledgements/${data.item.id}/edit`">{{ data.value }}</a>
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
        <b-modal ref="modal" title="Create Order Acknowledgement" ok-title="Create" @ok="createAcknowledgement">
            <b-form-group label="Supplier">
                <supplier-select v-model="modal.supplier" />
            </b-form-group>
            <b-form-group label="Our Reference">
                <b-form-input v-model="modal.our_reference" />
            </b-form-group>
            <b-form-group label="Supplier Reference">
                <b-form-input v-model="modal.supplier_reference" />
            </b-form-group>
        </b-modal>
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SearchInput from "../../components/SearchInput";
import CreateSalesOrderModal from "../sales-orders/CreateSalesOrderModal";
import TableHeader from "../../components/table/TableHeader";
import SupplierSelect from "../../components/SupplierSelect";

export default {
    name: "ListOrderAcknowledgements",
    filters: {
        date: dateFilter
    },
    components: {SupplierSelect, SearchInput, CreateSalesOrderModal, TableHeader},
    computed: {
        fields() {
            return [
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
                    key: 'status_description'
                }, {
                    key: 'supplier.name',
                    sortable: true,
                    label: 'Supplier'
                }
            ];
        }
    },
    methods: {
        createAcknowledgement(){
            const loading = this.$loading.show();
            this.$http.post('/api/order-acknowledgements', this.modal).then(response => {
                console.log(response);
                loading.hide();
                window.location.href = `/order-acknowledgements/${response.data.id}/edit`
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        openModal(){
            this.modal = {
                supplier: {},
                our_reference: null,
                supplier_reference: null
            }
            this.$refs['modal'].show()
        },
        restoreSalesOrder(order){
            this.$bvModal.msgBoxConfirm('Are you sure you want to restore this Order Acknowledgement?', {
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
            const url = `/api/order-acknowledgements`;
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    search: this.search,
                    stage: this.stage,
                    include: 'supplier'
                }
            );
        }
    },
    data() {
        return {
            search: '',
            stage: this.initStage,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            modal: {
                supplier: {},
                our_reference: null,
                supplier_reference: null
            }
        }
    }
}
</script>

<style scoped>

</style>