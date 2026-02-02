<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="User Management"
      subtitle="Manage portal access, roles, and status for all members."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <UserPlusIcon class="h-4 w-4" />
          Add New User
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100 relative z-30"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search by name or email..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debounceSearch"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
            @apply="fetchUsers"
          >
            <FilterSectionHelper label="Member Role">
              <ContextDropdown
                v-model="filters.role"
                :options="roleFilterOptions"
                :icon="ShieldIcon"
              />
            </FilterSectionHelper>

            <FilterSectionHelper label="Account Status" last>
              <ContextDropdown
                v-model="filters.status"
                :options="statusFilterOptions"
                :icon="ActivityIcon"
              />
            </FilterSectionHelper>
          </FilterDropdown>

          <button
            v-if="activeFiltersCount > 0"
            @click="resetFilters"
            class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors px-2"
          >
            Clear Filters
          </button>
        </div>

        <div
          class="text-xs font-semibold text-slate-400 uppercase tracking-widest"
        >
          Showing {{ meta.total || 0 }} members
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="users"
      :loading="loading"
      empty-text="No users found matching your criteria."
    >
      <!-- Custom User Cell -->
      <template #cell-user="{ item: user }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs overflow-hidden shrink-0"
          >
            <img
              v-if="user.photo"
              :src="getImageSource(user.photo)"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ userInitials(user.name) }}</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-slate-900 truncate">{{
              user.name
            }}</span>
            <span class="text-[11px] text-slate-500 truncate">{{
              user.email
            }}</span>
          </div>
        </div>
      </template>

      <!-- Custom Role Cell -->
      <template #cell-role="{ item: user }">
        <span
          :class="
            cn(
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border transition-colors duration-200',
              roleStyles[user.role.toLowerCase()] ||
                'bg-slate-100 text-slate-800 border-slate-200',
            )
          "
        >
          {{ user.role }}
        </span>
      </template>

      <!-- Custom Status Cell -->
      <template #cell-status="{ item: user }">
        <div class="flex items-center gap-2">
          <span
            class="h-1.5 w-1.5 rounded-full"
            :class="
              user.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'
            "
          ></span>
          <span class="text-xs font-medium text-slate-700 capitalize">{{
            user.status
          }}</span>
        </div>
      </template>

      <!-- Custom Date Cell -->
      <template #cell-joined="{ item: user }">
        <span class="text-xs text-slate-500">{{
          formatDate(user.created_at)
        }}</span>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item: user }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            @click="handleView(user)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Info"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(user)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit User"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(user)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete User"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>

      <!-- Pagination Section -->
      <template #pagination>
        <div
          v-if="!loading && users.length > 0"
          class="px-6 py-4 border-t border-slate-100 flex items-center justify-between"
        >
          <p class="text-[11px] text-slate-500 font-medium">
            Showing
            <span class="text-slate-900"
              >{{ meta.from || 0 }} to {{ meta.to || 0 }}</span
            >
            of <span class="text-slate-900">{{ meta.total || 0 }}</span> results
          </p>
          <div class="flex items-center gap-2">
            <button
              :disabled="!links.prev"
              @click="fetchUsers(links.prev)"
              class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
              <ChevronLeftIcon class="h-4 w-4 text-slate-600" />
            </button>
            <button
              :disabled="!links.next"
              @click="fetchUsers(links.next)"
              class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
              <ChevronRightIcon class="h-4 w-4 text-slate-600" />
            </button>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Modals -->
    <!-- Premium Info Modal -->
    <InfoModal
      v-if="selectedUser && isViewMode"
      :isOpen="isModalOpen"
      :title="selectedUser.name"
      subtitle="Complete member profile and system access details."
      :icon="UserIcon"
      @close="isModalOpen = false"
    >
      <div class="space-y-10">
        <!-- Visual ID Card -->
        <div
          class="flex flex-col items-center justify-center p-8 bg-slate-50/50 rounded-[2.5rem] border border-slate-100 relative overflow-hidden group"
        >
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-700"
          ></div>
          <div
            class="absolute bottom-0 left-0 w-24 h-24 bg-primary/5 rounded-full -ml-12 -mb-12 group-hover:scale-110 transition-transform duration-700 delay-100"
          ></div>

          <div
            class="w-24 h-24 rounded-[2rem] bg-primary flex items-center justify-center text-white text-3xl font-bold shadow-2xl relative z-10 overflow-hidden ring-4 ring-white"
          >
            <img
              v-if="selectedUser.photo"
              :src="getImageSource(selectedUser.photo)"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ userInitials(selectedUser.name) }}</span>
          </div>
          <h4 class="mt-5 text-xl font-bold text-slate-900 relative z-10">
            {{ selectedUser.name }}
          </h4>
          <span
            class="px-4 py-1.5 mt-2 rounded-full bg-white text-primary text-[10px] font-bold uppercase tracking-[0.15em] border border-primary/10 shadow-sm relative z-10"
            >{{ selectedUser.role }}</span
          >
        </div>

        <!-- Data Sections -->
        <InfoSection title="Account Identity" columns="2">
          <InfoItem
            label="Display Name"
            :value="selectedUser.name"
            :icon="UserIcon"
          />
          <InfoItem
            label="Email Address"
            :value="selectedUser.email"
            :icon="MailIcon"
          />
        </InfoSection>

        <InfoSection title="System Access & Roles" columns="2">
          <InfoItem
            label="Designated Role"
            :value="selectedUser.role"
            :icon="ShieldIcon"
          />
          <InfoItem label="Current Status" :icon="ActivityIcon">
            <div class="flex items-center gap-2">
              <span
                class="h-2 w-2 rounded-full"
                :class="
                  selectedUser.status === 'active'
                    ? 'bg-emerald-500'
                    : 'bg-slate-300'
                "
              ></span>
              <span class="text-sm font-semibold text-slate-900 capitalize">{{
                selectedUser.status
              }}</span>
            </div>
          </InfoItem>
        </InfoSection>

        <InfoSection title="Registration Timeline" columns="2">
          <InfoItem
            label="Joined Date"
            :value="formatDate(selectedUser.created_at)"
            :icon="CalendarIcon"
          />
          <InfoItem
            label="Member ID"
            :value="`#USR-${selectedUser.id.toString().padStart(5, '0')}`"
            :icon="HashIcon"
          />
        </InfoSection>
      </div>

      <template #footer>
        <button
          @click="isModalOpen = false"
          class="px-8 py-3.5 rounded-2xl bg-slate-900 text-white font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-xl shadow-slate-200"
        >
          Confirm Details
        </button>
      </template>
    </InfoModal>

    <!-- Refined Edit Modal -->
    <UserModal
      v-else
      :isOpen="isModalOpen"
      :editUser="selectedUser"
      @close="isModalOpen = false"
      @refresh="fetchUsers"
    />

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Delete User"
      :description="`Are you sure you want to delete ${selectedUser?.name}? This will move them to the trash and revoke their access.`"
      confirmLabel="Delete User"
      variant="danger"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from "vue";
