<template>
    <box :theme="this.errors.any() ? 'danger' : data.theme"
        icon="fa fa-lightbulb-o"
        :title="data.title"
        open collapsible removable
        :border="!data.solid"
        :solid="data.solid"
        :overlay="loading">
        <form @submit.prevent="onSubmit">
            <div class="row">
                <div v-for="element in data.attributes"
                        :class="data.wrapperClass">
                    <div class="form-group"
                        :class="{ 'has-error' : errors.has(element.column) }">
                        <label>
                            {{ element.label }}
                        </label>
                        <small v-if="errors.has(element.column)"
                            class="text-danger"
                            style="float:right;">
                            {{ errors.get(element.column) }}
                        </small>
                        <vue-form-input v-if="element.config.type === 'input'"
                            :element="element"
                            @update="errors.clear(element.column)">
                        </vue-form-input>
                        <vue-select v-if="element.config.type === 'select'"
                            v-model="element.value"
                            :name="element.field"
                            @input="errors.clear(element.column)"
                            :options="element.config.options || null"
                            :source="element.config.source || null"
                            :reset="!element.config.multiple"
                            :selected="element.value"
                            :multiple="element.config.multiple">
                        </vue-select>
                        <datepicker v-if="element.config.type === 'datepicker'"
                            v-model="element.value"
                            reset
                            @input="errors.clear(element.column)">
                        </datepicker>
                        <timepicker v-if="element.config.type === 'timepicker'"
                            v-model="element.value"
                            reset
                            @input="errors.clear(element.column)">
                        </timepicker>
                    </div>
                </div>
            </div>
            <center>
                <button type="submit"
                    :disabled="errors.any()"
                    class="btn btn-primary">
                    {{ data.submit }}
                </button>
            </center>
        </form>
    </box>
</template>

<script>

    import Errors from '../pages/Errors.js';
    import Box from '../vendor/laravel-enso/components/vueadminlte/Box.vue';
    import VueSelect from '../vendor/laravel-enso/components/select/VueSelect.vue';
    import Datepicker from '../vendor/laravel-enso/components/enso/Datepicker.vue';
    import Timepicker from '../vendor/laravel-enso/components/enso/Timepicker.vue';
    import VueFormInput from './VueFormInput.vue';

    export default {
        props: {
            data: {
                type: Object,
                required: true
            }
        },

        components: { Box, VueSelect, Datepicker, Timepicker, VueFormInput },

        data() {
            return {
                loading: false,
                errors: new Errors()
            };
        },
        methods: {
            onSubmit() {
                this.loading = true;
                axios[this.data.action](this.data.url, this.formColumns()).then(response => {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }

                    this.loading = false;
                    toastr.success(response.data.message);
                    this.$emit('submit');
                }).catch(error => {
                    this.errors.set(error.response.data);
                    this.loading = false;
                });
            },
            formColumns() {
                return this.data.attributes.reduce((object, element) => {
                    object[element.column] = element.value;
                    return object;
                }, {});
            }
        },

        mounted() {}
    };

</script>