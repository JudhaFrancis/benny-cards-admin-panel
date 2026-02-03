<template>
  <div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <label
        v-for="shop in shops"
        :key="shop"
        class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
      >
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span class="text-xs font-bold text-slate-700">{{ shop }}</span>
      </label>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Place Name</label>
      <input
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        placeholder="Enter place name"
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

const shops = ["NGL Shop", "Marthandam Shop", "TVL Shop", "Chennai Shop"];
</script>
