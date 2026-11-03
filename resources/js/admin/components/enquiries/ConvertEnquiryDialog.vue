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
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-4xl transform overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Header -->
              <div class="relative bg-emerald-600 px-8 py-6 text-white shrink-0 flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/10">
                    <CheckCircleIcon class="h-6 w-6 text-white" />
                  </div>
                  <div>
                    <DialogTitle as="h3" class="text-xl font-bold tracking-tight text-white">
                      Convert to Production Order
                    </DialogTitle>
                    <p class="text-xs text-white/80">
                      Convert {{ enquiry?.enquiry_number }} into an active order with printing & design stages
                    </p>
                  </div>
                </div>
                <button
                  @click="handleClose"
                  class="rounded-xl p-2 text-white/70 hover:bg-white/10 hover:text-white transition-colors"
                >
                  <XIcon class="h-5 w-5" />
                </button>
              </div>

              <!-- Form Body -->
              <form @submit.prevent="handleConvert" class="overflow-y-auto p-8 space-y-6 flex-1">
                <!-- Customer Confirmation -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Customer Information
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name</label>
                      <input
                        v-model="form.customer_name"
                        type="text"
                        required
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                      <input
                        v-model="form.customer_phone"
                        type="text"
                        required
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                      <input
                        v-model="form.customer_email"
                        type="email"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-500"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Address</label>
                    <textarea
                      v-model="form.customer_address"
                      rows="2"
                      class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-500"
                    ></textarea>
                  </div>
                </div>

                <!-- Production & Timeline Options -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Timeline & Priority
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Final Delivery Date</label>
                      <DatePicker
                        v-model="form.delivery_date"
                        placeholder="Select Delivery Date"
                        custom-class="py-2.5 text-xs rounded-xl"
                      >
                        <template #leading>
                          <CalendarIcon class="h-4 w-4 text-slate-400" />
                        </template>
                      </DatePicker>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Order Priority</label>
                      <SearchableDropdown
                        v-model="form.priority"
                        :options="PRIORITY_OPTIONS"
                        placeholder="Select Priority"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Budget Tier</label>
                      <SearchableDropdown
                        v-model="form.budget_tier"
                        :options="BUDGET_TIER_OPTIONS"
                        placeholder="Select Budget Tier"
                      />
                    </div>
                  </div>
                </div>

                <!-- Items Review -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Confirmed Card Items
                  </h4>
                  <div
                    v-for="(item, idx) in form.items"
                    :key="idx"
                    class="p-4 rounded-xl bg-white border border-slate-200 grid grid-cols-1 md:grid-cols-4 gap-3 items-end"
                  >
                    <div class="md:col-span-2">
                      <label class="block text-xs font-medium text-slate-600 mb-1">Card Title</label>
                      <input
                        v-model="item.product_name"
                        type="text"
                        required
                        class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        required
                        class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">Unit Price (₹)</label>
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        min="0"
                        step="0.5"
                        required
                        class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-sm"
                      />
                    </div>
                  </div>
                </div>

                <!-- Financials -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Payment & Totals
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Discount Amount (₹)</label>
                      <input
                        v-model.number="form.discount"
                        type="number"
                        min="0"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Extra Charges (₹)</label>
                      <input
                        v-model.number="form.extra_charges"
                        type="number"
                        min="0"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">Advance Paid Now (₹)</label>
                      <input
                        v-model.number="form.paid_amount"
                        type="number"
                        min="0"
                        placeholder="0.00"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm text-emerald-700 font-bold"
                      />
                    </div>
                  </div>

                  <!-- Summary calculation -->
                  <div class="p-4 rounded-xl bg-white border border-slate-200 flex justify-between items-center text-sm">
                    <div>
                      <span class="text-xs text-slate-400 font-bold uppercase">Subtotal:</span>
                      <span class="ml-2 font-bold text-slate-800">₹{{ subtotal.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 font-bold uppercase">Grand Total:</span>
                      <span class="ml-2 text-base font-extrabold text-primary">₹{{ grandTotal.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 font-bold uppercase">Balance Due:</span>
                      <span class="ml-2 font-bold text-rose-600">₹{{ balanceDue.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                  <button
                    type="button"
                    @click="handleClose"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all disabled:opacity-50 flex items-center gap-2"
                  >
                    <CheckCircleIcon class="h-4 w-4" />
                    {{ loading ? "Converting..." : "Confirm & Create Order" }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  CheckCircle as CheckCircleIcon,
  X as XIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import SearchableDropdown from "../ui/dropdowns/SearchableDropdown.vue";
import DatePicker from "../ui/pickers/DatePicker.vue";
import { PRIORITY_OPTIONS } from "../../constants/orderPriorities";
import { BUDGET_TIER_OPTIONS } from "../../constants/orderBudgetTiers";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  enquiry: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "converted"]);
const toast = useToast();
const loading = ref(false);

const form = reactive({
  customer_name: "",
  customer_phone: "",
  customer_email: "",
  customer_address: "",
  delivery_date: "",
  priority: "P4",
  budget_tier: "",
  discount: 0,
  extra_charges: 0,
  paid_amount: 0,
  items: [],
});

watch(
  () => props.enquiry,
  (enq) => {
    if (enq) {
      form.customer_name = enq.customer_name || "";
      form.customer_phone = enq.customer_phone || "";
      form.customer_email = enq.customer_email || "";
      form.customer_address = enq.customer_address || "";
      form.delivery_date = enq.expected_delivery_date ? enq.expected_delivery_date.substring(0, 10) : "";
      form.priority = enq.priority || "P4";
      form.budget_tier = enq.budget_tier || "";
      form.discount = 0;
      form.extra_charges = 0;
      form.paid_amount = 0;

      if (enq.items && enq.items.length) {
        form.items = enq.items.map((it) => ({
          product_id: it.product_id,
          product_name: it.product_name,
          product_image: it.product_image,
          quantity: it.quantity || 1,
          unit_price: Number(it.unit_price) || 0,
        }));
      } else {
        form.items = [
          {
            product_id: null,
            product_name: enq.specifications || "Custom Wedding Card",
            quantity: 1,
            unit_price: Number(enq.quoted_amount) || 0,
          },
        ];
      }
    }
  },
  { immediate: true }
);

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const grandTotal = computed(() => {
  return Math.max(0, subtotal.value + (Number(form.extra_charges) || 0) - (Number(form.discount) || 0));
});

const balanceDue = computed(() => {
  return Math.max(0, grandTotal.value - (Number(form.paid_amount) || 0));
});

const handleClose = () => {
  emit("close");
};

const handleConvert = async () => {
  loading.value = true;
  try {
    const res = await axios.post(`/api/v1/enquiries/${props.enquiry.id}/convert`, form);
    if (res.data.success) {
      toast.success(res.data.message || "Order created successfully!");
      emit("converted", res.data.data);
      handleClose();
    }
  } catch (err) {
    console.error("Failed to convert enquiry", err);
    toast.error(err.response?.data?.message || "Failed to convert enquiry.");
  } finally {
    loading.value = false;
  }
};
</script>
