<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order No</label>
      <input
        :value="order.id"
        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        readonly
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order Date</label>
      <input
        type="date"
        :value="order.orderDate"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order Taken By</label>
      <input
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        placeholder="Enter name"
      />
    </div>
    <div class="space-y-2 md:col-span-2">
      <label class="text-sm font-medium text-slate-700 block mb-1"
        >Order Placed In</label
      >
      <div class="flex flex-wrap gap-4">
        <label
          v-for="place in ['NGL', 'MTM', 'TVL', 'Chennai', 'Online']"
          :key="place"
          class="flex items-center gap-2.5 cursor-pointer group"
        >
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20 transition-all"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >{{ place }}</span
          >
        </label>
      </div>
    </div>
    <div class="space-y-2 md:col-span-2">
      <label class="text-sm font-medium text-slate-700 block mb-1"
        >Reference</label
      >
      <div class="flex flex-wrap gap-4">
        <label
          v-for="ref in ['Already Client', 'Instagram', 'Walk-In', 'By Client']"
          :key="ref"
          class="flex items-center gap-2.5 cursor-pointer group"
        >
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20 transition-all"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >{{ ref }}</span
          >
        </label>
      </div>
      <input
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all mt-3"
        placeholder="Other reference..."
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
