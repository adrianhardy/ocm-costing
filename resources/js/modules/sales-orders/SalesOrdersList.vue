<template>
  <div>
    <b-card class="shadow-sm">
      <template #header>
        <table-header v-model="pagination">
          <slot>
            <b-button @click="$refs['create'].open()">Create New Sales Order</b-button>
          </slot>
        </table-header>
      </template>
      <b-table
          ref="table"
          :current-page="pagination.currentPage"
          hover
          :busy.sync="loading"
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
    <create-sales-order-modal ref="create" />
  </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import {dateFilter} from "vue-date-fns";
import CreateSalesOrderModal from "./CreateSalesOrderModal";
export default {
  name: "SalesOrdersList",
  components: {CreateSalesOrderModal, TableHeader},
  filters: {
    date: dateFilter
  },
  methods: {
    items(ctx) {
      return this.$paginatedFetch(
          '/api/sales-orders',
          this.pagination,
          ctx, {
            include: ''
          }
      );
    }
  },
  data() {
    return {
      pagination: {
        total: 0,
        perPage: 100,
        currentPage: 1,
      },
      loading: false,
      fields: [
        {
          key: 'reference',
          label: 'Reference',
          sortable: true,
        },{
          key: 'created_at',
          sortable: true
        },{
          key: 'sent_at',
          sortable: true
        },{
          key: 'total_gbp',
          sortable: true
        }
      ]
    }
  }
}
</script>

<style scoped>

</style>
