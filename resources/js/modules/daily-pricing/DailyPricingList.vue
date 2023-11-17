<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-input-group class="w-30">
                            <b-form-datepicker v-model="nonViableStartDate"
                               placeholder="All Dates"
                               style="width: 8em"
                               :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"
                            />
                            <b-button v-if="canExport" variant="primary" class="mr-2" @click="exportNonViable">
                                Export Non Viable
                            </b-button>
                        </b-input-group>
                        <country-select placeholder="Country" use-code amazon class="mr-2 w-15" @input="$refs['table'].refresh()" v-model="country" />
                        <search-input class="w-15" v-model="search" @search="$refs['table'].refresh()"/>
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
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(new_suggested_price)="data">
                    <b-input-group :prepend="currencySymbol(data.item.country)">
                        <b-form-input
                            class="border-success"
                            @blur="checkViable(data.item)"
                            v-model.number="data.item.new_suggested_price"
                        ></b-form-input>
                    </b-input-group>
                </template>
                <template #cell(new_fba_fee)="data">
                    <b-input-group :prepend="currencySymbol(data.item.country)">
                        <b-form-input
                            class="border-success"
                            @blur="checkViable(data.item)"
                            v-model.number="data.item.new_fba_fee"
                        ></b-form-input>
                        <b-input-group-append is-text v-if="data.item.new_suggested_price && data.item.new_suggested_profit">
                            <b-icon-circle-fill scale="0.5" variant="success" v-if="data.item.new_suggested_is_viable"/>
                            <b-icon-circle-fill scale="0.5" variant="danger" v-else></b-icon-circle-fill>
                        </b-input-group-append>
                    </b-input-group>
                </template>
                <template #cell(action)="data">
                    <b-button block size="sm" @click="updateDailyPricing(data.item)" variant="primary">
                        Submit
                    </b-button>
                </template>
                <template #cell(submitted_at)="data">
                    <div :id="`checked-${data.item.id}`">
                        {{ new Date(data.item.submitted_at) | date('dd/MM/yy HH:mm') }}
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
                                    {{ currencySymbol(data.item.country) }}{{ history.suggested_price?history.suggested_price.toFixed(2):'' }}
                                </td>
                                <td>
                                    {{ currencySymbol(data.item.country) }}{{ history.fba_fee?history.fba_fee.toFixed(2):'' }}
                                </td>
                                <td>
                                    <b-icon-circle-fill variant="success" v-if="history.is_viable"/>
                                    <b-icon-circle-fill variant="danger" v-else></b-icon-circle-fill>
                                </td>
                            </tr>
                        </table>
                    </b-popover>
                </template>
                <template #cell(product.name)="data">
                    <div>{{ data.value }}</div>
                    <div class="small" v-if="data.item.product.daily_pricing_notes && data.item.product.daily_pricing_notes.length > 0">
                        <span v-b-tooltip.bottom.hover.dh2000 :title="data.item.product.daily_pricing_notes">
                            <b-icon-info-circle-fill  />
                            Daily Pricing Notes
                        </span>
                    </div>
                </template>
                <template #cell(margin)="data">
                    <span v-if="data.value">
                        {{ data.value }}%
                    </span>
                    <div v-if="data.item.new_suggested_profit">
                        <b-badge>{{ data.item.new_suggested_margin.toFixed(2) }}%</b-badge>
                    </div>
                </template>
                <template #cell(suggested_price)="data">
                    <span v-if="data.value">
                        {{ currencySymbol(data.item.country) }}{{data.value.toFixed(2)}}
                    </span>
                </template>
                <template #cell(fba_fee)="data">
                    <span v-if="data.value">
                        {{ currencySymbol(data.item.country) }}{{data.value.toFixed(2)}}
                        <b-icon-circle-fill variant="success" v-if="data.item.is_viable"/>
                        <b-icon-circle-fill variant="danger" v-else></b-icon-circle-fill>
                    </span>
                </template>

                <template #cell(local_profit)="data">
                    <div v-if="data.value">
                            {{ getLocalProfitCurrencySymbol(data.item.country) }} {{data.item.local_profit.toFixed(2)}}
                    </div>
                    <div v-if="data.item.new_suggested_profit">
                        <b-badge>
                            {{ getLocalProfitCurrencySymbol(data.item.country) }} {{ data.item.new_suggested_profit.toFixed(2) }}
                        </b-badge>
                    </div>
                </template>
                <template #cell(product.unit_price_gbp)="data">
                    <span v-if="data.value">
                        &pound;{{ data.value }}
                    </span>
                    <span v-else-if="data.item.product.current_landed_cost_per_unit_gbp">
                        &pound;{{ data.item.product.current_landed_cost_per_unit_gbp }}
                        <span class="small text-uppercase">cn</span>
                    </span>
                    <span v-else class="text-danger">NO COST</span>
                </template>
            </b-table>
        </b-card>
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
            return 'submitter,approver,country,country.currency,product,pricing_history,pricing_history.submitter,product.current_landed_cost_per_unit_gbp'
        },
        fields(){
            const fields = [
                {
                    key: 'product.article_number',
                    label: 'Item #',
                    tdClass: 'align-middle',
                    thClass: 'align-middle',
                    sortable: true
                }, {
                    key: 'product.primary_sku',
                    label: 'SKU',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle',
                    sortable: true
                }, {
                    key: 'product.primary_asin',
                    label: 'ASIN',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle',
                    sortable: true
                }, {
                    key: 'product.name',
                    label: 'Description',
                    tdClass: 'align-middle w-25',
                    thClass: 'align-middle',
                    sortable: true
                }, {
                    key: 'country.iso_2_code',
                    label: 'Mk\'t',
                    tdClass: 'align-middle w-1',
                    thClass: 'align-middle text-nowrap'
                }, {
                    key: 'product.unit_price_gbp',
                    label: 'GBP/Unit',
                    tdClass: 'align-middle w-1',
                    thClass: 'align-middle'
                }, {
                    key: 'new_suggested_price',
                    label: 'New Price',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-8'
                }, {
                    key: 'new_fba_fee',
                    label: 'New FBA Fee',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-9'
                }, {
                    key: 'action',
                    label: '',
                    tdClass: 'align-middle w-1',
                },{
                    key: 'suggested_price',
                    label: 'Price',
                    thClass: 'align-middle w-1 text-nowrap',
                    tdClass: 'align-middle text-nowrap',
                    sortable: true
                },{
                    key: 'fba_fee',
                    label: 'FBA Fee',
                    thClass: 'align-middle w-1 text-nowrap',
                    tdClass: 'align-middle text-nowrap',
                    sortable: true
                }, {
                    key: 'submitted_at',
                    label: 'Submitted At',
                    tdClass: 'align-middle text-nowrap',
                    thClass: 'align-middle w-1 text-nowrap',
                    sortable: true,
                }, {
                    key: 'submitter.initials',
                    label: 'Checker',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-1'
                },{
                    key: 'margin',
                    label: 'Margin',
                    thClass: 'align-middle w-1',
                    tdClass: 'align-middle',
                    sortable: true
                },{
                    key: 'local_profit',
                    label: 'Profit',
                    thClass: 'align-middle w-1',
                    tdClass: 'align-middle text-nowrap',
                    sortable: true
                }
            ]

            if(!this.canApprove) {
                return _.filter(fields, o =>
                    o.key !== 'product.unit_price_gbp' &&
                    o.key !== 'margin' &&
                    o.key !== 'local_profit'
                );
            }
            return fields;
        }
    },
    methods: {
        /**
         * Send a dummy check to the API, get it to fill out the daily pricing's
         * viability, profit and margin, and we'll use that to red/green the
         * suggested price...
         * @param pricing
         */
        checkViable(pricing) {
            if (!(pricing.new_suggested_price && pricing.new_fba_fee)) {

                const oldPrice = pricing.new_suggested_price;
                pricing.new_suggested_price = 0;
                pricing.new_suggested_margin = null;
                pricing.new_suggested_profit = null;
                pricing.new_suggested_is_viable = null;
                // trigger a repaint
                pricing.new_suggested_price = oldPrice;
                return;
            }
            const url = `/api/daily-pricing/${pricing.id}`;
            const payload = {
                suggested_price: pricing.new_suggested_price,
                suggested_fba_fee: pricing.new_fba_fee,
            }
            this.$http.get(url, {params: payload}).then(response => {
                pricing.new_suggested_margin = response.data.margin;
                pricing.new_suggested_profit = response.data.local_profit;
                pricing.new_suggested_is_viable = response.data.is_viable;

                // trigger a repaint
                const oldPrice = pricing.new_suggested_price;
                pricing.new_suggested_price = 0;
                pricing.new_suggested_price = oldPrice;

                console.log(pricing);


            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            });
        },
        async exportNonViable() {
            let params = new URLSearchParams({
                non_viable_submitted_since: this.nonViableStartDate,
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
        updateDailyPricing(pricing) {
            const url = `/api/daily-pricing/`;
            const payload = {
                product_id: pricing.product.id,
                country_id: pricing.country.id,
                suggested_price: pricing.new_suggested_price,
                suggested_fba_fee: pricing.new_fba_fee,
            }
            const loading = this.$loading.show();
            this.$http.post(url, payload).then(response => {
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
                ctx, payload, {
                    'product.item_number': 'products.item_number',
                    'product.name': 'products.name',
                    'product.primary_sku': 'products.primary_sku',
                    'product.primary_asin': 'products.primary_asin'
                }
            )
        },
        getLocalProfitCurrencySymbol(country) {
            if (['SE', 'PL'].indexOf(country.iso_2_code) > -1) {
                return getSymbolFromCurrency('GBP');
            }
            return this.currencySymbol(country);
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
            nonViableStartDate: null,
            pagination: {
                total: 0,
                perPage: 25,
                currentPage: 1,
            },
            search: '',
            country: null
        }
    }
}
</script>

<style scoped>

</style>