<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section (Matching OrderList.vue) -->
    <PageHeader title="Order Enquiries" subtitle="Manage and track wedding card enquiries, follow-ups, and quotes">
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
            {{ enq.enquiry_number }}
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

      <!-- Dates Cell (Matching Orders Table OrderDatesCell style) -->
      <template #cell-dates="{ item: enq }">
        <div class="flex flex-col gap-1 text-[11px] font-medium tracking-tight">
          <!-- Enquiry Date -->
          <div class="flex items-center gap-1.5 text-slate-600">
            <span class="w-5 text-slate-400 font-bold">ED</span>
            <span class="text-slate-800">{{ formatDate(enq.created_at) }}</span>
          </div>

          <!-- Delivery Date -->
          <div class="flex items-center gap-1.5 text-slate-600">
            <span class="w-5 text-slate-400 font-bold">DD</span>
            <span class="text-slate-800">{{ enq.expected_delivery_date ? formatDate(enq.expected_delivery_date) : 'N/A' }}</span>
          </div>
        </div>
      </template>

      <!-- Source Cell (Clean source name only) -->
      <template #cell-source="{ item: enq }">
        <span class="text-xs font-semibold text-slate-800">{{ formatSource(enq.source) }}</span>
      </template>

      <!-- Status Cell (Matching OrderStatusCell) -->
      <template #cell-status="{ item: enq }">
        <div class="flex flex-col gap-1 items-start">
          <span
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200',
              enquiryStatusStyles[enq.status?.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200'
            ]"
          >
            {{ formatStatusLabel(enq.status) }}
          </span>
          <span v-if="enq.assigned_to" class="text-[10px] text-slate-500 font-medium">
            <span class="text-slate-400">By:</span> {{ enq.assigned_to }}
          </span>
        </div>
      </template>

      <!-- Quoted Amount Cell (Correctly labelled Quoted Amount) -->
      <template #cell-quoted="{ item: enq }">
        <div class="flex flex-col gap-0.5">
          <span
            v-if="enq.quoted_amount"
            class="font-bold text-slate-900 text-xs"
          >
            ₹{{ Number(enq.quoted_amount).toLocaleString('en-IN') }}
          </span>
          <span
            v-else
            class="text-xs text-slate-400 italic font-normal"
          >
            Not Quoted
          </span>
          <span v-if="enq.next_follow_up_at && enq.status !== 'confirmed'" class="text-[10px] text-amber-600 font-medium mt-0.5">
            Due: {{ formatDate(enq.next_follow_up_at) }}
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
            by {{ enq.added_by?.name || 'Admin' }}
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
            by {{ enq.modified_by?.name || enq.added_by?.name || 'Admin' }}
          </span>
        </div>
      </template>

      <!-- Actions Cell (View & Delete) -->
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
            v-if="canDelete"
            @click.stop="confirmDelete(enq)"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
            title="Delete Enquiry"
          >
            <Trash2Icon class="h-4 w-4" />
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
      @close="isCreateOpen = false"
      @created="handleCreated"
    />

    <EnquiryInfoDialog
      :isOpen="isInfoOpen"
      :enquiry="selectedEnquiry"
      @close="isInfoOpen = false"
      @updated="handleUpdated"
      @convert="openConvertDialog(selectedEnquiry)"
    />

    <ConvertEnquiryDialog
      :isOpen="isConvertOpen"
      :enquiry="selectedEnquiry"
      @close="isConvertOpen = false"
      @converted="handleConverted"
    />

    <ConfirmationModal
      :isOpen="isDeleteOpen"
      title="Delete Enquiry"
      :description="`Are you sure you want to delete enquiry ${enquiryToDelete?.enquiry_number}?`"
      confirmLabel="Delete"
      variant="danger"
      @close="isDeleteOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import {
  Plus as PlusIcon,
  Eye as EyeIcon,
  Trash2 as Trash2Icon,
  Phone as PhoneIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
} from "lucide-vue-next";
import axios from "axios";
import DataTable from "../../components/ui/data-table/DataTable.vue";
import EnquiryCreateDialog from "../../components/enquiries/EnquiryCreateDialog.vue";
import EnquiryInfoDialog from "../../components/enquiries/EnquiryInfoDialog.vue";
import ConvertEnquiryDialog from "../../components/enquiries/ConvertEnquiryDialog.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";

const toast = useToast();
const { getModulePermissions } = usePermissions();
const { canView, canAdd, canEdit, canDelete } = getModulePermissions("Enquiry");

