<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Status Overview Card -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Packaging Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <label
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            packagingStatus.packed_with_gift
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              packagingStatus.packed_with_gift
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <GiftIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              With Gift
            </p>
            <input
              type="checkbox"
              :checked="packagingStatus.packed_with_gift"
              @change="toggleGift(true)"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              packagingStatus.packed_with_gift
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="packagingStatus.packed_with_gift"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>

        <label
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            packagingStatus.packed_without_gift
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              packagingStatus.packed_without_gift
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <PackageIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Without Gift
            </p>
            <input
              type="checkbox"
              :checked="packagingStatus.packed_without_gift"
              @change="toggleGift(false)"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              packagingStatus.packed_without_gift
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="packagingStatus.packed_without_gift"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>
      </div>
    </div>

    <!-- Assignment -->
    <div class="space-y-4">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Packed By <span class="text-red-500">*</span></label
        >
        <ContextDropdown
          v-model="packagingStatus.packed_by"
          :options="staffOptions"
          placeholder="Select staff"
          :icon="UserIcon"
        />
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="packagingStatus._audit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(packagingStatus._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ packagingStatus._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Package as PackageIcon,
  Check as CheckIcon,
  Gift as GiftIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";
import ContextDropdown from "../../../ui/ContextDropdown.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:order"]);

const packagingStatus = computed(() => {
  if (!props.order.tracking.packaging_status) {
    props.order.tracking.packaging_status = {};
  }
  return props.order.tracking.packaging_status;
});

const toggleGift = (withGift) => {
  if (withGift) {
    packagingStatus.value.packed_with_gift =
      !packagingStatus.value.packed_with_gift;
    if (packagingStatus.value.packed_with_gift) {
      packagingStatus.value.packed_without_gift = false;
    }
  } else {
    packagingStatus.value.packed_without_gift =
      !packagingStatus.value.packed_without_gift;
    if (packagingStatus.value.packed_without_gift) {
      packagingStatus.value.packed_with_gift = false;
    }
  }
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
