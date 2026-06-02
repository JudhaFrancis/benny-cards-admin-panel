<template>
  <aside :class="cn(
    'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-500 ease-in-out border-r bg-primary text-white/70',
    isCollapsed ? 'w-24' : 'w-64',
    'border-white/10 shadow-[8px_0_32px_rgba(0,0,0,0.1)]',
  )
    ">
    <!-- Brand / Logo Area -->
    <div class="h-24 flex items-center px-6 gap-4 shrink-0 border-b border-white/10 bg-white/5 backdrop-blur-md">
      <div
        class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-xl shadow-black/10 shrink-0 transform transition-all hover:scale-105 hover:rotate-2 duration-500 cursor-pointer group overflow-hidden">
        <img v-if="settings.logo" :src="getLogoSource()" class="w-full h-full object-contain p-1.5" alt="Logo" />
        <ScrollTextIcon v-else class="h-6 w-6 text-primary group-hover:scale-110 transition-transform" />
      </div>
      <div v-if="!isCollapsed"
        class="flex flex-col overflow-hidden animate-in fade-in slide-in-from-left-4 duration-500">
        <span class="font-bold text-lg text-white tracking-tight leading-none">
          {{ settings.company_name || "Agoo Order" }}
        </span>
        <span class="text-[10px] text-white font-bold uppercase tracking-[0.2em] mt-1.5 opacity-60">{{ user?.role ?
          user.role?.name + " Portal" : "HQ Portal" }}</span>
      </div>
    </div>

    <!-- Navigation Area -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-8 px-2 space-y-10">
      <!-- Dashboard - Standalone -->
      <div class="space-y-1">
        <SidebarNavItem :item="navDashboard" :isCollapsed="isCollapsed" :isActive="isActive(navDashboard.url)" />
      </div>

      <!-- Section: Order Management -->
      <div v-if="navOrders.length > 0 || navStages.length > 0" class="space-y-2">
        <p v-if="!isCollapsed"
          class="px-5 mb-4 text-[10px] font-bold text-white/90 uppercase tracking-[0.3em] leading-none">
          Order Management
        </p>
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navOrders" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />

          <!-- Stage Tracking Dropdown -->
          <div v-if="navStages.length > 0" class="px-2 mt-2">
            <div @click="toggleTracking" :class="cn(
              'flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-300 group',
              trackingOpen && !isCollapsed
                ? 'bg-white/20 text-white backdrop-blur-md'
                : 'text-white/40 hover:bg-white/10 hover:text-white',
            )
              ">
              <DashboardIcon class="h-5 w-5 shrink-0 transition-all duration-300 group-hover:scale-110" :class="trackingOpen && !isCollapsed
                ? 'text-white'
                : 'text-white/40 group-hover:text-white'
                " />
              <div v-if="!isCollapsed" class="flex flex-1 items-center justify-between">
                <span class="text-[14px] font-medium tracking-tight">Order Management</span>
                <ChevronDownIcon :class="cn(
                  'h-4 w-4 transition-transform duration-500 opacity-100 text-white',
                  trackingOpen ? 'rotate-180' : '',
                )
                  " />
              </div>
            </div>

            <!-- Stage Children -->
            <div v-if="trackingOpen && !isCollapsed"
              class="mt-2 border-l border-white/10 space-y-1 animate-in fade-in slide-in-from-top-4 duration-500">
              <SidebarNavItem v-for="item in navStages" :key="item.title" :item="item" :isActive="isActive(item.url)"
                isSubItem />
            </div>
          </div>

          <!-- Standalone Payments Link -->
          <SidebarNavItem v-for="item in navPayments" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>
      </div>

      <!-- Section: Product Management -->
      <div v-if="navProducts.length > 0 || navCatalog.length > 0" class="space-y-2">
        <p v-if="!isCollapsed"
          class="px-5 mb-4 text-[10px] font-bold text-white/90 uppercase tracking-[0.3em] leading-none">
          Product Management
        </p>

        <!-- Standalone Product Items -->
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navProducts" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>

        <!-- Catalog Management Dropdown -->
        <div v-if="navCatalog.length > 0" class="px-2 mt-2">
          <div @click="toggleCatalog" :class="cn(
            'flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-300 group',
            catalogOpen && !isCollapsed
              ? 'bg-white/20 text-white backdrop-blur-md'
              : 'text-white/40 hover:bg-white/10 hover:text-white',
          )
            ">
            <PackageIcon class="h-5 w-5 shrink-0 transition-all duration-300 group-hover:scale-110" :class="catalogOpen && !isCollapsed
              ? 'text-white'
              : 'text-white/40 group-hover:text-white'
              " />
            <div v-if="!isCollapsed" class="flex flex-1 items-center justify-between">
              <span class="text-[14px] font-medium tracking-tight">Catalog Management</span>
              <ChevronDownIcon :class="cn(
                'h-4 w-4 transition-transform duration-500 opacity-100 text-white',
                catalogOpen ? 'rotate-180' : '',
              )
                " />
            </div>
          </div>

          <!-- Catalog Children -->
          <div v-if="catalogOpen && !isCollapsed"
            class="mt-2 ml-4 border-l border-white/10 space-y-1 animate-in fade-in slide-in-from-top-4 duration-500">
            <SidebarNavItem v-for="item in navCatalog" :key="item.title" :item="item" :isActive="isActive(item.url)"
              isSubItem />
          </div>
        </div>
      </div>

      <!-- Section: Analytics & Reports -->
      <div v-if="navReports.length > 0" class="space-y-2">
        <p v-if="!isCollapsed"
          class="px-5 mb-4 text-[10px] font-bold text-white/90 uppercase tracking-[0.3em] leading-none">
          Analytics & Reports
        </p>
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navReports" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>
      </div>

      <!-- Section: Administration -->
      <div v-if="navBottom.length > 0" class="space-y-2">
        <p v-if="!isCollapsed"
          class="px-5 mb-4 text-[10px] font-bold text-white/90 uppercase tracking-[0.3em] leading-none">
          Administration
        </p>
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navBottom" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>
      </div>
    </div>

    <!-- Footer / User Area -->
    <div class="p-4 border-t border-white/10 bg-white/5 backdrop-blur-md space-y-3">
      <div class="flex gap-3 px-1">
        <button @click="isLogoutModalOpen = true" :class="cn(
          'flex-1 flex items-center justify-center h-12 rounded-xl transition-all duration-300 border bg-white/10 active:scale-95 group shadow-sm',
          isCollapsed
            ? 'border-white/10 text-white/60 hover:text-white hover:bg-rose-500/20 hover:border-rose-500/30'
            : 'border-white/10 text-white/50 hover:bg-rose-500/20 hover:border-rose-500/20 hover:text-white',
        )
          " title="Sign Out">
          <LogOutIcon :class="cn(
            'h-5 w-5 transition-all duration-300 group-hover:scale-110',
            !isCollapsed && 'mr-2',
          )
            " />
          <span v-if="!isCollapsed" class="text-sm font-bold text-white tracking-tight">Sign Out</span>
        </button>

        <button @click="isCollapsed = !isCollapsed"
          class="w-12 h-12 flex items-center justify-center rounded-xl bg-white text-primary shadow-xl shadow-black/10 hover:bg-slate-50 transition-all active:scale-90 group">
          <ChevronRightIcon v-if="isCollapsed" class="h-5 w-5 group-hover:translate-x-0.5 transition-transform" />
          <ChevronLeftIcon v-else class="h-5 w-5 group-hover:-translate-x-0.5 transition-transform" />
        </button>
      </div>
      <div v-if="!isCollapsed" class="text-center text-[10px] text-white/30 tracking-wider">
        <p>© Benny Cards 2026</p>
        <span>v{{ settings.app_version || '1.0.4' }}</span>
      </div>
    </div>
  </aside>

  <!-- Content Spacer -->
  <div :class="cn('transition-all duration-300 shrink-0', isCollapsed ? 'w-20' : 'w-64')
    " aria-hidden="true" />

  <!-- Logout Confirmation Modal -->
  <ConfirmationModal :isOpen="isLogoutModalOpen" title="Sign Out"
    description="Are you sure you want to sign out of the Benny Cards Admin Panel? Your session will be ended."
    confirmLabel="Sign Out" variant="danger" :loading="logoutLoading" @close="isLogoutModalOpen = false"
    @confirm="handleLogout" />
