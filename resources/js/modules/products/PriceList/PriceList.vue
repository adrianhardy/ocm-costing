<template>
<div>
  <b-card class="shadow-sm">
    <template #header>
      <table-header v-model="pagination">
        <slot>
          <search-input v-model="search" @search="$refs['table'].refresh()">
            <template #right>
              <b-input-group-append>
                <b-input-group-text>
                  <b-form-checkbox v-model="todoOnly" @change="$refs['table'].refresh()" /> To-do only
                </b-input-group-text>
              </b-input-group-append>
            </template>
          </search-input>
        </slot>
      </table-header>
    </template>
    <b-table ref="table" :fields="fields" :items="items">
      <template #cell(product.image)="data">
        <b-avatar v-if="data.item.product.media[0]">
          <b-img fluid :src="data.item.product.media[0].url" />
        </b-avatar>
      </template>
      <template #cell(created_at)="data">
        {{ data.value | date_dmY }}
      </template>
      <template #cell(profit)="data">
        {{ data.value | cash_value }}
      </template>
      <template #cell(unit_price_gbp)="data">
        <a href="#" @click.prevent="openModal(data.item)">{{data.value}}</a>
      </template>
      <template #cell(button)="data">
        <div class="row">
          <b-button variant="primary" size="sm" @click="submitOrder(data.item)">Submit</b-button>
        </div>
      </template>
    </b-table>
  </b-card>
  <pricing-modal ref="pricing-modal" @updated="updatePricing"/>
</div>
</template>

<script>

import PricingModal from "./PricingModal";
import SearchInput from "../../../components/common/SearchInput";
import TableHeader from "../../../components/table/TableHeader";

export default {
  name: "PriceList.vue",
  components: {
    TableHeader,
    SearchInput,
    'pricing-modal' : PricingModal
  },
  methods: {
    openModal(line){
      this.$refs['pricing-modal'].open(line);
    },
    updatePricing(){
      this.$bvToast.toast('Pricing Updated', {
        variant: 'success',
        title: 'Pricing'
      })
      this.$refs['table'].refresh();
    },
    submitOrder(line){
      line.submitted = true;
      this.$http.put(`api/pricing/${line.id}`, {...line}).then(response => {
        this.$bvToast.toast('Pricing Submitted', {
          variant: 'success',
          title: 'Pricing'
        })
        this.$refs['table'].refresh();
      })
    },

    items(ctx){
      return this.$paginatedFetch(
          'api/pricing',
          this.pagination,
          ctx, {
            include: 'product,product.media',
            todo_only: this.todoOnly,
            search: this.search
          });
    },
  },
  data(){
    return{
      search: '',
      todoOnly: true,
      pagination: {
        total: 0,
        perPage: 100,
        currentPage: 1,
      },
      fields: [
        {key: 'product.image', label: 'Image'},
        {key: 'product.name', label: 'Product'},
        {key: 'created_at', label: 'Created At'},
        {key: 'unit_price_gbp', label: 'Unit Price GBP'},
        {key: 'est_landed_cost_per_unit_gbp', label: 'Est Landed Cost'},
        {key: 'marketplace_commission_pc', label: 'Marketplace Comm\' %'},
        {key: 'margin_pc', label: 'Margin %'},
        {key: 'profit', label: 'profit'},
        {key: 'button', label: ''}
      ]
    }
  }
}
</script>

<style scoped>

</style>
