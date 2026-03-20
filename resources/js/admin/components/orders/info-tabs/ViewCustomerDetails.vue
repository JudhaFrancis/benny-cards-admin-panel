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
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Full Name</label
          >
          <div
            class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
          >
            <UserIcon class="h-4 w-4 text-slate-400" />
            <span class="text-sm font-medium text-slate-900">{{
              order.customer_details?.name || "N/A"
            }}</span>
          </div>
        </div>

        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Phone Number</label
          >
          <div
            class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
          >
            <PhoneIcon class="h-4 w-4 text-slate-400" />
            <span class="text-sm font-medium text-slate-900 font-mono">{{
              order.customer_details?.phone || "N/A"
            }}</span>
          </div>
        </div>

        <div class="space-y-2 md:col-span-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Email Address</label
          >
          <div
            class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
          >
            <MailIcon class="h-4 w-4 text-slate-400" />
            <span class="text-sm font-medium text-slate-900">{{
              order.customer_details?.email || "N/A"
            }}</span>
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

        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wide"
            >Primary Address</label
          >
          <div
            class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 leading-relaxed whitespace-pre-wrap"
          >
            {{ order.customer_details?.address_1 || "N/A" }}
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
                >Secondary Address</label
              >
              <div
                class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 leading-relaxed whitespace-pre-wrap"
              >
                {{ order.customer_details?.address_2 || "N/A" }}
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</template>

<script setup>
import {
  User as UserIcon,
  Phone as PhoneIcon,
  Mail as MailIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});
</script>
