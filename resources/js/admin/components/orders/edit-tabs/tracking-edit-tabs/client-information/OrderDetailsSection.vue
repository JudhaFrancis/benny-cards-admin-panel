<template>
  <div class="space-y-8">
    <!-- Order Information Group -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
          <line x1="10" x2="21" y1="6" y2="6" />
          <line x1="10" x2="21" y1="12" y2="12" />
          <line x1="10" x2="21" y1="18" y2="18" />
          <path d="M4 6h1v4" />
          <path d="M4 10h2" />
          <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1" />
        </svg>
        Order Information
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Order No
          </label>
          <div class="relative">
            <input :value="order.order_number"
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/50 border border-slate-200 text-slate-600 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all cursor-not-allowed"
              readonly />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
              #
            </div>
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Order Date
          </label>
          <input type="date" v-model="formattedOrderDate"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" />
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Order Taken By
          </label>
          <ContextDropdown v-model="jobDetails.order_taken_by" :options="staffOptions" placeholder="Select staff"
            :icon="UserIcon" />
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Delivery Date
          </label>
          <DatePicker v-model="jobDetails.expected_delivery_date" placeholder="Select Delivery Date"
            custom-class="py-2.5 text-xs">
            <template #leading>
              <CalendarIcon class="h-4 w-4 text-slate-400" />
            </template>
          </DatePicker>
        </div>
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    <!-- Source & Reference Group -->
    <div class="space-y-6">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
          <circle cx="12" cy="12" r="10" />
          <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" />
        </svg>
        Source & Reference
      </h3>

      <div class="space-y-8">
        <!-- Order Placed In -->
        <div class="space-y-3">
          <label class="text-sm font-medium text-slate-700 flex items-center gap-2">
            Order Placed In <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <label v-for="place in ['NGL', 'MTM', 'TVL', 'Chennai', 'Online']" :key="place"
              class="relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-200 group hover:shadow-md"
              :class="jobDetails.order_placed_in === place
                  ? 'bg-primary/5 border-primary shadow-sm'
                  : 'bg-white border-slate-200 hover:border-primary/50'
                ">
              <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors" :class="jobDetails.order_placed_in === place
                  ? 'bg-primary border-primary text-white'
                  : 'border-slate-300 bg-white group-hover:border-primary'
                ">
                <div v-if="jobDetails.order_placed_in === place" class="w-2 h-2 rounded-full bg-white"></div>
              </div>
              <span class="text-xs font-medium" :class="jobDetails.order_placed_in === place
                  ? 'text-primary'
                  : 'text-slate-600'
                ">
                {{ place }}
              </span>
              <input type="radio" :value="place" v-model="jobDetails.order_placed_in"
                class="absolute inset-0 opacity-0 cursor-pointer" />
            </label>
          </div>
        </div>

        <!-- Reference -->
        <div class="space-y-3">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Reference <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <label v-for="ref in [
              'Already Client',
              'Instagram',
              'Walk-In',
              'By Client',
              'Other',
            ]" :key="ref"
              class="relative flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-200 group hover:shadow-md"
              :class="jobDetails.reference === ref
                  ? 'bg-emerald-50 border-emerald-500 shadow-sm'
                  : 'bg-white border-slate-200 hover:border-emerald-500/50'
                ">
              <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors" :class="jobDetails.reference === ref
                  ? 'bg-emerald-500 border-emerald-500 text-white'
                  : 'border-slate-300 bg-white group-hover:border-emerald-500'
                ">
                <div v-if="jobDetails.reference === ref" class="w-2 h-2 rounded-full bg-white"></div>
              </div>
              <span class="text-xs font-medium" :class="jobDetails.reference === ref
                  ? 'text-emerald-700'
                  : 'text-slate-600'
                ">
                {{ ref }}
              </span>
              <input type="radio" :value="ref" v-model="jobDetails.reference"
                class="absolute inset-0 opacity-0 cursor-pointer" />
            </label>
          </div>

          <div v-if="jobDetails.reference === 'Other' || jobDetails.remarks" class="space-y-2 pt-2">
            <label class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider ml-1">
              Other Reference / Remarks
            </label>
            <div class="relative">
              <input v-model="jobDetails.remarks"
                class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50/30 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-sm"
                placeholder="Enter details here..." />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Footer -->
    <div v-if="jobDetails._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400">
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600">{{
          formatAuditDate(jobDetails._audit.updated_at) }}</span>
        by
        <span class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2">{{
          jobDetails._audit.updated_by }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { User as UserIcon, Clock as ClockIcon, Calendar as CalendarIcon } from "lucide-vue-next";
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const jobDetails = computed(() => {
  if (!props.order.client_information) {
    props.order.client_information = { job_details: {} };
  }
  if (!props.order.client_information.job_details) {
    props.order.client_information.job_details = {};
  }
  return props.order.client_information.job_details;
});

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const d = date.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};

const formattedOrderDate = computed({
  get: () => {
    if (!props.order.order_date) return "";
    // Handle both "YYYY-MM-DD HH:mm:ss" and "YYYY-MM-DDTHH:mm:ss.sssZ"
    // safely return the first 10 chars "YYYY-MM-DD"
    return String(props.order.order_date).substring(0, 10);
  },
  set: (val) => {
    // val is YYYY-MM-DD from the input
    props.order.order_date = val;
  },
});
</script>
