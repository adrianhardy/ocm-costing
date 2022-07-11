<template>
    <div>
        <b-card class="shadow-sm">
            <h4>Documents</h4>
            <div class="mb-2 d-flex justify-content-between">
                <table-header v-model="pagination">
                    <slot>
                        <b-select class="w-25" v-model="type" @change="$refs['table'].refresh()">
                            <b-select-option value="">All</b-select-option>
                            <b-select-option value="shipment">Shipment</b-select-option>
                            <b-select-option value="factory">Factory</b-select-option>
                            <b-select-option value="supplier">Supplier</b-select-option>
                        </b-select>
                        <search-input @search="$refs['table'].refresh()" v-model="search" />
                    </slot>
                </table-header>
            </div>
            <b-table
                ref="table"
                show-empty
                empty-text="No Documents"
                :items="items"
                :fields="fields"
                :per-page="pagination.perPage"
                :current-page="pagination.currentPage"
            >
                <template #cell(created_at)="data">
                    <span>{{ data.value | date_dmY }}</span>
                </template>
                <template #cell(model_type)="data">
                    <span v-if="data.item.model_type === 'App\\Models\\Consignment'">
                        Shipment
                    </span>
                    <span v-if="data.item.model_type === 'App\\Models\\Factory'">
                        Factory
                    </span>
                    <span v-if="data.item.model_type === 'App\\Models\\Supplier'">
                        Supplier
                    </span>
                </template>
                <template #cell(model.name)="data">
                    <b-link :href="`/shipments/${data.item.model.id}`" v-if="data.item.model_type === 'App\\Models\\Consignment'">
                        {{data.item.model.reference}}
                    </b-link>
                    <b-link :href="`/admin/factories/${data.item.model.id}/edit `" v-if="data.item.model_type === 'App\\Models\\Factory'">
                        {{ data.item.model.name }}
                    </b-link>
                    <b-link :href="`/admin/suppliers/${data.item.model.id}/edit`" v-if="data.item.model_type === 'App\\Models\\Supplier'">
                        {{data.item.model.name}}
                    </b-link>
                </template>
                <template #cell(name)="data">
                    <b-link :href="data.item.url">
                        {{data.value}}
                    </b-link>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import SearchInput from "../../components/common/SearchInput";
import TableHeader from "../../components/table/TableHeader";

export default {
    name: "ListDocuments",
    components: {SearchInput, TableHeader},
    computed: {
        include(){
            return 'model'
        },
        fields(){
            return [
                {key: 'created_at', label: 'Created', sortable: true},
                {key: 'name', label: 'Filename', tdClass: 'w-50', sortable: true},
                {key: 'model_type', label: 'type', tdClass: 'w-5'},
                {key: 'model.name', label: 'Source', tdClass: 'w-50'},
            ]
        }
    },
    methods: {
        items(ctx){
            const payload = {search: this.search, include: this.include, type:this.type};
            return this.$paginatedFetch(
                '/api/admin/documents',
                this.pagination,
                ctx, payload
            )
        }
    },
    data(){
        return {
            search: '',
            type: '',
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
                orderBy: 'desc'
            },
        }
    }
}
</script>

<style scoped>

</style>
