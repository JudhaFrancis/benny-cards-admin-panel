<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-[60]">
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
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" />
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
              class="w-full max-w-6xl transform rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh] overflow-hidden"
            >
              <!-- Header (Pixel-perfect match to OrderInfoDialog) -->
              <div class="relative bg-primary text-white overflow-hidden shrink-0">
                <!-- Decorative Background -->
                <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"></div>
                <div class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>

                <div class="relative px-8 py-6 flex items-center justify-between">
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <ClipboardListIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle as="h3" class="text-2xl font-bold tracking-tight text-white mb-1">
                        Enquiry {{ formatDisplayNumber(activeEnquiry?.enquiry_number) || "Details" }}{{ activeEnquiry?.branch?.name ? ` - ${activeEnquiry.branch.name}` : "" }}
                      </DialogTitle>
                      <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <ActivityIcon class="h-3.5 w-3.5" />
                          {{ formatStatusLabel(activeEnquiry?.status) }}
                        </span>
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <CalendarIcon class="h-3.5 w-3.5" />
                          {{ formatDate(activeEnquiry?.created_at) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <template v-if="isPending">
                      <button
                        v-if="canEdit"
                        @click="$emit('edit', activeEnquiry)"
                        class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center gap-1.5 border border-white/10 active:scale-95 transition-all"
                      >
                        <PencilIcon class="h-4 w-4" />
                        Edit
                      </button>
                      <button
                        v-if="canApprove"
                        @click="$emit('reject', activeEnquiry)"
                        class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-rose-900/20 active:scale-95 transition-all"
                      >
                        <XCircleIcon class="h-4 w-4" />
                        Reject
                      </button>
                      <button
                        v-if="canApprove"
                        @click="$emit('approve', activeEnquiry)"
                        class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-emerald-900/20 active:scale-95 transition-all"
                      >
                        <CheckCircleIcon class="h-4 w-4" />
                        Approve & Create Order
                      </button>
                    </template>

                    <button
                      @click="handleClose"
                      class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white transition-all border border-white/5"
                    >
                      <XIcon class="h-6 w-6" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content Container -->
              <div class="flex-1 overflow-y-auto bg-white custom-scrollbar">
                <div class="flex flex-col md:flex-row min-h-full">
                    
                    <!-- Left Column: Primary Information (Matching ViewOrderGeneralDetails) -->
                    <div class="flex-1 p-8 space-y-10 custom-scrollbar">

                      <!-- Rejection Reason (rejected enquiries only) -->
                      <section v-if="activeEnquiry?.status === 'rejected'" class="p-5 rounded-3xl bg-rose-50 border border-rose-100">
                        <h4 class="text-sm font-bold text-rose-700 mb-2 flex items-center gap-2">
                          <XCircleIcon class="h-4 w-4" /> Rejection Reason
                        </h4>
                        <p class="text-sm text-rose-800 font-medium leading-relaxed whitespace-pre-line">
                          {{ activeEnquiry?.rejection_reason || "No reason given" }}
                        </p>
                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mt-3">
                          Rejected{{ activeEnquiry?.rejected_by?.name ? ` by ${activeEnquiry.rejected_by.name}` : "" }} on {{ formatDate(activeEnquiry?.rejected_at) }}
                        </p>
                      </section>

                      <!-- Customer Info Section -->
                      <section>
                        <h4 class="text-sm font-bold text-slate-700 mb-6 flex items-center gap-2 ml-1">
                          <UserIcon class="h-4 w-4 text-primary" /> Customer Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                          <InfoItem label="Full Name" :value="activeEnquiry?.customer_name" bordered class="md:col-span-2" />
                          <InfoItem label="Email Address" :value="activeEnquiry?.customer_email" bordered />
                          <InfoItem label="Phone Number" :value="activeEnquiry?.customer_phone" bordered />
                          <InfoItem label="Branch" :value="activeEnquiry?.branch?.name || 'N/A'" bordered />
                          <InfoItem label="Budget Tier" :value="activeEnquiry?.budget_tier || 'N/A'" bordered />
                          <InfoItem label="Primary Address" bordered>
                            <span class="text-xs font-semibold text-slate-700 leading-relaxed">{{ activeEnquiry?.address_1 || "N/A" }}</span>
                          </InfoItem>
                          <InfoItem label="Secondary Address" bordered>
                            <span class="text-xs font-semibold text-slate-700 leading-relaxed">{{ activeEnquiry?.address_2 || "N/A" }}</span>
                          </InfoItem>

                          <div v-if="notesText" class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">
                              Additional Notes
                            </label>
                            <div class="p-5 rounded-3xl bg-slate-50 border border-slate-100 shadow-inner">
                              <p class="text-xs text-slate-600 font-medium leading-relaxed italic whitespace-pre-line">
                                {{ notesText }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </section>

                      <hr class="border-slate-100" />

                      <!-- Ordered Items Section (Matching ViewOrderGeneralDetails lines 40-93) -->
                      <section>
                        <h4 class="text-sm font-bold text-slate-700 mb-6 flex items-center gap-2 ml-1">
                          <PackageIcon class="h-4 w-4 text-primary" /> Ordered Items
                        </h4>

                        <div v-if="activeEnquiry?.items?.length" class="space-y-4">
                          <div
                            v-for="item in activeEnquiry.items"
                            :key="item.id"
                            class="grid grid-cols-12 gap-4 items-center p-6 rounded-[2rem] bg-slate-50/50 border border-slate-100 shadow-sm relative group"
                          >
                            <!-- Product Info -->
                            <div class="col-span-12 md:col-span-6 flex items-center gap-4">
                              <!-- Product Thumbnail -->
                              <div
                                class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-100 shadow-sm shrink-0 group cursor-zoom-in bg-white"
                                @click="previewImage(getItemImage(item), item.product_name)"
                              >
                                <img
                                  :src="getItemImage(item)"
                                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                  @error="handleImageError"
                                />
                                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                  <Maximize2Icon class="h-4 w-4 text-white" />
                                </div>
                              </div>

                              <div class="min-w-0">
                                <h5 class="text-sm font-bold text-slate-900 truncate uppercase tracking-tight">
                                  {{ item.product_name }}
                                </h5>
                                <p class="text-[10px] font-black text-slate-400 mt-0.5 uppercase tracking-widest">
                                  SKU: {{ item.product?.sku || 'N/A' }}
                                </p>
                              </div>
                            </div>

                            <!-- Qty -->
                            <div class="col-span-4 md:col-span-2 text-center">
                              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Qty</label>
                              <span class="text-xs font-black text-slate-700">{{ item.quantity }}</span>
                            </div>

                            <!-- Price -->
                            <div class="col-span-4 md:col-span-2 text-center">
                              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price</label>
                              <span class="text-xs font-black text-slate-700">₹{{ Number(item.unit_price || 0).toFixed(2) }}</span>
                            </div>

                            <!-- Total -->
                            <div class="col-span-4 md:col-span-2 text-center">
                              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subtotal</label>
                              <span class="text-sm font-black text-primary">₹{{ Number(item.total_price || (item.unit_price * item.quantity) || 0).toFixed(2) }}</span>
                            </div>
                          </div>
                        </div>

                        <div v-else class="py-10 text-center text-xs text-slate-400 italic bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                          No specific items attached to this enquiry.
                        </div>
                      </section>
                    </div>

                    <!-- Right Column: Sidebar Style Summary (Matching ViewOrderGeneralDetails lines 97-147) -->
                    <div class="w-full md:w-[360px] bg-slate-50/50 border-l border-slate-100 p-8 flex flex-col">
                      <h4 class="text-sm font-bold text-slate-700 mb-8 ml-1">Order Summary</h4>

                      <div class="flex-1 space-y-6">
                        <!-- Totals Card -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-5">
                          <div v-for="row in summaryRows" :key="row.label" class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">{{ row.label }}</span>
                            <span class="font-bold text-slate-900 tracking-tight text-xs">{{ row.value }}</span>
                          </div>

                          <div class="pt-5 border-t border-slate-50 flex justify-between items-center">
                            <span class="text-slate-900 font-black uppercase tracking-widest text-[10px] text-primary">Grand Total</span>
                            <span class="text-2xl font-black text-slate-900 tracking-tighter">
                              ₹{{ grandTotal.toFixed(2) }}
                            </span>
                          </div>
                        </div>

                        <!-- Status Indicators -->
                        <div class="px-4 space-y-4">
                          <div class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                            <span>Enquiry Status: <b class="text-slate-900">{{ formatStatusLabel(activeEnquiry?.status) }}</b></span>
                          </div>

                          <div class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                            <span>Created by: <b class="text-slate-900">{{ activeEnquiry?.added_by?.name || "Website" }}</b></span>
                          </div>

                          <div v-if="activeEnquiry?.converted_order_id && activeEnquiry?.converted_order" class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                            <div class="w-2 h-2 rounded-full bg-teal-500"></div>
                            <span>Order: <router-link to="/orders" class="text-primary font-bold hover:underline">#{{ formatDisplayNumber(activeEnquiry.converted_order.order_number) }}</router-link></span>
                          </div>

                          <div v-if="activeEnquiry?.approved_at" class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span>Approved: <b class="text-slate-900">{{ activeEnquiry?.approved_by?.name || "—" }}, {{ formatDate(activeEnquiry.approved_at) }}</b></span>
                          </div>
                        </div>
                      </div>

                      <!-- Sticky Note Icon decoration at bottom of sidebar -->
                      <div class="mt-auto pt-10 opacity-10 flex justify-center">
                        <StickyNoteIcon class="h-20 w-20 text-slate-400 rotate-12" />
                      </div>
                    </div>

                  </div>
                </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Image Preview Modal -->
  <ImagePreviewModal
    :isOpen="isImagePreviewOpen"
    :imageSrc="previewSrc"
    :title="previewTitle"
    @close="isImagePreviewOpen = false"
  />
</template>

<script setup>
import { ref, computed, watch } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  ClipboardList as ClipboardListIcon,
  X as XIcon,
  Pencil as PencilIcon,
  CheckCircle as CheckCircleIcon,
  XCircle as XCircleIcon,
  Calendar as CalendarIcon,
  Maximize2 as Maximize2Icon,
  Package as PackageIcon,
  User as UserIcon,
  Activity as ActivityIcon,
  StickyNote as StickyNoteIcon,
} from "lucide-vue-next";
import axios from "axios";
import InfoItem from "../ui/display/InfoItem.vue";
import ImagePreviewModal from "../ui/modals/ImagePreviewModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { getEnquiryStatusLabel } from "../../constants/enquiryStatuses";
import { formatDisplayNumber } from "../../utils/displayNumber";

const { getModulePermissions } = usePermissions();
const { canEdit } = getModulePermissions("Enquiry");

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  enquiry: {
    type: Object,
    default: null,
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "edit", "approve", "reject"]);

const fullEnquiry = ref(null);
const activeEnquiry = computed(() => fullEnquiry.value || props.enquiry);
const isPending = computed(() => activeEnquiry.value?.status === "pending");

const money = (v) => `₹${Number(v || 0).toFixed(2)}`;

const grandTotal = computed(() => {
  const e = activeEnquiry.value;
  if (!e) return 0;
  if (e.total_amount !== null && e.total_amount !== undefined) return Number(e.total_amount);
  return Math.max(0, itemsSubtotal.value - Number(e.discount || 0) + Number(e.extra_charges || 0));
});

const summaryRows = computed(() => {
  const e = activeEnquiry.value || {};
  return [
    { label: "Subtotal", value: money(itemsSubtotal.value) },
    { label: "Discount", value: money(e.discount) },
    { label: "Extra Charges", value: money(e.extra_charges) },
  ];
});

const notesText = computed(() => {
  const e = activeEnquiry.value;
  if (!e) return "";
  return e.remarks || "";
});

const isImagePreviewOpen = ref(false);
const previewSrc = ref("");
const previewTitle = ref("");

const previewImage = (src, title = "") => {
  if (!src) return;
  previewSrc.value = src;
  previewTitle.value = title;
  isImagePreviewOpen.value = true;
};

const getItemImage = (item) => {
  const path =
    item?.product_image ||
    item?.image ||
    item?.product?.photo ||
    item?.product?.image;
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("data:") || path.startsWith("http") || path.startsWith("/")) return path;
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
};

const itemsSubtotal = computed(() => {
  if (!activeEnquiry.value?.items?.length) return 0;
  return activeEnquiry.value.items.reduce((sum, item) => {
    return sum + (Number(item.total_price) || (Number(item.unit_price) * Number(item.quantity)) || 0);
  }, 0);
});

watch(
  () => [props.isOpen, props.enquiry?.id],
  async ([isOpen, id]) => {
    if (isOpen && id) {
      fullEnquiry.value = props.enquiry;
      try {
        const res = await axios.get(`/api/v1/enquiries/${id}`);
        if (res.data?.success && res.data?.data) {
          fullEnquiry.value = res.data.data;
        }
      } catch (err) {
        console.error("Failed to load enquiry details:", err);
      }
    } else if (!isOpen) {
      fullEnquiry.value = null;
    }
  },
  { immediate: true }
);

const handleClose = () => {
  emit("close");
};


const formatDate = (dateString) => {
  if (!dateString) return "—";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "—";
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const formatStatusLabel = (status) => getEnquiryStatusLabel(status).toUpperCase();
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
