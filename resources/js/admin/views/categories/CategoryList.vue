<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Category Management"
      subtitle="Manage product categories, hierarchies, and organization."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add New Category
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="categories"
      :loading="loading"
      :from="meta.from"
      manual-filters
      @filter-change="handleFilterChange"
      empty-text="No categories found matching your criteria."
    >
      <!-- Custom Category Cell -->
      <template #cell-category="{ item: category }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-xs overflow-hidden shrink-0 cursor-pointer hover:scale-110 active:scale-95 transition-transform"
            @click="openImagePreview(category.photo, category.title)"
            title="Click to preview"
          >
            <img
              v-if="category.photo"
              :src="getImageSource(category.photo)"
              class="w-full h-full object-cover"
            />
            <FolderIcon v-else class="h-5 w-5" />
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-700 truncate">{{
              category.title
            }}</span>
            <span class="text-[11px] text-gray-500 truncate">{{
              category.slug
            }}</span>
          </div>
        </div>
      </template>

      <!-- Custom Parent Cell -->
      <template #cell-parent="{ item: category }">
        <span
          v-if="category.parent"
          class="text-xs text-gray-600 font-medium"
          >{{ category.parent.title }}</span
        >
        <span v-else class="text-xs text-gray-400 italic">—</span>
      </template>

      <!-- Custom Status Cell -->
      <template #cell-status="{ item: category }">
        <div class="flex items-center gap-2">
          <span
            class="h-1.5 w-1.5 rounded-full"
            :class="
              category.status === 'active' ? 'bg-emerald-500' : 'bg-gray-300'
            "
          ></span>
          <span class="text-xs font-medium text-gray-700 capitalize">{{
            category.status
          }}</span>
        </div>
      </template>

      <!-- Custom Created Cell -->
      <template #cell-created="{ item: category }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">{{
            formatDate(category.created_at)
          }}</span>
          <span v-if="category.added_by" class="text-[10px] text-gray-400"
            >by {{ category.added_by?.name || "Unknown" }}</span
          >
        </div>
      </template>

      <!-- Custom Modified Cell -->
      <template #cell-modified="{ item: category }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-500">{{
            formatDate(category.updated_at)
          }}</span>
          <span v-if="category.modified_by" class="text-[10px] text-gray-400"
            >by {{ category.modified_by?.name || "Unknown" }}</span
          >
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item: category }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
            @click="handleView(category)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(category)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Category"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(category)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Category"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>

    </DataTable>

    <!-- Modals -->
    <!-- Info Modal -->
    <InfoModal
      v-if="selectedCategory && isViewMode"
      :isOpen="isModalOpen"
      :title="selectedCategory.title"
      subtitle="Complete category details and hierarchy information."
      :icon="FolderIcon"
      @close="isModalOpen = false"
    >
      <div class="space-y-10">
        <!-- Visual Card -->
        <div
          class="flex flex-col items-center justify-center p-8 bg-gray-50/50 rounded-[2.5rem] border border-gray-100 relative overflow-hidden group"
        >
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-700"
          ></div>
          <div
            class="absolute bottom-0 left-0 w-24 h-24 bg-primary/5 rounded-full -ml-12 -mb-12 group-hover:scale-110 transition-transform duration-700 delay-100"
          ></div>

          <div
            class="w-24 h-24 rounded-[2rem] bg-primary/10 flex items-center justify-center text-primary text-3xl font-bold shadow-2xl relative z-10 overflow-hidden ring-4 ring-white"
          >
            <img
              v-if="selectedCategory.photo"
              :src="getImageSource(selectedCategory.photo)"
              class="w-full h-full object-cover"
            />
            <FolderIcon v-else class="h-12 w-12" />
          </div>
          <h4 class="mt-5 text-xl font-bold text-gray-700 relative z-10">
            {{ selectedCategory.title }}
          </h4>
          <span
            class="px-4 py-1.5 mt-2 rounded-full bg-white text-primary text-[10px] font-bold uppercase tracking-[0.15em] border border-primary/10 shadow-sm relative z-10"
            >{{
              selectedCategory.is_parent ? "Parent" : "Child"
            }}
            Category</span
          >
        </div>

        <!-- Data Sections -->
        <InfoSection title="Category Identity" columns="2">
          <InfoItem
            label="Category Name"
            :value="selectedCategory.title"
            :icon="FolderIcon"
          />
          <InfoItem
            label="URL Slug"
            :value="selectedCategory.slug"
            :icon="LinkIcon"
          />
        </InfoSection>

        <InfoSection
          v-if="selectedCategory.summary"
          title="Description"
          columns="1"
        >
          <InfoItem label="Summary" :value="selectedCategory.summary" />
        </InfoSection>

        <InfoSection title="Hierarchy & Status" columns="2">
          <InfoItem
            label="Parent Category"
            :value="selectedCategory.parent?.title || 'None (Root Category)'"
            :icon="FolderTreeIcon"
          />
          <InfoItem label="Current Status" :icon="ActivityIcon">
            <div class="flex items-center gap-2">
              <span
                class="h-2 w-2 rounded-full"
                :class="
                  selectedCategory.status === 'active'
                    ? 'bg-emerald-500'
                    : 'bg-gray-300'
                "
              ></span>
              <span class="text-sm font-semibold text-gray-700 capitalize">{{
                selectedCategory.status
              }}</span>
            </div>
          </InfoItem>
        </InfoSection>

        <InfoSection title="Audit Information" columns="2">
          <InfoItem
            label="Created Date"
            :value="formatDate(selectedCategory.created_at)"
            :icon="CalendarIcon"
          />
          <InfoItem
            label="Created By"
            :value="selectedCategory.added_by?.name || 'Unknown'"
            :icon="UserIcon"
          />
        </InfoSection>

        <InfoSection title="Modification History" columns="2">
          <InfoItem
            label="Last Modified"
            :value="formatDate(selectedCategory.updated_at)"
            :icon="CalendarIcon"
          />
          <InfoItem
            label="Modified By"
            :value="selectedCategory.modified_by?.name || 'Not modified yet'"
            :icon="UserIcon"
          />
        </InfoSection>

        <InfoSection title="System Info" columns="1">
          <InfoItem
            label="Category ID"
            :value="`#CAT-${selectedCategory.id.toString().padStart(5, '0')}`"
            :icon="HashIcon"
          />
        </InfoSection>
      </div>

      <template #footer>
        <button
          @click="isModalOpen = false"
          class="px-8 py-3.5 rounded-2xl bg-primary text-white font-bold hover:opacity-90 transition-all active:scale-95 shadow-xl shadow-primary/20"
        >
          Confirm Details
        </button>
      </template>
    </InfoModal>

    <!-- Edit/Create Modal -->
    <CategoryModal
      v-else
      :isOpen="isModalOpen"
      :editCategory="selectedCategory"
      @close="isModalOpen = false"
      @refresh="fetchCategories"
    />

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Delete Category"
      :description="`Are you sure you want to delete ${selectedCategory?.title}? This action cannot be undone.`"
      confirmLabel="Delete Category"
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
  Folder as FolderIcon,
  FolderTree as FolderTreeIcon,
  Link as LinkIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
} from "lucide-vue-next";
import axios from "axios";
import CategoryModal from "./CategoryModal.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";
import InfoModal from "../../components/ui/modals/InfoModal.vue";
import InfoSection from "../../components/ui/display/InfoSection.vue";
import InfoItem from "../../components/ui/display/InfoItem.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import ImagePreviewModal from "../../components/ui/modals/ImagePreviewModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";



