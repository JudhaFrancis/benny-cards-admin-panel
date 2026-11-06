<template>
  <div class="flex flex-col gap-0.5">
    <!-- Customer Name -->
    <span class="text-sm font-medium text-slate-900">{{
      order.customer_details?.name || "N/A"
    }}</span>

    <!-- Product Name with Truncation & Tooltip -->
    <div v-if="displayProduct" class="flex items-center gap-1">
      <AppTooltip v-if="needsTooltip" :content="fullProductName" position="top">
        <template #trigger>
          <span class="text-xs text-slate-500 truncate cursor-help border-b border-dotted border-slate-400">
            {{ displayProduct }}
          </span>
        </template>
      </AppTooltip>
      <span v-else class="text-xs text-slate-500">
        {{ displayProduct }}
      </span>

      <!-- Additional products badge -->
      <span v-if="additionalCount > 0" class="text-[10px] bg-slate-100 text-slate-600 px-1.5 rounded-full">
        +{{ additionalCount }} more
      </span>
    </div>
    <span v-else class="text-xs text-slate-400 italic">No items</span>

    <!-- Quantity -->
    <span class="text-[10px] text-slate-500 font-medium mt-0.5">
      Qty: <span class="font-bold text-slate-700">{{ order.total_quantity || 0 }}</span>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import AppTooltip from '../../ui/display/AppTooltip.vue';

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
});

const items = computed(() => props.order.items || []);
const firstItem = computed(() => items.value[0]);
const additionalCount = computed(() => Math.max(0, items.value.length - 1));

const fullProductName = computed(() => firstItem.value?.product_name || "");

const displayProduct = computed(() => {
  const name = fullProductName.value;
  if (!name) return "";
  if (name.length > 25) {
    return name.substring(0, 25) + "...";
  }
  return name;
});

const needsTooltip = computed(() => fullProductName.value.length > 25);
</script>