</template>

<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import {
  CreditCard as CreditCardIcon,
  LayoutDashboard as DashboardIcon,
  ShoppingBag as OrdersIcon,
  Wallet as PaymentsIcon,
  PieChart as PieChartIcon,
  User as UserIcon,
  Users as UsersIcon,
  Shield as ShieldIcon,
  LogOut as LogOutIcon,
  Settings as SettingsIcon,
  ChevronDown as ChevronDownIcon,
  ChevronRight as ChevronRightIcon,
  ChevronLeft as ChevronLeftIcon,
  ScrollText as ScrollTextIcon,
  Package as PackageIcon,
  Box as BoxIcon,
  Image as ImageIcon,
  FolderOpen as FolderOpenIcon,
  Palette as PaletteIcon,
  Printer as PrinterIcon,
  CheckCircle as CheckCircleIcon,
  Truck as TruckIcon,
} from "lucide-vue-next";
import SidebarNavItem from "./SidebarNavItem.vue";
import ConfirmationModal from "../ui/modals/ConfirmationModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useAuth } from "../../composables/useAuth";
import { useSettings } from "../../composables/useSettings";

const route = useRoute();
const router = useRouter();
const { isSuperAdmin, hasPermission } = usePermissions();
const { user, setUser } = useAuth();
const { settings, fetchSettings, getLogoSource } = useSettings();
const isCollapsed = ref(false);
const catalogOpen = ref(false);
const trackingOpen = ref(false);
const isLogoutModalOpen = ref(false);
const logoutLoading = ref(false);

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  return `/${path}`;
};

