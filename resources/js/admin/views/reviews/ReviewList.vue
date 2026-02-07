<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Product Reviews"
      subtitle="Manage customer reviews, ratings, and feedback."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Review
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
              class="absolute left-4 top-1/2 -trangray-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search by product, reviewer, content..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
              style="color: #475569 !important"
              @input="debounceSearch"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
            @apply="fetchReviews"
          >
            <FilterSection label="Status">
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
          Showing {{ meta.total || 0 }} reviews
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="reviews"
      :loading="loading"
      empty-text="No reviews found matching your criteria."
    >
      <!-- Product Cell -->
      <template #cell-product="{ item: review }">
        <div class="flex flex-col min-w-0">
          <span
            class="text-sm font-semibold text-gray-700 truncate"
            :title="review.product?.title"
          >
            {{ review.product?.title || "Unknown Product" }}
          </span>
          <span
            class="text-[11px] text-gray-500 truncate"
            v-if="review.product"
          >
            SKU: {{ review.product.sku || "N/A" }}
          </span>
        </div>
      </template>

      <!-- Reviewer Cell -->
      <template #cell-reviewer="{ item: review }">
        <div class="flex flex-col min-w-0">
          <span class="text-sm font-medium text-gray-700 truncate">
            {{ review.user?.name || review.reviewer_name || "Guest" }}
          </span>
          <span
            class="text-[11px] text-gray-400 truncate"
            v-if="review.user?.email"
          >
            {{ review.user.email }}
          </span>
        </div>
      </template>

      <!-- Rating Cell -->
      <template #cell-rating="{ item: review }">
        <div class="flex items-center gap-1">
          <StarIcon
            v-for="i in 5"
            :key="i"
            class="h-3.5 w-3.5"
            :class="
              i <= review.rating
                ? 'fill-amber-400 text-amber-400'
                : 'text-gray-300'
            "
          />
          <span class="text-xs font-bold text-gray-600 ml-1"
            >({{ review.rating }})</span
          >
        </div>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: review }">
        <div class="flex items-center gap-2">
          <span
            class="h-1.5 w-1.5 rounded-full"
            :class="
              review.status === 'active' ? 'bg-emerald-500' : 'bg-gray-300'
            "
          ></span>
          <span class="text-xs font-medium text-gray-700 capitalize">{{
            review.status
          }}</span>
        </div>
      </template>

      <!-- Created Cell -->
      <template #cell-created="{ item: review }">
        <span class="text-xs text-gray-500">{{
          formatDate(review.created_at)
        }}</span>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: review }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
            @click="handleView(review)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(review)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Review"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(review)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Review"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>

      <!-- Pagination Section -->
      <template #pagination>
        <div
          v-if="!loading && reviews.length > 0"
          class="px-6 py-4 border-t border-gray-100 flex items-center justify-between"
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
              :disabled="!links.prev"
              @click="fetchReviews(links.prev)"
              class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
              <ChevronLeftIcon class="h-4 w-4 text-gray-600" />
            </button>
            <button
              :disabled="!links.next"
              @click="fetchReviews(links.next)"
              class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
              <ChevronRightIcon class="h-4 w-4 text-gray-600" />
            </button>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Modals -->
    <ReviewInfoDialog
      v-if="selectedReview && isViewMode"
      :isOpen="isModalOpen"
      :review="selectedReview"
      @close="isModalOpen = false"
    />

    <ReviewModal
      v-else
      :isOpen="isModalOpen"
      :editReview="selectedReview"
      @close="isModalOpen = false"
      @refresh="fetchReviews"
    />

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Delete Review"
      :description="`Are you sure you want to delete this review? This action cannot be undone.`"
      confirmLabel="Delete Review"
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
  Activity as ActivityIcon,
  Star as StarIcon,
} from "lucide-vue-next";
import axios from "axios";
import ReviewModal from "./ReviewModal.vue";
import ReviewInfoDialog from "../../components/reviews/ReviewInfoDialog.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import FilterSection from "../../components/ui/FilterSection.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const statusFilterOptions = [
  { label: "All Statuses", value: "" },
  {
    label: "Active",
    value: "active",
    description: "Review is visible.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Review is hidden.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

const reviews = ref([]);
const loading = ref(true);
const isDeleting = ref(false);
const isModalOpen = ref(false);
const isViewMode = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReview = ref(null);

const meta = ref({});
const links = ref({});
const { getModulePermissions } = usePermissions();
// Note: Assuming module name 'Reviews' or 'ProductReview'. Let's stick with specific naming if possible, but 'Reviews' is generic enough.
// Controller is ProductReview. Permission likely based on resource name. 'reviews' resource -> 'reviews-list'.
// Assuming generic map for now.
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Review");

const { success: toastSuccess, error: toastError } = useToast();

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "product", label: "Product", align: "left" },
  { key: "reviewer", label: "Reviewer", align: "left" },
  { key: "rating", label: "Rating", align: "left" },
  { key: "status", label: "Status", align: "left" },
  { key: "created", label: "Date", align: "left" },
  { key: "actions", label: "Actions", align: "right" },
];

const filters = reactive({
  search: "",
  status: "",
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (filters.status) count++;
  return count;
});

let searchTimeout = null;

const fetchReviews = async (url = "/api/v1/reviews") => {
  loading.value = true;
  try {
    const params = {
      search: filters.search,
      status: filters.status,
    };

    const finalUrl = url.includes("?") ? url : url;
    const response = await axios.get(finalUrl, { params });

    if (response.data.success) {
      reviews.value = response.data.data.data.map((item, index) => ({
        ...item,
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
  } catch (e) {
    console.error("Failed to fetch reviews", e);
    toastError("Failed to load reviews");
  } finally {
    loading.value = false;
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchReviews();
  }, 500);
};

const resetFilters = () => {
  filters.status = "";
  fetchReviews();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

const handleView = (review) => {
  selectedReview.value = review;
  isViewMode.value = true;
  isModalOpen.value = true;
};

const openCreateModal = () => {
  selectedReview.value = null;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const openEditModal = (review) => {
  selectedReview.value = review;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const confirmDelete = (review) => {
  selectedReview.value = review;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/reviews/${selectedReview.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message || "Review deleted successfully");
      isDeleteModalOpen.value = false;
      fetchReviews();
    }
  } catch (e) {
    console.error("Failed to delete review", e);
    const message = e.response?.data?.message || "Failed to delete review";
    toastError(message);
  } finally {
    isDeleting.value = false;
  }
};

onMounted(fetchReviews);
</script>
