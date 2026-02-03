<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Coupon Management"
      subtitle="Manage your promotional coupons, discount types, and audit history."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Coupon
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100 relative z-30"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search by coupon code..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
              style="color: #475569 !important"
              @input="debounceSearch"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
            @apply="fetchCoupons"
          >
            <FilterSection label="Coupon Status">
              <ContextDropdown
                v-model="filters.status"
                :options="statusFilterOptions"
                :icon="ActivityIcon"
              />
            </FilterSection>
            <FilterSection label="Coupon Type" last>
              <ContextDropdown
                v-model="filters.type"
                :options="typeFilterOptions"
                :icon="TagIcon"
              />
            </FilterSection>
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
          class="text-xs font-semibold text-gray-400 uppercase tracking-widest"
        >
          Showing {{ meta.total || 0 }} coupons
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="coupons"
      :loading="loading"
      empty-text="No coupons found matching your criteria."
    >
      <!-- Custom Code Cell -->
      <template #cell-code="{ item: coupon }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shadow-inner border border-primary/10"
          >
            <TicketIcon class="h-5 w-5" />
          </div>
          <div class="flex flex-col min-w-0">
            <span
              class="text-sm font-bold text-gray-700 tracking-wider truncate uppercase"
              >{{ coupon.code }}</span
            >
            <span class="text-[11px] text-gray-500 truncate"
              >Added {{ formatDate(coupon.created_at) }}</span
            >
          </div>
        </div>
      </template>

      <!-- Type Cell -->
      <template #cell-type="{ item: coupon }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border',
              coupon.type === 'percent'
                ? 'bg-purple-50 text-purple-600 border-purple-100'
                : 'bg-blue-50 text-blue-600 border-blue-100',
            ]"
          >
            {{ coupon.type }}
          </span>
        </div>
      </template>

      <!-- Value Cell -->
      <template #cell-value="{ item: coupon }">
        <span class="text-sm font-bold text-gray-700">
          <template v-if="coupon.type === 'percent'">
            {{ coupon.value }}%
          </template>
          <template v-else>
            ${{ parseFloat(coupon.value).toFixed(2) }}
          </template>
        </span>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: coupon }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
              coupon.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full mr-2',
                coupon.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400',
              ]"
            ></span>
            {{ coupon.status === "active" ? "Live" : "Inactive" }}
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: coupon }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(coupon.created_at)
          }}</span>
          <span v-if="coupon.added_by" class="text-[10px] text-gray-400">
            by {{ coupon.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Modified Date Cell -->
      <template #cell-updated_at="{ item: coupon }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(coupon.updated_at)
          }}</span>
          <span v-if="coupon.modified_by" class="text-[10px] text-gray-400">
            by {{ coupon.modified_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: coupon }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            @click="viewCoupon(coupon)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(coupon)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Coupon"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(coupon)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Coupon"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Pagination -->
    <div
      v-if="meta.total > 0"
      class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm"
    >
      <p class="text-[11px] text-gray-500 font-medium">
        Showing
        <span class="text-gray-700"
          >{{ meta.from || 0 }} to {{ meta.to || 0 }}</span
        >
        of <span class="text-gray-700">{{ meta.total || 0 }}</span> results
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="fetchCoupons(links.prev)"
          :disabled="!links.prev"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="fetchCoupons(links.next)"
          :disabled="!links.next"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!loading"
      class="bg-white rounded-[2.5rem] border border-gray-200 p-16 text-center shadow-sm"
    >
      <div
        class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-gray-100 shadow-inner"
      >
        <TicketIcon class="h-10 w-10 text-gray-300" />
      </div>
      <h3 class="text-xl font-bold text-gray-700 mb-2">No Coupons Found</h3>
      <p class="text-gray-500 max-w-sm mx-auto text-sm leading-relaxed mb-8">
        We couldn't find any coupons. Start by creating a new one or adjust your
        search filters.
      </p>
      <button
        v-if="canAdd"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-2xl text-sm font-semibold hover:opacity-90 transition-all shadow-lg shadow-primary/20 active:scale-95"
      >
        <PlusIcon class="h-4 w-4" />
        Create Coupon
      </button>
    </div>

    <!-- Modals -->
    <CouponModal
      :isOpen="isModalOpen"
      :editCoupon="selectedCoupon"
      @close="closeModal"
      @refresh="fetchCoupons"
    />

    <InfoModal
      :isOpen="isInfoModalOpen"
      title="Coupon Details"
      @close="isInfoModalOpen = false"
    >
      <div v-if="selectedCoupon" class="space-y-8">
        <!-- Visual Profile -->
        <div class="flex flex-col items-center">
          <div
            class="w-24 h-24 rounded-[2rem] bg-primary/5 flex items-center justify-center text-primary shadow-xl overflow-hidden mb-4 ring-8 ring-gray-50/50 border-2 border-white"
          >
            <TicketIcon class="h-10 w-10" />
          </div>
          <h4
            class="mt-5 text-xl font-black text-gray-700 tracking-wider uppercase"
          >
            {{ selectedCoupon.code }}
          </h4>
          <span
            :class="[
              'mt-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border',
              selectedCoupon.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            {{ selectedCoupon.status }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Coupon Info" :icon="TicketIcon">
            <InfoItem label="Code" :value="selectedCoupon.code" />
            <InfoItem
              label="Type"
              :value="
                selectedCoupon.type === 'percent'
                  ? 'Percentage'
                  : 'Fixed Amount'
              "
            />
            <InfoItem
              label="Value"
              :value="
                selectedCoupon.type === 'percent'
                  ? selectedCoupon.value + '%'
                  : '$' + parseFloat(selectedCoupon.value).toFixed(2)
              "
            />
          </InfoSection>

          <InfoSection title="Audit Details" :icon="CalendarIcon">
            <InfoItem
              label="Created By"
              :value="selectedCoupon.added_by?.name || 'N/A'"
            />
            <InfoItem
              label="Created At"
              :value="formatDate(selectedCoupon.created_at, true)"
            />
            <InfoItem
              label="Modified By"
              :value="selectedCoupon.modified_by?.name || 'N/A'"
            />
            <InfoItem
              label="Modified At"
              :value="formatDate(selectedCoupon.updated_at, true)"
            />
          </InfoSection>
        </div>
      </div>
    </InfoModal>

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Deletion"
      :description="`Are you sure you want to delete coupon ${selectedCoupon?.code}? This action cannot be undone.`"
      confirmLabel="Delete Coupon"
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
  Plus as PlusIcon,
  Search as SearchIcon,
  Edit3 as Edit3Icon,
  Trash2 as Trash2Icon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  Eye as EyeIcon,
  Ticket as TicketIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Tag as TagIcon,
} from "lucide-vue-next";
import axios from "axios";
import CouponModal from "./CouponModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import InfoModal from "../../components/ui/InfoModal.vue";
import InfoSection from "../../components/ui/InfoSection.vue";
import InfoItem from "../../components/ui/InfoItem.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import FilterSection from "../../components/ui/FilterSection.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { canAdd, canEdit, canDelete } = usePermissions();

// State
const coupons = ref([]);
const loading = ref(true);
const meta = ref({});
const links = ref({});
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCoupon = ref(null);
const isDeleting = ref(false);

const filters = reactive({
  search: "",
  status: "",
  type: "",
});

const columns = [
  { key: "code", label: "Coupon Code", sortable: true },
  { key: "type", label: "Type" },
  { key: "value", label: "Value" },
  { key: "status", label: "Status" },
  { key: "created_at", label: "Created" },
  { key: "updated_at", label: "Modified" },
  { key: "actions", label: "Actions", align: "right" },
];

const statusFilterOptions = [
  { label: "All Status", value: "" },
  { label: "Live Only", value: "active", icon: ActivityIcon },
  { label: "Hidden Only", value: "inactive", icon: ActivityIcon },
];

const typeFilterOptions = [
  { label: "All Types", value: "" },
  { label: "Fixed Amount", value: "fixed", icon: TagIcon },
  { label: "Percentage", value: "percent", icon: TagIcon },
];

// Computed
const activeFiltersCount = computed(() => {
  let count = 0;
  if (filters.status) count++;
  if (filters.type) count++;
  return count;
});

// Methods
const fetchCoupons = async (url = "/api/v1/coupons") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        search: filters.search,
        status: filters.status,
        type: filters.type,
      },
    });
    if (response.data.success) {
      coupons.value = response.data.data.data;
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
  } catch (error) {
    console.error("Failed to fetch coupons", error);
    toastError("Failed to load coupons");
  } finally {
    loading.value = false;
  }
};

let debounceTimeout;
const debounceSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchCoupons();
  }, 500);
};

const resetFilters = () => {
  filters.search = "";
  filters.status = "";
  filters.type = "";
  fetchCoupons();
};

const openCreateModal = () => {
  selectedCoupon.value = null;
  isModalOpen.value = true;
};

const openEditModal = (coupon) => {
  selectedCoupon.value = coupon;
  isModalOpen.value = true;
};

const viewCoupon = (coupon) => {
  selectedCoupon.value = coupon;
  isInfoModalOpen.value = true;
};

const confirmDelete = (coupon) => {
  selectedCoupon.value = coupon;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedCoupon.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/coupons/${selectedCoupon.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message);
      fetchCoupons();
      isDeleteModalOpen.value = false;
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete coupon");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedCoupon.value = null;
};

const formatDate = (dateString, includeTime = false) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const options = { year: "numeric", month: "short", day: "numeric" };
  if (includeTime) {
    options.hour = "2-digit";
    options.minute = "2-digit";
  }
  return date.toLocaleDateString("en-US", options);
};

onMounted(() => {
  fetchCoupons();
});
</script>
