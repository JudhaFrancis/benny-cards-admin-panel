<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
      <div class="px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <button
              @click="handleBack"
              class="p-2.5 rounded-xl hover:bg-slate-100 text-slate-600 transition-all active:scale-95 border border-slate-200"
            >
              <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <div class="border-l border-slate-200 pl-4">
              <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-bold text-slate-900">
                  Order {{ editedOrder?.id }}
                </h1>
                <span
                  class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100"
                >
                  {{ editedOrder?.orderStatus || "Processing" }}
                </span>
              </div>
              <div class="flex items-center gap-2 text-xs text-slate-500">
                <router-link
                  to="/orders"
                  class="hover:text-primary transition-colors"
                >
                  Orders
                </router-link>
                <ChevronRightIcon class="h-3 w-3" />
                <span class="text-slate-400">Edit Order</span>
              </div>
            </div>
          </div>
          <div class="text-xs text-slate-400">
            Customer:
            <span class="font-semibold text-slate-600">{{
              editedOrder?.customer
            }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="flex h-[calc(100vh-73px)]">
      <!-- Sidebar Navigation -->
      <div
        class="w-80 bg-white border-r border-slate-200 flex-shrink-0 overflow-y-auto"
      >
        <div class="p-6">
          <h2
            class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4"
          >
            Edit Sections
          </h2>
          <nav class="space-y-2">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl text-left transition-all duration-200 group"
              :class="
                activeTab === tab.id
                  ? 'bg-primary text-white shadow-lg shadow-primary/20'
                  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
              "
            >
              <component
                :is="tab.icon"
                class="h-5 w-5 flex-shrink-0"
                :class="
                  activeTab === tab.id
                    ? 'text-white'
                    : 'text-slate-400 group-hover:text-primary'
                "
              />
              <div class="flex-1 min-w-0">
                <div class="font-bold text-sm">{{ tab.label }}</div>
                <div
                  class="text-xs mt-0.5 opacity-75 truncate"
                  :class="
                    activeTab === tab.id ? 'text-white' : 'text-slate-500'
                  "
                >
                  {{ tab.description }}
                </div>
              </div>
              <ChevronRightIcon
                v-if="activeTab === tab.id"
                class="h-4 w-4 text-white flex-shrink-0"
              />
            </button>
          </nav>
        </div>
      </div>

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto bg-slate-50">
        <div class="p-8">
          <!-- Content Header -->
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-2">
              {{ activeTabLabel }}
            </h2>
            <p class="text-sm text-slate-500">
              {{ activeTabDescription }}
            </p>
          </div>

          <!-- Content Body -->
          <div
            class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8"
          >
            <div v-if="editedOrder">
              <EditOrderItems
                v-if="activeTab === 'items'"
                :order="editedOrder"
                @update:order="(val) => (editedOrder = val)"
                @save="handleSave"
              />
              <EditCustomerDetails
                v-if="activeTab === 'customer'"
                :order="editedOrder"
                @update:order="(val) => (editedOrder = val)"
                @save="handleSave"
              />
              <EditOrderTracking
                v-if="activeTab === 'tracking'"
                :order="editedOrder"
                @update:order="(val) => (editedOrder = val)"
                @save="handleSave"
                @cancel="handleBack"
              />
            </div>
            <div v-else class="text-center py-12">
              <div class="inline-flex items-center gap-2 text-slate-400">
                <div
                  class="animate-spin rounded-full h-5 w-5 border-2 border-slate-300 border-t-primary"
                ></div>
                <span>Loading order...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ArrowLeft as ArrowLeftIcon,
  ChevronRight as ChevronRightIcon,
  ShoppingCart as ShoppingCartIcon,
  User as UserIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import { useToast } from "../../composables/useToast";
import { mockOrders } from "../../data/mockOrders";
import EditOrderItems from "../../components/orders/edit-tabs/EditOrderItems.vue";
import EditCustomerDetails from "../../components/orders/edit-tabs/EditCustomerDetails.vue";
import EditOrderTracking from "../../components/orders/edit-tabs/EditOrderTracking.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();

const activeTab = ref("items");
const editedOrder = ref(null);

const tabs = [
  {
    id: "items",
    label: "Order Items",
    description: "Products and quantities",
    icon: ShoppingCartIcon,
  },
  {
    id: "customer",
    label: "Customer Details",
    description: "Contact and shipping info",
    icon: UserIcon,
  },
  {
    id: "tracking",
    label: "Order Tracking",
    description: "Progress and fulfillment",
    icon: MapPinIcon,
  },
];

const activeTabLabel = computed(() => {
  return tabs.find((t) => t.id === activeTab.value)?.label || "";
});

const activeTabDescription = computed(() => {
  return tabs.find((t) => t.id === activeTab.value)?.description || "";
});

onMounted(() => {
  const orderId = route.params.id;
  const order = mockOrders.find((o) => o.id === orderId);

  if (order) {
    editedOrder.value = JSON.parse(JSON.stringify(order));
  } else {
    toast.error("Order not found");
    router.push("/orders");
  }

  // Set active tab from query param if provided
  if (route.query.tab && tabs.some((t) => t.id === route.query.tab)) {
    activeTab.value = route.query.tab;
  }
});

const handleBack = () => {
  router.push("/orders");
};

const handleSave = () => {
  // In a real app, this would make an API call
  // For now, we'll just show a success message and navigate back
  toast.success("Order updated successfully");
  router.push("/orders");
};
</script>
