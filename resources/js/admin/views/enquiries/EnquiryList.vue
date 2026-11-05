<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section (Matching OrderList.vue) -->
    <PageHeader title="Order Enquiries" subtitle="Enquiries created by sales staff. Approving an enquiry creates the order.">
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateDialog"
          class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95 whitespace-nowrap"
        >
          <PlusIcon class="h-4 w-4" />
          New Enquiry
        </button>
      </template>
    </PageHeader>

    <!-- Date Filter (Matching OrderList.vue) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <AdvancedDateFilter v-model="dateFilters" @change="handleDateFilterChange" />
    </div>

    <!-- Main Table Container with In-Header Column Search Filters (Matching OrdersTable / DataTable) -->
    <DataTable
      ref="dataTableRef"
      :columns="columns"
      :items="enquiries"
      :from="pagination.from"
      manual-filters
      :loading="loading"
      @filter-change="handleFilterChange"
      @row-click="canView ? openInfoDialog($event) : null"
      empty-text="No enquiries found."
    >
      <!-- S.NO is handled automatically by DataTable from + index -->

      <!-- Enquiry No Cell (Clean, no clutter) -->
      <template #cell-enquiry_number="{ item: enq }">
        <div class="flex flex-col">
          <span
            :class="[
              'font-semibold text-slate-900 italic transition-colors duration-200',
              canView ? 'cursor-pointer hover:text-primary' : ''
            ]"
            @click.stop="canView ? openInfoDialog(enq) : null"
          >
            {{ formatDisplayNumber(enq.enquiry_number) }}
          </span>
          <span class="text-[10px] font-semibold text-slate-500 mt-0.5">
            {{ enq.branch?.name || "No branch" }}
          </span>
        </div>
      </template>

      <!-- Customer Details Cell (Clean Name, Product, Qty) -->
      <template #cell-customer="{ item: enq }">
        <div class="flex flex-col gap-0.5" @click.stop>
          <!-- Customer Name -->
          <span class="text-sm font-semibold text-slate-900">{{ enq.customer_name || "N/A" }}</span>

          <!-- Product Name with Truncation -->
          <div v-if="getEnquiryProduct(enq)" class="flex items-center gap-1">
            <span class="text-xs text-slate-500 truncate max-w-[200px]" :title="getEnquiryProduct(enq)">
              {{ getEnquiryProduct(enq) }}
            </span>
            <span v-if="getAdditionalCount(enq) > 0" class="text-[10px] bg-slate-100 text-slate-600 px-1.5 rounded-full">
              +{{ getAdditionalCount(enq) }} more
            </span>
          </div>

          <!-- Quantity -->
          <span class="text-[10px] text-slate-500 font-medium">
            Qty: <span class="font-bold text-slate-700">{{ getEnquiryQuantity(enq) }}</span>
          </span>
        </div>
      </template>

      <!-- Status Cell (Matching OrderStatusCell) -->
      <template #cell-status="{ item: enq }">
        <div class="flex flex-col gap-1 items-start">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200',
              getEnquiryStatusClass(enq.status?.toLowerCase())
            ]"
          >
            {{ getEnquiryStatusLabel(enq.status) }}
          </span>
          <span v-if="enq.status === 'approved' && enq.converted_order" class="text-[10px] text-emerald-600 font-semibold">
            Order #{{ formatDisplayNumber(enq.converted_order.order_number) }}
          </span>
          <span
            v-else-if="enq.status === 'rejected' && enq.rejection_reason"
            class="text-[10px] text-rose-600 font-medium max-w-[160px] truncate"
            :title="enq.rejection_reason"
          >
            {{ shortReason(enq.rejection_reason) }}
          </span>
        </div>
      </template>

      <!-- Created Cell (Matching OrderMetaCell) -->
      <template #cell-created_at="{ item: enq }">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] text-slate-600 font-extrabold uppercase tracking-tight">
            {{ formatDate(enq.created_at) }}
          </span>
          <span class="text-[9px] text-slate-400 font-medium">
            by {{ enq.added_by?.name || 'Website' }}
          </span>
        </div>
      </template>

      <!-- Modified Cell (Matching OrderMetaCell) -->
      <template #cell-modified_at="{ item: enq }">
        <div class="flex flex-col gap-0.5">
          <span class="text-[10px] text-slate-600 font-extrabold uppercase tracking-tight">
            {{ formatDate(enq.updated_at || enq.created_at) }}
          </span>
          <span class="text-[9px] text-slate-400 font-medium">
            by {{ enq.modified_by?.name || enq.added_by?.name || 'Website' }}
          </span>
        </div>
      </template>

      <!-- Actions Cell (View, Edit, Approve, Reject) -->
      <template #cell-actions="{ item: enq }">
        <div class="flex items-center justify-end gap-1.5" @click.stop>
          <button
            v-if="canView"
            @click.stop="openInfoDialog(enq)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
            title="View Info"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canEdit && enq.status === 'pending'"
            @click.stop="openEditDialog(enq)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-500/10 hover:text-slate-700 transition-all duration-200"
            title="Edit Enquiry"
          >
            <PencilIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canApprove && enq.status === 'pending'"
            @click.stop="openApprove(enq)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-200"
            title="Approve & Create Order"
          >
            <CheckCircleIcon class="h-4 w-4" />
          </button>
          <button
            v-if="canApprove && enq.status === 'pending'"
            @click.stop="openReject(enq)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-amber-500 hover:bg-amber-500/10 hover:text-amber-600 transition-all duration-200"
            title="Reject Enquiry"
          >
            <XCircleIcon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Pagination Controls (Matching OrderList.vue) -->
    <div
      v-if="pagination.total > 0"
      class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm"
    >
      <p class="text-[11px] text-gray-500 font-medium">
        Showing
        <span class="text-gray-700 font-semibold">{{ pagination.from || 0 }} to {{ Math.min(pagination.to || ((pagination.from || 1) + enquiries.length - 1), pagination.total) }}</span>
        of <span class="text-gray-700 font-semibold">{{ pagination.total || 0 }}</span> results
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Modals -->
    <EnquiryCreateDialog
      :isOpen="isCreateOpen"
      :enquiry="editingEnquiry"
      @close="closeFormDialog"
      @created="handleCreated"
      @updated="handleUpdated"
    />

    <EnquiryInfoDialog
      :isOpen="isInfoOpen"
      :enquiry="selectedEnquiry"
      :canApprove="canApprove"
      @close="isInfoOpen = false"
      @edit="openEditDialog"
      @approve="openApprove"
      @reject="openReject"
    />

    <!-- Approve confirmation -->
    <ConfirmationModal
      :isOpen="isApproveOpen"
      title="Approve Enquiry"
      :description="`Approve ${formatDisplayNumber(actionEnquiry?.enquiry_number)} for ${actionEnquiry?.customer_name}? An order will be created for ₹${Number(actionEnquiry?.total_amount || 0).toLocaleString('en-IN')}.`"
      confirmLabel="Approve & Create Order"
      :icon="CheckCircleIcon"
      :loading="actionLoading"
      @close="isApproveOpen = false"
      @confirm="handleApprove"
    />

    <!-- Reject with reason -->
    <ConfirmationModal
      :isOpen="isRejectOpen"
      title="Reject Enquiry"
      :description="`Reject ${formatDisplayNumber(actionEnquiry?.enquiry_number)}? No order will be created.`"
      confirmLabel="Reject"
      variant="danger"
      :icon="XCircleIcon"
      :loading="actionLoading"
      @close="isRejectOpen = false"
      @confirm="handleReject"
    >
      <label class="block text-xs font-semibold text-slate-700 mb-1">Reason <span class="text-rose-500">*</span></label>
      <textarea
        v-model="rejectReason"
        rows="3"
        placeholder="e.g. Customer chose another vendor, budget too low..."
        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
      ></textarea>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import {
  Plus as PlusIcon,
  Eye as EyeIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  Pencil as PencilIcon,
  CheckCircle as CheckCircleIcon,
  XCircle as XCircleIcon,
} from "lucide-vue-next";
import axios from "axios";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import EnquiryCreateDialog from "../../components/enquiries/EnquiryCreateDialog.vue";
import EnquiryInfoDialog from "../../components/enquiries/EnquiryInfoDialog.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";
import { getEnquiryStatusLabel, getEnquiryStatusClass } from "../../constants/enquiryStatuses";
import { formatDisplayNumber, hideYearInText } from "../../utils/displayNumber";

