<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Name</label
      >
      <input
        :value="order.customer"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Place</label
      >
      <input
        :value="order.customerDetails?.city"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Contact No</label
      >
      <input
        :value="order.customerDetails?.mobile"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Occasion</label
      >
      <input
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        placeholder="Wedding, Birthday, etc."
      />
    </div>
    <div class="space-y-2 md:col-span-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Expected Delivery Date</label
      >
      <input
        type="date"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
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
