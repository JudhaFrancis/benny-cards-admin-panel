<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section -->
    <PageHeader
      title="Payments"
      subtitle="Track and manage financial transactions"
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="openCreateModal"
          class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          New Payment
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Table Section -->
    <PaymentsTable
      :payments="payments"
      :loading="loading"
      :can-view="canView"
      :can-edit="canEdit"
      :can-delete="canDelete"
      @view="handleView"
      @edit="handleEdit"
      @delete="handleConfirmDelete"
    />

    <!-- Pagination Controls -->
    <div
      v-if="meta.total > 0"
      class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm"
    >
      <p class="text-[11px] text-gray-500 font-medium">
        Showing
        <span class="text-gray-700"
          >{{ (page - 1) * 10 + 1 }} to {{ Math.min(page * 10, meta.total) }}</span
        >
        of <span class="text-gray-700">{{ meta.total || 0 }}</span> results
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="page--"
          :disabled="page <= 1"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="page++"
          :disabled="page >= meta.last_page"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Dialogs -->
    <PaymentInfoDialog
      :is-open="isInfoModalOpen"
      :payment="selectedPayment"
      @close="isInfoModalOpen = false"
    />

    <PaymentCreateDialog
      :is-open="isCreateModalOpen"
      @close="isCreateModalOpen = false"
      @success="fetchPayments"
    />

    <PaymentEditDialog
      :is-open="isEditModalOpen"
      :payment="selectedPayment"
      @close="isEditModalOpen = false"
      @success="fetchPayments"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Payment"
      :message="`Are you sure you want to delete payment ${selectedPayment?.payment_number}? This action can be undone if soft deleted.`"
      confirm-text="Delete Payment"
      variant="danger"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import {
  Activity as ActivityIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
} from "lucide-vue-next";
import axios from "axios";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import PaymentsTable from "../../components/payments/PaymentsTable.vue";
import PaymentInfoDialog from "../../components/payments/PaymentInfoDialog.vue";
import PaymentCreateDialog from "../../components/payments/PaymentCreateDialog.vue";
import PaymentEditDialog from "../../components/payments/PaymentEditDialog.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";

const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Payment");
const toast = useToast();

const payments = ref([]);
const loading = ref(true);
const page = ref(1);
const meta = ref({ total: 0 });


const fetchPayments = async () => {
  loading.value = true;
  try {
    const params = {
      page: page.value,
      per_page: 10,
    };

    const response = await axios.get("/api/v1/payments", { params });
    if (response.data.success) {
      payments.value = response.data.data.data.map((payment, index) => ({
        ...payment,
        sn: index + (response.data.data.from || 1),
      }));
      meta.value = {
        total: response.data.data.total,
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
      };
    }
  } catch (error) {
    console.error("Error fetching payments:", error);
    toast.error("Failed to load payments");
  } finally {
    loading.value = false;
  }
};

onMounted(fetchPayments);

const isInfoModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPayment = ref(null);

watch(page, fetchPayments);

const handleView = (payment) => {
  selectedPayment.value = payment;
  isInfoModalOpen.value = true;
};

const handleEdit = (payment) => {
  selectedPayment.value = payment;
  isEditModalOpen.value = true;
};

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const handleConfirmDelete = (payment) => {
  selectedPayment.value = payment;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedPayment.value) return;
  try {
    await axios.delete(`/api/v1/payments/${selectedPayment.value.id}`);
    toast.success("Payment deleted successfully");
    fetchPayments();
    isDeleteModalOpen.value = false;
  } catch (error) {
    console.error("Error deleting payment:", error);
    toast.error("Failed to delete payment");
  }
};
</script>
