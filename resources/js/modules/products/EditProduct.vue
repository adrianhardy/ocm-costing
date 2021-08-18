<template>
    <div>
        <b-card class="shadow-sm">
            <div class="row">
                <div class="col-12 col-md-6">
                    <b-form-group label="Name">
                        <b-form-input v-model="product.name" required="required"/>
                    </b-form-group>
                    <b-form-group label="Supplier">
                        <supplier-select all v-model="product.supplier"/>
                    </b-form-group>
                    <div class="row">
                        <div class="col-6">
                            <b-form-group label="Item Number">
                                <b-form-input all v-model="product.item_number"/>
                            </b-form-group>
                        </div>
                        <div class="col-6">
                            <b-form-group label="Article Number">
                                <b-form-input all v-model="product.article_number"/>
                            </b-form-group>
                        </div>
                    </div>
                    <h5>Images</h5>
                    <edit-product-media :product="product" @updated="refresh()"/>
                </div>
                <div class="col-12 col-md-6">
                    <b-tabs content-class="pt-3">
                        <b-tab title="Product">
                            <div class="row">
                                <div class="col-6">
                                    <b-form-group label="Case Qty">
                                        <b-form-input v-model="product.case_qty"/>
                                    </b-form-group>
                                </div>
                                <div class="col-6">
                                    <b-form-group label="Inner Qty">
                                        <b-form-input v-model="product.inner_qty"/>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <b-form-group label="Length (cm)">
                                        <b-form-input v-model="product.length_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Width (cm)">
                                        <b-form-input v-model="product.width_cm"/>
                                    </b-form-group>
                                </div>
                                <div class="col-4">
                                    <b-form-group label="Height (cm)">
                                        <b-form-input v-model="product.height_cm"/>
                                    </b-form-group>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <b-form-group label="Case Weight (kg)">
                                        <b-form-input v-model="product.case_weight_kg"/>
                                    </b-form-group>
                                </div>
                                <div class="col-6">
                                    <b-form-group label="Unit Price ($)">
                                        <b-form-input v-model="product.unit_price"/>
                                    </b-form-group>
                                </div>
                            </div>
                            <b-form-group label="Product Description">
                                <b-textarea v-model="product.product_description" maxlength="500"/>
                                <template #description>
                                    <span v-if="product.product_description">
                                        {{ product.product_description.length }} / 500 characters
                                    </span>
                                    <span v-else>0 / 500</span>
                                </template>
                            </b-form-group>
                            <b-form-group label="Packing Instructions">
                                <b-textarea v-model="product.packing_instructions" maxlength="500"/>
                                <template #description>
                                    <span v-if="product.packing_instructions">
                                        {{ product.packing_instructions.length }} / 500 characters
                                    </span>
                                    <span v-else>0 / 500</span>
                                </template>
                            </b-form-group>


                            <b-button variant="outline-primary float-right" @click="save()">Update Product</b-button>
                        </b-tab>
                        <b-tab title="Notes">
                            <b-table-simple small>
                                <b-thead>
                                    <b-tr>
                                        <b-th class="w-25">Created</b-th>
                                        <b-th>Note</b-th>
                                    </b-tr>
                                </b-thead>
                                <b-tr v-if="product.comments.length < 1">
                                    <b-td colspan="2" class="text-center">No Notes Added</b-td>
                                </b-tr>
                                <b-tr v-for="note in product.comments" :key="note.id">
                                    <b-td>
                                        <div class="small">
                                            <span v-if="note.commentator && note.commentator.id">{{ note.commentator.name }}</span>
                                            <span v-else>System</span>
                                            <br/>
                                            <span>{{ new Date(note.created_at) | date('dd/MM/yy HH:mm') }}</span>
                                        </div>
                                    </b-td>
                                    <b-td class="align-top"> {{ note.comment }}</b-td>
                                </b-tr>
                            </b-table-simple>
                            <hr />
                            <b-form-group>
                                <b-textarea v-model="note" placeholder="Add new note... "/>
                            </b-form-group>
                            <b-button variant="outline-primary float-right" @click="addNewNote">Add Note</b-button>
                        </b-tab>
                    </b-tabs>
                </div>
            </div>
        </b-card>
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../components/SupplierSelect";
import EditProductMedia from "./EditProductMedia";

export default {
    name: "ProductsList",
    components: {EditProductMedia, SupplierSelect},
    props: {productId: Number},
    filters: {
        date: dateFilter
    },
    mounted() {
        this.refresh();
    },
    computed: {
        include() {
            return 'supplier,comments,comments.commentator,media';
        }
    },
    methods: {
        save() {
            const url = `/api/products/${this.product.id}`;
            const loader = this.$loading.show();
            const method = this.product.id?'PUT':'POST';
            this.$http({
                method: method,
                url: url,
                data: {...this.product},
                params: {include: this.include}
            }).then(response => {
                this.product = response.data
                loader.hide();
                this.refresh();
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        },
        addNewNote() {
            const url = `/api/products/${this.product.id}/comments`;
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
            this.$http.get(`/api/products/${this.productId}`, {
                params: {include: this.include}
            }).then(response => {
                this.product = response.data;
                loader.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        }
    },
    data() {
        return {
            note: '',
            product: {
                product_description: '',
                packing_instructions: '',
                comments: [],
                supplier: {}
            }
        }
    }
}
</script>

<style scoped>

</style>
