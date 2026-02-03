<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Banner Management"
      subtitle="Manage your promotional banners, featured slides, and front-page graphics."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Banner
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
              placeholder="Search by title or text..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
              style="color: #475569 !important"
              @input="debounceSearch"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
            @apply="fetchBanners"
          >
            <FilterSection label="Banner Status" last>
              <ContextDropdown
                v-model="filters.status"
                :options="statusFilterOptions"
                :icon="ActivityIcon"
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
          Showing {{ meta.total || 0 }} banners
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="banners"
      :loading="loading"
      empty-text="No banners found matching your criteria."
    >
      <!-- Image Cell -->
      <template #cell-photo="{ item: banner }">
        <div class="flex items-center">
          <div
            class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-100 shadow-sm group cursor-zoom-in"
            @click="previewImage(banner.photo, banner.title)"
          >
            <img
              :src="getImageSource(banner.photo)"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              @error="handleImageError"
            />
            <div
              class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
            >
              <Maximize2Icon class="h-4 w-4 text-white" />
            </div>
          </div>
        </div>
      </template>

      <!-- Title Cell -->
      <template #cell-title="{ item: banner }">
        <div class="flex flex-col min-w-[200px]">
          <span class="text-sm font-bold text-gray-700 tracking-tight">{{
            banner.title
          }}</span>
          <span class="text-[11px] text-gray-400 truncate max-w-xs">{{
            banner.text || "No subtitle text"
          }}</span>
        </div>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: banner }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
              banner.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full mr-2',
                banner.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400',
              ]"
            ></span>
            {{ banner.status === "active" ? "Live" : "Inactive" }}
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: banner }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(banner.created_at)
          }}</span>
          <span v-if="banner.added_by" class="text-[10px] text-gray-400">
            by {{ banner.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Modified Date Cell -->
      <template #cell-updated_at="{ item: banner }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(banner.updated_at)
          }}</span>
          <span v-if="banner.modified_by" class="text-[10px] text-gray-400">
            by {{ banner.modified_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: banner }">
        <div class="flex justify-end gap-1.5">
          <button
            @click="viewBanner(banner)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(banner)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Banner"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(banner)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Banner"
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
          @click="fetchBanners(links.prev)"
          :disabled="!links.prev"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="fetchBanners(links.next)"
          :disabled="!links.next"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Modals -->
    <BannerModal
      :isOpen="isModalOpen"
      :editBanner="selectedBanner"
      @close="closeModal"
      @refresh="fetchBanners"
    />

    <InfoModal
      :isOpen="isInfoModalOpen"
      title="Banner Details"
      @close="isInfoModalOpen = false"
    >
      <div v-if="selectedBanner" class="space-y-8">
        <!-- Visual Profile -->
        <div class="flex flex-col items-center">
          <div
            class="w-full h-48 rounded-[2rem] bg-gray-50 flex items-center justify-center shadow-xl overflow-hidden border-2 border-white cursor-zoom-in"
            @click="previewImage(selectedBanner.photo, selectedBanner.title)"
          >
            <img
              :src="getImageSource(selectedBanner.photo)"
              class="w-full h-full object-cover"
            />
          </div>
          <h4 class="mt-6 text-2xl font-black text-gray-700 tracking-tight">
            {{ selectedBanner.title }}
          </h4>
          <span
            :class="[
              'mt-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border',
              selectedBanner.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            {{ selectedBanner.status }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Banner Info" :icon="ImageIcon">
            <InfoItem label="Title" :value="selectedBanner.title" />
            <InfoItem label="Slug" :value="selectedBanner.slug" />
            <InfoItem label="Subtitle" :value="selectedBanner.text || 'N/A'" />
            <div class="col-span-2 pt-2">
              <span
                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1"
                >Description</span
              >
              <p class="text-sm text-gray-600 leading-relaxed">
                {{ selectedBanner.description || "No description provided." }}
              </p>
            </div>
          </InfoSection>

          <InfoSection title="Audit Details" :icon="CalendarIcon">
            <InfoItem
              label="Created By"
              :value="selectedBanner.added_by?.name || 'N/A'"
            />
            <InfoItem
              label="Created At"
              :value="formatDate(selectedBanner.created_at, true)"
            />
            <InfoItem
              label="Modified By"
              :value="selectedBanner.modified_by?.name || 'N/A'"
            />
            <InfoItem
              label="Modified At"
              :value="formatDate(selectedBanner.updated_at, true)"
            />
          </InfoSection>
        </div>
      </div>
    </InfoModal>

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Deletion"
      :description="`Are you sure you want to delete banner '${selectedBanner?.title}'? This action will permanently remove the associated image file.`"
      confirmLabel="Delete Banner"
      variant="danger"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />

    <ImagePreviewModal
      :isOpen="isImagePreviewOpen"
      :imageSrc="previewSrc"
      :title="previewTitle"
      @close="isImagePreviewOpen = false"
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
  Image as ImageIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Maximize2 as Maximize2Icon,
} from "lucide-vue-next";
import axios from "axios";
import BannerModal from "./BannerModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import InfoModal from "../../components/ui/InfoModal.vue";
import InfoSection from "../../components/ui/InfoSection.vue";
import InfoItem from "../../components/ui/InfoItem.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import FilterSection from "../../components/ui/FilterSection.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import ImagePreviewModal from "../../components/ui/ImagePreviewModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { canAdd, canEdit, canDelete } = usePermissions();

// State
const banners = ref([]);
const loading = ref(true);
const meta = ref({});
const links = ref({});
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedBanner = ref(null);
const isDeleting = ref(false);

const isImagePreviewOpen = ref(false);
const previewSrc = ref("");
const previewTitle = ref("");

const filters = reactive({
  search: "",
  status: "",
});

const columns = [
  { key: "photo", label: "Image", width: "80px" },
  { key: "title", label: "Banner Info", sortable: true },
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

// Computed
const activeFiltersCount = computed(() => {
  let count = 0;
  if (filters.status) count++;
  return count;
});

// Methods
const fetchBanners = async (url = "/api/v1/banners") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        search: filters.search,
        status: filters.status,
      },
    });
    if (response.data.success) {
      banners.value = response.data.data.data;
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
    console.error("Failed to fetch banners", error);
    toastError("Failed to load banners");
  } finally {
    loading.value = false;
  }
};

let debounceTimeout;
const debounceSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchBanners();
  }, 500);
};

const resetFilters = () => {
  filters.search = "";
  filters.status = "";
  fetchBanners();
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  return `/${path}`;
};

const handleImageError = (e) => {
  e.target.src = "/images/placeholder.webp";
};

const previewImage = (src, title) => {
  previewSrc.value = getImageSource(src);
  previewTitle.value = title;
  isImagePreviewOpen.value = true;
};

const openCreateModal = () => {
  selectedBanner.value = null;
  isModalOpen.value = true;
};

const openEditModal = (banner) => {
  selectedBanner.value = banner;
  isModalOpen.value = true;
};

const viewBanner = (banner) => {
  selectedBanner.value = banner;
  isInfoModalOpen.value = true;
};

const confirmDelete = (banner) => {
  selectedBanner.value = banner;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedBanner.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/banners/${selectedBanner.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message);
      fetchBanners();
      isDeleteModalOpen.value = false;
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete banner");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedBanner.value = null;
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
  fetchBanners();
});
</script>
