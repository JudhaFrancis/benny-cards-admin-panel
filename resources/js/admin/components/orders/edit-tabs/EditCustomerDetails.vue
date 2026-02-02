<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-slate-700"
          >Full Name</label
        >
        <input
          id="name"
          v-model="customerDetails.name"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
      <div class="space-y-2">
        <label for="mobile" class="text-sm font-medium text-slate-700"
          >Mobile Number</label
        >
        <input
          id="mobile"
          v-model="customerDetails.mobile"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
    </div>

    <div class="space-y-2">
      <label for="email" class="text-sm font-medium text-slate-700"
        >Email Address</label
      >
      <input
        id="email"
        type="email"
        v-model="customerDetails.email"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>

    <div class="space-y-2">
      <label for="addressLine1" class="text-sm font-medium text-slate-700"
        >Address Line 1</label
      >
      <input
        id="addressLine1"
        v-model="customerDetails.addressLine1"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>

    <div class="space-y-2">
      <label for="addressLine2" class="text-sm font-medium text-slate-700"
        >Address Line 2</label
      >
      <input
        id="addressLine2"
        v-model="customerDetails.addressLine2"
        placeholder="Apartment, suite, etc. (optional)"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <label for="city" class="text-sm font-medium text-slate-700"
          >City</label
        >
        <input
          id="city"
          v-model="customerDetails.city"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
      <div class="space-y-2">
        <label for="state" class="text-sm font-medium text-slate-700"
          >State</label
        >
        <input
          id="state"
          v-model="customerDetails.state"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <label for="pincode" class="text-sm font-medium text-slate-700"
          >Pincode / ZIP</label
        >
        <input
          id="pincode"
          v-model="customerDetails.pincode"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
      <div class="space-y-2">
        <label for="country" class="text-sm font-medium text-slate-700"
          >Country</label
        >
        <input
          id="country"
          v-model="customerDetails.country"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
    </div>

    <button
      @click="$emit('save')"
      class="w-full bg-primary text-white hover:opacity-90 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all active:scale-[0.98]"
    >
      Save Customer Details
    </button>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order", "save"]);

const customerDetails = computed({
  get: () => props.order.customerDetails || {},
  set: (val) => {
    emit("update:order", {
      ...props.order,
      customerDetails: val,
    });
  },
});
</script>
