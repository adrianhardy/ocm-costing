<template>
    <div>
        <b-card class="shadow-sm">
            <slot name="title"/>
            <div class="row">
                <div class="col-12 col-md-6 offset-md-3">
                    <b-form-group label="Name" description="The full currency name, e.g. Pound Sterling">
                        <b-form-input v-model="name"/>
                    </b-form-group>
                    <b-form-group description="The ISO 4217 approved, 3 letter currency code">
                        <template #label>
                            Code <span class="small text-danger">(Please note that once set, this cannot be changed)</span>
                        </template>
                        <b-form-input v-model="code"/>
                    </b-form-group>
                    <b-form-group label="FX Rate to GBP" description="How many of these does 1 GBP get you">
                        <b-form-input v-model="fx"/>
                    </b-form-group>
                </div>
            </div>
            <b-button class="float-right" variant="primary" @click="save()">Save</b-button>
        </b-card>
    </div>
</template>

<script>

export default {
    name: "CreateCurrency",
    props: {
        redirectUrl: String
    },
    methods: {
        save() {
            const loader = this.$loading.show();
            const payload = {
                name: this.name,
                code: this.code,
                fx: this.fx
            };
            this.$http('/api/currencies', {
                method: 'POST',
                data: payload
            }).then(() => {
                window.location.href = this.redirectUrl
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true)
            })
        }
    },
    data() {
        return {
            name: '',
            code: '',
            fx: 0
        }
    }
}
</script>

<style scoped>

</style>
