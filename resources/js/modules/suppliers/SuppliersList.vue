<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <div>
                        <search-input v-model="search" placeholder="Type and press enter to search"
                                      @search="$refs['table'].refresh()"></search-input>
                    </div>
                    <slot>
                        <b-button href="/admin/suppliers/create" variant="primary">Create New Supplier</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields"
                     :items="items" sort-by="name">
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(name)="data">
                    <a :href="`/admin/suppliers/${data.item.id}/edit`">{{ data.value }}</a>
                </template>
                <template #cell(image)="data">
                    <b-avatar v-if="data.item.media[0]">
                        <b-img fluid :src="data.item.media[0].url"/>
                    </b-avatar>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import SearchInput from "../../components/common/SearchInput";

export default {
    name: "SuppliersList",
    components: {TableHeader, SearchInput},
    methods: {
        items(ctx) {
            const payload = {search: this.search, include: 'currency'}
            return this.$paginatedFetch(
                '/api/admin/suppliers',
                this.pagination,
                ctx, payload
            );
        }
    },
    data() {
        return {
            search: '',
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            loading: false,
            fields: [
                {key: 'name', label: 'Name', sortable: true},
                {key: 'country', label: 'Country'},
                {key: 'currency.code', label: 'Currency'},
                {key: 'delivery_address', label: 'Delivery Address'},
                {key: 'contact_name', label: 'Contact Name'},
                {key: 'contact_email', label: 'Contact Email'},
                {key: 'contact_phone', label: 'Contact Phone'}
            ]
        }
    }
}
</script>

<style scoped>

</style>
