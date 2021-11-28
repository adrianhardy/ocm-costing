<template>
    <vue-select :clearable="false" :disabled="readonly" :class="size === 'sm'?'small':''" ref="select" :value="value" @input="$emit('selected', $event) && $emit('input', $event)" :options="options" :filterable="false" @search="fetch" label="id">
        <template #option="option">
            <div class="d-flex truncated-result-entry">
                <div class="flex-column mr-2">
                    <b-avatar :src="option | product_image_url" />
                    <div class="text-center">
                        <span v-if="option.unit_price > 0">
                            {{ option.unit_price | 2dp_value }}
                        </span>
                    </div>
                </div>
                <div class="flex-column">
                    <div class="small">{{ option.item_number }} / {{ option.article_number }}</div>
                    <div>
                        {{ option.name }}
                    </div>
                </div>
            </div>
        </template>
        <template slot="selected-option" slot-scope="data">
            <div>{{ data.name | dotdotdot(30) }}</div>
        </template>
    </vue-select>
</template>

<script>
import {VueSelect} from 'vue-select';
export default {
    name: "ProductSelect",
    components: {VueSelect},
    props: {
        salesOrderId: Number,
        'readonly': Boolean,
        'size': String,
        'value': [Object, String, Array],
        factoryId: Number
    },
    methods: {
        debounceFetch: _.debounce(async (search, loading, vm) => {
            if (search.length < 2) return;

            const params = {
                sales_order_id: this.salesOrderId,
                search: search,
                page: 1,
                per_page: 50,
                include: 'media',
                factory_id: this.factoryId
            };

            loading(true);
            let response = await axios.get('/api/products/', {params});
            vm.options = response.data.items;
            loading(false);
        }, 250),
        fetch(search, loading) {
            this.debounceFetch(search, loading, this);
        },
    },
    data() {
        return {
            options: [],
        }
    }
}
</script>

<style scoped>
.truncated-result-entry {
    text-overflow: ellipsis;
    white-space: normal;
    overflow: hidden;
}
</style>
