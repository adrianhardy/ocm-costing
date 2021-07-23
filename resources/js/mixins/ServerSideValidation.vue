<script>
export default {
    data() {
        return {
            errors: {}
        }
    },
    computed: {
        hasErrors() {
            return typeof this.errors != 'undefined' && Object.keys(this.errors).length > 0;
        }
    },
    methods: {
        // return null/false if the field is valid/invalid
        bvState(field) {
            return this.isValid(field)?null:false;
        },
        invalidMsg(field) {
            if (typeof this.errors == 'undefined') {
                return null;
            }
            let error = this.errors[field];
            return error?error[0]:'';
        },
        isValid(field) {

            if (!this.hasErrors) {
                return true;
            }

            return this.errors && !this.errors[field] ||
                (this.errors[field] &&
                    Object.keys(this.errors[field]).length < 1);
        },
        $handleExceptionResponse(error, popup) {
            console.log(popup, error.response.data.message, error.response.status);
            if (popup && error.response.data.message && error.response.status !== 422) {
                this.$bvModal.msgBoxOk(error.response.data.message, {
                    title: 'Unhandled Error'
                });
            }
            if (popup && error.response.data.errors) {
                let message = '';

                if (!error.response.data.errors) {
                    message = error.response.data.message;
                } else {
                    const errors = error.response.data.errors
                    let messages = [];
                    const fields = Object.keys(errors);
                    for (const field in fields) {
                        const msg = errors[fields[field]][0];
                        messages.push(this.$createElement('div', msg));
                    }
                    this.$bvModal.msgBoxOk(messages, {
                        title: 'Invalid data submitted'
                    });
                }
            }

            this.errors = error.response.data.errors;
        }
    }
}
</script>