const categories = ref([]);
const loading = ref(true);
const isDeleting = ref(false);
const isModalOpen = ref(false);
const isViewMode = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCategory = ref(null);
const isPreviewOpen = ref(false);
const previewImage = ref("");
const previewTitle = ref("");
const columnFilters = ref({});

const meta = ref({ from: 1 });
const links = ref({});
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("Category");
const { success: toastSuccess, error: toastError } = useToast();

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "category", label: "Category", align: "left", filterKey: "title" },
  { key: "parent", label: "Parent", align: "left", filterKey: "parent.title" },
  { key: "status", label: "Status", align: "left" },
  { key: "created", label: "Created", align: "left", type: "date", filterKey: "created_at" },
  { key: "modified", label: "Modified", align: "left", type: "date", filterKey: "updated_at" },
  { key: "actions", label: "Actions", align: "right" },
];

const fetchCategories = async (url = "/api/v1/categories") => {
  loading.value = true;
  try {
    const params = {
      per_page: 10,
      ...columnFilters.value
    };

    const response = await axios.get(url, { params });

    if (response.data.success) {
      categories.value = response.data.data.data;
      meta.value = {
        total: response.data.data.total,
        from: response.data.data.from || 1,
        to: response.data.data.to,
      };
      links.value = {
        next: response.data.data.next_page_url,
        prev: response.data.data.prev_page_url,
      };
    }
  } catch (e) {
    console.error("Failed to fetch categories", e);
  } finally {
    loading.value = false;
  }
};

const handleFilterChange = (filters) => {
  columnFilters.value = filters;
  fetchCategories();
};


const resetFilters = () => {
  filters.status = "";
  filters.is_parent = "";
  fetchCategories();
};

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  if (path.startsWith("http")) return path;
  return `/${path}`;
};

const handleView = (category) => {
  selectedCategory.value = category;
  isViewMode.value = true;
  isModalOpen.value = true;
};

const openImagePreview = (image, title) => {
  if (!image) return;
  previewImage.value = getImageSource(image);
  previewTitle.value = title;
  isPreviewOpen.value = true;
};

const formatDate = (date) => {
  if (!date) return "N/A";
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  return `${day}-${month}-${year}`;
};

const openCreateModal = () => {
  selectedCategory.value = null;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const openEditModal = (category) => {
  selectedCategory.value = category;
  isViewMode.value = false;
  isModalOpen.value = true;
};

const confirmDelete = (category) => {
  selectedCategory.value = category;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/categories/${selectedCategory.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message || "Category deleted successfully");
      isDeleteModalOpen.value = false;
      fetchCategories();
    }
  } catch (e) {
    console.error("Failed to delete category", e);
    const message = e.response?.data?.message || "Failed to delete category";
    toastError(message);
  } finally {
    isDeleting.value = false;
  }
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

onMounted(fetchCategories);
</script>

<style scoped></style>
