<template>
    <div>
        <b-card class="shadow-sm" header-class="justify-content-between">
            <template #header>
                <div>
                    <slot name="title"/>
                </div>
                <div>
                    <b-button variant="outline-primary" :href="`/sales-orders/${orderId}/edit`">Back to Sales Order
                    </b-button>
                </div>
            </template>
            <div>
                <b-table
                    small
                    ref="table"
                    :fields="fields"
                    :items="items"
                    :sort-by="pagination.sort_by"
                    :sort-desc="pagination.sort_desc"
                    :current-page="pagination.current_page"
                    :per-page="pagination.per_page"
                >
                    <template #cell(expand)="data">
                        <div>
                            <b-button variant="transparent" size="sm"
                                      @click="data.toggleDetails(); $set(expandedRows, data.item.id, data.item._showDetails)">
                                <b-icon-chevron-down v-if="data.detailsShowing"/>
                                <b-icon-chevron-left v-else/>
                            </b-button>
                        </div>
                    </template>
                    <template #row-details="row">
                        <b-table-simple striped borderless outlined class="p-8">
                            <b-tr>
                                <b-th>Reference</b-th>
                                <b-th>Supplier Ref</b-th>
                                <b-th>Our Order #</b-th>
                                <b-th>Vessel Name</b-th>
                                <b-th>Container #</b-th>
                                <b-th>B/L Number</b-th>
                                <b-th>Ship/Agent Ref</b-th>
                                <b-th class="text-nowrap w-1">Total Invoice Amt</b-th>
                                <b-th class="text-nowrap">Qty Shipped</b-th>
                            </b-tr>
                            <b-tbody>
                                <b-tr v-if="row.item.consignments.length < 1">
                                    <b-td colspan="8" class="text-center">No shipments recorded yet</b-td>
                                </b-tr>
                                <b-tr v-for="consignment in row.item.consignments" :key="consignment.id +':'+ row.item.id">
                                    <b-td class="w-15">{{ consignment.reference }}</b-td>
                                    <b-td>{{ consignment.supplier_ref }}</b-td>
                                    <b-td>{{ consignment.our_order_number }}</b-td>
                                    <b-td>{{ consignment.vessel_name }}</b-td>
                                    <b-td>{{ consignment.container_number }}</b-td>
                                    <b-td>{{ consignment.bl_number }}</b-td>
                                    <b-td>{{ consignment.shipping_agent_ref }}</b-td>
                                    <b-td>{{ consignment.invoice_amt | cash_value }}</b-td>
                                    <b-td class="w-10">
                                        <b-form-input size="sm" @change="updateConsignment(row.item, consignment)"
                                                      v-model="consignment.pivot.qty_shipped"/>
                                    </b-td>
                                </b-tr>
                            </b-tbody>
                        </b-table-simple>
                    </template>
                </b-table>
            </div>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "ConsignmentsList",
    props: {orderId: Number},
    computed: {
        include() {
            return 'product,landed_cost,consignments,consignments.pivot';
        }
    },
    methods: {
        updateConsignment(line, consignment) {
            const loader = this.$loading.show();
            const url = `/api/sales-orders/lines/${line.id}/consignments/${consignment.id}`
            this.$http.put(url, consignment, {
                params: {include: this.include}
            }).then(response => {
                consignment = response.data;
                loader.hide();
                this.$bvToast.toast('Consignment Updated', {
                    variant: 'success'
                });
                this.$refs['table'].refresh();
            }).catch(error => {
                loader.hide();
                console.log(error);
                this.$handleExceptionResponse(error, true)
            })
        },
        /**
         * @param ctx
         * @returns {Promise<*|*|Array>}
         */
        async items(ctx) {
            const url = `/api/sales-orders/${this.orderId}/consignments/report`;
            const payload = {
                include: this.include
            }
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, payload
            ).then(items => {
                _.each(items, item => {
                    if (this.expandedRows[item.id]) {
                        item._showDetails = true;
                    }
                });
                return items;
            });
        }
    },
    data() {
        return {
            expandedRows: {},
            fields: [
                {key: 'item_number', label: 'Item #'},
                {key: 'article_number', label: 'Art. #'},
                {key: 'product.name', label: 'name'},
                {key: 'units_requested', label: 'Requested'},
                {key: 'unit_price', label: 'Unit Price'},
                {key: 'landed_cost.shipping_cost_gbp', label: 'Est Shipping Cost GBP'},
                {key: 'landed_cost.est_landed_cost_per_unit_gbp', label: 'Est Landed Cost / Unit GBP'},
                {key: 'expand', label: ''},
            ],
            pagination: {
                perPage: 10,
                currentPage: 1,
                sort_by: 'id',
                sort_desc: true
            }
        }
    }
}
</script>

<style scoped>

</style>
