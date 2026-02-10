<template>
  <div class="space-y-8">
    <!-- Dispatch Mode Selection -->
    <div class="space-y-4">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <TruckIcon class="h-4 w-4 text-primary" />
        Mode of Dispatch <span class="text-red-500">*</span>
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <label
          v-for="mode in modeOptions"
          :key="mode.id"
          class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden"
          :class="[
            dispatchMode.modes === mode.id
              ? 'bg-primary/5 border-primary shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm"
            :class="
              dispatchMode.modes === mode.id
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <component :is="mode.icon" class="h-5 w-5" />
          </div>
          <div class="flex-1">
            <span
              class="block text-xs font-black uppercase tracking-wider"
              :class="
                dispatchMode.modes === mode.id
                  ? 'text-primary'
                  : 'text-slate-700'
              "
              >{{ mode.label }}</span
            >
          </div>

          <!-- Selection Indicator -->
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              dispatchMode.modes === mode.id
                ? 'border-primary bg-primary scale-110 shadow-sm'
                : 'border-slate-200 group-hover:border-slate-300'
            "
          >
            <CheckIcon
              v-if="dispatchMode.modes === mode.id"
              class="h-3 w-3 text-white"
            />
          </div>

          <input
            type="radio"
            name="dispatch_mode"
            :value="mode.id"
            v-model="dispatchMode.modes"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="space-y-4">
        <label
          class="text-sm font-semibold text-slate-700 flex items-center gap-2"
        >
          <CalendarIcon class="h-4 w-4 text-primary" />
          Dispatch Details with Date <span class="text-red-500">*</span>
        </label>
        <div class="relative group">
          <CalendarIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="date"
            v-model="dispatchMode.date"
            class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
          />
        </div>
      </div>

      <div class="space-y-4">
        <label
          class="text-sm font-semibold text-slate-700 flex items-center gap-2"
        >
          <BanknoteIcon class="h-4 w-4 text-primary" />
          Dispatch Expense <span class="text-red-500">*</span>
        </label>
        <div class="relative group">
          <BanknoteIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="number"
            v-model="dispatchMode.expense"
            class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
            placeholder="0.00"
          />
        </div>
      </div>
    </div>

    <div class="space-y-4">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <UserIcon class="h-4 w-4 text-primary" />
        Signature & Name <span class="text-red-500">*</span>
      </label>
      <div class="relative group">
        <UserIcon
          class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
        />
        <input
          v-model="dispatchMode.signature_name"
          class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
          placeholder="Enter name"
        />
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="dispatchMode._audit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(dispatchMode._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ dispatchMode._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Truck as TruckIcon,
  Store as StoreIcon,
  Bus as BusIcon,
  Package as PackageIcon,
  Calendar as CalendarIcon,
  Banknote as BanknoteIcon,
  User as UserIcon,
  Check as CheckIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);

const dispatchMode = computed(() => {
  if (!props.order.tracking.dispatch_mode) {
    props.order.tracking.dispatch_mode = {};
  }
  return props.order.tracking.dispatch_mode;
});

const modeOptions = [
  { id: "Shop Pickup", label: "Shop Pickup", icon: StoreIcon },
  { id: "Bus", label: "Bus", icon: BusIcon },
  { id: "Transport", label: "Transport", icon: TruckIcon },
  { id: "Courier", label: "Courier", icon: PackageIcon },
];

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