const toast = useToast();
const { getModulePermissions, hasPermission } = usePermissions();
const { canView, canAdd, canEdit } = getModulePermissions("Enquiry");
const canApprove = computed(() => hasPermission("Enquiry", "approve"));

const dataTableRef = ref(null);
const loading = ref(false);
const enquiries = ref([]);
// Pending Approval is selected by default (matches the status column's defaultFilter)
const DEFAULT_STATUS = "pending";
const columnFilters = ref({ status: DEFAULT_STATUS });

const formatFilterDate = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const dateFilters = ref({
  filter_type: "month",
  filter_option: "this_month",
  from_date: formatFilterDate(new Date(new Date().getFullYear(), new Date().getMonth(), 1)),
  to_date: formatFilterDate(new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)),
});

const handleDateFilterChange = () => {
  fetchEnquiries(1);
};

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 1,
  to: 0,
});

const columns = [
  { key: "sn", label: "S.NO", width: "60px", align: "center", class: "whitespace-nowrap" },
  { key: "enquiry_number", label: "ENQUIRY", align: "left", width: "170px", class: "whitespace-nowrap", filterKey: "enquiry_number", tooltip: "Enquiry Number\nBranch" },
  { key: "customer", label: "CUSTOMER", align: "left", width: "240px", filterKey: "customer", tooltip: "Customer Name\nProduct Name\nTotal Quantity" },
  {
    key: "status",
    label: "STATUS",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "select",
    filterKey: "status",
    placeholder: "All Status",
    defaultFilter: DEFAULT_STATUS,
    options: [
      { label: "Pending Approval", value: "pending" },
      { label: "Approved", value: "approved" },
      { label: "Rejected", value: "rejected" }
    ]
  },
  { key: "created_at", label: "CREATED", align: "left", width: "130px", type: "date", filterKey: "date" },
  { key: "modified_at", label: "MODIFIED", align: "left", width: "130px", filterKey: "updated_at" },
  { key: "actions", label: "ACTIONS", align: "right", width: "90px", class: "whitespace-nowrap" },
];

