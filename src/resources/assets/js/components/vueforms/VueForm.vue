<template>
    <card :icon="data.icon"
        :title="data.title"
        :overlay="loading"
        :controls="3">
        <a slot="control-1" class="card-header-icon"
            v-if="data.actions.update && hasChanges">
            <span class="icon is-small"
                @click="setOriginal()">
                <i class="fa fa-undo"></i>
            </span>
        </a>
        <a slot="control-2" class="card-header-icon">
            <span class="icon is-small"
                @click="clear()">
                <i class="fa fa-eraser"></i>
            </span>
        </a>
        <a slot="control-3" class="card-header-icon">
            <button class="button is-small is-primary is-outlined"
                @click="create()"
                v-if="data.actions.create">
                {{ data.actions.create.label }}
            </button>
        </a>
        <form @submit.prevent="submit()">
            <div class="columns is-multiline">
                <div v-for="element in data.fields"
                    class="column"
                    :class="columnSize">
                    <div class="field">
                        <label class="label">
                            {{ element.label }}
                        </label>
                        <p v-if="errors.has(element.column)"
                            class="help is-danger is-pulled-right">
                            {{ errors.get(element.column) }}
                        </p>
                        <span v-if="element.meta.custom">
                            <slot :name="element.column"
                                :element="element"
                                :errors="errors">
                            </slot>
                        </span>
                        <span v-else>
                            <vue-form-input v-if="element.meta.type === 'input'"
                                :element="element"
                                @update="errors.clear(element.column)"
                                :has-error="errors.has(element.column)">
                            </vue-form-input>
                            <vue-select v-if="element.meta.type === 'select'"
                                @input="errors.clear(element.column);"
                                v-model="element.value"
                                :options="element.meta.options"
                                :source="element.meta.source"
                                :key-map="element.meta.keyMap"
                                :multiple="element.meta.multiple"
                                :disabled="element.meta.disabled">
                            </vue-select>
                            <datepicker v-if="element.meta.type === 'datepicker'"
                                @input="errors.clear(element.column)"
                                v-model="element.value"
                                :format="element.meta.format"
                                :time="element.meta.time"
                                :disabled="element.meta.disabled">
                            </datepicker>
                            <datepicker v-if="element.meta.type === 'timepicker'"
                                @input="errors.clear(element.column)"
                                v-model="element.value"
                                :format="element.meta.format"
                                time-only
                                :disabled="element.meta.disabled">
                            </datepicker>
                            <textarea v-if="element.meta.type === 'textarea'"
                                @input="errors.clear(element.column)"
                                class="textarea"
                                v-model="element.value"
                                :rows="element.meta.rows"
                                :disabled="element.meta.disabled">
                            </textarea>
                        </span>
                    </div>
                </div>
            </div>
            <center>
                <button class="button is-danger"
                    v-if="data.actions.destroy"
                    @click.prevent="showModal = true">
                    <span>{{ data.actions.destroy.label }}</span>
                </button>
                <button type="submit"
                    class="button is-success"
                    v-if="data.actions.store || data.actions.update">
                    <span v-if="data.actions.store">{{ data.actions.store.label }}</span>
                    <span v-if="data.actions.update">{{ data.actions.update.label }}</span>
                </button>
            </center>
        </form>
        <modal :show="showModal"
            @cancel-action="showModal = false"
            @commit-action="destroy()">
        </modal>
    </card>
</template>

<script>

    import Errors from '../classes/Errors.js';
    import Card from '../components/Card.vue';
    import Modal from '../components/Modal.vue';
    import VueSelect from '../select/VueSelect.vue';
    import Datepicker from '../components/Datepicker.vue';
    import VueFormInput from './VueFormInput.vue';

    export default {
        components: { Card, Modal, VueSelect, Datepicker, VueFormInput },

        props: {
            data: {
                type: Object,
                required: true
            }
        },

        computed: {
            hasChanges() {
                let self = this;

                return this.data.fields.filter((attribute, index) => {
                    if (Array.isArray(attribute.value)) {
                        return attribute.value.sort().toString() === self.originalData[index].sort().toString();
                    }

                    return !attribute.value && !self.originalData[index] ||
                        (attribute.value && self.originalData[index]
                        && attribute.value === self.originalData[index]);
                }).length !== this.data.fields.length;
            },
            columnSize() {
                return 'is-' + parseInt(12/this.data.columns);
            }
        },

        data() {
            return {
                loading: false,
                showModal: false,
                errors: new Errors(),
                originalData: this.data.fields.pluck('value')
            };
        },

        methods: {
            create() {
                this.$router.push({ path: this.data.actions.create.path });
            },
            submit() {
                this.loading = true;

                axios[this.data.method](this.getSubmitPath(), this.getFormData()).then(response => {
                    this.loading = false;
                    toastr.success(response.data.message);
                    this.errors.empty();

                    if (this.data.method === 'post') {
                        this.$bus.$emit('redirect', response.data.redirect);
                    }
                }).catch(error => {
                    this.loading = false;
                    this.reportEnsoException(error);
                }).catch(error=> {
                    this.errors.set(error.response.data);
                });
            },
            getSubmitPath() {
                return this.data.method === 'post'
                    ? this.data.actions.store.path
                    : this.data.actions.update.path
            },
            getFormData() {
                return this.data.fields.reduce((object, element) => {
                    object[element.column] = element.value;
                    return object;
                }, {});
            },
            setOriginal() {
                let self = this;

                this.data.fields.forEach((attribute, index) => {
                    attribute.value = self.originalData[index];
                });

                this.errors.empty();
            },
            clear() {
                this.data.fields.forEach(element => {
                    if (Array.isArray(element.value)) {
                        return element.value = [];
                    }

                    if (typeof(element.value) === "boolean") {
                        return element.value = false;
                    }

                    element.value = null;
                });
            },
            destroy() {
                this.showModal = false;
                this.loading = true;

                axios.delete(this.data.actions.destroy.path).then(response => {
                    this.loading = false;
                    toastr.success(response.data.message);
                    this.$bus.$bus.$emit('redirect', response.data.redirect);
                }).catch(error => {
                    this.loading = false;
                    this.reportEnsoException(error);
                });
            }
        }
    };

</script>