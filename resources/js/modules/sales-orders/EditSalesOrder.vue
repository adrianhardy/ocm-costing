<template>
  <div>
    <b-card class="shadow-sm">
      <div class="row">
        <div class="col-12 col-md-4">
          <b-form-group label="Supplier">
            <supplier-select v-model="so.supplier" />
          </b-form-group>
        </div>
        <div class="col-12 col-md-4">
          <b-form-group label="Currency">
            <currency-select v-model="so.currency" />
          </b-form-group>
        </div>
        <div class="col-12 col-md-4">
          <b-form-group label="Reference">
            <b-form-input v-model="so.reference" />
          </b-form-group>
        </div>
      </div>
      <b-table
        show-empty
        empty-text="No sales order lines to show"
        :items="so.lines"
        :fields="fields"
    </b-card>
  </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../components/SupplierSelect";
import CurrencySelect from "../../components/CurrencySelect";


export default {
  name: "EditSalesOrder",
  components: {CurrencySelect, SupplierSelect},
  props: {salesOrderId: Number},
  filters: {
    date: dateFilter
  },
  mounted() {
    this.refresh();
  },
  computed: {
    fields() {
      return [{
        key: 'name',
        label: 'Name',
      },{
        key: 'product.article_number',
        label: 'Art #',
      },{
        key: 'product.item_number',
        label: 'Item #',
      },{
        key: 'packing_instructions',
        label: 'Packing',
      },{
        key: 'unit_price',
        label: 'Unit Price'
      },{
        key: 'units_requested',
        label: 'Qty'
      },{
        key: 'units_requested',
        label: 'Qty'
      }]
    },
    include() {
      return 'supplier,currency,lines,lines.product,lines.product.supplier';
    }
  },
  methods: {
    save() {
    },
    addNewNote() {
      const url = `/api/sales-orders/${this.salesOrderId}/comments`;
      const loader = this.$loading.show();
      this.$http.post(url, {comment: this.note}).then(() => {
        this.note = '';
        loader.hide();
        this.refresh();
      }).catch(error => {
        loader.hide();
        this.$handleExceptionResponse(error, true);
      })
    },
    refresh() {
      const loader = this.$loading.show();
      this.$http.get(`/api/sales-orders/${this.salesOrderId}`, {
        params: {include: this.include}
      }).then(response => {
        this.so = response.data;
        loader.hide();
      }).catch(error => {
        this.$handleExceptionResponse(error, true);
      })
    }
  },
  data() {
    return {
      so: {},
      note: '',
    }
  }
}
</script>

<style scoped>

</style>
