<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Name</label>
      <input
        v-model="order.customer_details.name"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Place (Address)</label>
      <input
        v-model="order.customer_details.address_1"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Contact No</label>
      <input
        v-model="order.customer_details.phone"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Occasion</label>
      <input
        v-model="order.occasion"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        placeholder="Wedding, Birthday, etc."
      />
    </div>
    <div class="space-y-2 md:col-span-2">
      <label class="text-sm font-medium text-slate-700"
        >Expected Delivery Date</label
      >
      <input
        type="date"
        v-model="order.expected_delivery_date"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);
</script>
