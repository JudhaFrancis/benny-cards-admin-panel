<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Product Management"
      subtitle="Organize your store inventory, manage pricing, and curate featured highlights."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Product
        </button>
      </template>
    </PageHeader>

    <!-- Tabs Container -->
    <div
      class="flex items-center gap-1 bg-gray-100/50 p-1.5 rounded-2xl w-fit border border-gray-100 shadow-sm animate-in fade-in duration-700"
    >
      <button
        v-for="tab in tabOptions"
        :key="tab.value"
        @click="switchTab(tab.value)"
        :class="[
          'px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2',
          filters.type === tab.value
            ? 'bg-white text-primary shadow-md shadow-primary/5 border border-gray-100'
            : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50',
        ]"
      >
        <component :is="tab.icon" class="h-4 w-4" />
        {{ tab.label }}
      </button>
    </div>

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
              placeholder="Search by title..."
              class="w-full pl-11 pr-4 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-gray-700"
              style="color: #475569 !important"
              @input="debounceSearch"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
            @apply="fetchProducts"
          >
            <!-- Status Filter -->
            <FilterSection label="Active Status">
              <ContextDropdown
                v-model="filters.status"
                :options="statusFilterOptions"
                :icon="ActivityIcon"
              />
            </FilterSection>

            <!-- Category Filter -->
            <FilterSection label="Categories">
              <select
                v-model="filters.cat_id"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-gray-700 font-semibold"
              >
                <option value="">All Categories</option>
                <option
                  v-for="cat in options.categories"
                  :key="cat.id"
                  :value="cat.id"
                >
                  {{ cat.title }}
                </option>
              </select>
            </FilterSection>

            <!-- Brand Filter -->
            <FilterSection label="Brands" last>
              <select
                v-model="filters.brand_id"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-gray-700 font-semibold"
              >
                <option value="">All Brands</option>
                <option
                  v-for="brand in options.brands"
                  :key="brand.id"
                  :value="brand.id"
                >
                  {{ brand.title }}
                </option>
              </select>
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
          Showing {{ meta.total || 0 }} items
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="products"
      :loading="loading"
      empty-text="No products found in this category."
    >
      <!-- Image Cell -->
      <template #cell-photo="{ item: product }">
        <div class="flex items-center">
          <div
            class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-100 shadow-sm group cursor-zoom-in"
            @click="previewImage(product.photo, product.title)"
          >
            <img
              :src="getImageSource(product.photo)"
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

      <!-- Info Cell -->
      <template #cell-title="{ item: product }">
        <div class="flex flex-col min-w-[200px]">
          <span class="text-sm font-bold text-gray-700 tracking-tight">{{
            product.title
          }}</span>
          <div class="flex items-center gap-2 mt-0.5">
            <span
              class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded font-bold uppercase"
              >{{ product.category?.title || "Uncategorized" }}</span
            >
            <span
              v-if="product.brand"
              class="text-[10px] px-1.5 py-0.5 bg-blue-50 text-blue-500 rounded font-bold uppercase"
              >{{ product.brand.title }}</span
            >
          </div>
        </div>
      </template>

      <!-- Price & Stock Cell -->
      <template #cell-price="{ item: product }">
        <div class="flex flex-col">
          <div class="text-sm font-black text-gray-700 flex items-center gap-1">
            ${{ product.price }}
            <span
              v-if="product.discount > 0"
              class="text-[10px] font-bold text-emerald-500"
              >(-{{ product.discount }}%)</span
            >
          </div>
          <span
            :class="[
              'text-[10px] font-bold uppercase tracking-wider mt-0.5',
              product.stock > 0
                ? 'text-gray-400'
                : 'text-rose-500 animate-pulse',
            ]"
          >
            {{
              product.stock > 0 ? `${product.stock} in stock` : "Out of Stock"
            }}
          </span>
        </div>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: product }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
              product.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full mr-2',
                product.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400',
              ]"
            ></span>
            {{ product.status === "active" ? "Live" : "Inactive" }}
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: product }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(product.created_at)
          }}</span>
          <span v-if="product.added_by" class="text-[10px] text-gray-400">
            by {{ product.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Audit Cell -->
      <template #cell-updated_at="{ item: product }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(product.updated_at)
          }}</span>
          <span v-if="product.modified_by" class="text-[10px] text-gray-400">
            by {{ product.modified_by?.name || "Unknown" }}
          </span>
          <span v-else-if="product.added_by" class="text-[10px] text-gray-400">
            by {{ product.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: product }">
        <div class="flex justify-end gap-1.5">
          <button
            @click="viewProduct(product)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(product)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Product"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(product)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Product"
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
          @click="fetchProducts(links.prev)"
          :disabled="!links.prev"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="fetchProducts(links.next)"
          :disabled="!links.next"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Modals -->
    <ProductModal
      :isOpen="isModalOpen"
      :editProduct="selectedProduct"
      :defaultType="filters.type"
      :options="options"
      @close="closeModal"
      @refresh="fetchProducts"
    />

    <InfoModal
      :isOpen="isInfoModalOpen"
      title="Product Details"
      @close="isInfoModalOpen = false"
    >
      <div v-if="selectedProduct" class="space-y-8">
        <!-- Visual Profile -->
        <div class="flex flex-col items-center">
          <div
            class="w-full h-56 rounded-[2rem] bg-gray-50 flex items-center justify-center shadow-xl overflow-hidden border-2 border-white cursor-zoom-in group"
            @click="previewImage(selectedProduct.photo, selectedProduct.title)"
          >
            <img
              :src="getImageSource(selectedProduct.photo)"
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>
          <h4
            class="mt-6 text-2xl font-black text-gray-800 tracking-tight text-center"
          >
            {{ selectedProduct.title }}
          </h4>
          <div class="flex items-center gap-2 mt-1">
            <span
              class="text-[10px] font-bold text-primary uppercase tracking-widest"
              >{{
                selectedProduct.type === "gift"
                  ? "Gift Item"
                  : "Invitation Card"
              }}</span
            >
            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            <span
              class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
              >{{ selectedProduct.category?.title }}</span
            >
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Pricing & Inventory" :icon="TagIcon">
            <InfoItem label="Base Price" :value="`$${selectedProduct.price}`" />
            <InfoItem
              label="Discount"
              :value="`${selectedProduct.discount || 0}%`"
            />
            <InfoItem label="Stock Level" :value="selectedProduct.stock" />
            <InfoItem
              label="Condition"
              :value="selectedProduct.condition"
              class="capitalize"
            />
          </InfoSection>

          <InfoSection title="Internal Tracking" :icon="CalendarIcon">
            <InfoItem
              label="Product ID"
              :value="`#PROD-${selectedProduct.id}`"
            />
            <InfoItem
              label="Status"
              :value="selectedProduct.status"
              class="capitalize"
            />
            <InfoItem
              label="Created By"
              :value="selectedProduct.added_by?.name || 'N/A'"
            />
            <InfoItem
              label="Last Modified"
              :value="
                selectedProduct.modified_by?.name ||
                selectedProduct.added_by?.name ||
                'N/A'
              "
            />
          </InfoSection>

          <div class="md:col-span-2 space-y-8">
            <!-- Gallery Section -->
            <div v-if="selectedProduct.images?.length" class="space-y-4">
              <span
                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block ml-1"
                >Product Gallery</span
              >
              <div class="grid grid-cols-4 gap-4">
                <div
                  v-for="(img, idx) in selectedProduct.images"
                  :key="idx"
                  class="aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm group cursor-zoom-in bg-gray-50 flex items-center justify-center p-1"
                  @click="
                    previewImage(
                      img.image_path,
                      selectedProduct.title + ' Gallery ' + (idx + 1),
                    )
                  "
                >
                  <img
                    :src="getImageSource(img.image_path)"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 rounded-xl"
                  />
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div
                class="p-5 rounded-3xl bg-gray-50 border border-gray-100 shadow-inner"
              >
                <span
                  class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2"
                  >Summary</span
                >
                <div
                  class="text-sm text-gray-700 leading-relaxed quill-content"
                  v-html="selectedProduct.summary"
                ></div>
              </div>
              <div
                class="p-5 rounded-3xl bg-gray-50 border border-gray-100 shadow-inner"
              >
                <span
                  class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2"
                  >Detailed Description</span
                >
                <div
                  class="text-sm text-gray-700 leading-relaxed quill-content"
                  v-html="
                    selectedProduct.description || 'No description provided.'
                  "
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </InfoModal>

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Delete Product"
      :description="`Are you sure you want to remove '${selectedProduct?.title}'? Current stock is ${selectedProduct?.stock}. The product image file will also be permanently deleted from the storage.`"
      confirmLabel="Confirm Delete"
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
  Ticket as TicketIcon,
  Gift as GiftIcon,
  Tag as TagIcon,
  Layers as LayersIcon,
} from "lucide-vue-next";
import axios from "axios";
import ProductModal from "./ProductModal.vue";
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
const products = ref([]);
const loading = ref(true);
const meta = ref({});
const links = ref({});
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedProduct = ref(null);
const isDeleting = ref(false);
const options = reactive({
  categories: [],
  brands: [],
});

