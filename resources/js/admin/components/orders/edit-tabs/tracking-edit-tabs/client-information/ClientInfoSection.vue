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
          :model-value="isOccasionOther ? 'other' : clientInfo.occasion"
          @update:model-value="handleOccasionChange"
          :options="occasionOptions"
          placeholder="Select an occasion"
          :icon="PartyPopperIcon"
        />
        <div v-if="isOccasionOther" class="mt-2 animate-in slide-in-from-top-1 duration-200">
          <input
            v-model="occasionOtherValue"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all placeholder:text-slate-400 shadow-sm"
            placeholder="Enter custom occasion (e.g. Puberty Function)..."
          />
        </div>
      </div>

    </div>

    <!-- Spacer to provide room for dropdowns at the bottom -->
    <div class="h-32 md:h-24"></div>

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
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";

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
  { label: "Half Saree Ceremony", value: "half_saree_ceremony" },
  { label: "Reception", value: "reception" },
  { label: "Corporate", value: "corporate" },
  { label: "Grand Opening", value: "grand_opening" },
  { label: "Ear-Piercing", value: "ear_piercing" },
  { label: "Holy Communion", value: "holy_communion" },
  { label: "Baptism", value: "baptism" },
  { label: "Other", value: "other" },
];

const clientInfo = computed(() => {
  // Ensure we return a reactive object that the user can bind to
  if (!props.order.client_information) {
    props.order.client_information = { client_info: {} };
  }
  if (!props.order.client_information.client_info) {
    props.order.client_information.client_info = {};
  }
  return props.order.client_information.client_info;
});

const autoFill = () => {
  // Auto-fill from order customer details if available
  const cd = props.order.customer_details || props.order.customerDetails;
  if (cd) {
    if (!props.order.client_information) {
      props.order.client_information = { client_info: {} };
    }
    if (!props.order.client_information.client_info) {
      props.order.client_information.client_info = {};
    }
    
    const target = props.order.client_information.client_info;
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
  (newVal) => {
    if (newVal) {
      autoFill();
    }
  },
  { immediate: true, deep: true },
);

const save = () => {
  // Pass the data up to the parent component
  emit("save", props.order.client_information);
};

const standardOccasions = [
  "wedding",
  "birthday",
  "engagement",
  "anniversary",
  "house_warming",
  "half_saree_ceremony",
  "reception",
  "corporate",
  "grand_opening",
  "ear_piercing",
  "holy_communion",
  "baptism",
];

const isOccasionOther = computed(() => {
  const val = clientInfo.value.occasion;
  if (!val) return false;
  return val === "other" || !standardOccasions.includes(val);
});

const occasionOtherValue = computed({
  get: () =>
    isOccasionOther.value && clientInfo.value.occasion !== "other"
      ? clientInfo.value.occasion
      : "",
  set: (val) => {
    clientInfo.value.occasion = val || "other";
  },
});

const handleOccasionChange = (val) => {
  if (val === "other") {
    if (
      !clientInfo.value.occasion ||
      standardOccasions.includes(clientInfo.value.occasion)
    ) {
      clientInfo.value.occasion = "other";
    }
  } else {
    clientInfo.value.occasion = val;
  }
};
</script>
