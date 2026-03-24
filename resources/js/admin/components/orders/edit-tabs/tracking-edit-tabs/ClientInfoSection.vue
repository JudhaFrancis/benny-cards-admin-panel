<template>
  <form @submit.prevent="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-slate-700"
          >Name <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <UserIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            id="name"
            v-model="clientInfo.name"
            placeholder="Client Name"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            required
          />
        </div>
      </div>

      <div class="space-y-2">
        <label for="address" class="text-sm font-medium text-slate-700"
          >Place (Address) <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <MapPinIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            id="address"
            v-model="clientInfo.address"
            placeholder="Full Address"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            required
          />
        </div>
      </div>

      <div class="space-y-2">
        <label for="phone" class="text-sm font-medium text-slate-700"
          >Contact No <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <PhoneIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            id="phone"
            v-model="clientInfo.phone"
            placeholder="Phone Number"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            required
          />
        </div>
      </div>

      <div class="space-y-2" @submit.prevent="save">
        <label for="occasion" class="text-sm font-medium text-slate-700"
          >Occasion</label
        >
        <ContextDropdown
          v-model="clientInfo.occasion"
          :options="occasionOptions"
          placeholder="Select an occasion"
          :icon="PartyPopperIcon"
        />
      </div>

      <div class="space-y-2 md:col-span-2">
        <label class="text-sm font-medium text-slate-700"
          >Expected Delivery Date <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <CalendarIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="date"
            v-model="clientInfo.expected_delivery_date"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            required
          />
        </div>
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="clientInfo._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(clientInfo._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ clientInfo._audit.updated_by }}</span
        >
      </span>
    </div>
  </form>
</template>

<script setup>
import { computed, onMounted, watch } from "vue";
import {
  User as UserIcon,
  MapPin as MapPinIcon,
  Phone as PhoneIcon,
  PartyPopper as PartyPopperIcon,
  Calendar as CalendarIcon,
  Clock as ClockIcon,
  Loader2 as Loader2Icon,
  Save as SaveIcon,
} from "lucide-vue-next";
import ContextDropdown from "../../../ui/ContextDropdown.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["save", "cancel"]);

const occasionOptions = [
  { label: "Wedding", value: "wedding" },
  { label: "Birthday", value: "birthday" },
  { label: "Engagement", value: "engagement" },
  { label: "Anniversary", value: "anniversary" },
  { label: "House Warming", value: "house_warming" },
  { label: "Other", value: "other" },
];

// Helper to access safe client_info
const clientInfo = computed(() => {
  if (!props.order.tracking) {
    props.order.tracking = {};
  }
  if (!props.order.tracking.client_info) {
    props.order.tracking.client_info = {};
  }
  return props.order.tracking.client_info;
});

const autoFill = () => {
  // Ensure the tracking object and client_info exist
  if (!props.order.tracking) {
    props.order.tracking = {};
  }
  if (!props.order.tracking.client_info) {
    props.order.tracking.client_info = {};
  }

  // Auto-fill from order customer details if available
  const cd = props.order.customer_details || props.order.customerDetails;
  if (cd) {
    const target = props.order.tracking.client_info;
    // We fill ONLY if fields are currently empty or null
    if (!target.name) {
      target.name = cd.name;
    }
    if (!target.address) {
      const addr = cd.address_1 || cd.address;
      const city = cd.city_1 || cd.city;
      const pc = cd.post_code_1 || cd.post_code;

      const addressParts = [addr, city, pc].filter(Boolean);
      if (addressParts.length > 0) {
        target.address = addressParts.join(", ");
      }
    }
    if (!target.phone) {
      target.phone = cd.phone;
    }
  }
};

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

onMounted(() => {
  autoFill();
});

// Watch for changes in the order object
watch(
  () => props.order,
  () => {
    autoFill();
  },
  { deep: true },
);

const save = () => {
  // Pass the data up to the parent component
  emit("save", props.order.tracking);
};
</script>