const isImagePreviewOpen = ref(false);
const previewSrc = ref("");
const previewTitle = ref("");

const filters = reactive({
  search: "",
  status: "",
  type: "card", // card = Invitation Card, gift = Gift
  cat_id: "",
  brand_id: "",
});

const tabOptions = [
  { label: "Invitation Cards", value: "card", icon: TicketIcon },
  { label: "Gifts", value: "gift", icon: GiftIcon },
];

const columns = [
  { key: "photo", label: "Product", width: "80px" },
  { key: "title", label: "Title & Meta", sortable: true },
  { key: "price", label: "Price / Stock" },
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
  if (filters.cat_id) count++;
  if (filters.brand_id) count++;
  return count;
});

// Methods
const fetchProducts = async (url = "/api/v1/products") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        search: filters.search,
        status: filters.status,
        type: filters.type,
        cat_id: filters.cat_id,
        brand_id: filters.brand_id,
      },
    });
    if (response.data.success) {
      products.value = response.data.data.data;
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
    console.error("Failed to fetch products", error);
    toastError("Failed to load products");
  } finally {
    loading.value = false;
  }
};

const fetchOptions = async () => {
  try {
    const response = await axios.get("/api/v1/products/options");
    if (response.data.success) {
      options.categories = response.data.data.categories;
      options.brands = response.data.data.brands;
    }
  } catch (error) {
    console.error("Failed to fetch product options", error);
  }
};

