<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination" @input="$refs['table'].refresh()">
                    <slot>
                        <b-form-select class="w-25 mr-2 " v-model="priority" @change="$refs['table'].refresh()">
                            <b-form-select-option :value="null" disabled>Filter by priority</b-form-select-option>
                            <b-form-select-option value="low">Low</b-form-select-option>
                            <b-form-select-option value="medium">Medium</b-form-select-option>
                            <b-form-select-option value="High">High</b-form-select-option>
                        </b-form-select>
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
                        <b-button class="ml-2" variant="primary" @click="exportSelected()">Export</b-button>
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
                <template #head(item_number)="data">
                    <span v-if="isChina">
                        Factory Item #
                    </span>
                    <span v-else>
                        Item #
                    </span>
                </template>
                <template #cell(item_number)="data">
                    <span v-if="isChina">
                        {{ data.item.product.item_number }}
                    </span>
                    <span v-else>
                        {{ data.item.product.article_number }}
                    </span>
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
                <template #cell(exported_at)="data">
                    <span v-if="data.item.exported_at">
                        {{ data.value | date_dmY }}
                    </span>
                </template>
                <template #cell(profit)="data">
                    {{ data.value | cash_value }}
                </template>
                <template #cell(product.name)="data">
                    <a href="#" @click.prevent="openModal(data.item)">{{ data.value }}</a>
                </template>
                <template #cell(est_landed_cost_per_unit_gbp)="data">
                    {{ data.value | cash_value }}
                </template>
                <template #cell(priority)="data">
                    <b-badge variant="secondary" v-if="data.value === 'low'">
                        {{data.value | capitalize}}
                    </b-badge>
                    <b-badge variant="primary" v-if="data.value === 'medium'">
                        {{data.value | capitalize}}
                    </b-badge>
                    <b-badge variant="danger" v-if="data.value === 'high'">
                        {{data.value | capitalize}}
                    </b-badge>
                </template>
            </b-table>
        </b-card>
        <pricing-modal
            :euro-countries="euroCountries"
            :row-countries="rowCountries"
            :non-euro-eu-countries="nonEuroEuCountries"
            :is-approver="isApprover"
            :uk="uk"
            ref="pricing-modal"
            @updated="updatePricing"
            @copy="copyPricing($event)"
            :is-china="isChina"
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
        nonEuroEuCountries: Array,
        uk: Object,
        isChina: {
            type: Boolean,
            default: false
        }
    },
    components: {
        TableHeader,
        SearchInput,
        'pricing-modal': PricingModal
    },
    filters: {
        capitalize(val) {
            return val && val[0].toUpperCase() + val.slice(1);
        }
    },
    methods: {
        copyPricing(pricing){
            this.copiedPricing = pricing
        },
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
            if (this.selectedForExport.length < 1) {
                this.$bvModal.msgBoxOk(
                    "You must select some products for export first", {
                        title: 'Product export'
                    }
                );
                return;
            }
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
            this.$refs['pricing-modal'].open(line, this.copiedPricing);
        },
        updatePricing() {
            this.$bvToast.toast('Pricing Updated', {
                variant: 'success',
                title: 'Pricing'
            })
            this.$refs['table'].refresh();
        },

        async items(ctx) {
            const pricings = await this.$paginatedFetch(
                '/api/pricing',
                this.pagination,
                ctx, {
                    include: 'product,product.media,selected_for_export,submitter,approver',
                    todo_only: this.todoOnly,
                    search: this.search,
                    priority: this.priority
                });

            this.pricings = pricings;
            return pricings;
        },
    },
    data() {
        return {
            copiedPricing: null,
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
            priority: null,
            fields: [
                {key: 'selected_for_export', label: '', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'product.image', label: 'Image', tdClass: 'align-middle'},
                {key: 'item_number', label: 'Item #', tdClass: 'align-middle'},
                {key: 'product.primary_sku', label: 'SKU', tdClass: 'align-middle'},
                {key: 'product.primary_asin', label: 'ASIN', tdClass: 'align-middle'},
                {key: 'product.name', label: 'Name', tdClass: 'align-middle'},
                {key: 'submitted_at', label: 'Priced', tdClass: 'align-middle', sortable: true},
                {key: 'submitter.initials', label: 'Priced By', tdClass: 'align-middle'},
                {key: 'approved_at', label: 'Approved', tdClass: 'align-middle', sortable: true},
                {key: 'approver.initials', label: 'Appr. by', tdClass: 'align-middle'},
                {key: 'exported_at', label: 'exported', tdClass: 'align-middle', sortable: true},
                {key: 'priority', label: 'Priority', tdClass: 'align-middle'},
            ]
        }
    }
}
</script>

<style scoped>

</style>