import {
  UserPlus as UserPlusIcon,
  Search as SearchIcon,
  Edit3 as Edit3Icon,
  Trash2 as Trash2Icon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  Eye as EyeIcon,
} from "lucide-vue-next";
import axios from "axios";
import UserModal from "./UserModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import InfoModal from "../../components/ui/InfoModal.vue";
import InfoSection from "../../components/ui/InfoSection.vue";
import InfoItem from "../../components/ui/InfoItem.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import {
  User as UserIcon,
  Mail as MailIcon,
  Shield as ShieldIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
} from "lucide-vue-next";

const roleFilterOptions = [
  { label: "All Roles", value: "" },
  {
    label: "Administrator",
    value: "admin",
    description: "Full system access.",
    badge: "Privileged",
    badgeClass: "bg-slate-900 text-white",
    metadata: [{ icon: ShieldIcon, text: "Admin" }],
  },
  {
    label: "Staff Member",
    value: "staff",
    description: "Order handling access.",
    badge: "Staff",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Content Moderator",
    value: "moderator",
    description: "Community management.",
    badge: "Mod",
    badgeClass: "bg-purple-100 text-purple-700",
  },
  {
    label: "Standard User",
    value: "user",
    description: "Default member access.",
    badge: "User",
    badgeClass: "bg-slate-100 text-slate-600",
  },
];

const statusFilterOptions = [
  { label: "All Statuses", value: "" },
  {
    label: "Active",
    value: "active",
    description: "Account is operational.",
    badge: "Online",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Access is suspended.",
    badge: "Blocked",
    badgeClass: "bg-slate-200 text-slate-500",
  },
];

const users = ref([]);
const loading = ref(true);
const isDeleting = ref(false);
const isModalOpen = ref(false);
const isViewMode = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser = ref(null);

const meta = ref({});
const links = ref({});
const { canAdd, canEdit, canDelete } = usePermissions();

const columns = [
  { key: "user", label: "User", align: "left" },
  { key: "role", label: "Role", align: "left" },
  { key: "status", label: "Status", align: "left" },
  { key: "joined", label: "Joined", align: "left" },
  { key: "actions", label: "Actions", align: "right" },
];

const filters = reactive({
  search: "",
  role: "",
  status: "",
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (filters.role) count++;
  if (filters.status) count++;
  return count;
});

let searchTimeout = null;

const fetchUsers = async (url = "/api/v1/users") => {
  loading.value = true;
  try {
    const params = {
      search: filters.search,
      role: filters.role,
      status: filters.status,
    };

    // Check if url is just path or full URL
    const finalUrl = url.includes("?") ? url : url;
    const response = await axios.get(finalUrl, { params });

    if (response.data.success) {
      users.value = response.data.data.data;
      meta.value = {
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to,
      };
      links.value = {
        next: response.data.data.next_page_url,
        prev: response.data.data.prev_page_url,
      };
    }
  } catch (e) {
    console.error("Failed to fetch users", e);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers();
  }, 500);
};

const resetFilters = () => {
  filters.role = "";
  filters.status = "";
  fetchUsers();
};

const userInitials = (name) => {
  if (!name) return "??";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
};

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  return `/${path}`;
};

const roleStyles = {
  admin: "bg-slate-900/10 text-slate-900 border-slate-900/20",
  staff: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  moderator: "bg-purple-500/10 text-purple-500 border-purple-500/20",
  user: "bg-slate-100 text-slate-500 border-slate-200",
};

const handleView = (user) => {
  selectedUser.value = user;
  isViewMode.value = true;
  isModalOpen.value = true;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

const openCreateModal = () => {
  selectedUser.value = null;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const openEditModal = (user) => {
  selectedUser.value = user;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const confirmDelete = (user) => {
  selectedUser.value = user;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  isDeleting.value = true;
  try {
    await axios.delete(`/api/v1/users/${selectedUser.value.id}`);
    isDeleteModalOpen.value = false;
    fetchUsers();
  } catch (e) {
    console.error("Failed to delete user", e);
  } finally {
    isDeleting.value = false;
  }
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

onMounted(fetchUsers);
</script>

<style scoped></style>
