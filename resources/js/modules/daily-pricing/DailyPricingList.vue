<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination" @input="$refs['table'].refresh()">
                    <slot>
                        <b-button v-if="canExport" variant="primary" class="mr-2" @click="exportNonViable">
                            Export Non Viable
                        </b-button>
                        <country-select amazon class="w-25 mr-2" @input="$refs['table'].refresh()" v-model="country"></country-select>
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
                    </slot>
                </table-header>
            </template>
            <b-table show-empty empty-text="No Daily Pricing Found" sort-by="submitted_at" :sort-desc="true" ref="table" :fields="fields" :items="items">
                <template #cell(suggested_price)="data">
                    <b-input-group :prepend="currencySymbol(data.item.country)">
                        <b-form-input
                            :readonly="data.item.approved_at !== null"
                            @change="updateDailyPricing(data.item)"
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
                    <b-input-group prepend="£">
                        <b-form-input
                            :readonly="data.item.approved_at !== null"
                            @change="updateDailyPricing(data.item)"
                            v-model="data.item.fba_fee"
                            type="number"
                        ></b-form-input>
                    </b-input-group>
                </template>
                <template #cell(last_checked_at)="data">
                    <div :id="`checked-${data.item.id}`">
                        <span v-if="data.item.last_checked_at">
                        {{ new Date(data.item.last_checked_at) | date('dd/MM/yy HH:mm') }}
                    </span>
                        <span v-else>
                        N/A
                    </span>
                    </div>
                    <b-tooltip triggers="hover" :target="`checked-${data.item.id}`">
                        <div v-for="history in data.item.pricing_history">
                            {{ new Date(history.submitted_at) | date('dd/MM/yy HH:mm') }}
                        </div>
                    </b-tooltip>
                </template>
                <template #cell(last_approved_at)="data">
                    <span v-if="data.item.last_approved_at">
                        {{new Date(data.item.last_approved_at) | date('dd/MM/yy HH:mm')}}
                    </span>
                    <span v-else>
                        N/A
                    </span>
                </template>
                <template #cell(margin)="data">
                    <span v-if="data.value">
                        {{ data.value }}%
                    </span>
                </template>
                <template #cell(profit)="data">
                    <span v-if="data.value">
                        {{ data.value }} {{ currencySymbol(data.item.country) }}
                    </span>
                </template>
                <template #cell(approver)="data">
                    <b-button :disabled="!data.item.suggested_price || !data.item.fba_fee" variant="success" @click="approveDailyPricing(data.item)" v-if="!data.item.approved_at && canApprove">
                        Approve
                    </b-button>
                    <div v-else>
                        <span v-if="data.item.approved_at">
                            {{data.item.approver.name}}
                        </span>
                        <span v-else>N/A</span>
                    </div>
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
            return 'submitter,approver,country,country.currency,product,pricing_history'
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
                    label: 'Marketplace',
                    tdClass: 'align-middle w-5',
                    thClass: 'align-middle'
                }, {
                    key: 'product.unit_price',
                    label: 'Price',
                    tdClass: 'align-middle w-1',
                    thClass: 'align-middle'
                }, {
                    key: 'suggested_price',
                    label: 'Sugg. Price',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-7'
                }, {
                    key: 'viable',
                    label: '',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-1'
                }, {
                    key: 'fba_fee',
                    label: 'FBA Fee',
                    tdClass: 'align-middle',
                    thClass: 'align-middle w-7'
                }, {
                    key: 'last_checked_at',
                    label: 'Last Checked',
                    tdClass: 'align-middle w-10',
                    thClass: 'align-middle'
                }, {
                    key: 'last_approved_at',
                    label: 'Last Apprv.',
                    tdClass: 'align-middle w-10',
                    thClass: 'align-middle'
                }, {
                    key: 'submitter.name',
                    label: 'Checker',
                    tdClass: 'align-middle w-5 ',
                    thClass: 'align-middle'
                }, {
                    key: 'approver',
                    label: 'Approver',
                    tdClass: 'align-middle w-5',
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
                return _.filter(fields, o => o.key !== 'product.unit_price' && o.key !== 'margin' && o.key !== 'profit')
            }
            return fields;
        }
    },
    methods: {
        async exportNonViable() {
            let params = new URLSearchParams({
                include: this.include,
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
        approveDailyPricing(pricing) {
            const loading = this.$loading.show();
            const url = `/api/daily-pricing/${pricing.id}`
            this.$http.put(url, {approve: true}).then(response => {
                loading.hide();
                this.$refs['table'].refresh();
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
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
        }
    }
}
</script>

<style scoped>

</style>