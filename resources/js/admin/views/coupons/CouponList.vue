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

    <!-- Filters & Search (REMOVED) -->

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
            ₹{{
              Number(coupon.value).toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              })
            }}
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
            v-if="canView"
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
                  : '₹' +
                    Number(selectedCoupon.value).toLocaleString('en-IN', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    })
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
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";
import InfoModal from "../../components/ui/modals/InfoModal.vue";
import InfoSection from "../../components/ui/display/InfoSection.vue";
import InfoItem from "../../components/ui/display/InfoItem.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";

const { success: toastSuccess, error: toastError } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Coupons");

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

const filters = reactive({});

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "code", label: "Coupon Code", sortable: true, filterKey: "code" },
  { key: "type", label: "Type", filterKey: "type" },
  { key: "value", label: "Value" },
  { key: "status", label: "Status" },
  { key: "created_at", label: "Created", type: "date" },
  { key: "updated_at", label: "Modified", type: "date" },
  { key: "actions", label: "Actions", align: "right" },
];




// Methods
const fetchCoupons = async (url = "/api/v1/coupons") => {
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        per_page: 50,
      },
    });
    if (response.data.success) {
      coupons.value = response.data.data.data.map((coupon, index) => ({
        ...coupon,
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
    console.error("Failed to fetch coupons", error);
    toastError("Failed to load coupons");
  } finally {
    loading.value = false;
  }
};


const viewCoupon = (coupon) => {
  selectedCoupon.value = coupon;
  isInfoModalOpen.value = true;
};

const openCreateModal = () => {
  selectedCoupon.value = null;
  isModalOpen.value = true;
};

const openEditModal = (coupon) => {
  selectedCoupon.value = coupon;
  isModalOpen.value = true;
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

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

onMounted(() => {
  fetchCoupons();
});
</script>
