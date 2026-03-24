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

    <!-- Filters & Search (REMOVED) -->

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

      <!-- Price Cell -->
      <template #cell-price="{ item: product }">
        <div class="flex flex-col">
          <div class="text-sm font-black text-gray-700 flex items-center gap-1">
            ₹{{
              Number(product.price).toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              })
            }}
            <span
              v-if="product.discount > 0"
              class="text-[10px] font-bold text-emerald-500"
              >(-{{ product.discount }}%)</span
            >
          </div>
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
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
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
            <InfoItem
              label="Base Price"
              :value="`₹${Number(selectedProduct.price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"
            />
            <InfoItem
              label="Discount"
              :value="`${selectedProduct.discount || 0}%`"
            />
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
      :description="`Are you sure you want to remove '${selectedProduct?.title}'? The product image file will also be permanently deleted from the storage.`"
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
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import ImagePreviewModal from "../../components/ui/ImagePreviewModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Product");

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
  type: "card", // card = Invitation Card, gift = Gift
});

const tabOptions = [
  { label: "Invitation Cards", value: "card", icon: TicketIcon },
  { label: "Gifts", value: "gift", icon: GiftIcon },
];

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "photo", label: "Product", width: "80px", filter: false },
  { key: "title", label: "Title & Meta", sortable: true },
  { key: "price", label: "Price", filterKey: "price" },
  { key: "status", label: "Status" },
  { key: "created_at", label: "Created", type: "date" },
  { key: "updated_at", label: "Modified", type: "date" },
  { key: "actions", label: "Actions", align: "right" },
];



// Methods
const fetchProducts = async (url = "/api/v1/products") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        type: filters.type,
        per_page: 50,
      },
    });
    if (response.data.success) {
      products.value = response.data.data.data.map((product, index) => ({
        ...product,
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



const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
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
