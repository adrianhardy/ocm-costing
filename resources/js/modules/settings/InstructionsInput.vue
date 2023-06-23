<template>
    <b-card>
        <h3>Pricing Export Instructions</h3>
        <b-input-group label="Instructions">
            <b-form-textarea rows="4" v-model="instructions"/>
        </b-input-group>
        <div class="mt-2 d-flex justify-content-end">
            <b-button @click="updateInstructions" variant="success">
                Update
            </b-button>
        </div>
    </b-card>
</template>

<script>
export default {
    name: "InstructionsInput",
    mounted() {
        this.getInstructions();
    },
    methods: {
        getInstructions() {
            return this.$http.get('/api/admin/settings/instructions').then(response => {
                this.instructions = response.data.instructions;
            });
        },
        updateInstructions(){
            this.$http.post('/api/admin/settings/instructions', {instructions: this.instructions}).then(response => {
                console.log(response)
            }).then(response => {
                this.$bvToast.toast('Pricing export instructions updated', {
                    variant: 'success'
                })
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
            })
        }
    },
    data(){
        return {
            instructions: null
        }
    }
}
</script>

<style scoped>

</style>