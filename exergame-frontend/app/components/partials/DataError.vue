<template>
  <div class="alert-warning">
    {{ error }}<br />
    <a href="#" @click="$emit('retry')">Try Again</a>
  </div>
</template>

<script setup>
defineProps({
  error: {
    type: String,
    default: "An error occurred while fetching data.",
  },
});

defineEmits(["retry"])
</script>
