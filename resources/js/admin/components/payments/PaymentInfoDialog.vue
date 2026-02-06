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
        <div class="flex min-h-full items-center justify-center p-4 text-center">
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
              class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-gray-100"
            >
              <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <DialogTitle as="h3" class="text-xl font-bold text-gray-900">
                  Payment Details
                </DialogTitle>
                <button @click="$emit('close')" class="p-2 rounded-xl hover:bg-gray-100 text-gray-400 transition-colors">
                  <XIcon class="h-5 w-5" />
                </button>
              </div>

              <div class="p-8 space-y-6" v-if="payment">
                <div class="grid grid-cols-2 gap-6">
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Number</p>
                    <p class="text-sm font-bold text-gray-900">{{ payment.payment_number }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Order Number</p>
                    <p class="text-sm font-bold text-gray-900">#{{ payment.order?.order_number || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Amount</p>
                    <p class="text-lg font-black text-primary">${{ Number(payment.amount).toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                    <span :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border', statusStyles[payment.payment_status])">
                      {{ payment.payment_status.toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Method</p>
                    <p class="text-sm font-bold text-gray-700 capitalize">{{ payment.payment_method.replace('_', ' ') }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Transaction ID</p>
                    <p class="text-sm font-medium text-gray-900">{{ payment.transaction_id || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Date</p>
                    <p class="text-sm font-medium text-gray-700">{{ formatDate(payment.payment_date) }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Added By</p>
                    <p class="text-sm font-medium text-gray-700">{{ payment.added_by?.name || 'System' }}</p>
                  </div>
                </div>

                <div v-if="payment.notes">
                  <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Notes</p>
                  <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded-2xl border border-gray-100 italic">
                    "{{ payment.notes }}"
                  </p>
                </div>
              </div>

              <div class="px-8 py-6 bg-gray-50 text-right">
                <button @click="$emit('close')" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-100 transition-colors">
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
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from "@headlessui/vue";
import { X as XIcon } from "lucide-vue-next";

const props = defineProps({
  isOpen: Boolean,
  payment: Object
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

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  });
};
</script>
