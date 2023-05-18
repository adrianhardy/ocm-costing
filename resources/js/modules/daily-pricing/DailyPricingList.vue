<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-button v-if="canExport" variant="primary" class="mr-2" @click="exportNonViable">
                            Export Non Viable
                        </b-button>
                        <country-select placeholder="Country" use-code amazon class="mr-2 w-20" @input="$refs['table'].refresh()" v-model="country" />
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
                    </slot>
                </table-header>
            </template>
            <b-table
                show-empty
                empty-text="No Daily Pricing Found"
                sort-by="submitted_at"
                :sort-desc="true"
                ref="table"
                :fields="fields"
                :items="items"
                :current-page="pagination.currentPage"
                :per-page="pagination.perPage"
            >
                <template #cell(suggested_price)="data">
                    <b-input-group :prepend="currencySymbol(data.item.country)">
                        <b-form-input
                            :readonly="data.item.approved_at !== null"
                            v-model="data.item.suggested_price"
                            type="number"
                        ></b-form-input>
                    </b-input-group>
                </template>
                <template #cell(viable)="data">
                    <b-icon-circle-fill variant="success" v-if="data.item.is_viable"/>
                    <b-icon-circle-fill variant="danger" v-else></b-icon-circle-fill>
                </template>
                <template #cell(fba_fee)="data">
                    <b-input-group :prepend="currencySymbol(data.item.country)">
                        <b-form-input
                            :readonly="data.item.approved_at !== null"
                            v-model="data.item.fba_fee"
                            type="number"
                        ></b-form-input>
                    </b-input-group>
                </template>
                <template #cell(action)="data">
                    <b-button block size="sm" @click="openReplaceModal(data.item.id)" v-if="data.item.approver.id" variant="success">
                        Replace
                    </b-button>
                    <b-button block size="sm" @click="updateDailyPricing(data.item)" v-else variant="primary">
                        Submit
                    </b-button>
                </template>
                <template #cell(last_checked_at)="data">
                    <div :id="`checked-${data.item.id}`">
                        <span v-if="data.item.last_checked_at">
                        {{ new Date(data.item.last_checked_at) | date('dd/MM/yy HH:mm') }}
                    </span>
                    </div>
                    <b-popover triggers="hover" :target="`checked-${data.item.id}`">
                        <table class="table table-sm">
                            <tr>
                                <th>When</th><th>Who</th><th>Price</th><th>Fee</th><th></th>
                            </tr>
                            <tr v-if="data.item.pricing_history.length < 1">
                                <td colspan="5">No daily pricing history</td>
                            </tr>
                            <tr v-for="history in data.item.pricing_history">
                                <td>
                                    {{ new Date(history.submitted_at) | date('dd/MM/yy') }}
                                </td>
                                <td>
                                    {{ history.submitter.initials }}
                                </td>
                                <td>
                                    {{ currencySymbol(data.item.country) }}{{ history.suggested_price.toFixed(2) }}
                                </td>
                                <td>
                                    {{ currencySymbol(data.item.country) }}{{ history.fba_fee.toFixed(2) }}
                                </td>
                                <td>
                                    <b-icon-circle-fill variant="success" v-if="history.is_viable"/>
                                    <b-icon-circle-fill variant="danger" v-else></b-icon-circle-fill>
                                </td>
                            </tr>
                        </table>

                    </b-popover>
                </template>
                <template #cell(margin)="data">
                    <span v-if="data.value">
                        {{ data.value }}%
                    </span>
                </template>
                <template #cell(profit)="data">
                    <div v-if="data.value">
                        <span v-if="data.item.country.iso_2_code !== 'SE' && data.item.country.iso_2_code !== 'PL'">
                            {{ currencySymbol(data.item.country) }}{{data.item.local_profit}}
                        </span>
                        <span v-else>
                            £{{data.item.profit}}
                        </span>
                    </div>
                </template>
                <template #cell(product.final_cost_gbp)="data">
                    <span v-if="data.value">
                        &pound;{{ data.value }}
                    </span>
                </template>
            </b-table>
        </b-card>
        <b-modal ref="replace-modal" centered size="md" title="Replace Daily Pricing" @ok="replaceDailyPricing">
            <b-form-group label="Suggested Price">
                <b-form-input type="number" v-model="replace.suggested_price" />
            </b-form-group>
            <b-form-group label="FBA Fee">
                <b-form-input type="number" v-model="replace.fba_fee" />
            </b-form-group>
            <b-alert :show="true" variant="warning">
                If there is an unapproved daily pricing for this product and country,
                the fields above will be used to update that.
            </b-alert>
        </b-modal>
    </div>
