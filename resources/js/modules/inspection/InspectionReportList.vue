<template>
  <div>
    <b-card class="shadow-sm">
      <template #header v-model="pagination">
        <div>
          <search-input v-model="search" placeholder="Type and press enter to search"></search-input>
        </div>
      </template>
      <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="created_at">
        <template #cell(created_at)="data">
          <span v-if="data.value">
            {{ new Date(data.value) | date_dmY }}
          </span>
        </template>
        <template #cell(sales_order_line.product.name)="data">
          <a href="#" @click="$refs['edit-report'].open(data.item)">{{data.value}}</a>
        </template>
      </b-table>
    </b-card>
    <edit-inspection-report-modal ref="edit-report" @updated="$refs['table'].refresh()"></edit-inspection-report-modal>
  </div>
</template>

<script>
import SearchInput from "../../components/common/SearchInput";
import EditInspectionReportModal from "./EditInspectionReportModal";
export default {
  name: "InspectionReportList",
  components: {
    SearchInput,
    'edit-inspection-report-modal' : EditInspectionReportModal,
  },
  computed: {
    include(){
      return 'sales_order_line, sales_order_line.product';
    }
  },
  methods: {
    items(ctx){
      const payload = {search: this.search, include: this.include};
      return this.$paginatedFetch(`/api/inspections`,
          this.pagination,
          ctx, payload
      );
    }
  },
  data(){
    return {
      search: '',
      pagination : {
        total: 0,
        perPage: 100,
        currentPage: 1
      },
      loading: false,
      fields: [
        {key: 'created_at', label: 'Created At'},
        {key: 'sales_order_line.product.name', label: 'Product'},
        {key: 'status', label: 'Status', formatter: (value) => {
            return value.toUpperCase();
        }}
      ]

    }
  }
}
</script>

<style scoped>

</style>