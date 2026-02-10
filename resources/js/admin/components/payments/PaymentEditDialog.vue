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
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
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

              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-bold text-gray-700 mb-2"
                        >Amount (₹) <span class="text-rose-500">*</span></label
                      >
                      <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-700 mb-2"
                        >Payment Date
                        <span class="text-rose-500">*</span></label
                      >
                      <input
                        v-model="form.payment_date"
                        type="date"
                        required
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                      />
                    </div>
                    <div>
                      <label
                        class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2"
                      >
                        <CreditCardIcon class="h-4 w-4 text-primary" />
                        Method <span class="text-rose-500">*</span>
                      </label>
                      <Listbox v-model="form.payment_method">
                        <div class="relative">
                          <ListboxButton
                            class="relative w-full cursor-pointer rounded-2xl bg-gray-50 py-4 pl-11 pr-10 text-left border border-gray-200 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all shadow-sm sm:text-sm"
                          >
                            <span
                              class="absolute left-4 top-1/2 -translate-y-1/2"
                            >
                              <component
                                :is="selectedMethodIcon"
                                class="h-5 w-5 text-gray-400"
                              />
                            </span>
                            <span
                              class="block truncate font-bold text-slate-700"
                            >
                              {{
                                selectedMethodLabel || "Select payment method"
                              }}
                            </span>
                            <span
                              class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4"
                            >
                              <ChevronDownIcon
                                class="h-5 w-5 text-gray-400 transition-transform duration-200"
                                aria-hidden="true"
                              />
                            </span>
                          </ListboxButton>
                          <transition
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                          >
                            <ListboxOptions
                              class="absolute z-20 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-2 text-base shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar"
                            >
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
                                      : 'text-gray-700',
                                    'relative cursor-pointer select-none py-3 pl-11 pr-4 transition-colors',
                                  ]"
                                >
                                  <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2"
                                  >
                                    <component
                                      :is="method.icon"
                                      :class="[
                                        active
                                          ? 'text-primary'
                                          : 'text-gray-400',
                                        'h-5 w-5 transition-colors',
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
                                    <CheckIcon
                                      class="h-5 w-5"
                                      aria-hidden="true"
                                    />
                                  </span>
                                </li>
                              </ListboxOption>
                            </ListboxOptions>
                          </transition>
                        </div>
                      </Listbox>
                    </div>
                    <div>
                      <label class="block text-sm font-bold text-gray-700 mb-2"
                        >Transaction ID
                      </label>
                      <input
                        v-model="form.transaction_id"
                        type="text"
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-bold text-gray-700 mb-2"
                        >Notes</label
                      >
                      <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold resize-none text-slate-600"
                      ></textarea>
                    </div>
                  </div>

                  <div class="flex gap-4 pt-4">
                    <button
                      type="button"
                      @click="handleClose"
                      class="flex-1 py-4 border border-gray-200 text-gray-500 rounded-2xl font-bold hover:bg-gray-50 transition-all"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="loading"
                      class="flex-1 py-4 bg-primary text-white rounded-2xl font-bold hover:shadow-xl hover:shadow-primary/20 transition-all disabled:opacity-50"
                    >
                      {{ loading ? "Saving..." : "Update Payment" }}
                    </button>
                  </div>
                </form>
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
  amount: 0,
  payment_date: "",
  payment_method: "cash",
  transaction_id: "",
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

const selectedMethodLabel = computed(() => {
  return (
    paymentMethods.find((m) => m.value === form.payment_method)?.label || ""
  );
});

const selectedMethodIcon = computed(() => {
  return (
    paymentMethods.find((m) => m.value === form.payment_method)?.icon ||
    CreditCardIcon
  );
});

watch(
  () => props.payment,
  (val) => {
    if (val) {
      form.amount = val.amount;
      form.payment_date = val.payment_date
        ? val.payment_date.split("T")[0]
        : "";
      form.payment_method = val.payment_method;
      form.transaction_id = val.transaction_id;
      form.notes = val.notes;
    }
  },
  { immediate: true },
);

const handleClose = () => emit("close");

const handleSubmit = async () => {
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
