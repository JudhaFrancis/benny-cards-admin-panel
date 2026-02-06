<template>
  <div class="space-y-8">
    <!-- Customer Information Card -->
    <div
      class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6"
    >
      <h4
        class="text-sm font-bold text-slate-900 pb-4 border-b border-slate-50 flex items-center gap-2"
      >
        <UserIcon class="h-4 w-4 text-primary" /> Personal Information
      </h4>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label
            for="name"
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Full Name</label
          >
          <div class="relative group">
            <UserIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              id="name"
              v-model="localDetails.name"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
              placeholder="e.g. John Doe"
            />
          </div>
        </div>

        <div class="space-y-2">
          <label
            for="mobile"
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Phone Number</label
          >
          <div class="relative group">
            <PhoneIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              id="mobile"
              v-model="localDetails.phone"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
              placeholder="e.g. +1 234 567 8900"
            />
          </div>
        </div>

        <div class="space-y-2 md:col-span-2">
          <label
            for="email"
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Email Address</label
          >
          <div class="relative group">
            <MailIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              id="email"
              type="email"
              v-model="localDetails.email"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
              placeholder="jane@example.com"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Address Card -->
    <div
      class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6"
    >
      <h4
        class="text-sm font-bold text-slate-900 pb-4 border-b border-slate-50 flex items-center gap-2"
      >
        <MapPinIcon class="h-4 w-4 text-primary" /> Primary Address
      </h4>

      <div class="space-y-6">
        <div class="space-y-2">
          <label
            for="addressLine1"
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Street Address</label
          >
          <input
            id="addressLine1"
            v-model="localDetails.address_1"
            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
            placeholder="123 Main St"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="city"
              class="text-xs font-bold text-slate-500 uppercase tracking-wide"
              >City</label
            >
            <input
              id="city"
              v-model="localDetails.city_1"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
            />
          </div>
          <div class="space-y-2">
            <label
              for="state"
              class="text-xs font-bold text-slate-500 uppercase tracking-wide"
              >State / Province</label
            >
            <input
              id="state"
              v-model="localDetails.state_1"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label
              for="pincode"
              class="text-xs font-bold text-slate-500 uppercase tracking-wide"
              >ZIP / Postal Code</label
            >
            <input
              id="pincode"
              v-model="localDetails.post_code_1"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
            />
          </div>
          <div class="space-y-2">
            <label
              for="country"
              class="text-xs font-bold text-slate-500 uppercase tracking-wide"
              >Country</label
            >
            <input
              id="country"
              v-model="localDetails.country"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
            />
          </div>
        </div>

        <!-- Extra Address Divider -->
        <div class="pt-6 border-t border-slate-100 mt-2">
          <h5
            class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-6 flex items-center gap-2"
          >
            <span class="w-8 h-[1px] bg-slate-200"></span>
            Extra / Secondary Address
          </h5>

          <div class="space-y-6">
            <div class="space-y-2">
              <label
                class="text-xs font-bold text-slate-500 uppercase tracking-wide"
                >Street Address (Extra)</label
              >
              <input
                v-model="localDetails.address_2"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
                placeholder="Secondary Street Address"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-wide"
                  >City (Extra)</label
                >
                <input
                  v-model="localDetails.city_2"
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
                />
              </div>
              <div class="space-y-2">
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-wide"
                  >State (Extra)</label
                >
                <input
                  v-model="localDetails.state_2"
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-wide"
                  >Post Code (Extra)</label
                >
                <input
                  v-model="localDetails.post_code_2"
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-300"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Footer -->
    <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
      <button
        @click="$emit('cancel')"
        class="px-8 py-3.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all active:scale-95 text-sm"
      >
        Cancel Changes
      </button>
      <button
        @click="handleSaveSection"
        :disabled="localSaving"
        class="px-8 py-3.5 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95 text-sm disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <Loader2Icon v-if="localSaving" class="h-4 w-4 animate-spin" />
        {{ localSaving ? "Saving..." : "Save Customer Details" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref } from "vue";
import {
  User as UserIcon,
  Phone as PhoneIcon,
  Mail as MailIcon,
  MapPin as MapPinIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../../composables/useToast";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order", "save", "cancel", "success"]);
const toast = useToast();
const localSaving = ref(false);

// Initialize local state from props
const localDetails = reactive({
  name: "",
  email: "",
  phone: "",
  address_1: "",
  address_2: "",
  city_1: "",
  city_2: "",
  state_1: "",
  state_2: "",
  post_code_1: "",
  post_code_2: "",
  country: "",
  ...props.order.customer_details,
});

// Watch for deep changes in localDetails and emit update to parent
watch(
  localDetails,
  (newVal) => {
    emit("update:order", {
      ...props.order,
      customer_details: { ...newVal },
    });
  },
  { deep: true },
);

// Watch for prop changes to update local state (handle external updates)
watch(
  () => props.order.customer_details,
  (newVal) => {
    if (newVal) {
      Object.assign(localDetails, newVal);
    }
  },
  { deep: true },
);

const handleSaveSection = async () => {
  try {
    localSaving.value = true;
    const response = await axios.put(
      `/api/v1/orders/${props.order.id}/customer-details`,
      localDetails,
    );
    if (response.data.success) {
      toast.success("Customer details updated successfully");
      emit("success", response.data.data);
      // Optional: emit save to close modal if needed, or just let user stay
    }
  } catch (error) {
    console.error("Failed to update customer details", error);
    toast.error("Failed to update customer details");
  } finally {
    localSaving.value = false;
  }
};
</script>
