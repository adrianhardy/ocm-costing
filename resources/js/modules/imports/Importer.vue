<template>
    <div>
        <div class="row">
            <div class="col-12 col-md-3">
                <b-form-group label="Import XLSX Product Sheet">
                    <b-form-file
                        accept=".xlsx, .xls"
                        v-model="file"
                        @input="preview()"
                        placeholder="Choose / drop a file here..."
                        drop-placeholder="Drop file here..."
                    />
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Supplier">
                    <supplier-select v-model="supplier" />
                </b-form-group>
            </div>
            <div class="col-12 col-md-3">
                <b-form-group label="Factory">
                    <factory-select v-model="factory" />
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Header Rows">
                    <b-form-input type="number" v-model.number.lazy="headerRows" debounce="500" />
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Saved Mapping">
                    <b-select @input="useSavedMapping($event)">
                        <b-select-option :value="mapping" v-for="mapping in mappingOptions" :key="mapping.id">
                            {{ mapping.name }}
                        </b-select-option>
                    </b-select>
                </b-form-group>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-2">
                <b-form-group label="Import Type" description="No updates to products, just the creation of landed costs">
                    <b-form-checkbox class="mt-2" label="Date" v-model="historic" @change="updateHistoric">Landed Costs Only</b-form-checkbox>
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Date">
                    <b-form-datepicker :disabled="historic === false" v-model="date" placeholder="Please select" :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"/>
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="FX Rate">
                    <b-form-input :disabled="historic === false" v-model="fxRate"/>
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Ship Cost Per Cu.ft.">
                    <b-input-group prepend="£">
                        <b-form-input :disabled="historic === false" v-model="cuft"/>
                    </b-input-group>
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Commission">
                    <b-input-group prepend="%">
                        <b-form-input :disabled="historic === false" v-model="commissionRate"/>
                    </b-input-group>
                </b-form-group>
            </div>
            <div class="col-12 col-md-2">
                <b-form-group label="Import Duty">
                    <b-input-group prepend="%">
                        <b-form-input :disabled="historic === false" v-model="importDuty"/>
                    </b-input-group>
                </b-form-group>
            </div>
        </div>
        <b-button class="mb-4" href="/admin/import/template/PRODUCT-IMPORT-TEMPLATE.xlsx" variant="outline-primary">Template</b-button>
        <b-card variant="primary" v-if="!quoteReference && !file" title="Quote Numbers">
            <p>
                Quote numbers are required to separate out historic pricing. An item number
                or article may be re-used and re-quoted over the years. If you upload the
                same costing document twice with the same quote number, any historic data
                will be updated.
            </p>
            <p>
                If you upload the same document with a different quote number, then the costs,
                weights, dimensions, etc will be stored separately.
            </p>
        </b-card>
        <div v-show="!this.file">Please select a CSV file to begin mapping columns</div>

        <div class="row" v-if="this.file">
            <div class="col-md-8">
                <b-table bordered class="mapping-table" small :items="sample" :fields="tableColumns">
                    <template v-for="(column, index) in tableColumns" v-slot:[`head(${column.key})`]="data">
                        <b-select size="sm" v-model="mapping[index]">
                            <b-select-option value="">[ None ]</b-select-option>
                            <b-select-option-group v-for="(options, group) in mappableColumns" :label="group" :key="group">
                                <b-select-option :value="value" v-for="(label, value) in options" :key="value">{{ label }}</b-select-option>
                            </b-select-option-group>
                        </b-select>
                    </template>
                    <template v-for="(column, index) in tableColumns" v-slot:[`cell(${column.key})`]="data">
                        {{ data.item[index] }}
                    </template>
                </b-table>
            </div>
            <div class="col-md-4">
                <div>
                    <product-previewer :code="code" :mapping="mapping" :headerRows="headerRows" :file="file" />
                </div>
            </div>
        </div>


        <div v-if="this.file" class="mt-4">
            <div class="row">
                <div class="col-6 offset-4">
                    <b-input-group prepend="Save Mapping Name As">
                        <b-form-input v-model="mappingName" placeholder="Save this mapping by giving it a name "/>
                    </b-input-group>
                </div>
                <div class="col-2">
                    <b-button block @click="submit()" variant="success">Upload</b-button>
                </div>
            </div>
        </div>
        <b-table class="w-100" v-if="completedImport.hasOwnProperty('log')"
            :items="completedImport['log']"
            :fields="[
                {key: 'datetime', label: 'Date', thClass: 'w-25'},
                {key: 'message', thClass: 'w-75'}
            ]"
        >
            <template #cell(datetime)="data">
                {{ data.value | datetime }}
            </template>
        </b-table>

    </div>
</template>

<script>

import get from 'lodash/get';
import _ from 'lodash';
import format from 'date-fns/format';
import ProductPreviewer from "./ProductPreviewer";
import SupplierSelect from "../../components/SupplierSelect";
import FactorySelect from "../../components/FactorySelect";