const fetchUser = async () => {
  try {
    const response = await axios.get("/api/v1/me");
    if (response.data.success) {
      setUser(response.data.data.user);
    }
  } catch (e) {
    console.error("Sidebar: Failed to fetch user info", e);
  }
};

// Local fetchSettings removed, using useSettings instead

onMounted(() => {
  fetchUser();
  fetchSettings();
});

// Dashboard - standalone
const navDashboard = { title: "Dashboard", url: "/", icon: DashboardIcon };

// Order Management section
const navOrders = computed(() => {
  const items = [
    { title: "Orders", url: "/orders", icon: OrdersIcon, module: "Order" },
  ];
  return items.filter((item) => hasPermission(item.module));
});

// Order Stage Tracking dropdown
const navStages = computed(() => {
  const items = [
    {
      title: "Client Information",
      url: "/order-management/client-information",
      icon: UserIcon,
      module: "Order",
    },
    {
      title: "Designing",
      url: "/order-management/designing",
      icon: PaletteIcon,
      module: "Order",
    },
    {
      title: "Printing",
      url: "/order-management/printing",
      icon: PrinterIcon,
      module: "Order",
    },
    {
      title: "Packaging",
      url: "/order-management/packaging",
      icon: PackageIcon,
      module: "Order",
    },
    {
      title: "Dispatch & Delivery",
      url: "/order-management/delivery",
      icon: CheckCircleIcon,
      module: "Order",
    },
  ];
  return items.filter((item) => hasPermission(item.module));
});

const navPayments = computed(() => {
  const items = [
    {
      title: "Payments",
      url: "/payments",
      icon: PaymentsIcon,
      module: "Payment",
    },
  ];
  return items.filter((item) => hasPermission(item.module));
});

