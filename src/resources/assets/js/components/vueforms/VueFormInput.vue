<template>

    <span>
        <div class="control has-icons-right"
            v-if="element.meta.content === 'text'">
            <input class="input"
                :class="{ 'is-danger': hasError }"
                :readonly="element.meta.readonly"
                @keydown="$emit('update')"
                v-model="element.value"
                type="text"
                :disabled="element.meta.disabled">
            <span class="icon is-small is-right"
                v-if="hasError">
                <i class="fa fa-warning"></i>
            </span>
        </div>
        <div class="control has-icons-right"
            v-if="element.meta.content === 'email'">
            <input class="input"
                :class="{ 'is-danger': hasError }"
                :readonly="element.meta.readonly"
                @keydown="$emit('update')"
                v-model="element.value"
                type="email"
                :disabled="element.meta.disabled">
            <span class="icon is-small is-right"
                v-if="hasError">
                <i class="fa fa-warning"></i>
            </span>
        </div>
        <div class="control has-icons-right"
            v-if="element.meta.content === 'number'">
            <input class="input"
                :class="{ 'is-danger': hasError }"
                :readonly="element.meta.readonly"
                @keydown="$emit('update')"
                v-model="element.value"
                type="number"
                :step="element.meta.step"
                :min="element.meta.min"
                :max="element.meta.max"
                :disabled="element.meta.disabled">
            <span class="icon is-small is-right"
                v-if="hasError">
                <i class="fa fa-warning"></i>
            </span>
        </div>
        <span v-if="element.meta.content === 'checkbox'">
            <input :id="element.column"
                class="toggle"
                :readonly="element.meta.readonly"
                @click="$emit('update')"
                v-model="element.value"
                type="checkbox"
                :disabled="element.meta.disabled">
            <label :for="element.column" class="toggle-input"><i></i></label>
        </span>
    </span>

</template>

<script>

    export default {
        props: {
            element: {
                type: Object,
                required: true
            },
            hasError: {
                type: Boolean,
                default: false
            }
        }
    }

</script>

<style>

    div.tile div.field label.switch {
        display: block;
        margin-top: 18px;
    }

</style>