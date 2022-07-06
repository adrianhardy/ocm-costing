<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination" @input="$refs['table'].refresh()">
                    <slot>
                        <search-input v-model="search" @search="$refs['table'].refresh()">
                            <template #right>
                                <b-input-group-append>
                                    <b-input-group-text>
                                        <b-form-checkbox v-model="todoOnly" @input="$refs['table'].refresh()"/>
                                        To-do only
                                    </b-input-group-text>
                                </b-input-group-append>
                            </template>
                        </search-input>
                    </slot>
                </table-header>
            </template>
            <b-table
                :current-page="pagination.currentPage"
                ref="table"
                :fields="fields"
                :items="items"
                small
            >
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #head(selected_for_export)="data">
                    <a href="#" @click.prevent="selectAllForExport(true)">All</a>
                </template>
                <template #cell(selected_for_export)="data">
                    <b-form-checkbox size="lg" :checked="selectedForExport.indexOf(data.item.id) >= 0"
                                     @change="selectForExport(data.item, $event)"/>
                </template>
                <template #cell(product.image)="data">
                    <b-avatar v-if="data.item.product.media[0]">
                        <b-img fluid :src="data.item.product.media[0].url"/>
                    </b-avatar>
                </template>
                <template #cell(created_at)="data">
                    {{ data.value | date_dmY }}
                </template>
                <template #cell(submitted_at)="data">
                    <span v-if="data.item.submitted_at">
                        {{ data.value | date_dmY }}
                    </span>
                </template>
                <template #cell(approved_at)="data">
                    <span v-if="data.item.approved_at">
                        {{ data.value | date_dmY }}
                    </span>
                </template>
                <template #cell(profit)="data">
                    {{ data.value | cash_value }}
                </template>
                <template #cell(unit_price_gbp)="data">
                    <a href="#" @click.prevent="openModal(data.item)">{{ data.value | cash_value }}</a>
                </template>
                <template #cell(button)="data">
                    <div v-if="!isApprover">
                        <div v-if="!data.item.approved_at">
                            <a href="#" size="sm" @click.prevent="submitOrder(data.item)">Submit</a>
                        </div>
                    </div>
                    <div v-if="isApprover && data.item.submitted_at && !data.item.approved_at">
                        <div>
                            <a href="#" size="sm" @click.prevent="submitOrder(data.item)">Approve</a>
                        </div>
                    </div>
                </template>
            </b-table>
        </b-card>
        <b-button variant="primary" @click="exportSelected()">Export</b-button>
        <pricing-modal
            :euro-countries="euroCountries"
            :row-countries="rowCountries"
            :uk="uk"
            ref="pricing-modal"
            @updated="updatePricing"
        />
    </div>
</template>

<script>

import PricingModal from "./PricingModal";
import SearchInput from "../../../components/common/SearchInput";
import TableHeader from "../../../components/table/TableHeader";

export default {
    name: "PriceList",
    props: {
        isApprover: Boolean,
        euroCountries: Array,
        rowCountries: Array,
        uk: Object
    },
    components: {
        TableHeader,
        SearchInput,
        'pricing-modal': PricingModal
    },
    methods: {
        selectAllForExport(value) {
            if (value) {
                this.selectedForExport = _.uniq([...this.selectedForExport, ..._.map(this.pricings, 'id')]);
            }
        },
        selectForExport(price, value) {
            if (!value) {
                const idx = this.selectedForExport.indexOf(price.id);
                this.selectedForExport.splice(idx, 1);

            } else {
                this.selectedForExport.push(price.id);
            }
        },
        selectedRows() {
            return this.items.filter(item => item.selected)
        },
        exportSelected() {
            window.location.href = `/price-list/export/?product_pricing_ids=${this.selectedForExport.join(',')}`;
        },
        toggleSelectAll(all) {
            if (all) {
                this.$refs['table'].selectAllRows();
            } else {
                this.$refs['table'].clearSelected();
            }
        },
        openModal(line) {
            this.$refs['pricing-modal'].open(line);
        },
        updatePricing() {
            this.$bvToast.toast('Pricing Updated', {
                variant: 'success',
                title: 'Pricing'
            })
            this.$refs['table'].refresh();
        },
        submitOrder(line) {
            line.submitted = true;
            this.$http.put(`/api/pricing/${line.id}`, {...line}).then(response => {
                this.$bvToast.toast('Pricing Submitted', {
                    variant: 'success',
                    title: 'Pricing'
                })
                this.$refs['table'].refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            });
        },

        async items(ctx) {
            const pricings = await this.$paginatedFetch(
                '/api/pricing',
                this.pagination,
                ctx, {
                    include: 'product,product.media,selected_for_export',
                    todo_only: this.todoOnly,
                    search: this.search
                });

            this.pricings = pricings;
            return pricings;
        },
    },
    data() {
        return {
            pricings: [],
            selected: [],
            search: '',
            todoOnly: true,
            pagination: {
                total: 0,
                perPage: 50,
                currentPage: 1,
            },
            selectedForExport: [],
            fields: [
                {key: 'selected_for_export', label: '', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'product.image', label: 'Image', tdClass: 'align-middle'},
                {key: 'product.name', label: 'Product', tdClass: 'align-middle'},
                {key: 'product.primary_sku', label: 'SKU', tdClass: 'align-middle'},
                {key: 'product.primary_asin', label: 'ASIN', tdClass: 'align-middle'},
                {key: 'created_at', label: 'Created At', tdClass: 'align-middle'},
                {key: 'submitted_at', label: 'Submitted At', tdClass: 'align-middle'},
                {key: 'approved_at', label: 'Approved At', tdClass: 'align-middle'},
                {key: 'unit_price_gbp', label: 'U/Price', tdClass: 'align-middle'},
                {key: 'est_landed_cost_per_unit_gbp', label: 'Est. LC', tdClass: 'align-middle'},
                {key: 'button', label: '', tdClass: 'align-middle'}
            ]
        }
    }
}
</script>

<style scoped>

</style>