export default {
    name: "Importer",
    components: {FactorySelect, SupplierSelect, ProductPreviewer},
    props: {
        'code': String,
        'types': Object,
        'accounts': Array,
        'columns': Object,
        'suppliers': Array
    },
    filters: {
        datetime(value) {
            return format(new Date(value), 'HH:mm:ss')
        }
    },
    computed: {
        tableColumns() {
            let columns = [];
            for (let i = 0; i < this.columnCount; i++) {
                columns.push({
                    key: 'field_' + i,
                    tdClass: 'truncated',
                    thClass: 'truncated'
                });
            }
            return columns;
        },
        sample() {
            return this.raw;
        },
        mappableColumns() {
            return this.columns['products'];
        },
    },
    mounted() {
        this.refreshMappingList();
    },
    methods: {
        updateHistoric(){
            if(this.historic === false){
                this.date = '';
                this.fxRate = '';
                this.commissionRate = '';
                this.importDuty = '';
                this.cuft = '';
            }
        },
        updateManualMapping() {
            this.$set(this, 'mapping', JSON.parse(this.manualMapping));
        },
        useSavedMapping(event) {
            this.manualMapping = JSON.stringify(event.mapping);
            this.mappingName = event.name;
            this.updateManualMapping();
        },
        refreshMappingList() {
            this.$http.get('/api/admin/import-mappings', {
                params: {type: this.type}
            }).then(response => {
                this.mappingOptions = response.data.items;
            });
        },
        submit() {
            let formData = new FormData(); // Currently empty
            if (this.supplier && this.supplier.id) {
                formData.append('supplier[id]', this.supplier.id);
                formData.append('supplier[name]', this.supplier.name);
            }
            if (this.factory && this.factory.id) {
                formData.append('factory[id]', this.factory.id);
                formData.append('factory[name]', this.factory.name);
            }
            formData.append('mapping_name', this.mappingName);
            formData.append('csv', this.file);
            formData.append('skip_rows', this.headerRows);
            formData.append('code', this.code);
            if(this.historic){
                formData.append('historic', this.historic)
                formData.append('date', this.date);
                formData.append('fxRate', this.fxRate);
                formData.append('cuft',this.cuft);
                formData.append('commissionRate', this.commissionRate);
                formData.append('importDuty', this.importDuty);
            }
            _.each(this.mapping, (value, column) => {
                formData.append(`mapping[${column}]`, value);
            });

            const loader = this.$loading.show();

            this.$http
                .post('/admin/imports', formData, {
                    params: {include: 'log'},
                    timeout: 1000 * 60 * 5 // 5 minutes
                })
                .then(response => {
                    if (response.status === 202) {
                        this.$bvModal.msgBoxOk('Your upload has been queued successfully', {
                            title: 'Upload Queued'
                        });
                    }
                    if (response.status === 200) {
                        this.$bvModal.msgBoxOk('Your upload has completed processing', {
                            title: 'Upload Completed'
                        });
                    }
                    loader.hide();
                    this.completedImport = response.data;
                }).catch(error => {
                    loader.hide();
                    this.$handleExceptionResponse(error, true);
                });
        },
        preview() {

            if (!this.file) {
              return;
            }

            const loader = this.$loading.show();
            let formData = new FormData();
            formData.append('xlsx', this.file);
            this.$http
                .post('/admin/imports/preview', formData, {
                    params: {include: 'log',
                        code: this.code,
                        skip_rows: this.headerRows
                    },
                    timeout: 1000 * 60 * 5 // 5 minutes
                })
                .then(response => {
                    this.raw = get(this.$papa.parse(response.data, {
                        skipEmptyLines: true
                    }), "data");
                    this.columnCount = this.raw[0].length;
                    loader.hide();
                }).catch(error => {
                    this.$handleExceptionResponse(error, true);
                });
        }
    },
    data() {
        return {

            quoteReference: '',

            completedImport: {},
            headerRows: 1,
            columnCount: 0,

            mapping: {},
            mappingOptions: [],

            mappingName: '',
            manualMapping: '',
            file: null,
            raw: null,
            supplier: null,
            factory: null,
            date: '',
            fxRate: '',
            cuft: '',
            commissionRate: '',
            importDuty: '',
            historic: false

        }
    }
}
</script>

<style scoped>

    >>> .table.mapping-table {
        display: block;
        max-width: fit-content;
        margin: 0 auto;
        overflow-x: auto;
        white-space: nowrap;
        max-height: 700px
    }

    >>> .table.mapping-table td {
      padding-top: 1px;
      padding-bottom: 1px;
    }

    .horizontal-scroll {
        overflow-x: scroll;
    }
    >>> .table .truncated, .table thead th.truncated {
        min-width: 8em;
        max-width: 250px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