// Product Management - standalone items
const navProducts = computed(() => {
  const items = [
    { title: "Products", url: "/products", icon: BoxIcon, module: "Product" },
  ];
  return items.filter((item) => hasPermission(item.module));
});

// Product Management - Catalog dropdown
const navCatalog = computed(() => {
  const items = [
    { title: "Categories", url: "/catalog/categories", module: "Category" },
    {
      title: "Price Range",
      url: "/catalog/price-ranges",
      module: "Price Range",
    },
    { title: "Brands", url: "/catalog/brands", module: "Brands" },
    { title: "Banners", url: "/catalog/banners", module: "Banners" },
    { title: "Reviews", url: "/catalog/reviews", module: "Review" },
    { title: "Coupons", url: "/catalog/coupons", module: "Coupons" },
  ];
  return items.filter((item) => hasPermission(item.module));
});

const navReports = computed(() => {
  const items = [
    { title: "Reports", url: "/reports", icon: PieChartIcon, module: "Order" },
  ];
  return items.filter((item) => hasPermission(item.module));
});

// Bottom items
const navBottom = computed(() => {
  const items = [];

  if (hasPermission("User")) {
    items.push({ title: "Users", url: "/users", icon: UsersIcon });
  }

  if (hasPermission("Role")) {
    items.push({ title: "Roles", url: "/roles", icon: ShieldIcon });
  }

  // Settings/Profile accessible to all authenticated dashboard users
  items.push({ title: "Settings", url: "/settings", icon: SettingsIcon });

  return items;
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const isActive = (url) => {
  if (url === "/") return route.path === "/";
  if (url.includes("?")) {
    return route.fullPath.includes(url);
  }
  // If the current route has a status filter, the base "Orders" link (which has no ?status=)
  // should not be active.
  if (route.query.status && !url.includes("status=")) {
    return false;
  }
  return route.path.startsWith(url);
};

const toggleTracking = () => {
  if (isCollapsed.value) isCollapsed.value = false;
  trackingOpen.value = !trackingOpen.value;
  // Close other dropdowns
  if (trackingOpen.value) {
    catalogOpen.value = false;
  }
};

const toggleCatalog = () => {
  if (isCollapsed.value) isCollapsed.value = false;
  catalogOpen.value = !catalogOpen.value;
  // Close other dropdowns
  if (catalogOpen.value) {
    trackingOpen.value = false;
  }
};

const handleLogout = async () => {
  console.log("Sidebar: Initiating logout");
  logoutLoading.value = true;
  try {
    await axios.post("/api/v1/logout");
    console.log("Sidebar: Logout API success");
  } catch (e) {
    console.error("Sidebar: Logout API failed", e);
  } finally {
    console.log("Sidebar: Clearing session and redirecting");
    logoutLoading.value = false;
    isLogoutModalOpen.value = false;
    localStorage.removeItem("auth_token");
    delete axios.defaults.headers.common["Authorization"];
    router.push("/login");
  }
};

// Auto-expand/close dropdowns based on current route
watch(
  () => route.path,
  (path) => {
    // Auto-expand if navigating to a dropdown route
    if (path.startsWith("/catalog")) {
      catalogOpen.value = true;
    } else if (path.startsWith("/order-management")) {
      trackingOpen.value = true;
    } else {
      // Close all dropdowns when navigating to non-dropdown routes
      catalogOpen.value = false;
      trackingOpen.value = false;
    }
  },
  { immediate: true },
);
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Animations */
.animate-in {
  animation-duration: 0.3s;
  animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  animation-fill-mode: forwards;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slide-in-left {
  from {
    transform: translateX(-10px);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slide-in-top {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes zoom-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

.fade-in {
  animation-name: fade-in;
}

.slide-in-from-left-4 {
  animation-name: slide-in-left;
}

.slide-in-from-top-2 {
  animation-name: slide-in-top;
}

.zoom-in-95 {
  animation-name: zoom-in;
}
</style>