const dataTableRef = ref(null);
const loading = ref(false);
const enquiries = ref([]);
const columnFilters = ref({});

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
  { key: "enquiry_number", label: "ENQUIRY NO", align: "left", width: "160px", class: "whitespace-nowrap", filterKey: "enquiry_number" },
  { key: "customer", label: "CUSTOMER", align: "left", width: "240px", filterKey: "customer", tooltip: "Customer Name\nProduct Name\nTotal Quantity" },
  { key: "dates", label: "DATES", align: "left", width: "150px", class: "whitespace-nowrap", type: "date", filterKey: "date", tooltip: "Enquiry Date (ED)\nDelivery Date (DD)" },
  {
    key: "source",
    label: "SOURCE",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "select",
    filterKey: "source",
    placeholder: "All Sources",
    options: [
      { label: "Walk-In", value: "walk_in" },
      { label: "Website", value: "website" },
      { label: "WhatsApp", value: "whatsapp" },
      { label: "Instagram", value: "instagram" },
      { label: "Phone Call", value: "phone" }
    ]
  },
  {
    key: "status",
    label: "STATUS",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "select",
    filterKey: "status",
    placeholder: "All Status",
    options: [
      { label: "New", value: "new" },
      { label: "Under Review", value: "under_review" },
      { label: "Quoted", value: "quoted" },
      { label: "Approved", value: "approved" },
      { label: "Confirmed", value: "confirmed" },
      { label: "Not Approved", value: "not_approved" }
    ]
  },
  { key: "quoted", label: "QUOTED AMOUNT", align: "left", width: "140px", class: "whitespace-nowrap", filterKey: "quoted" },
  { key: "created_at", label: "CREATED", align: "left", width: "130px", filterKey: "created_at" },
  { key: "modified_at", label: "MODIFIED", align: "left", width: "130px", filterKey: "updated_at" },
  { key: "actions", label: "ACTIONS", align: "right", width: "90px", class: "whitespace-nowrap" },
];

const enquiryStatusStyles = {
  new: "bg-blue-500/10 text-blue-600 border-blue-500/20",
  under_review: "bg-amber-500/10 text-amber-600 border-amber-500/20",
  quoted: "bg-purple-500/10 text-purple-600 border-purple-500/20",
  approved: "bg-teal-500/10 text-teal-600 border-teal-500/20",
  confirmed: "bg-emerald-500/10 text-emerald-600 border-emerald-500/20",
  not_approved: "bg-rose-500/10 text-rose-600 border-rose-500/20",
  cancelled: "bg-rose-500/10 text-rose-600 border-rose-500/20",
};

// Modals state
const isCreateOpen = ref(false);
const isInfoOpen = ref(false);
const isConvertOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedEnquiry = ref(null);
const enquiryToDelete = ref(null);

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
  isCreateOpen.value = true;
};

const openInfoDialog = (enq) => {
  selectedEnquiry.value = enq;
  isInfoOpen.value = true;
};

const openConvertDialog = (enq) => {
  selectedEnquiry.value = enq;
  isInfoOpen.value = false;
  isConvertOpen.value = true;
};

const confirmDelete = (enq) => {
  enquiryToDelete.value = enq;
  isDeleteOpen.value = true;
};

const handleDelete = async () => {
  if (!enquiryToDelete.value) return;
  try {
    const res = await axios.delete(`/api/v1/enquiries/${enquiryToDelete.value.id}`);
    if (res.data.success) {
      toast.success("Enquiry deleted.");
      isDeleteOpen.value = false;
      enquiryToDelete.value = null;
      fetchEnquiries(pagination.current_page);
    }
  } catch (err) {
    console.error("Failed to delete enquiry", err);
    toast.error(err.response?.data?.message || "Failed to delete enquiry.");
  }
};

const handleCreated = () => {
  fetchEnquiries(1);
};

const handleUpdated = () => {
  fetchEnquiries(pagination.current_page);
};

const handleConverted = () => {
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

const formatSource = (src) => {
  if (!src) return "N/A";
  const map = {
    website: "Website",
    walk_in: "Walk-In",
    whatsapp: "WhatsApp",
    instagram: "Instagram",
    phone: "Phone Call"
  };
  return map[src.toLowerCase()] || src.replace(/_/g, " ");
};

const formatStatusLabel = (status) => {
  if (!status) return "NEW";
  return status.replace(/_/g, " ").toUpperCase();
};

const getEnquiryProduct = (enq) => {
  if (enq.items && enq.items.length > 0) {
    return enq.items[0]?.product_name;
  }
  return enq.specifications || (enq.card_type === 'customize' ? 'Custom Card' : (enq.card_type || 'Wedding Card'));
};

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
