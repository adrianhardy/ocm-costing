<template>
    <b-row class="w-100" no-gutters>
        <b-col cols="6">
            <div class="d-flex">
                <b-pagination
                    class="mb-0"
                    limit="10"
                    align="left"
                    v-model="value.currentPage"
                    :total-rows="value.total"
                    :per-page="value.perPage"
                    aria-controls="my-table"
                />
                <b-select style="width: 10em"
                    :options="pageSizes"
                    v-model="value.perPage"
                    @input="$emit('input', value)"
                />
            </div>
        </b-col>
        <b-col cols="6">
            <b-button-toolbar class="justify-content-end">
                <slot />
            </b-button-toolbar>
        </b-col>
    </b-row>
</template>
<script>
export default {
    name: 'header',
    props: ['value'],
    computed: {
        pageSizes() {
            return [
                {value: 100, text: '100 rows'},
                {value: 50, text: '50 rows'},
                {value: 25, text: '25 rows'},
                {value: 15, text: '15 rows'},
                {value: 2, text: '2 rows'}
            ];
        }
    }
}
</script>