const switchTab = (tabValue) => {
  filters.type = tabValue;
  fetchProducts();
};

let debounceTimeout;
const debounceSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchProducts();
  }, 500);
};

const resetFilters = () => {
  filters.search = "";
  filters.status = "";
  filters.cat_id = "";
  filters.brand_id = "";
  fetchProducts();
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
  selectedProduct.value = null;
  isModalOpen.value = true;
};

const openEditModal = (product) => {
  selectedProduct.value = product;
  isModalOpen.value = true;
};

const viewProduct = (product) => {
  selectedProduct.value = product;
  isInfoModalOpen.value = true;
};

const confirmDelete = (product) => {
  selectedProduct.value = product;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedProduct.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/products/${selectedProduct.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message);
      fetchProducts();
      isDeleteModalOpen.value = false;
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete product");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedProduct.value = null;
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
  fetchProducts();
  fetchOptions();
});
</script>

<style>
.quill-content h1 {
  @apply text-2xl font-bold mb-2;
}
.quill-content h2 {
  @apply text-xl font-bold mb-2;
}
.quill-content p {
  @apply mb-2;
}
.quill-content ul {
  @apply list-disc ml-4 mb-2;
}
.quill-content ol {
  @apply list-decimal ml-4 mb-2;
}
</style>
