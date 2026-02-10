<template>
  <div class="space-y-8">
    <!-- Bus Details -->
    <div
      class="p-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm space-y-8"
    >
      <div class="flex items-center gap-3 pb-2 border-b border-slate-50">
        <div class="p-2 bg-blue-50 rounded-xl">
          <BusIcon class="h-5 w-5 text-blue-600" />
        </div>
        <h4 class="font-black text-slate-800 uppercase tracking-tight">
          Bus Details
        </h4>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <HashIcon class="h-4 w-4 text-primary" />
            Bus No <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <HashIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="busDetails.bus_no"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter bus number"
            />
          </div>
        </div>

        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <ClockIcon class="h-4 w-4 text-primary" />
            Reaching Time <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <ClockIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              type="time"
              v-model="busDetails.reaching_time"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
            />
          </div>
        </div>

        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <PhoneIcon class="h-4 w-4 text-primary" />
            Contact No <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <PhoneIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="busDetails.contact_no"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter contact number"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Courier Details -->
    <div
      class="p-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm space-y-8"
    >
      <div class="flex items-center gap-3 pb-2 border-b border-slate-50">
        <div class="p-2 bg-purple-50 rounded-xl">
          <PackageIcon class="h-5 w-5 text-purple-600" />
        </div>
        <h4 class="font-black text-slate-800 uppercase tracking-tight">
          Courier Details
        </h4>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <TagIcon class="h-4 w-4 text-primary" />
            Courier Name <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <TagIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="courierDetails.name"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter courier service name"
            />
          </div>
        </div>

        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <LayersIcon class="h-4 w-4 text-primary" />
            Tracking No <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <LayersIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="courierDetails.tracking_no"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter tracking ID"
            />
          </div>
        </div>
      </div>

      <!-- WhatsApp Selection Card -->
      <label
        class="relative flex items-center gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden max-w-md"
        :class="[
          courierDetails.shared_whatsapp
            ? 'bg-[#25D366]/5 border-[#25D366] shadow-md'
            : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
        ]"
      >
        <div
          class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors shadow-sm"
          :class="
            courierDetails.shared_whatsapp
              ? 'bg-[#25D366] text-white'
              : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
          "
        >
          <MessageCircleIcon class="h-6 w-6" />
        </div>
        <div class="flex-1">
          <span
            class="block text-[10px] font-black uppercase tracking-[0.15em] mb-0.5"
            :class="
              courierDetails.shared_whatsapp
                ? 'text-[#25D366]'
                : 'text-slate-400'
            "
            >Notification Status</span
          >
          <span
            class="block text-sm font-bold"
            :class="
              courierDetails.shared_whatsapp
                ? 'text-slate-900'
                : 'text-slate-600'
            "
            >Shared in WhatsApp Group</span
          >
        </div>

        <!-- Selection Indicator -->
        <div
          class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
          :class="
            courierDetails.shared_whatsapp
              ? 'border-[#25D366] bg-[#25D366] scale-110 shadow-sm'
              : 'border-slate-200 group-hover:border-slate-300'
          "
        >
          <CheckIcon
            v-if="courierDetails.shared_whatsapp"
            class="h-3.5 w-3.5 text-white"
          />
        </div>

        <input
          type="checkbox"
          v-model="courierDetails.shared_whatsapp"
          class="absolute inset-0 opacity-0 cursor-pointer"
        />
      </label>
    </div>

    <!-- Transport Details -->
    <div
      class="p-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm space-y-8"
    >
      <div class="flex items-center gap-3 pb-2 border-b border-slate-50">
        <div class="p-2 bg-amber-50 rounded-xl">
          <TruckIcon class="h-5 w-5 text-amber-600" />
        </div>
        <h4 class="font-black text-slate-800 uppercase tracking-tight">
          Transport Details
        </h4>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <BuildingIcon class="h-4 w-4 text-primary" />
            Transport Name <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <BuildingIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="transportDetails.name"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter transport service name"
            />
          </div>
        </div>

        <div class="space-y-4">
          <label
            class="text-sm font-semibold text-slate-700 flex items-center gap-2"
          >
            <FileTextIcon class="h-4 w-4 text-primary" />
            LR Number <span class="text-red-500">*</span>
          </label>
          <div class="relative group">
            <FileTextIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="transportDetails.lr_number"
              class="w-full px-11 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              placeholder="Enter LR number"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="dispatchDetails._audit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(dispatchDetails._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ dispatchDetails._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Bus as BusIcon,
  Package as PackageIcon,
  Truck as TruckIcon,
  Clock as ClockIcon,
  Hash as HashIcon,
  Phone as PhoneIcon,
  Tag as TagIcon,
  Layers as LayersIcon,
  MessageCircle as MessageCircleIcon,
  Check as CheckIcon,
  Building as BuildingIcon,
  FileText as FileTextIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);

const dispatchDetails = computed(() => {
  if (!props.order.tracking.dispatch_details) {
    props.order.tracking.dispatch_details = {};
  }
  return props.order.tracking.dispatch_details;
});

const busDetails = computed(() => {
  if (!dispatchDetails.value.bus) {
    dispatchDetails.value.bus = {};
  }
  return dispatchDetails.value.bus;
});

const courierDetails = computed(() => {
  if (!dispatchDetails.value.courier) {
    dispatchDetails.value.courier = {};
  }
  return dispatchDetails.value.courier;
});

const transportDetails = computed(() => {
  if (!dispatchDetails.value.transport) {
    dispatchDetails.value.transport = {};
  }
  return dispatchDetails.value.transport;
});

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
