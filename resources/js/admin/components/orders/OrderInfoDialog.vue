<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-[60]">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
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
              class="w-full max-w-3xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col max-h-[90vh]"
            >
              <!-- Header -->
              <div
                class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-white sticky top-0 z-10"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary"
                  >
                    <PackageIcon class="h-6 w-6" />
                  </div>
                  <div>
                    <DialogTitle
                      as="h3"
                      class="text-2xl font-bold text-slate-900 tracking-tight"
                    >
                      Order Details
                    </DialogTitle>
                    <p class="text-sm text-slate-500 mt-0.5">
                      Summary and tracking history.
                    </p>
                  </div>
                </div>
                <button
                  @click="$emit('close')"
                  class="p-3 rounded-2xl hover:bg-slate-100 text-slate-400 transition-all active:scale-95"
                >
                  <XIcon class="h-6 w-6" />
                </button>
              </div>

              <!-- Content -->
              <div
                class="flex-1 overflow-y-auto p-8 custom-scrollbar space-y-8 bg-slate-50/10"
              >
                <div v-if="order" class="space-y-8">
                  <!-- Stats Header -->
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="space-y-1">
                      <p
                        class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1"
                      >
                        <HashIcon class="h-3 w-3" /> Order ID
                      </p>
                      <p class="font-bold text-slate-900">#{{ order.id }}</p>
                    </div>
                    <div class="space-y-1">
                      <p
                        class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1"
                      >
                        <CalendarIcon class="h-3 w-3" /> Date
                      </p>
                      <p class="font-bold text-slate-900">
                        {{ order.orderDate }}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <p
                        class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1"
                      >
                        <TruckIcon class="h-3 w-3" /> Tracking
                      </p>
                      <p class="font-bold text-slate-900">
                        {{ order.trackingId || "N/A" }}
                      </p>
                    </div>
                    <div class="space-y-1">
                      <p
                        class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                      >
                        Courier
                      </p>
                      <p class="font-bold text-slate-900">
                        {{ order.courier || "N/A" }}
                      </p>
                    </div>
                  </div>

                  <hr class="border-slate-100" />

                  <!-- Timeline -->
                  <div v-if="order.timeline && order.timeline.length > 0">
                    <h4
                      class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2"
                    >
                      <ClockIcon class="h-4 w-4 text-primary" /> Order Progress
                    </h4>
                    <div
                      class="relative pl-6 space-y-8 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100"
                    >
                      <div
                        v-for="(step, index) in order.timeline"
                        :key="index"
                        class="relative"
                      >
                        <div
                          class="absolute -left-6 top-1.5 w-[22px] h-[22px] rounded-full border-4 border-white z-10 transition-colors shadow-sm"
                          :class="
                            step.completed ? 'bg-emerald-500' : 'bg-slate-200'
                          "
                        />
                        <div class="space-y-1">
                          <div class="flex items-center gap-2">
                            <p
                              class="text-sm font-bold"
                              :class="
                                step.completed
                                  ? 'text-slate-900'
                                  : 'text-slate-400'
                              "
                            >
                              {{ step.status }}
                            </p>
                            <span
                              v-if="step.date"
                              class="text-[10px] font-bold text-slate-400 uppercase"
                            >
                              {{ step.date }} • {{ step.time }}
                            </span>
                          </div>
                          <p
                            class="text-xs text-slate-500 font-medium max-w-md"
                          >
                            {{ step.description }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <hr class="border-slate-100" />

                  <!-- Items Table -->
                  <div>
                    <h4 class="text-sm font-bold text-slate-900 mb-4">
                      Ordered Items
                    </h4>
                    <div class="space-y-3">
                      <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm"
                      >
                        <div
                          class="w-14 h-14 rounded-xl bg-slate-50 flex items-center justify-center overflow-hidden border border-slate-100 shrink-0"
                        >
                          <img
                            v-if="item.image"
                            :src="item.image"
                            class="w-full h-full object-cover"
                          />
                          <PackageIcon v-else class="h-6 w-6 text-slate-300" />
                        </div>
                        <div class="flex-1">
                          <p class="text-sm font-bold text-slate-900">
                            {{ item.name }}
                          </p>
                          <p class="text-xs text-slate-500 font-medium">
                            Qty: {{ item.quantity }} × ${{
                              item.price.toFixed(2)
                            }}
                          </p>
                        </div>
                        <p class="text-sm font-bold text-slate-900">
                          ${{ (item.price * item.quantity).toFixed(2) }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <hr class="border-slate-100" />

                  <!-- Customer & Total -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                      <h4
                        class="text-sm font-bold text-slate-900 flex items-center gap-2"
                      >
                        <MapPinIcon class="h-4 w-4 text-primary" /> Delivery
                        Address
                      </h4>
                      <div
                        class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1"
                      >
                        <p class="text-sm font-bold text-slate-900">
                          {{ order.customerDetails?.name }}
                        </p>
                        <p class="text-xs text-slate-500 font-medium">
                          {{ order.customerDetails?.addressLine1 }}
                        </p>
                        <p
                          v-if="order.customerDetails?.addressLine2"
                          class="text-xs text-slate-500 font-medium"
                        >
                          {{ order.customerDetails?.addressLine2 }}
                        </p>
                        <p class="text-xs text-slate-500 font-medium">
                          {{ order.customerDetails?.city }},
                          {{ order.customerDetails?.state }}
                          {{ order.customerDetails?.pincode }}
                        </p>
                        <p
                          class="text-xs text-slate-500 font-bold uppercase tracking-wider pt-2"
                        >
                          {{ order.customerDetails?.mobile }}
                        </p>
                      </div>
                    </div>
                    <div class="flex flex-col justify-end">
                      <div
                        class="p-6 rounded-[2.5rem] bg-primary text-white shadow-xl shadow-primary/20 space-y-1 text-center"
                      >
                        <p
                          class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-60"
                        >
                          Total Amount
                        </p>
                        <p class="text-3xl font-black">
                          ${{ order.amount.toFixed(2) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div
                class="px-8 py-5 border-t border-slate-50 bg-white flex justify-end gap-3"
              >
                <button
                  @click="$emit('close')"
                  class="px-8 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-all active:scale-95"
                >
                  Close
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
import {
  X as XIcon,
  Package as PackageIcon,
  Truck as TruckIcon,
  MapPin as MapPinIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  order: {
    type: Object,
    default: null,
  },
});

defineEmits(["close"]);
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
</style>
