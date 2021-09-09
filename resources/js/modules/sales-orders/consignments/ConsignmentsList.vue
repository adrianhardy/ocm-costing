<template>
<div>
  <b-card class="shadow-sm">
    <slot name="title"/>
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
            <b-button variant="transparent" size="sm" @click="data.toggleDetails(); $set(expandedRows, data.item.id, data.item._showDetails)">
              <b-icon-chevron-down v-if="data.detailsShowing"/>
              <b-icon-chevron-left v-else />
            </b-button>
          </div>
        </template>
        <template #row-details="row">
          <h5>Consignments</h5>
          <b-table-simple striped borderless outlined class="p-8">
            <b-tr>
              <b-th>Reference</b-th>
              <b-th>Qty Shipped</b-th>
              <b-th>Goods Cost</b-th>
              <b-th>Goods Cost GBP</b-th>
              <b-th>Shipping Cost GBP</b-th>
              <b-th>Shipping Cost GBP/Unit</b-th>
              <b-th>Invoice Amt</b-th>
            </b-tr>
            <b-tbody>
              <b-tr v-for="consignment in row.item.consignments" :key="consignment.id +':'+ row.item.id">
                <b-td class="w-15">{{ consignment.reference }}</b-td>
                <b-td class="w-5">
                  <b-form-group>
                    <b-form-input size="sm" @change="updateConsignment(row.item, consignment)" v-model="consignment.pivot.qty_shipped"/>
                  </b-form-group>
                </b-td>
                <b-td>{{consignment.pivot.goods_cost}}</b-td>
                <b-td>£{{consignment.pivot.goods_cost_gbp}}</b-td>
                <b-td>£{{consignment.pivot.shipping_cost_gbp}}</b-td>
                <b-td>£{{consignment.pivot.shipping_cost_gbp_per_unit}}</b-td>
                <b-td class="w-10">
                  <b-form-group>
                    <b-form-input size="sm" @change="updateConsignment(row.item, consignment)" v-model="consignment.pivot.invoice_amt"/>
                  </b-form-group>
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
  props: ['order-id'],
  computed: {
    include() {
      return 'product,landed_cost,consignments,consignments.pivot';
    }
  },
  methods: {
    updateConsignment(line, consignment){
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
      const url = `/api/sales-orders/${this.orderId}/consignments`;
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
  data(){
    return {
      expandedRows: {},
      fields: [
        {key: 'product.name', label: 'name'},
        {key: 'units_requested', label: 'Requested'},
        {key: 'article_number', label: 'Art. #'},
        {key: 'item_number', label: 'Item #'},
        {key: 'unit_price', label: 'Unit Price'},
        {key: 'estimated_shipping_cost_gbp', label: 'Est Shipping Cost GBP'},
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