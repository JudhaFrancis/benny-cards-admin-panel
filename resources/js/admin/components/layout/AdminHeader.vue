<template>
  <header
    class="sticky top-0 z-40 h-16 w-full border-b backdrop-blur-md transition-all duration-300 bg-white/70 border-slate-200 shadow-sm"
  >
    <div class="flex h-full items-center justify-between px-6">
      <!-- Left Section: Search -->
      <div class="flex flex-1 items-center max-w-md relative">
        <div class="relative w-full group">
          <div
            class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none"
          >
            <SearchIcon
              class="h-4 w-4 transition-colors duration-200 text-slate-400 group-focus-within:text-primary"
            />
          </div>
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            placeholder="Search modules... (Ctrl + K)"
            class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm transition-all duration-200 focus:ring-2 focus:ring-inset bg-slate-100/50 text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-primary/20 focus:shadow-sm"
            @focus="showResults = true"
            @keydown.down.prevent="navigateResults(1)"
            @keydown.up.prevent="navigateResults(-1)"
            @keydown.enter="selectActive"
            @keydown.esc="showResults = false"
          />
        </div>

        <!-- Search Results Dropdown -->
        <div
          v-if="showResults && searchQuery"
          v-click-outside="() => (showResults = false)"
          class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl p-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200"
        >
          <div v-if="filteredModules.length > 0" class="space-y-1">
            <button
              v-for="(item, index) in filteredModules"
              :key="item.url"
              class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 group"
              :class="
                activeIndex === index
                  ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-[1.02]'
                  : 'hover:bg-slate-50 text-slate-700'
              "
              @mouseenter="activeIndex = index"
              @click="navigateTo(item.url)"
            >
              <div class="flex items-center gap-3">
                <component
                  :is="item.icon"
                  :class="
                    cn(
                      'h-4 w-4',
                      activeIndex === index
                        ? 'text-white'
                        : 'text-slate-400 group-hover:text-primary',
                    )
                  "
                />
                <span class="text-sm font-semibold tracking-tight">{{
                  item.title
                }}</span>
              </div>
              <span
                v-if="activeIndex === index"
                class="text-[10px] font-bold bg-white/20 px-1.5 py-0.5 rounded uppercase tracking-widest"
                >Enter</span
              >
              <span
                v-else
                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                >{{ item.category }}</span
              >
            </button>
          </div>
          <div v-else class="py-12 text-center">
            <div
              class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 mb-3"
            >
              <SearchIcon class="h-6 w-6 text-slate-300" />
            </div>
            <p class="text-sm font-bold text-slate-900">No modules found</p>
            <p
              class="text-[10px] text-slate-400 uppercase tracking-widest mt-1"
            >
              Try searching for 'Orders' or 'Users'
            </p>
          </div>
        </div>
      </div>

      <!-- Right Section: Actions -->
      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <button
          @click="isWhatsAppLogsOpen = true"
          class="relative flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 border group bg-white border-slate-200 text-slate-500 hover:text-primary hover:bg-slate-50 shadow-sm"
        >
          <BellIcon
            class="h-5 w-5 transition-transform group-hover:scale-110"
          />
          <!-- Notification Badge with Count -->
          <span v-if="whatsappLogsCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center">
            <span
              class="relative inline-flex rounded-full h-5 w-5 bg-rose-500 items-center justify-center text-[10px] font-bold text-white"
              >{{ whatsappLogsCount > 99 ? '99+' : whatsappLogsCount }}</span
            >
          </span>
        </button>

        <!-- User Dropdown -->
        <Menu as="div" class="relative">
          <MenuButton
            class="flex items-center gap-3 pl-1 pr-3 py-1 rounded-xl transition-all duration-200 border bg-white border-slate-200 hover:border-slate-300 text-slate-700 hover:shadow-sm shadow-sm"
          >
            <div
              class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-xs shrink-0 overflow-hidden"
            >
              <img
                v-if="user?.photo"
                :src="getImageSource(user.photo)"
                class="w-full h-full object-cover"
              />
              <span v-else>{{ userInitials }}</span>
            </div>
            <span
              class="hidden sm:block text-sm font-semibold truncate max-w-[100px]"
              >{{ user?.name || "Admin" }}</span
            >
            <ChevronDownIcon class="h-4 w-4 text-slate-400" />
          </MenuButton>

          <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <MenuItems
              class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl p-1.5 shadow-xl border focus:outline-none bg-white border-slate-100 text-slate-700"
            >
              <div class="px-1 py-1">
                <MenuItem v-slot="{ active }">
                  <router-link
                    to="/profile"
                    :class="
                      cn(
                        'group flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm transition-colors',
                        active ? 'bg-primary/10 text-primary font-medium' : '',
                      )
                    "
                  >
                    <UserIcon class="h-4 w-4" />
                    Profile
                  </router-link>
                </MenuItem>
              </div>
              <div class="px-1 py-1 border-t border-slate-100 mt-1 pt-1">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="isConfirmOpen = true"
                    :class="
                      cn(
                        'group flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm transition-colors text-rose-500',
                        active ? 'bg-rose-500/10' : '',
                      )
                    "
                  >
                    <LogOutIcon class="h-4 w-4" />
                    Logout
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
  </header>

  <!-- Logout Confirmation Modal -->
  <ConfirmationModal
    :isOpen="isConfirmOpen"
    title="Confirm Logout"
    description="You are about to log out from the Benny Cards platform. Do you wish to proceed?"
    confirmLabel="Logout"
    variant="danger"
    :loading="isLoading"
    @close="isConfirmOpen = false"
    @confirm="handleLogout"
  />

  <WhatsAppLogsSidebar 
    :isOpen="isWhatsAppLogsOpen" 
    @close="isWhatsAppLogsOpen = false" 
  />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
  Search as SearchIcon,
  Bell as BellIcon,
  ChevronDown as ChevronDownIcon,
  User as UserIcon,
  LogOut as LogOutIcon,
  LayoutDashboard as DashboardIcon,
  ShoppingBag as OrdersIcon,
  Wallet as PaymentsIcon,
  Users as UsersIcon,
  Settings as SettingsIcon,
  Shield as ShieldIcon,
  PieChart as ChartIcon,
} from "lucide-vue-next";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import { useRouter } from "vue-router";
import ConfirmationModal from "../ui/ConfirmationModal.vue";
import { useAuth } from "../../composables/useAuth";
import { usePermissions } from "../../composables/usePermissions";
import WhatsAppLogsSidebar from "../whatsapp/WhatsAppLogsSidebar.vue";

