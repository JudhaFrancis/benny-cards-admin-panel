<template>
  <OrderFormDialog
    :isOpen="isOpen"
    mode="enquiry"
    :enquiry="enquiry"
    @close="emit('close')"
    @success="handleSuccess"
  />
</template>

<script setup>
import OrderFormDialog from "../orders/OrderFormDialog.vue";

/**
 * Create or edit an enquiry using the same form as Create Order.
 * Pass `enquiry` to edit a pending enquiry; omit it to create a new one.
 */
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  enquiry: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "created", "updated"]);

const handleSuccess = (data) => {
  emit(props.enquiry?.id ? "updated" : "created", data);
};
</script>
