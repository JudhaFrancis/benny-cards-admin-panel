<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Price Range Management"
      subtitle="Manage product price ranges, tiers, and visibility."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Price Range
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="priceRanges"
      :loading="loading"
      empty-text="No price ranges found matching your criteria."
    >
      <!-- Custom Range Cell -->
      <template #cell-range="{ item: range }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden shrink-0 border border-gray-200 cursor-pointer hover:scale-110 active:scale-95 transition-transform"
            @click="openImagePreview(range.photo, range.title)"
            title="Click to preview"
          >
            <img
              v-if="range.photo"
              :src="getImageSource(range.photo)"
              class="w-full h-full object-cover"
            />
            <DollarSignIcon v-else class="h-5 w-5" />
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-700 truncate">{{
              range.title
            }}</span>
            <span class="text-[11px] text-gray-500 truncate">{{
              range.slug
            }}</span>
          </div>
        </div>
      </template>

      <!-- Custom Min Price Cell -->
      <template #cell-min_price="{ item: range }">
        <span class="text-sm font-medium text-gray-600">
          {{
            range.min_price
              ? `₹${Number(range.min_price).toLocaleString("en-IN")}`
              : "No Min"
          }}
        </span>
      </template>

      <!-- Custom Max Price Cell -->
      <template #cell-max_price="{ item: range }">
        <span class="text-sm font-medium text-gray-600">
          {{
            range.max_price
              ? `₹${Number(range.max_price).toLocaleString("en-IN")}`
              : "∞"
          }}
        </span>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: range }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
              range.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full mr-2',
                range.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400',
              ]"
            ></span>
            {{ range.status === "active" ? "Live" : "Hidden" }}
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: range }">
        <div class="flex flex-col">
          <span class="text-sm text-gray-600">{{
            formatDate(range.created_at)
          }}</span>
          <span v-if="range.added_by" class="text-[11px] text-gray-400">
            by {{ range.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Modified Date Cell -->
      <template #cell-updated_at="{ item: range }">
        <div class="flex flex-col">
          <span class="text-sm text-gray-600">{{
            formatDate(range.updated_at)
          }}</span>
          <span v-if="range.modified_by" class="text-[11px] text-gray-400">
            by {{ range.modified_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: range }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
            @click="handleView(range)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(range)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Range"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(range)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Range"
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
          @click="fetchPriceRanges(links.prev)"
          :disabled="!links.prev"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="fetchPriceRanges(links.next)"
          :disabled="!links.next"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>


    <!-- Modals -->
    <PriceRangeModal
      :isOpen="isModalOpen"
      :editRange="selectedRange"
      @close="closeModal"
      @refresh="fetchPriceRanges"
    />

    <InfoModal
      :isOpen="isInfoModalOpen"
      title="Price Range Details"
      @close="isInfoModalOpen = false"
    >
      <div v-if="selectedRange" class="space-y-8">
        <!-- Visual Profile -->
        <div class="flex flex-col items-center">
          <div
            class="w-24 h-24 rounded-[2rem] bg-gray-50 flex items-center justify-center border-2 border-white shadow-xl overflow-hidden mb-4 ring-8 ring-gray-50/50"
          >
            <img
              v-if="selectedRange.photo"
              :src="getImageSource(selectedRange.photo)"
              class="w-full h-full object-cover"
            />
            <DollarSignIcon v-else class="h-10 w-10 text-gray-300" />
          </div>
          <h4 class="mt-5 text-xl font-bold text-gray-700 relative z-10">
            {{ selectedRange.title }}
          </h4>
          <span
            :class="[
              'mt-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border',
              selectedRange.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            {{ selectedRange.status }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Range Info" :icon="DollarSignIcon">
            <InfoItem label="Title" :value="selectedRange.title" />
            <InfoItem label="Slug" :value="selectedRange.slug" />
            <InfoItem
              label="Pricing"
              :value="`${rangeDisplay(selectedRange)}`"
            />
          </InfoSection>

          <InfoSection title="Audit Details" :icon="CalendarIcon">
            <InfoItem
              label="Created By"
              :value="selectedRange.added_by?.name || 'N/A'"
            />
            <InfoItem
              label="Created At"
              :value="formatDate(selectedRange.created_at, true)"
            />
            <InfoItem
              label="Modified By"
              :value="selectedRange.modified_by?.name || 'N/A'"
            />
            <InfoItem
              label="Modified At"
              :value="formatDate(selectedRange.updated_at, true)"
            />
          </InfoSection>
        </div>
      </div>
    </InfoModal>

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Deletion"
      :description="`Are you sure you want to delete ${selectedRange?.title}? This action cannot be undone.`"
      confirmLabel="Delete Range"
      variant="danger"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />

    <ImagePreviewModal
      :isOpen="isPreviewOpen"
      :imageSrc="previewImage"
      :title="previewTitle"
      @close="isPreviewOpen = false"
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
  DollarSign as DollarSignIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
} from "lucide-vue-next";
import axios from "axios";
import PriceRangeModal from "./PriceRangeModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import InfoModal from "../../components/ui/InfoModal.vue";
import InfoSection from "../../components/ui/InfoSection.vue";
import InfoItem from "../../components/ui/InfoItem.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import ImagePreviewModal from "../../components/ui/ImagePreviewModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("Price Range");

// State
const priceRanges = ref([]);
const loading = ref(true);
const meta = ref({});
const links = ref({});
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedRange = ref(null);
const isDeleting = ref(false);
const isPreviewOpen = ref(false);
const previewImage = ref("");
const previewTitle = ref("");


const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "range", label: "Price Range", sortable: true, filterKey: "title" },
  { key: "min_price", label: "Min Price", sortable: true },
  { key: "max_price", label: "Max Price", sortable: true },
  { key: "status", label: "Status" },
  { key: "created_at", label: "Created", type: "date" },
  { key: "updated_at", label: "Modified", type: "date" },
  { key: "actions", label: "Actions", align: "right" },
];



// Methods
const fetchPriceRanges = async (url = "/api/v1/price-ranges") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        per_page: 50,
      },
    });
    if (response.data.success) {
      priceRanges.value = response.data.data.data.map((range, index) => ({
        ...range,
        sn: index + (response.data.data.from || 1),
      }));
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
    console.error("Failed to fetch price ranges", error);
    toastError("Failed to load price ranges");
  } finally {
    loading.value = false;
  }
};


