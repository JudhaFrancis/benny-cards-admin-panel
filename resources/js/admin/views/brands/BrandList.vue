<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Brand Management"
      subtitle="Manage your product brands, status, and audit history."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Brand
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="brands"
      :loading="loading"
      empty-text="No brands found matching your criteria."
    >
      <!-- Custom Brand Cell -->
      <template #cell-brand="{ item: brand }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden shrink-0 border border-gray-200 font-bold text-xs"
          >
            {{ brand.title.substring(0, 2).toUpperCase() }}
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-700 truncate">{{
              brand.title
            }}</span>
            <span class="text-[11px] text-gray-500 truncate">{{
              brand.slug
            }}</span>
          </div>
        </div>
      </template>

      <!-- Status Cell -->
      <template #cell-status="{ item: brand }">
        <div class="flex">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
              brand.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full mr-2',
                brand.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400',
              ]"
            ></span>
            {{ brand.status === "active" ? "Live" : "Hidden" }}
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: brand }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(brand.created_at)
          }}</span>
          <span v-if="brand.added_by" class="text-[10px] text-gray-400">
            by {{ brand.added_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Modified Date Cell -->
      <template #cell-updated_at="{ item: brand }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(brand.updated_at)
          }}</span>
          <span v-if="brand.modified_by" class="text-[10px] text-gray-400">
            by {{ brand.modified_by?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: brand }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
            @click="viewBrand(brand)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(brand)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Brand"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(brand)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Brand"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>


    <!-- Modals -->
    <BrandModal
      :isOpen="isModalOpen"
      :editBrand="selectedBrand"
      @close="closeModal"
      @refresh="fetchBrands"
    />

    <InfoModal
      :isOpen="isInfoModalOpen"
      title="Brand Details"
      @close="isInfoModalOpen = false"
    >
      <div v-if="selectedBrand" class="space-y-8">
        <!-- Visual Profile -->
        <div class="flex flex-col items-center">
          <div
            class="w-24 h-24 rounded-[2rem] bg-gray-50 flex items-center justify-center border-2 border-white shadow-xl overflow-hidden mb-4 ring-8 ring-gray-50/50 text-2xl font-bold text-gray-300"
          >
            {{ selectedBrand.title.substring(0, 2).toUpperCase() }}
          </div>
          <h4 class="mt-5 text-xl font-bold text-gray-700 relative z-10">
            {{ selectedBrand.title }}
          </h4>
          <span
            :class="[
              'mt-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border',
              selectedBrand.status === 'active'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            {{ selectedBrand.status }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Brand Info" :icon="TagIcon">
            <InfoItem label="Title" :value="selectedBrand.title" />
            <InfoItem label="Slug" :value="selectedBrand.slug" />
          </InfoSection>

          <InfoSection title="Audit Details" :icon="CalendarIcon">
            <InfoItem
              label="Created By"
              :value="selectedBrand.added_by?.name || 'N/A'"
            />
            <InfoItem
              label="Created At"
              :value="formatDate(selectedBrand.created_at, true)"
            />
            <InfoItem
              label="Modified By"
              :value="selectedBrand.modified_by?.name || 'N/A'"
            />
            <InfoItem
              label="Modified At"
              :value="formatDate(selectedBrand.updated_at, true)"
            />
          </InfoSection>
        </div>
      </div>
    </InfoModal>

    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Confirm Deletion"
      :description="`Are you sure you want to delete ${selectedBrand?.title}? This action cannot be undone.`"
      confirmLabel="Delete Brand"
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
  Tag as TagIcon,
  Activity as ActivityIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
} from "lucide-vue-next";
import axios from "axios";
import BrandModal from "./BrandModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import InfoModal from "../../components/ui/InfoModal.vue";
import InfoSection from "../../components/ui/InfoSection.vue";
import InfoItem from "../../components/ui/InfoItem.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Brands");

// State
const brands = ref([]);
const loading = ref(true);
const meta = ref({});
const links = ref({});
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedBrand = ref(null);
const isDeleting = ref(false);

const filters = reactive({});

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "brand", label: "Brand", sortable: true, filterKey: "title" },
  { key: "status", label: "Status" },
  { key: "created_at", label: "Created", type: "date" },
  { key: "updated_at", label: "Modified", type: "date" },
  { key: "actions", label: "Actions", align: "right" },
];



// Methods
const fetchBrands = async (url = "/api/v1/brands") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        per_page: 50,
      },
    });
    if (response.data.success) {
      brands.value = response.data.data.data.map((brand, index) => ({
        ...brand,
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
    console.error("Failed to fetch brands", error);
    toastError("Failed to load brands");
  } finally {
    loading.value = false;
  }
};


const openCreateModal = () => {
  selectedBrand.value = null;
  isModalOpen.value = true;
};

const openEditModal = (brand) => {
  selectedBrand.value = brand;
  isModalOpen.value = true;
};

const viewBrand = (brand) => {
  selectedBrand.value = brand;
  isInfoModalOpen.value = true;
};

const confirmDelete = (brand) => {
  selectedBrand.value = brand;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedBrand.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/brands/${selectedBrand.value.id}`,
    );
    if (response.data.success) {
      toastSuccess(response.data.message);
      fetchBrands();
      isDeleteModalOpen.value = false;
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete brand");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedBrand.value = null;
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
  fetchBrands();
});
</script>
