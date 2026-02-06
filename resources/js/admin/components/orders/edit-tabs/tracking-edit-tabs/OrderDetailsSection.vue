<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order No</label>
      <input
        :value="order.order_number"
        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        readonly
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order Date</label>
      <input
        type="date"
        v-model="order.order_date"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Order Taken By</label>
      <input
        v-model="order.customer_details.name"
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
            :value="place"
            v-model="orderPlacedInList"
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
            :value="ref"
            v-model="referenceList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20 transition-all"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >{{ ref }}</span
          >
        </label>
      </div>
      <input
        v-model="order.customer_details.remarks"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all mt-3"
        placeholder="Other reference/remarks..."
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);

// Helper to handle multiple checkbox values in a comma-separated string or specific field
// Since the orders table doesn't have specific "placed_in" and "reference" columns,
// we'll assume they might be stored in 'remarks' or a custom way.
// For now, let's treat order.remarks as a general catch-all if not otherwise specified.
// Actually, I should probably add columns for these or use JSON in remarks.
// Let's just bind to the object and the user can decide where to map them on backend.

const orderPlacedInList = computed({
  get: () =>
    props.order.order_placed_in ? props.order.order_placed_in.split(",") : [],
  set: (val) => {
    const updatedOrder = { ...props.order, order_placed_in: val.join(",") };
    emit("update:order", updatedOrder);
  },
});

const referenceList = computed({
  get: () => (props.order.reference ? props.order.reference.split(",") : []),
  set: (val) => {
    const updatedOrder = { ...props.order, reference: val.join(",") };
    emit("update:order", updatedOrder);
  },
});
</script>