// Modals state
const isCreateOpen = ref(false);
const isInfoOpen = ref(false);
const isApproveOpen = ref(false);
const isRejectOpen = ref(false);
const selectedEnquiry = ref(null);
const editingEnquiry = ref(null);
const actionEnquiry = ref(null);
const actionLoading = ref(false);
const rejectReason = ref("");

const handleFilterChange = (filters) => {
  columnFilters.value = { ...filters };
  fetchEnquiries(1);
};

const fetchEnquiries = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      start_date: dateFilters.value?.from_date || undefined,
      end_date: dateFilters.value?.to_date || undefined,
      ...columnFilters.value,
    };
    const res = await axios.get("/api/v1/enquiries", { params });
    if (res.data.success) {
      const data = res.data.data;
      enquiries.value = data.paginator.data;

      pagination.current_page = data.paginator.current_page;
      pagination.last_page = data.paginator.last_page;
      pagination.per_page = data.paginator.per_page;
      pagination.total = data.paginator.total;
      pagination.from = data.paginator.from || 1;
      pagination.to = data.paginator.to || 0;
    }
  } catch (err) {
    console.error("Failed to load enquiries", err);
    toast.error("Failed to load enquiries.");
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchEnquiries(page);
  }
};

const openCreateDialog = () => {
  editingEnquiry.value = null;
  isCreateOpen.value = true;
};

const openEditDialog = async (enq) => {
  isInfoOpen.value = false;
  try {
    // load full record (items, addresses, money fields) before editing
    const res = await axios.get(`/api/v1/enquiries/${enq.id}`);
    editingEnquiry.value = res.data?.data || enq;
  } catch (err) {
    editingEnquiry.value = enq;
  }
  isCreateOpen.value = true;
};

const closeFormDialog = () => {
  isCreateOpen.value = false;
  editingEnquiry.value = null;
};

const openInfoDialog = (enq) => {
  selectedEnquiry.value = enq;
  isInfoOpen.value = true;
};

const openApprove = (enq) => {
  actionEnquiry.value = enq;
  isInfoOpen.value = false;
  isApproveOpen.value = true;
};

const openReject = (enq) => {
  actionEnquiry.value = enq;
  rejectReason.value = "";
  isInfoOpen.value = false;
  isRejectOpen.value = true;
};

const handleApprove = async () => {
  if (!actionEnquiry.value) return;
  actionLoading.value = true;
  try {
    const res = await axios.post(`/api/v1/enquiries/${actionEnquiry.value.id}/approve`);
    if (res.data.success) {
      toast.success(hideYearInText(res.data.message) || "Enquiry approved.");
      isApproveOpen.value = false;
      actionEnquiry.value = null;
      fetchEnquiries(pagination.current_page);
    }
  } catch (err) {
    toast.error(hideYearInText(err.response?.data?.message) || "Failed to approve enquiry.");
  } finally {
    actionLoading.value = false;
  }
};

const handleReject = async () => {
  if (!actionEnquiry.value) return;
  if (!rejectReason.value.trim()) {
    toast.error("Please enter a reason for rejecting.");
    return;
  }
  actionLoading.value = true;
  try {
    const res = await axios.post(`/api/v1/enquiries/${actionEnquiry.value.id}/reject`, {
      reason: rejectReason.value.trim(),
    });
    if (res.data.success) {
      toast.success(hideYearInText(res.data.message) || "Enquiry rejected.");
      isRejectOpen.value = false;
      actionEnquiry.value = null;
      fetchEnquiries(pagination.current_page);
    }
  } catch (err) {
    toast.error(hideYearInText(err.response?.data?.message) || "Failed to reject enquiry.");
  } finally {
    actionLoading.value = false;
  }
};

const handleCreated = () => {
  fetchEnquiries(1);
};

const handleUpdated = () => {
  fetchEnquiries(pagination.current_page);
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "N/A";
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

// First 20 characters of the reject reason for the status column (full text in the tooltip and info modal)
const shortReason = (reason) => {
  const text = (reason || "").trim();
  return text.length > 20 ? `${text.slice(0, 20)}…` : text;
};

const getEnquiryProduct = (enq) => enq.items?.[0]?.product_name || "";

const getAdditionalCount = (enq) => {
  if (!enq.items || enq.items.length <= 1) return 0;
  return enq.items.length - 1;
};

const getEnquiryQuantity = (enq) => {
  if (enq.items && enq.items.length > 0) {
    const total = enq.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
    if (total > 0) return total;
  }
  return enq.converted_order?.total_quantity || 1;
};

onMounted(() => {
  fetchEnquiries();
});
</script>
