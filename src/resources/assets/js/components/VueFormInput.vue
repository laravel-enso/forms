<template>

    <span>
        <input v-if="element.config.content === 'text'"
            @keydown="$emit('update')"
            :name="element.column"
            type="text"
            class="form-control"
            v-model="element.value">
        <input v-if="element.config.content === 'number'"
            @keydown="$emit('update')"
            :name="element.column"
            type="number"
            :step="element.config.step"
            :min="element.config.min"
            :max="element.config.max"
            class="form-control"
            v-model="element.value">
        <input v-if="element.config.content === 'checkbox'"
            :name="element.column"
            type="checkbox"
            @click="$emit('update')"
            class="form-control"
            v-model="element.value">
    </span>

</template>

<script>

    export default {
        props: {
            element: {
                type: Object,
                required: true
            }
        }
    }

</script>