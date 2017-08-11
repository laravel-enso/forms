<template>
    <box :theme="this.errors.any() ? 'danger' : data.theme"
        icon="fa fa-lightbulb-o"
        :title="data.title"
        open collapsible removable
        :border="!data.solid"
        :solid="data.solid"
        :overlay="loading">
        <span slot="btn-box-tool">
            <i class="btn btn-sm fa fa-eraser"
                @click="clear()">
            </i>
        </span>
        <form :name="'form-' + _uid"
            @submit.prevent="onSubmit">
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
                        <span v-if="element.config.custom">
                            <slot :name="element.column"
                                :element="element"
                                :errors="errors">
                            </slot>
                        </span>
                        <span v-else>
                            <vue-form-input v-if="element.config.type === 'input'"
                                :element="element"
                                @update="errors.clear(element.column)">
                            </vue-form-input>
                            <vue-select v-if="element.config.type === 'select'"
                                @input="errors.clear(element.column);"
                                v-model="element.value"
                                :name="element.config.multiple ? element.column + '[]' : element.column"
                                :options="element.config.options"
                                :source="element.config.source"
                                :multiple="element.config.multiple"
                                :disabled="element.config.disabled">
                            </vue-select>
                            <datepicker v-if="element.config.type === 'datepicker'"
                                @input="errors.clear(element.column)"
                                v-model="element.value"
                                :disabled="element.config.disabled">
                            </datepicker>
                            <timepicker v-if="element.config.type === 'timepicker'"
                                @input="errors.clear(element.column)"
                                v-model="element.value"
                                :disabled="element.config.disabled">
                            </timepicker>
                            <textarea v-if="element.config.type === 'textarea'"
                                @input="errors.clear(element.column)"
                                class="form-control"
                                v-model="element.value"
                                :rows="element.config.rows"
                                :disabled="element.config.disabled">
                            </textarea>
                        </span>
                    </div>
                </div>
            </div>
            <center>
                <button type="submit"
                    :disabled="errors.any()"
                    class="btn btn-primary">
                    <span v-if="data.action === 'post'">{{ data.storeSubmit }}</span>
                    <span v-else>{{ data.updateSubmit }}</span>
                </button>
            </center>
        </form>
    </box>
</template>

<script>

    import Errors from '../../classes/Errors.js';
    import Box from '../vueadminlte/Box.vue';
    import VueSelect from '../select/VueSelect.vue';
    import Datepicker from '../enso/Datepicker.vue';
    import Timepicker from '../enso/Timepicker.vue';
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
                errors: new Errors(),
                test: ""
            };
        },
        methods: {
            onSubmit() {
                this.loading = true;
                axios[this.data.action](this.data.url, this.formData()).then(response => {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }

                    this.loading = false;
                    toastr.success(response.data.message);
                    this.$emit('submit');
                }).catch(error => {
                    if (error.response && error.response.data.level) {
                        return toastr[error.response.data.level](error.response.data.message);
                    }

                    this.errors.set(error.response.data);
                    this.loading = false;
                });
            },
            formData() {
                return this.data.attributes.reduce((object, element) => {
                    object[element.column] = element.value;
                    return object;
                }, {});
            },
            clear() {
                this.data.attributes.forEach(element => {
                    element.value = Array.isArray(element.value) ? [] : null;
                });
            }
        },

        mounted() {}
    };

</script>