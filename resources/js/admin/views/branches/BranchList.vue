<template>
  <div class="space-y-6">
    <PageHeader title="Branch Management" subtitle="Branches orders are placed in. Inactive branches are hidden from pickers but keep their orders.">
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Branch
        </button>
      </template>
    </PageHeader>

    <DataTable
      :columns="columns"
      :items="branches"
      :loading="loading"
      :from="1"
      manual-filters
      @filter-change="handleFilterChange"
      @row-click="canView ? viewBranch($event) : null"
      empty-text="No branches found."
    >
      <!-- Branch -->
      <template #cell-branch="{ item: branch }">
        <div class="flex items-center gap-4">
          <div class="h-10 w-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary shrink-0 border border-primary/10 font-bold text-xs">
            {{ initials(branch.name) }}
          </div>
          <span class="text-sm font-semibold text-gray-700 truncate">{{ branch.name }}</span>
        </div>
      </template>

      <!-- Status -->
      <template #cell-status="{ item: branch }">
        <span
          :class="[
            'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider border',
            branch.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-50 text-gray-500 border-gray-100',
          ]"
        >
          <span :class="['w-1.5 h-1.5 rounded-full mr-2', branch.is_active ? 'bg-emerald-500' : 'bg-gray-400']"></span>
          {{ branch.is_active ? "Active" : "Inactive" }}
        </span>
      </template>

      <!-- Orders -->
      <template #cell-orders="{ item: branch }">
        <span class="text-sm font-semibold text-gray-700">{{ Number(branch.orders_count || 0).toLocaleString("en-IN") }}</span>
      </template>

      <!-- Created -->
      <template #cell-created_at="{ item: branch }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{ formatDate(branch.created_at) }}</span>
          <span v-if="branch.added_by" class="text-[10px] text-gray-400">by {{ branch.added_by.name }}</span>
        </div>
      </template>

      <!-- Modified -->
      <template #cell-updated_at="{ item: branch }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{ formatDate(branch.updated_at) }}</span>
          <span v-if="branch.modified_by" class="text-[10px] text-gray-400">by {{ branch.modified_by.name }}</span>
        </div>
      </template>

      <!-- Actions -->
      <template #cell-actions="{ item: branch }">
        <div class="flex justify-end gap-1.5" @click.stop>
          <button
            v-if="canView"
            @click="viewBranch(branch)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="openEditModal(branch)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Branch"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
          <button
            v-if="canDelete"
            @click="confirmDelete(branch)"
            :disabled="isUsed(branch)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed"
            :title="isUsed(branch) ? 'Used by orders or enquiries — set it to Inactive instead' : 'Delete Branch'"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Add / Edit -->
    <BranchModal :isOpen="isModalOpen" :editBranch="selectedBranch" @close="closeModal" @refresh="refresh" />

    <!-- Info -->
    <InfoModal :isOpen="isInfoModalOpen" title="Branch Details" @close="isInfoModalOpen = false">
      <div v-if="selectedBranch" class="space-y-8">
        <div class="flex flex-col items-center">
          <div class="w-24 h-24 rounded-[2rem] bg-primary/5 flex items-center justify-center border-2 border-white shadow-xl mb-4 ring-8 ring-gray-50/50 text-2xl font-bold text-primary">
            {{ initials(selectedBranch.name) }}
          </div>
          <h4 class="mt-5 text-xl font-bold text-gray-700">{{ selectedBranch.name }}</h4>
          <span
            :class="[
              'mt-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border',
              selectedBranch.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-50 text-gray-500 border-gray-100',
            ]"
          >
            {{ selectedBranch.is_active ? "Active" : "Inactive" }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <InfoSection title="Branch Info" :icon="StoreIcon">
            <InfoItem label="Name" :value="selectedBranch.name" />
            <InfoItem label="Status" :value="selectedBranch.is_active ? 'Active — shown in pickers' : 'Inactive — hidden from pickers'" />
            <InfoItem label="Orders" :value="Number(selectedBranch.orders_count || 0).toLocaleString('en-IN')" />
            <InfoItem label="Enquiries" :value="Number(selectedBranch.enquiries_count || 0).toLocaleString('en-IN')" />
            <InfoItem label="Display Order" :value="String(selectedBranch.sort_order ?? '—')" />
          </InfoSection>

          <InfoSection title="Audit Details" :icon="CalendarIcon">
            <InfoItem label="Created By" :value="selectedBranch.added_by?.name || 'System'" />
            <InfoItem label="Created At" :value="formatDate(selectedBranch.created_at)" />
            <InfoItem label="Modified By" :value="selectedBranch.modified_by?.name || 'System'" />
            <InfoItem label="Modified At" :value="formatDate(selectedBranch.updated_at)" />
          </InfoSection>
        </div>
      </div>
    </InfoModal>

    <!-- Delete -->
    <ConfirmationModal
      :isOpen="isDeleteModalOpen"
      title="Delete Branch"
      :description="`Delete branch ${selectedBranch?.name}? It has no orders or enquiries.`"
      confirmLabel="Delete Branch"
      variant="danger"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import {
  Plus as PlusIcon,
  Edit3 as Edit3Icon,
  Trash2 as Trash2Icon,
  Eye as EyeIcon,
  Store as StoreIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import axios from "axios";
import BranchModal from "./BranchModal.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";
import InfoModal from "../../components/ui/modals/InfoModal.vue";
import InfoSection from "../../components/ui/display/InfoSection.vue";
import InfoItem from "../../components/ui/display/InfoItem.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import { useBranches } from "../../composables/useBranches";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Branch");
const { fetchBranches: refreshPickerBranches } = useBranches();

const branches = ref([]);
const loading = ref(true);
const isModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedBranch = ref(null);
const isDeleting = ref(false);
const columnFilters = ref({});

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "branch", label: "Branch", filterKey: "name" },
  {
    key: "status",
    label: "Status",
    type: "select",
    filterKey: "status",
    placeholder: "All Status",
    options: [
      { label: "Active", value: "active" },
      { label: "Inactive", value: "inactive" },
    ],
  },
  { key: "orders", label: "Orders", filter: false },
  { key: "created_at", label: "Created", filter: false },
  { key: "updated_at", label: "Modified", filter: false },
  { key: "actions", label: "Actions", align: "right" },
];

