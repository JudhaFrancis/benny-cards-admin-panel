<template>
  <div class="space-y-8">
    <!-- Shop Selection -->
    <div class="space-y-4">
      <label
        class="text-xs font-semibold text-slate-700 flex items-center gap-2"
      >
        <StoreIcon class="h-4 w-4 text-primary" />
        Select Shop Location
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <label
          v-for="shop in shopOptions"
          :key="shop.id"
          class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden"
          :class="[
            deliveryLocation.shops === shop.id
              ? 'bg-primary/5 border-primary shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm"
            :class="
              deliveryLocation.shops === shop.id
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <component :is="shop.icon" class="h-5 w-5" />
          </div>
          <div class="flex-1">
            <span
              class="block text-xs font-black uppercase tracking-wider"
              :class="
                deliveryLocation.shops === shop.id
                  ? 'text-primary'
                  : 'text-slate-700'
              "
              >{{ shop.label }}</span
            >
          </div>

          <!-- Selection Indicator -->
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              deliveryLocation.shops === shop.id
                ? 'border-primary bg-primary scale-110 shadow-sm'
                : 'border-slate-200 group-hover:border-slate-300'
            "
          >
            <CheckIcon
              v-if="deliveryLocation.shops === shop.id"
              class="h-3 w-3 text-white"
            />
          </div>

          <input
            type="radio"
            name="delivery_shop"
            :value="shop.id"
            v-model="deliveryLocation.shops"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    <!-- Address Input -->
    <div class="space-y-4">
      <label
        class="text-xs font-semibold text-slate-700 flex items-center gap-2"
      >
        <NavigationIcon class="h-4 w-4 text-primary" />
        Address
      </label>
      <div class="relative group">
        <MapPinIcon
          class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
        />
        <input
          v-model="deliveryLocation.place_name"
          class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
          placeholder="Enter detailed delivery location..."
        />
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="deliveryLocation._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(deliveryLocation._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ deliveryLocation._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from "vue";
import {
  Store as StoreIcon,
  MapPin as MapPinIcon,
  ShoppingBag as ShoppingBagIcon,
  Building2 as Building2Icon,
  Navigation as NavigationIcon,
  Clock as ClockIcon,
  Check as CheckIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const deliveryLocation = computed(() => {
  if (!props.order.dispatch_delivery) {
    props.order.dispatch_delivery = { delivery_location: {} };
  }
  if (!props.order.dispatch_delivery.delivery_location || Array.isArray(props.order.dispatch_delivery.delivery_location)) {
    props.order.dispatch_delivery.delivery_location = {};
  }
  return props.order.dispatch_delivery.delivery_location;
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

const shopOptions = [
  { id: "NGL Shop", label: "NGL Shop", icon: StoreIcon },
  { id: "Marthandam Shop", label: "Marthandam Shop", icon: MapPinIcon },
  { id: "TVL Shop", label: "TVL Shop", icon: ShoppingBagIcon },
  { id: "Chennai Shop", label: "Chennai Shop", icon: Building2Icon },
];

const autoFill = () => {
  if (!props.order.dispatch_delivery) {
    props.order.dispatch_delivery = { delivery_location: {} };
  }
  if (!props.order.dispatch_delivery.delivery_location) {
    props.order.dispatch_delivery.delivery_location = {};
  }

  const target = props.order.dispatch_delivery.delivery_location;
  if (!target.place_name) {
    const cd = props.order.customer_details || props.order.customerDetails;
    if (cd) {
      const addr = cd.address_1 || cd.address;
      const city = cd.city_1 || cd.city;
      const pc = cd.post_code_1 || cd.post_code;

      const addressParts = [addr, city, pc].filter(Boolean);
      if (addressParts.length > 0) {
        target.place_name = addressParts.join(", ");
      }
    }
  }
};

onMounted(() => {
  // Only auto-fill if we don't have an address yet
  const target = props.order.dispatch_delivery?.delivery_location;
  if (!target?.place_name) {
    autoFill();
  }
});

// Watch for SHOP changes to auto-populate address
// We only do this when the shop selection actually CHANGES
watch(
  () => deliveryLocation.value.shops,
  (newShop, oldShop) => {
    if (newShop && newShop !== oldShop) {
      const shop = shopOptions.find((s) => s.id === newShop);
      if (shop) {
        deliveryLocation.value.place_name = shop.label;
      }
    }
  }
);
</script>

