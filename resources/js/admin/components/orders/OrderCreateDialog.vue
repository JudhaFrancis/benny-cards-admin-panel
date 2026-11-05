<template>
  <OrderFormDialog
    :isOpen="isOpen"
    mode="order"
    @close="emit('close')"
    @success="emit('success', $event)"
  />
</template>

<script setup>
import OrderFormDialog from "./OrderFormDialog.vue";

defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["close", "success"]);
</script>