const handleView = (range) => {
  selectedRange.value = range;
  isInfoModalOpen.value = true;
};

const openCreateModal = () => {
  selectedRange.value = null;
  isModalOpen.value = true;
};

const openEditModal = (range) => {
  selectedRange.value = range;
  isModalOpen.value = true;
};

const viewRange = (range) => {
  selectedRange.value = range;
  isInfoModalOpen.value = true;
};

const confirmDelete = (range) => {
  selectedRange.value = range;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedRange.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/price-ranges/${selectedRange.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message);
      fetchPriceRanges();
      isDeleteModalOpen.value = false;
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete price range");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedRange.value = null;
};

const openImagePreview = (image, title) => {
  if (!image) return;
  previewImage.value = getImageSource(image);
  previewTitle.value = title;
  isPreviewOpen.value = true;
};

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("http")) return path;
  return `/${path}`;
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

const rangeDisplay = (range) => {
  if (!range.min_price && !range.max_price) return "Any Price";
  if (!range.min_price)
    return `Up to ₹${Number(range.max_price).toLocaleString("en-IN")}`;
  if (!range.max_price)
    return `From ₹${Number(range.min_price).toLocaleString("en-IN")}`;
  return `₹${Number(range.min_price).toLocaleString("en-IN")} - ₹${Number(range.max_price).toLocaleString("en-IN")}`;
};

onMounted(() => {
  fetchPriceRanges();
});
</script>
