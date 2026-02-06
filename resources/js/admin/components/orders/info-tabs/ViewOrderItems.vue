<template>
  <div class="space-y-8">
    <!-- Current Items -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label
          class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2"
        >
          <PackageIcon class="h-3 w-3" /> Ordered Items
        </label>
        <span
          class="px-2 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-500"
        >
          {{ order.items?.length || 0 }} Items
        </span>
      </div>

      <div class="space-y-4">
        <div
          v-for="item in order.items"
          :key="item.id"
          class="group flex items-center gap-6 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all"
        >
          <!-- Image -->
          <div
            class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform"
          >
            <img
              v-if="item.image"
              :src="getImageSource(item.image)"
              :alt="item.product_name"
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
            <PackageIcon v-else class="h-6 w-6 text-slate-300" />
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-900 truncate text-base mb-1">
              {{ item.product_name }}
            </p>
            <div class="flex items-center gap-4">
              <p
                class="text-xs text-slate-500 font-medium bg-slate-50 px-2 py-1 rounded-lg"
              >
                ₹{{ Number(item.unit_price || 0).toFixed(2) }} / unit
              </p>
            </div>
          </div>

          <!-- Quantity & Total -->
          <div class="text-right">
            <p class="text-sm font-black text-slate-900">
              x {{ item.quantity }}
            </p>
            <p class="text-xs text-slate-500 font-bold mt-1">
              ₹{{ Number(item.total_price || 0).toFixed(2) }}
            </p>
          </div>
        </div>

        <div
          v-if="!order.items?.length"
          class="text-center py-12 px-6 rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50"
        >
          <div
            class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <PackageIcon class="h-8 w-8 text-slate-300" />
          </div>
          <p class="text-slate-500 font-medium mb-1">No items in this order</p>
        </div>
      </div>
    </div>

    <!-- Totals & Summary -->
    <div class="mt-8 border-t border-slate-100 pt-8">
      <div class="flex flex-col md:flex-row gap-8 justify-end">
        <!-- Totals Section -->
        <div
          class="w-full md:w-80 bg-slate-50 rounded-2xl p-6 border border-slate-100"
        >
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Subtotal</span>
              <span class="text-sm font-bold text-slate-900"
                >₹{{ subtotal.toFixed(2) }}</span
              >
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Discount</span>
              <span class="text-sm font-bold text-emerald-500"
                >-₹{{ Number(order.discount || 0).toFixed(2) }}</span
              >
            </div>

            <div class="h-px bg-slate-200 my-4"></div>

            <div class="flex justify-between items-center">
              <span
                class="text-base font-black text-slate-900 uppercase tracking-wide"
                >Total Due</span
              >
              <span class="text-2xl font-black text-primary"
                >₹{{ total.toFixed(2) }}</span
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Package as PackageIcon } from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const subtotal = computed(() => {
  return (props.order.items || []).reduce(
    (acc, item) => acc + (parseFloat(item.unit_price) || 0) * item.quantity,
    0,
  );
});

const total = computed(() => {
  return Math.max(0, subtotal.value - (props.order.discount || 0));
});

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
};
</script>