</template>

<script>
import TableHeader from "../../components/table/TableHeader";
import SearchInput from "../../components/SearchInput";
import CountrySelect from "../../components/CountrySelect";
import {dateFilter} from "vue-date-fns";
import getSymbolFromCurrency from "currency-symbol-map";


export default {
    name: "DailyPricingList",
    components: {
        CountrySelect,
        TableHeader, SearchInput
    },
    props: {
        canApprove: {
            type: Boolean,
            default: false
        },
        canExport: {
            type: Boolean,
            default: false,
        }
    },
    filters: {
        date: dateFilter
    },
    computed: {
        include(){
            return 'submitter,approver,country,country.currency,product,pricing_history,pricing_history.submitter'
        },
        fields(){
            const fields = [
                {
                    key: 'product.item_number',
                    label: 'Item #',
                    tdClass: 'align-middle w-8',
                    thClass: 'align-middle'
                }, {
                    key: 'product.primary_sku',
                    label: 'SKU',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle'
                }, {
                    key: 'product.primary_asin',
                    label: 'ASIN',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle'
                }, {
                    key: 'product.name',
                    label: 'Description',
                    tdClass: 'align-middle w-15',
                    thClass: 'align-middle'
                }, {
                    key: 'country.iso_2_code',
                    label: 'Mk\'t',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle'
                }, {
                    key: 'product.final_cost_gbp',
                    label: 'GBP/Unit',
                    tdClass: 'align-middle w-1',
                    thClass: 'align-middle'
                }, {
                    key: 'suggested_price',
                    label: 'Sugg. Price',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-10'
                }, {
                    key: 'viable',
                    label: '',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-1'
                }, {
                    key: 'fba_fee',
                    label: 'FBA Fee',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-10'
                }, {
                    key: 'action',
                    label: '',
                    tdClass: 'align-middle w-1',
                }, {
                    key: 'last_checked_at',
                    label: 'Last Checked',
                    tdClass: 'align-middle w-1 text-nowrap',
                    thClass: 'align-middle'
                }, {
                    key: 'submitter.initials',
                    label: 'Checker',
                    tdClass: 'align-middle w-5 ',
                    thClass: 'align-middle'
                },{
                    key: 'margin',
                    label: 'Margin',
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                },{
                    key: 'profit',
                    label: 'Profit',
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }
            ]

            if(!this.canApprove) {
                return _.filter(fields, o =>
                    o.key !== 'product.final_cost_gbp' &&
                    o.key !== 'margin' &&
                    o.key !== 'profit'
                );
            }
            return fields;
        }
    },
    methods: {
        async exportNonViable() {
            let params = new URLSearchParams({
                include: this.include,
                non_viable: true,
                export: true
            });

            if(this.search !== '') {
                params.append('search', this.search)
            }

            if(this.country) {
                params.append('country_id', this.country.id)
            }

            const link = document.createElement('a');
            link.href = '/daily-pricing/export?' + params.toString();
            link.download = 'daily_pricing_export.csv';
            link.click();
        },
        replaceDailyPricing() {
            const loading = this.$loading.show();
            const url = `/api/daily-pricing/${this.replacingId}`
            this.$http.put(url, {replace: this.replace}).then(response => {
                loading.hide();
                this.$refs['table'].refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        openReplaceModal(id){
            this.replacingId = id
            this.replace = {};
            this.$refs['replace-modal'].show();
        },
        updateDailyPricing(pricing) {
            const loading = this.$loading.show();
            const url = `/api/daily-pricing/${pricing.id}`
            this.$http.put(url, pricing).then(response => {
                loading.hide();
                this.$refs['table'].refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        items(ctx) {
            const payload = {
                include: this.include,
                search: this.search,
                country_id: this.country?this.country.id:null,
            }
            return this.$paginatedFetch(
                '/api/daily-pricing',
                this.pagination,
                ctx, payload
            )
        },
        currencySymbol(country) {
            if (country && country.currency) {
                return getSymbolFromCurrency(country.currency.code)
            }
            return '';
        },
    },
    data(){
        return {
            pagination: {
                total: 0,
                perPage: 25,
                currentPage: 1,
            },
            search: '',
            country: null,
            replace: {}
        }
    }
}
</script>

<style scoped>

</style>