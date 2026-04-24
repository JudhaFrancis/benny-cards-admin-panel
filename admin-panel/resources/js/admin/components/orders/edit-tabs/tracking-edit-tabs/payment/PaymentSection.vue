<template>
  <div class="space-y-8">
    <!-- Status Badge -->
    <div class="flex justify-center -mb-4">
      <div
        :class="[
          'px-6 py-2 rounded-2xl border-2 flex items-center gap-2 transition-all shadow-sm',
          isFullyPaid
            ? 'bg-emerald-50 border-emerald-500 text-emerald-700'
            : 'bg-amber-50 border-amber-500 text-amber-700',
        ]"
      >
        <div
          :class="[
            'w-2.5 h-2.5 rounded-full animate-pulse',
            isFullyPaid ? 'bg-emerald-500' : 'bg-amber-500',
          ]"
        ></div>
        <span class="text-sm font-black uppercase tracking-widest">
          {{ isFullyPaid ? "Completed" : "Pending" }}
        </span>
      </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Total Amount -->
      <div
        class="relative overflow-hidden p-6 rounded-[1.5rem] bg-white border border-slate-200 shadow-sm group transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-slate-50 text-slate-900 rounded-2xl shadow-inner">
            <BanknoteIcon class="h-6 w-6 text-slate-500" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5"
            >
              Total Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.total_amount || 0).toFixed(2) }}
            </p>
          </div>
        </div>
        <div
          class="absolute inset-0 bg-gradient-to-br from-slate-50 to-transparent pointer-events-none"
        ></div>
      </div>

      <!-- Paid Amount -->
      <div
        class="relative overflow-hidden p-6 rounded-[1.5rem] bg-white border border-slate-200 shadow-sm group transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-emerald-50 rounded-2xl shadow-inner">
            <CheckCircleIcon class="h-6 w-6 text-emerald-600" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-emerald-600/60 uppercase tracking-widest mb-0.5"
            >
              Paid Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.paid_amount || 0).toFixed(2) }}
            </p>
          </div>
        </div>
        <div
          class="absolute right-0 top-0 w-24 h-24 bg-emerald-50/20 rounded-full -mr-12 -mt-12 blur-2xl group-hover:bg-emerald-100/30 transition-colors"
        ></div>
      </div>

      <!-- Due Amount -->
      <div
        class="relative overflow-hidden p-6 rounded-[1.5rem] bg-white border border-slate-200 shadow-sm group transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-rose-50 rounded-2xl shadow-inner">
            <AlertCircleIcon class="h-6 w-6 text-rose-600" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-rose-600/60 uppercase tracking-widest mb-0.5"
            >
              Due Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.balance_due || 0).toFixed(2) }}
            </p>
          </div>
        </div>
        <div
          class="absolute right-0 top-0 w-24 h-24 bg-rose-50/20 rounded-full -mr-12 -mt-12 blur-2xl group-hover:bg-rose-100/30 transition-colors"
        ></div>
      </div>
    </div>

    <!-- Payments List -->
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400">
          Payment History
        </h4>
        <button
          @click="showAddForm = !showAddForm"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all active:scale-95"
        >
          <PlusIcon v-if="!showAddForm" class="h-4 w-4" />
          <XIcon v-else class="h-4 w-4" />
          {{ showAddForm ? "Cancel" : "Add New Payment" }}
        </button>
      </div>

      <!-- Add/Edit Form -->
      <div
        v-if="showAddForm || editingIndex !== null"
        class="p-8 bg-slate-50 border border-slate-200 rounded-3xl space-y-8 animate-in fade-in slide-in-from-top-4 duration-300"
      >
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 bg-primary/10 text-primary rounded-xl">
            <PlusIcon v-if="editingIndex === null" class="h-5 w-5" />
            <Edit2Icon v-else class="h-5 w-5" />
          </div>
          <h5 class="text-lg font-bold text-slate-900">
            {{
              editingIndex === null ? "New Payment Entry" : "Edit Payment Entry"
            }}
          </h5>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Payment Method Listbox -->
          <div class="space-y-4">
            <label
              class="text-sm font-semibold text-slate-700 flex items-center gap-2"
            >
              <CreditCardIcon class="h-4 w-4 text-primary" />
              Payment Via <span class="text-red-500">*</span>
            </label>
            <Listbox v-model="form.payment_method">
              <div class="relative">
                <ListboxButton
                  class="relative w-full cursor-pointer rounded-xl bg-white py-2.5 pl-11 pr-10 text-left border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm sm:text-xs"
                >
                  <span class="absolute left-4 top-1/2 -translate-y-1/2">
                    <component
                      :is="selectedMethodIcon"
                      class="h-4 w-4 text-slate-400"
                    />
                  </span>
                  <span class="block truncate font-medium text-slate-900">
                    {{ selectedMethodLabel || "Select payment method" }}
                  </span>
                  <span
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4"
                  >
                    <ChevronDownIcon
                      class="h-4 w-4 text-slate-400 transition-transform duration-200"
                      aria-hidden="true"
                    />
                  </span>
                </ListboxButton>
                <transition
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="opacity-100"
                  leave-to-class="opacity-0"
                >
                  <ListboxOptions class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-xs shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none custom-scrollbar min-w-[140px] text-left">
                    <ListboxOption
                      v-slot="{ active, selected }"
                      v-for="method in paymentMethods"
                      :key="method.value"
                      :value="method.value"
                      as="template"
                    >
                      <li
                        :class="[
                          active
                            ? 'bg-primary/5 text-primary'
                            : 'text-slate-700',
                          'relative cursor-pointer select-none py-2.5 pl-11 pr-4 transition-colors',
                        ]"
                      >
                        <span class="absolute left-4 top-1/2 -translate-y-1/2">
                          <component
                            :is="method.icon"
                            :class="[
                              active ? 'text-primary' : 'text-slate-400',
                              'h-4 w-4 transition-colors',
                            ]"
                          />
                        </span>
                        <span
                          :class="[
                            selected ? 'font-bold' : 'font-medium',
                            'block truncate',
                          ]"
                          >{{ method.label }}</span
                        >
                        <span
                          v-if="selected"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-primary"
                        >
                          <CheckIcon class="h-4 w-4" aria-hidden="true" />
                        </span>
                      </li>
                    </ListboxOption>
                  </ListboxOptions>
                </transition>
              </div>
            </Listbox>
          </div>

          <div class="space-y-4">
            <label
              class="text-sm font-semibold text-slate-700 flex items-center gap-2"
            >
              <HashIcon class="h-4 w-4 text-primary" />
              Transaction ID
            </label>
            <div class="relative group">
              <HashIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
              <input
                v-model="form.transaction_id"
                class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                placeholder="Reference #"
              />
            </div>
          </div>

          <div class="space-y-4">
            <label
              class="text-sm font-semibold text-slate-700 flex items-center gap-2"
            >
              <CalendarIcon class="h-4 w-4 text-primary" />
              Payment Date <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
              <DatePicker
                v-model="form.payment_date"
                placeholder="Select Payment Date"
                custom-class="pl-11 py-2.5 text-xs"
              >
                <template #leading>
                  <CalendarIcon
                    class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                  />
                </template>
              </DatePicker>
            </div>
          </div>

          <div class="space-y-4">
            <label
              class="text-sm font-semibold text-slate-700 flex items-center gap-2"
            >
              <BanknoteIcon class="h-4 w-4 text-primary" />
              Amount <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
              <BanknoteIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
              <input
                type="number"
                v-model="form.amount"
                step="0.01"
                class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                placeholder="0.00"
              />
            </div>
          </div>

          <div class="space-y-4 md:col-span-2">
            <label
              class="text-sm font-semibold text-slate-700 flex items-center gap-2"
            >
              <UserIcon class="h-4 w-4 text-primary" />
              Signature & Name <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
              <UserIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
              <input
                v-model="form.signature_name"
                class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                placeholder="Enter name"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button
            @click="resetForm"
            class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:bg-white rounded-xl transition-all"
          >
            Cancel
          </button>
          <button
            @click="savePayment"
            class="px-8 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95"
          >
            {{ editingIndex === null ? "Add Payment" : "Update Payment" }}
          </button>
        </div>
      </div>

      <!-- Payment Items Grid -->
      <div v-if="paymentList.length > 0" class="grid grid-cols-1 gap-4">
        <div
          v-for="(payment, index) in paymentList"
          :key="index"
          class="relative group bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all hover:border-primary/20"
        >
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div
                class="p-3 bg-slate-50 text-slate-400 rounded-2xl group-hover:bg-primary/5 group-hover:text-primary transition-colors"
              >
                <component
                  :is="getMethodIcon(payment.payment_method)"
                  class="h-6 w-6"
                />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-slate-900"
                    >₹{{ parseFloat(payment.amount).toFixed(2) }}</span
                  >
                  <span
                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full"
                  >
                    {{ payment.payment_method }}
                  </span>
                </div>
                <p
                  class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1"
                >
                  {{ formatDate(payment.payment_date) }} •
                  {{ payment.transaction_id || "No ref" }}
                </p>
                <p
                  v-if="payment.signature_name"
                  class="text-[10px] font-medium text-slate-400 mt-0.5"
                >
                  Signed by: {{ payment.signature_name }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="editPayment(index)"
                class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all"
                title="Edit payment"
              >
                <Edit2Icon class="h-4 w-4" />
              </button>
              <button
                @click="removePayment(index)"
                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                title="Delete payment"
              >
                <Trash2Icon class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Audit info for each item -->
          <div
            v-if="payment._audit"
            class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-end"
          >
            <p
              class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"
            >
              Last saved by
              <span class="text-slate-600">{{
                payment._audit.updated_by
              }}</span>
            </p>
          </div>
        </div>
      </div>

      <div
        v-else-if="!showAddForm"
        class="py-12 flex flex-col items-center justify-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200"
      >
        <div class="p-4 bg-white rounded-full shadow-sm mb-4">
          <BanknoteIcon class="h-8 w-8 text-slate-300" />
        </div>
        <p class="text-sm font-bold text-slate-500">
          No payment records found in tracking.
        </p>
        <p class="text-xs text-slate-400 mt-1">
          Add a payment to start tracking.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";
import {
  Banknote as BanknoteIcon,
  CheckCircle as CheckCircleIcon,
  AlertCircle as AlertCircleIcon,
  CreditCard as CreditCardIcon,
  Hash as HashIcon,
  User as UserIcon,
  ChevronDown as ChevronDownIcon,
  Clock as ClockIcon,
  Calendar as CalendarIcon,
  Smartphone as SmartphoneIcon,
  Building2 as Building2Icon,
  QrCode as QrCodeIcon,
  ClipboardCheck as ClipboardCheckIcon,
  Wallet as WalletIcon,
  Globe as GlobeIcon,
  Check as CheckIcon,
  Plus as PlusIcon,
  Trash2 as Trash2Icon,
  Edit2 as Edit2Icon,
  X as XIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const emit = defineEmits(["update:order"]);

const showAddForm = ref(false);
const editingIndex = ref(null);

const form = ref({
  id: null,
  payment_method: "cash",
  transaction_id: "",
  payment_date: new Date().toISOString().split("T")[0],
  amount: 0,
  signature_name: "",
});

const paymentList = computed(() => {
  return props.order.payments || [];
});

const paymentMethods = [
  { label: "Cash", value: "cash", icon: BanknoteIcon },
  { label: "Card", value: "card", icon: CreditCardIcon },
  { label: "UPI", value: "upi", icon: SmartphoneIcon },
  { label: "Net Banking", value: "net_banking", icon: GlobeIcon },
  { label: "QR Code", value: "qr_code", icon: QrCodeIcon },
  { label: "Bank Transfer", value: "bank_transfer", icon: Building2Icon },
  { label: "Cheque", value: "cheque", icon: ClipboardCheckIcon },
  { label: "Wallet", value: "wallet", icon: WalletIcon },
];

const selectedMethodLabel = computed(() => {
  return (
    paymentMethods.find((m) => m.value === form.value.payment_method)?.label ||
    ""
  );
});

const selectedMethodIcon = computed(() => {
  return (
    paymentMethods.find((m) => m.value === form.value.payment_method)?.icon ||
    CreditCardIcon
  );
});

const getMethodIcon = (methodValue) => {
  return (
    paymentMethods.find((m) => m.value === methodValue)?.icon || CreditCardIcon
  );
};

const isFullyPaid = computed(() => {
  const total = parseFloat(props.order.total_amount || 0);
  const alreadyPaid = parseFloat(props.order.paid_amount || 0);
  return total > 0 && Math.abs(total - alreadyPaid) < 0.01;
});

const resetForm = () => {
  form.value = {
    id: null,
    payment_method: "cash",
    transaction_id: "",
    payment_date: new Date().toISOString().split("T")[0],
    amount: parseFloat(props.order.balance_due || 0).toFixed(2),
    signature_name: props.order.customer_details?.name || "",
  };
  editingIndex.value = null;
  showAddForm.value = false;
};

// Helper to ensure payments is an array
const ensurePaymentArray = () => {
  if (!props.order.payments) {
    props.order.payments = [];
  }
  return props.order.payments;
};

const savePayment = () => {
  if (parseFloat(form.value.amount) <= 0) {
    alert("Payment amount must be greater than zero.");
    return;
  }

  const payments = ensurePaymentArray();

  if (editingIndex.value !== null) {
    payments[editingIndex.value] = { ...form.value };
  } else {
    payments.push({ ...form.value });
  }
  resetForm();
};

const editPayment = (index) => {
  const payments = ensurePaymentArray();
  // Ensure the item exists before editing
  if (payments[index]) {
    form.value = { ...payments[index] };
    editingIndex.value = index;
    showAddForm.value = false;
  }
};

const removePayment = (index) => {
  if (
    confirm("Are you sure you want to remove this payment entry from tracking?")
  ) {
    const payments = ensurePaymentArray();
    payments.splice(index, 1);
  }
};

onMounted(() => {
  if (props.order.customer_details?.name) {
    form.value.signature_name = props.order.customer_details.name;
  }
  form.value.amount = parseFloat(props.order.balance_due || 0).toFixed(2);
});

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: #0f172a !important;
  -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
  box-shadow: 0 0 0px 1000px #ffffff inset !important;
  transition: background-color 5000s ease-in-out 0s;
}

input {
  color: #0f172a !important;
}

@keyframes animate-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: animate-in 0.3s ease-out;
}
</style>