const router = useRouter();
const { isSuperAdmin } = usePermissions();
const isConfirmOpen = ref(false);
const isLoading = ref(false);
const { user, logout } = useAuth();

const isWhatsAppLogsOpen = ref(false);
const whatsappLogsCount = ref(0);

const fetchWhatsAppCount = async () => {
  try {
    const response = await fetch('/api/v1/whatsapp-logs?per_page=1');
    const data = await response.json();
    if (data.success) {
      whatsappLogsCount.value = data.data.total || 0;
    }
  } catch (error) {
    console.error('Failed to fetch WhatsApp logs count:', error);
  }
};

// Search State
const searchInput = ref(null);
const searchQuery = ref("");
const showResults = ref(false);
const activeIndex = ref(0);

const allModules = [
  { title: "Dashboard", url: "/", icon: DashboardIcon, category: "Core" },
  { title: "Users", url: "/users", icon: UsersIcon, category: "Management" },
  { title: "Roles", url: "/roles", icon: ShieldIcon, category: "Management" },
  { title: "Orders", url: "/orders", icon: OrdersIcon, category: "Sales" },
  {
    title: "Payments",
    url: "/payments",
    icon: PaymentsIcon,
    category: "Finance",
  },
  {
    title: "Order Trends",
    url: "/reports/orders",
    icon: ChartIcon,
    category: "Reports",
  },
  {
    title: "Payment Logs",
    url: "/reports/payments",
    icon: ChartIcon,
    category: "Reports",
  },
  {
    title: "Settings",
    url: "/settings",
    icon: SettingsIcon,
    category: "Admin",
    adminOnly: true,
  },
];

const filteredModules = computed(() => {
  if (!searchQuery.value) return [];
  const query = searchQuery.value.toLowerCase();
  return allModules.filter((m) => {
    if (m.adminOnly && !isSuperAdmin.value) return false;
    return (
      m.title.toLowerCase().includes(query) ||
      m.category.toLowerCase().includes(query)
    );
  });
});

const navigateResults = (direction) => {
  if (filteredModules.value.length === 0) return;
  const next = activeIndex.value + direction;
  if (next < 0) activeIndex.value = filteredModules.value.length - 1;
  else if (next >= filteredModules.value.length) activeIndex.value = 0;
  else activeIndex.value = next;
};

const selectActive = () => {
  const selected = filteredModules.value[activeIndex.value];
  if (selected) {
    navigateTo(selected.url);
  }
};

const navigateTo = (url) => {
  router.push(url);
  searchQuery.value = "";
  showResults.value = false;
};

const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    searchInput.value?.focus();
  }
};

const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener("click", el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener("click", el.clickOutsideEvent);
  },
};

// Utility for classes
function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const userInitials = computed(() => {
  if (!user.value?.name) return "AD";
  return user.value.name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
});

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  return `/${path}`;
};

const handleLogout = async () => {
  isLoading.value = true;
  await logout();
  isLoading.value = false;
  isConfirmOpen.value = false;
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
  
  // Fetch WhatsApp logs count on mount
  fetchWhatsAppCount();
  
  // Refresh count every 30 seconds
  const interval = setInterval(fetchWhatsAppCount, 30000);
  
  // Clean up interval on unmount
  onUnmounted(() => {
    clearInterval(interval);
  });
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
});
</script>

<style scoped>
input:focus {
  outline: none;
}
</style>