const fetchBranchList = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/branches", { params: { ...columnFilters.value } });
    if (response.data.success) {
      branches.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch branches", error);
    toastError("Failed to load branches");
  } finally {
    loading.value = false;
  }
};

// Reload this table and the shared list used by Order Placed In pickers and filters
const refresh = () => {
  fetchBranchList();
  refreshPickerBranches(true);
};

const handleFilterChange = (filters) => {
  columnFilters.value = filters;
  fetchBranchList();
};

const openCreateModal = () => {
  selectedBranch.value = null;
  isModalOpen.value = true;
};

const openEditModal = (branch) => {
  selectedBranch.value = branch;
  isModalOpen.value = true;
};

const viewBranch = (branch) => {
  selectedBranch.value = branch;
  isInfoModalOpen.value = true;
};

const confirmDelete = (branch) => {
  if (isUsed(branch)) return;
  selectedBranch.value = branch;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedBranch.value) return;
  isDeleting.value = true;
  try {
    const response = await axios.delete(`/api/v1/branches/${selectedBranch.value.id}`);
    if (response.data.success) {
      toastSuccess(response.data.message);
      isDeleteModalOpen.value = false;
      refresh();
    }
  } catch (error) {
    toastError(error.response?.data?.message || "Failed to delete branch");
  } finally {
    isDeleting.value = false;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedBranch.value = null;
};

// A branch with orders or enquiries cannot be deleted (only deactivated)
const isUsed = (branch) => (branch.orders_count || 0) + (branch.enquiries_count || 0) > 0;

const initials = (name) => (name || "").replace(/[^A-Za-z0-9]/g, "").substring(0, 2).toUpperCase();

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "N/A";
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  return `${day}-${month}-${date.getFullYear()}`;
};

onMounted(fetchBranchList);
</script>
