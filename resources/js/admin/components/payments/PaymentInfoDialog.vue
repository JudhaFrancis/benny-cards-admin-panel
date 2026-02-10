<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-50">
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
        <div class="flex min-h-full items-center justify-center p-4 text-start">
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
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
            >
              <div
                class="bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10"
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
                      <CreditCardIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Payment Details
                      </DialogTitle>
                      <p class="text-sm text-white/80" v-if="payment">
                        Transaction #{{ payment.payment_number }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <div class="px-8 py-10 space-y-8 overflow-y-auto" v-if="payment">
                <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Payment Number
                    </p>
                    <p class="text-sm font-bold text-gray-900">
                      {{ payment.payment_number }}
                    </p>
                  </div>
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Order Number
                    </p>
                    <p class="text-sm font-bold text-gray-900">
                      #{{ payment.order?.order_number || "N/A" }}
                    </p>
                  </div>
                  <div class="space-y-1.5 border-t border-gray-50 pt-5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Amount
                    </p>
                    <p class="text-2xl font-black text-primary">
                      ₹{{
                        Number(payment.amount).toLocaleString("en-IN", {
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2,
                        })
                      }}
                    </p>
                  </div>
                  <div class="space-y-1.5 border-t border-gray-50 pt-5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Status
                    </p>
                    <div class="pt-1">
                      <span
                        :class="
                          cn(
                            'inline-flex items-center px-4 py-1 rounded-full text-[10px] font-black tracking-widest border uppercase transition-all duration-300',
                            statusStyles[payment.payment_status],
                          )
                        "
                      >
                        {{ payment.payment_status }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Method
                    </p>
                    <p class="text-sm font-bold text-gray-700 capitalize">
                      {{ payment.payment_method.replace("_", " ") }}
                    </p>
                  </div>
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Transaction ID
                    </p>
                    <p
                      class="text-sm font-semibold text-gray-900 font-mono tracking-tight"
                    >
                      {{ payment.transaction_id || "N/A" }}
                    </p>
                  </div>
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Payment Date
                    </p>
                    <p class="text-sm font-bold text-gray-700">
                      {{ formatDate(payment.payment_date) }}
                    </p>
                  </div>
                  <div class="space-y-1.5">
                    <p
                      class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                    >
                      Added By
                    </p>
                    <div class="flex items-center gap-2">
                      <div
                        class="w-2 h-2 rounded-full bg-primary/40 animate-pulse"
                      ></div>
                      <p class="text-sm font-bold text-gray-700">
                        {{ payment.added_by?.name || "System" }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-if="payment.notes" class="pt-2">
                  <p
                    class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3"
                  >
                    Notes & Observations
                  </p>
                  <div class="relative">
                    <div
                      class="absolute -left-4 top-0 bottom-0 w-1 bg-primary/10 rounded-full"
                    ></div>
                    <p
                      class="text-sm leading-relaxed text-gray-600 bg-gray-50/50 p-5 rounded-2xl border border-gray-100/50 italic"
                    >
                      "{{ payment.notes }}"
                    </p>
                  </div>
                </div>
              </div>

              <div class="px-8 py-6 bg-gray-50 text-right">
                <button
                  @click="$emit('close')"
                  class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-100 transition-colors"
                >
                  Close Details
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import { X as XIcon, CreditCard as CreditCardIcon } from "lucide-vue-next";

const props = defineProps({
  isOpen: Boolean,
  payment: Object,
});

defineEmits(["close"]);

const statusStyles = {
  completed: "bg-emerald-50 text-emerald-700 border-emerald-100",
  pending: "bg-amber-50 text-amber-700 border-amber-100",
  failed: "bg-rose-50 text-rose-700 border-rose-100",
  refunded: "bg-blue-50 text-blue-700 border-blue-100",
  cancelled: "bg-gray-50 text-gray-700 border-gray-100",
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};
</script>
