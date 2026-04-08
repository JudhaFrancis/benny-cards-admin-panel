<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translateY(20px)"
            enter-to="opacity-100 scale-100 translateY(0)"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translateY(0)"
            leave-to="opacity-0 scale-95 translateY(20px)"
          >
            <DialogPanel
              class="w-full max-w-6xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10"
              >
                <div
                  class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"
                ></div>
                <div
                  class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"
                ></div>
                <div
                  class="relative px-8 py-8 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <PencilIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Edit Payment #{{ payment?.payment_number }}
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        Update the details of this financial transaction.
                      </p>
                    </div>
                  </div>
                  <button
                    @click="handleClose"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                <!-- Left Side: Form -->
                <div class="flex-1 overflow-y-auto p-8 pb-32 custom-scrollbar">
                  <form @submit.prevent="handleSubmit" id="edit-payment-form" class="space-y-10">
                    <div class="space-y-6">
                      <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2 ml-1">
                          <CreditCardIcon class="h-4 w-4 text-primary" />
                          Payment Details
                        </h4>
                        <button type="button" @click="addEntry" class="px-4 py-2 bg-primary/5 text-primary rounded-xl font-bold hover:bg-primary/10 transition-all flex items-center gap-2 active:scale-95 text-xs">
                          <PlusIcon class="h-4 w-4" /> Add Method
                        </button>
                      </div>

                      <div class="space-y-4">
                        <div v-for="(entry, index) in form.payment_details" :key="index" class="p-6 rounded-[2rem] bg-gray-50/50 border border-gray-100 shadow-sm relative group">
                          <button v-if="form.payment_details.length > 1" type="button" @click="removeEntry(index)" class="absolute -right-2 -top-2 p-2 bg-white border border-gray-100 text-rose-500 rounded-full shadow-sm opacity-0 group-hover:opacity-100 transition-all hover:bg-rose-50">
                            <XIcon class="h-4 w-4" />
                          </button>

                          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                              <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Method</label>
                              <Listbox v-model="entry.method">
                                <div class="relative">
                                  <ListboxButton class="relative w-full cursor-pointer rounded-xl bg-white py-3 pl-10 pr-8 text-left border border-gray-200 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2">
                                      <component :is="getMethodIcon(entry.method)" class="h-4 w-4 text-gray-400" />
                                    </span>
                                    {{ getMethodLabel(entry.method) }}
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                      <ChevronDownIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    </span>
                                  </ListboxButton>
                                  <transition leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                                    <ListboxOptions class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-xs shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none custom-scrollbar min-w-[140px]">
                                      <ListboxOption v-slot="{ active, selected }" v-for="method in paymentMethods" :key="method.value" :value="method.value" as="template">
                                        <li :class="[active ? 'bg-primary/5 text-primary' : 'text-gray-700', 'relative cursor-pointer select-none py-2.5 pl-10 pr-4 transition-colors']">
                                          <span class="absolute left-3 top-1/2 -translate-y-1/2">
                                            <component :is="method.icon" :class="[active ? 'text-primary' : 'text-gray-400', 'h-4 w-4 transition-colors']" />
                                          </span>
                                          {{ method.label }}
                                        </li>
                                      </ListboxOption>
                                    </ListboxOptions>
                                  </transition>
                                </div>
                              </Listbox>
                            </div>
                            <div>
                              <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Amount (₹)</label>
                              <input v-model="entry.amount" type="number" step="0.01" required class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-bold text-sm text-slate-600" placeholder="0.00" />
                            </div>
                            <div>
                              <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Transaction ID</label>
                              <input v-model="entry.transaction_id" type="text" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-bold text-sm text-slate-600" placeholder="Optional" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div>
                      <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2 ml-1">
                        Additional Notes
                      </h4>
                      <textarea v-model="form.notes" rows="4" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-[2rem] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold resize-none text-slate-600" placeholder="Any additional details..."></textarea>
                    </div>
                  </form>
                </div>

                <!-- Right Side: Sidebar -->
                <div class="w-full md:w-[380px] bg-gray-50/50 border-l border-gray-100 p-8 flex flex-col overflow-y-auto custom-scrollbar">
                  <h4 class="text-sm font-bold text-gray-700 mb-8 ml-1">Update Summary</h4>
                  
                  <div class="flex-1 space-y-6">
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-6">
                      <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                          <span class="text-gray-500 font-semibold">Payment Date</span>
                          <input v-model="form.payment_date" type="date" class="bg-transparent border-none text-right font-bold text-gray-900 focus:ring-0 p-0" />
                        </div>
                        <div class="flex justify-between items-center text-sm pt-4 border-t border-gray-50">
                          <span class="text-gray-500 font-semibold">Methods Used</span>
                          <span class="font-bold text-gray-900">{{ form.payment_details.length }}</span>
                        </div>
                      </div>

                      <div class="pt-6 border-t border-gray-50 flex justify-between items-center">
                        <span class="text-gray-900 font-bold">Total Amount</span>
                        <div class="text-right">
                          <p class="text-2xl font-black text-primary">₹{{ totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-auto pt-10 pb-2 space-y-4">
                    <button @click="handleSubmit" :disabled="loading" class="w-full py-4 bg-primary text-white rounded-[1.5rem] font-bold hover:shadow-xl hover:shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-[0.98] disabled:opacity-50">
                      <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                      <span v-else>Update Payment</span>
                    </button>
                    <button type="button" @click="handleClose" class="w-full py-4 border border-gray-200 text-gray-400 rounded-[1.5rem] font-bold hover:text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-all text-sm">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { reactive, watch, ref, computed } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";
import {
  Pencil as PencilIcon,
  X as XIcon,
  ChevronDown as ChevronDownIcon,
  Check as CheckIcon,
  CreditCard as CreditCardIcon,
  Banknote as BanknoteIcon,
  Smartphone as SmartphoneIcon,
  Globe as GlobeIcon,
  QrCode as QrCodeIcon,
  Building2 as Building2Icon,
  ClipboardCheck as ClipboardCheckIcon,
  Wallet as WalletIcon,
  Plus as PlusIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";

const props = defineProps({
  isOpen: Boolean,
  payment: Object,
});

const emit = defineEmits(["close", "success"]);
const toast = useToast();
const loading = ref(false);

const form = reactive({
  payment_date: "",
  payment_details: [
    { method: "cash", amount: 0, transaction_id: "" }
  ],
  notes: "",
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

const totalAmount = computed(() => {
  return (form.payment_details || []).reduce((sum, entry) => sum + (Number(entry.amount) || 0), 0);
});

const getMethodLabel = (value) => {
  return paymentMethods.find((m) => m.value === value)?.label || "Select Method";
};

const getMethodIcon = (value) => {
  return paymentMethods.find((m) => m.value === value)?.icon || CreditCardIcon;
};

const addEntry = () => {
  form.payment_details.push({ method: "cash", amount: 0, transaction_id: "" });
};

const removeEntry = (index) => {
  if (form.payment_details.length > 1) {
    form.payment_details.splice(index, 1);
  }
};

watch(
  () => props.payment,
  (val) => {
    if (val) {
      form.payment_date = val.payment_date
        ? val.payment_date.split("T")[0]
        : "";
      form.payment_details = val.payment_details ? JSON.parse(JSON.stringify(val.payment_details)) : [
        { method: "cash", amount: 0, transaction_id: "" }
      ];
      form.notes = val.notes;
    }
  },
  { immediate: true },
);

const handleClose = () => emit("close");

const handleSubmit = async () => {
  if (totalAmount.value <= 0) {
    toast.error("Total amount must be greater than 0");
    return;
  }

  loading.value = true;
  try {
    const response = await axios.put(
      `/api/v1/payments/${props.payment.id}`,
      form,
    );
    if (response.data.success) {
      toast.success("Payment updated successfully");
      emit("success");
      emit("close");
    }
  } catch (error) {
    toast.error(error.response?.data?.message || "Failed to update payment");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}

/* Autofill styling */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus {
  -webkit-text-fill-color: #475569 !important;
  -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
  box-shadow: 0 0 0px 1000px #ffffff inset !important;
  transition: background-color 5000s ease-in-out 0s;
}

input,
textarea,
select {
  color: #475569 !important;
}
</style>
