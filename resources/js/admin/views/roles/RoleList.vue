<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Role Management"
      subtitle="Manage user roles and their assigned permissions."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="isCreateModalOpen = true"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Role
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="roles"
      :loading="loading"
      empty-text="No roles found matching your criteria."
    >
      <!-- Custom Checkbox Cell (if needed, otherwise handled by DataTable if selectable) -->


      <!-- Role Name Cell -->
      <template #cell-name="{ item: role }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden shrink-0 border border-gray-200 font-bold text-xs"
          >
            {{ role.name.substring(0, 2).toUpperCase() }}
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-700 truncate">{{
              role.name
            }}</span>
          </div>
        </div>
      </template>

      <!-- Permissions Cell -->
      <template #cell-permissions="{ item: role }">
        <div class="flex flex-wrap gap-1">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ role.permissions ? role.permissions.length : 0 }} Permissions
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: role }">
        <div class="flex flex-col">
          <span class="text-xs text-gray-600">{{
            formatDate(role.created_at)
          }}</span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: role }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <button
            v-if="canView"
            @click="viewRole(role)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Details"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit"
            @click="handleEdit(role)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
            title="Edit Role"
          >
            <Edit3Icon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Dialogs -->
    <RoleInfoDialog
      :isOpen="isInfoModalOpen"
      :role="selectedRole"
      @close="isInfoModalOpen = false"
    />

    <RoleEditDialog
      :open="isEditModalOpen"
      :role="selectedRole"
      :modules="modules"
      :is-saving="isSaving"
      @openChange="isEditModalOpen = $event"
      @save="handleSave"
    />

    <RoleCreateDialog
      :isOpen="isCreateModalOpen"
      :modules="modules"
      :is-saving="isSaving"
      @close="isCreateModalOpen = false"
      @save="handleSaveCreate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Edit3 as Edit3Icon,
  Eye as EyeIcon,
  Shield as ShieldIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import axios from "axios";
import RoleInfoDialog from "../../components/roles/RoleInfoDialog.vue";
import RoleEditDialog from "../../components/roles/RoleEditDialog.vue";
import RoleCreateDialog from "../../components/roles/RoleCreateDialog.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Role");

// State
const roles = ref([]);
const modules = ref([]);
const loading = ref(true);
const isSaving = ref(false);
const isInfoModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const selectedRole = ref(null);

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "name", label: "Role Name", sortable: true },
  { key: "permissions", label: "Permissions", filterKey: "permissions.length" },
  { key: "created_at", label: "Created", type: "date" },
  { key: "actions", label: "Actions", align: "right" },
];

// Computed

// Methods
const fetchRoles = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/roles");
    roles.value = response.data.roles.map((role, index) => ({
      ...role,
      sn: index + 1,
    }));
    modules.value = response.data.modules;
  } catch (error) {
    console.error("Error fetching roles:", error);
    toastError("Failed to load roles");
  } finally {
    loading.value = false;
  }
};

const handleEdit = (role) => {
  selectedRole.value = role;
  isEditModalOpen.value = true;
};

const handleSave = async (formData) => {
  isSaving.value = true;
  try {
    await axios.put(`/api/v1/roles/${formData.id}`, formData);
    toastSuccess("Role updated successfully");
    isEditModalOpen.value = false;
    fetchRoles();
  } catch (error) {
    console.error("Error updating role:", error);
    toastError("Failed to update role");
  } finally {
    isSaving.value = false;
  }
};

const handleSaveCreate = async (formData) => {
  isSaving.value = true;
  try {
    await axios.post("/api/v1/roles", formData);
    toastSuccess("Role created successfully");
    isCreateModalOpen.value = false;
    fetchRoles();
  } catch (error) {
    console.error("Error creating role:", error);
    toastError("Failed to create role");
  } finally {
    isSaving.value = false;
  }
};

const viewRole = (role) => {
  selectedRole.value = role;
  isInfoModalOpen.value = true;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

onMounted(() => {
  fetchRoles();
});
</script>
