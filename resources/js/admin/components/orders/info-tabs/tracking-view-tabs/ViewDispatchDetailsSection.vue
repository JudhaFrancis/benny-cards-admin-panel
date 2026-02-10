<template>
  <div class="space-y-8">
    <!-- Bus Details -->
    <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100 space-y-6">
      <div class="flex items-center gap-3 pb-4 border-b border-blue-100/50">
        <div class="p-2 bg-blue-100 rounded-xl">
          <BusIcon class="h-5 w-5 text-blue-600" />
        </div>
        <h4 class="text-sm font-black text-blue-900 uppercase tracking-tight">
          Bus Details
        </h4>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-blue-400"
            >Bus No</label
          >
          <div class="text-sm font-bold text-blue-900">
            {{ busDetails.bus_no || "—" }}
          </div>
        </div>
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-blue-400"
            >Reaching Time</label
          >
          <div class="text-sm font-bold text-blue-900">
            {{ busDetails.reaching_time || "—" }}
          </div>
        </div>
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-blue-400"
            >Contact No</label
          >
          <div class="text-sm font-bold text-blue-900">
            {{ busDetails.contact_no || "—" }}
          </div>
        </div>
      </div>
    </div>

    <!-- Courier Details -->
    <div
      class="p-6 bg-purple-50/50 rounded-2xl border border-purple-100 space-y-6"
    >
      <div class="flex items-center gap-3 pb-4 border-b border-purple-100/50">
        <div class="p-2 bg-purple-100 rounded-xl">
          <PackageIcon class="h-5 w-5 text-purple-600" />
        </div>
        <h4 class="text-sm font-black text-purple-900 uppercase tracking-tight">
          Courier Details
        </h4>
        <div
          v-if="courierDetails.shared_whatsapp"
          class="ml-auto flex items-center gap-2 px-3 py-1 rounded-full bg-[#25D366] text-white text-[10px] font-black uppercase"
        >
          <MessageCircleIcon class="h-3 w-3" /> Shared in WhatsApp
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-purple-400"
            >Courier Name</label
          >
          <div class="text-sm font-bold text-purple-900">
            {{ courierDetails.name || "—" }}
          </div>
        </div>
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-purple-400"
            >Tracking No</label
          >
          <div class="text-sm font-bold text-purple-900">
            {{ courierDetails.tracking_no || "—" }}
          </div>
        </div>
      </div>
    </div>

    <!-- Transport Details -->
    <div
      class="p-6 bg-amber-50/50 rounded-2xl border border-amber-100 space-y-6"
    >
      <div class="flex items-center gap-3 pb-4 border-b border-amber-100/50">
        <div class="p-2 bg-amber-100 rounded-xl">
          <TruckIcon class="h-5 w-5 text-amber-600" />
        </div>
        <h4 class="text-sm font-black text-amber-900 uppercase tracking-tight">
          Transport Details
        </h4>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-amber-400"
            >Transport Name</label
          >
          <div class="text-sm font-bold text-amber-900">
            {{ transportDetails.name || "—" }}
          </div>
        </div>
        <div class="space-y-1">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-amber-400"
            >LR Number</label
          >
          <div class="text-sm font-bold text-amber-900">
            {{ transportDetails.lr_number || "—" }}
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="dispatchDetails._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          dispatchDetails._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(dispatchDetails._audit.updated_at)
        }}</span>
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
  MessageCircle as MessageCircleIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const dispatchDetails = computed(
  () => props.order.tracking?.dispatch_details || {},
);
const busDetails = computed(() => dispatchDetails.value.bus || {});
const courierDetails = computed(() => dispatchDetails.value.courier || {});
const transportDetails = computed(() => dispatchDetails.value.transport || {});

const formatAuditDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
