<template>
    <div>
        <b-card header="Drag and drop files to upload here" class="mt-3" body-class="p-0">
            <label for="file-upload-input" class="d-block p-0 m-0">
                <b-card-body>
                    <div @drop.prevent="addFile" @dragover.prevent class="d-flex justify-content-between align-items-center">
                        <div>
                            Please drag and drop your file here, or click to select a file
                        </div>
                        <b-button size="lg" variant="primary" @click="start" :disabled="!files || !files.length >= 1">Upload</b-button>
                    </div>
                    <b-form-checkbox class="mt-2" v-model="skip_first_row" value="1" unchecked-value="0">Skip First Row?</b-form-checkbox>
                </b-card-body>
                <input
                    :disabled="queue.length > 0"
                    type="file"
                    id="file-upload-input"
                    class="d-none"
                    @change="addFile"
                    ref="input"
                    accept="text/csv"
                />
            </label>
            <b-list-group>
                <b-list-group-item v-for="(file, i) in files" :key="i">
                    <div class="d-flex align-items-center">
                        <div>
                        <span class="h2">
                            <b-icon-check v-if="file.uploaded" class="mr-3" />
                            <b-spinner v-else-if="file.uploading" class="mr-3" />
                            <b-icon v-else icon="image" class="mr-3" />
                        </span>
                        </div>
                        <div class="flex-grow-1">
                            <div>{{ file.name }} <small>{{ file.size | prettyBytes }}</small></div>
                            <b-progress height="5px" :max="100" :value="(file.loaded / file.size)*100" />
                        </div>
                    </div>
                </b-list-group-item>
            </b-list-group>
        </b-card>
        <b-card v-if="log.length >= 1" class="mt-4" header="Logs">
            <b-table
                :items="log"
                :fields="[
                    {key: 'datetime', label: 'Date', thClass: 'w-25'},
                    {key: 'message', thClass: 'w-75'},
                ]"
            >
                <template #cell(datetime)="data">
                    <span>{{new Date(data.value) | date('dd/MM/yy HH:mm')}}</span>
                </template>
            </b-table>
        </b-card>
        <div v-else class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Template</h5>
            </div>
            <div class="card-body">
                <code class="">
                    item_number,
                    article_number,
                    product_description,
                    name,
                    barcode,
                    inner_qty,
                    case_weight_kg,
                    height_m,
                    length_m,
                    width_m,
                    units_per_case,
                    unit_weight_kg,
                    unit_length_m,
                    unit_width_m,
                    primary_sku,
                    reorder_number,
                    eta,
                    unit_price,
                    units_requested
                </code>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OALinesImporter",
    props: {
        token: String,
        columns: {
            type: Array
        },
        orderAcknowledgementId: Number
    },
    watch: {
        queue() {
            if (this.queue.length > 0) {
                this.upload();
            }
        }
    },
    computed : {
        implodedColumns() {
            return this.columns.join(', ')
        }
    },
    filters: {
        /**
         * From https://gist.github.com/james2doyle/4aba55c22f084800c199
         * @param num
         * @returns {string}
         */
        prettyBytes(num) {
            if (typeof num !== 'number' || isNaN(num)) {
                throw new TypeError('Expected a number');
            }

            let exponent;
            let unit;
            let neg = num < 0;
            let units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            if (neg) {
                num = -num;
            }

            if (num < 1) {
                return (neg ? '-' : '') + num + ' B';
            }

            exponent = Math.min(Math.floor(Math.log(num) / Math.log(1000)), units.length - 1);
            num = (num / Math.pow(1000, exponent)).toFixed(2) * 1;
            unit = units[exponent];

            return (neg ? '-' : '') + num + ' ' + unit;
        }
    },
    methods: {
        /**
         * Open the file input
         */
        open() {
            this.$refs.input.trigger('click');
        },
        /**
         * Handle either dropped files or click/selected ones ...
         * @param e
         */
        addFile(e) {

            if (!this.files) {
                this.files = [];
            }

            let files = null;
            if (e.dataTransfer && e.dataTransfer.files) {
                files = e.dataTransfer.files;
            } else {
                files = e.target.files;
            }

            // this tip, convert FileList to array,
            // credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
            ([...files]).forEach(f => {
                this.files.push({
                    name: f.name,
                    size: f.size,
                    file: f,
                    loaded: 0,
                    uploaded: false,
                    uploading: false
                });
            });
        },
        removeFile(index){
            this.files.slice(index, 1);
        },

        /**
         * Kickstart the queued upload
         */
        start() {
            this.queue = [];
            this.files.forEach(file => {
                // push the raw JS file object onto the queue, the queue
                // watcher will see the length change and then call
                // upload()
                this.queue.push(file.file);
            });
        },
        /**
         * Run through the queue, one at a time
         */
        upload() {
            /*
            const timer = setInterval(() => {
                this.$http.get('/check', {
                    params: { UPLOAD_IDENTIFIER: this.token}
                }).then(response => {
                    console.log(response.data);
                })
            }, 1000);
            */
            const position = this.files.length - this.queue.length;
            const currentFile = this.files[position];
            currentFile.uploading = true;

            let formData = new FormData();
            formData.append('file', this.queue[0]);
            formData.append('skip', this.skip_first_row);
            axios.post(`/order-acknowledgements/${this.orderAcknowledgementId}/import-lines`, formData, {
                onUploadProgress: event => {
                    if (event.loaded && event.loaded > 0) {
                        currentFile.loaded = event.loaded;
                    }
                }
            }).then(response => {
                currentFile.uploading = false;
                currentFile.uploaded = true;
                currentFile.loaded = currentFile.size;
                this.queue.shift();

                if (this.queue.length < 1) {
                    this.$bvModal.msgBoxOk("All files have been uploaded", {
                        title: 'Congrats',
                        centered: true
                    });
                }
                if (response.data) {
                    this.log = response.data.log
                }


            }).catch(error => {

            });
        }
    },
    data() {
        return {
            queue: [],
            files: null,
            log: [],
            skip_first_row: 0,
            supplier: null,
        }
    }
}
</script>

<style scoped>

</style>