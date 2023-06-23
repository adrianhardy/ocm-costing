<template>
    <b-modal ref="modal" title="Volumetric Calculator" ok-only>
        <b-form-group label="Length">
            <b-form-input v-model="length" />
        </b-form-group>
        <b-form-group label="Width">
            <b-form-input v-model="width" />
        </b-form-group>
        <b-form-group label="Height">
            <b-form-input v-model="height" />
        </b-form-group>
        <b-table-simple>
            <b-tr>
                <b-th>Volume</b-th><b-td>{{ volume }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Final</b-th><b-td>{{ answer }}</b-td>
            </b-tr>
        </b-table-simple>
    </b-modal>
</template>

<script>
export default {
    name: "VolumetricCalculator",
    computed: {
        volume() {
            return (this.length * this.width * this.height);
        },
        answer() {
            return (this.volume / 5000).toFixed(2);
        }
    },
    methods: {
        show() {
            this.$refs['modal'].show();
        }
    },
    data() {
        return {
            length: 0,
            width: 0,
            height: 0
        }
    }
}
</script>

<style scoped>

</style>