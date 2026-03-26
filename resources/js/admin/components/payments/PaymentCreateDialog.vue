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
                      <WalletIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        New Payment
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        Log a new financial transaction into the system.
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
                    <div class="md:col-span-2">
                      <label
                        class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2"
                      >
                        <PackageIcon class="h-4 w-4 text-primary" />
                        Select Order <span class="text-rose-500">*</span>
                      </label>
                      <Combobox
                        v-model="form.order_id"
                        @update:modelValue="handleOrderChange"
                      >
                        <div class="relative mt-1">
                          <div
                            class="relative w-full cursor-default overflow-hidden rounded-2xl bg-gray-50 border border-gray-200 text-left focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary transition-all font-semibold"
                          >
                            <ComboboxInput
                              class="w-full border-none py-4 pl-12 pr-10 text-sm leading-5 text-gray-700 focus:ring-0 outline-none bg-transparent"
                              :displayValue="
                                (id) =>
                                  orders.find((o) => o.id === id)
                                    ?.order_number || ''
                              "
                              @change="searchQuery = $event.target.value"
                              placeholder="Search by order number or customer..."
                              required
                            />
                            <div
                              class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none"
                            >
                              <SearchIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <ComboboxButton
                              class="absolute inset-y-0 right-0 flex items-center pr-2"
                            >
                              <ChevronDownIcon
                                class="h-5 w-5 text-gray-400"
                                aria-hidden="true"
                              />
                            </ComboboxButton>
                          </div>
                          <TransitionRoot
                            leave="transition ease-in duration-100"
                            leaveFrom="opacity-100"
                            leaveTo="opacity-0"
                            @after-leave="searchQuery = ''"
                          >
                            <ComboboxOptions
                              class="absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-2 text-base shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar text-left"
                            >
                              <div
                                v-if="
                                  filteredOrders.length === 0 &&
                                  searchQuery !== ''
                                "
                                class="relative cursor-default select-none py-4 px-4 text-gray-500 italic"
                              >
                                No matching orders found.
                              </div>

                              <ComboboxOption
                                v-for="order in filteredOrders"
                                :key="order.id"
                                :value="order.id"
                                v-slot="{ selected, active }"
                              >
                                <li
                                  class="relative cursor-default select-none py-3 pl-10 pr-4 transition-colors"
                                  :class="{
                                    'bg-primary/5 text-primary': active,
                                    'text-gray-700': !active,
                                  }"
                                >
                                  <div class="flex flex-col">
                                    <span
                                      class="block truncate font-bold"
                                      :class="{ 'text-primary': selected }"
                                    >
                                      {{ order.order_number }}
                                    </span>
                                    <span
                                      class="block truncate text-xs font-medium text-gray-400 mt-0.5"
                                    >
                                      {{ order.customer_details?.name }} • ₹{{
                                        Number(
                                          order.total_amount,
                                        ).toLocaleString("en-IN")
                                      }}
                                      • Due: ₹{{
                                        Number(
                                          order.balance_due,
                                        ).toLocaleString("en-IN")
                                      }}
                                    </span>
                                  </div>
                                  <span
                                    v-if="selected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary"
                                  >
                                    <CheckIcon
                                      class="h-5 w-5"
                                      aria-hidden="true"
                                    />
                                  </span>
                                </li>
                              </ComboboxOption>
                            </ComboboxOptions>
                          </TransitionRoot>
                        </div>
                      </Combobox>
                    </div>

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
                        placeholder="0.00"
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
                              class="absolute z-20 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-2 text-base shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar text-left"
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
                      <label
                        class="block text-sm font-bold text-gray-700 mb-2 text-gray-400"
                        >Transaction ID
                      </label>
                      <input
                        v-model="form.transaction_id"
                        type="text"
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                        placeholder="e.g. TXN123456"
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
                        placeholder="Any additional details..."
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
                      {{ loading ? "Saving..." : "Confirm Payment" }}
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
import { reactive, ref, onMounted, watch, computed } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";
import {
  Wallet as WalletIcon,
  X as XIcon,
  Search as SearchIcon,
  ChevronDown as ChevronDownIcon,
  Check as CheckIcon,
  Package as PackageIcon,
  Banknote as BanknoteIcon,
  CreditCard as CreditCardIcon,
  Smartphone as SmartphoneIcon,
  Globe as GlobeIcon,
  QrCode as QrCodeIcon,
  Building2 as Building2Icon,
  ClipboardCheck as ClipboardCheckIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";

const props = defineProps({
  isOpen: Boolean,
});

const emit = defineEmits(["close", "success"]);
const toast = useToast();
const loading = ref(false);
const orders = ref([]);
const searchQuery = ref("");

const form = reactive({
  order_id: "",
  amount: 0,
  payment_date: new Date().toISOString().split("T")[0],
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

const filteredOrders = computed(() => {
  if (searchQuery.value === "") return orders.value;
  return orders.value.filter((order) => {
    return (
      order.order_number
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase()) ||
      order.customer_details?.name
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase())
    );
  });
});

const handleOrderChange = (id) => {
  const selectedOrder = orders.value.find((o) => o.id === id);
  if (selectedOrder) {
    form.amount = selectedOrder.balance_due;
  }
};

const fetchOrders = async () => {
  try {
    const response = await axios.get("/api/v1/orders");
    if (response.data.success) {
      orders.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch orders", error);
  }
};

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      fetchOrders();
      resetForm();
    }
  },
);

const handleClose = () => emit("close");

const resetForm = () => {
  Object.assign(form, {
    order_id: "",
    amount: 0,
    payment_date: new Date().toISOString().split("T")[0],
    payment_method: "cash",
    transaction_id: "",
    notes: "",
  });
  searchQuery.value = "";
};

const handleSubmit = async () => {
  if (!form.order_id) {
    toast.error("Please select an order");
    return;
  }

  loading.value = true;
  try {
    const response = await axios.post("/api/v1/payments", form);
    if (response.data.success) {
      toast.success("Payment recorded successfully");
      emit("success");
      emit("close");
      resetForm();
    }
  } catch (error) {
    toast.error(error.response?.data?.message || "Failed to record payment");
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
