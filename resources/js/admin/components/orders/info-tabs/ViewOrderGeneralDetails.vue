<template>
  <div class="animate-in fade-in duration-500 subpixel-antialiased">
    <!-- Main Content: Two Column Layout Mirroring Edit Modal -->
    <div class="flex flex-col md:flex-row min-h-full">
      
      <!-- Left Column: Primary Information -->
      <div class="flex-1 p-8 space-y-10 custom-scrollbar">
        <!-- Customer Info Section -->
        <section>
          <h4 class="text-sm font-bold text-slate-700 mb-6 flex items-center gap-2 ml-1">
            <UserIcon class="h-4 w-4 text-primary" /> Customer Information
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <InfoItem label="Full Name" :value="order.customer_details?.name" bordered class="md:col-span-2" />
            <InfoItem label="Email Address" :value="order.customer_details?.email" bordered />
            <InfoItem label="Phone Number" :value="order.customer_details?.phone" bordered />
            
            <InfoItem label="Primary Address" bordered class="md:col-span-1">
              <span class="text-xs font-semibold text-slate-700 leading-relaxed">{{ order.customer_details?.address_1 || "N/A" }}</span>
            </InfoItem>
            <InfoItem label="Secondary Address" bordered class="md:col-span-1">
              <span class="text-xs font-semibold text-slate-700 leading-relaxed">{{ order.customer_details?.address_2 || "N/A" }}</span>
            </InfoItem>

            <!-- Remarks integrated into customer section as per Edit modal -->
            <div v-if="order.remarks" class="md:col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Additional Notes</label>
              <div class="p-5 rounded-3xl bg-slate-50 border border-slate-100 shadow-inner">
                <p class="text-xs text-slate-600 font-medium leading-relaxed italic">
                  "{{ order.remarks }}"
                </p>
              </div>
            </div>
          </div>
        </section>

        <hr class="border-slate-100" />

        <!-- Ordered Items Section -->
        <section>
          <h4 class="text-sm font-bold text-slate-700 mb-6 flex items-center gap-2 ml-1">
            <PackageIcon class="h-4 w-4 text-primary" /> Ordered Items
          </h4>
          
          <div class="space-y-4">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="grid grid-cols-12 gap-4 items-center p-6 rounded-[2rem] bg-slate-50/50 border border-slate-100 shadow-sm relative group"
            >
              <!-- Product Info -->
              <div class="col-span-12 md:col-span-6 flex items-center gap-4">
                <!-- Product Thumbnail (Matched with ProductList table) -->
                <div
                  class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-100 shadow-sm shrink-0 group cursor-zoom-in bg-white"
                  @click="previewImage(item.product_image || item.product?.photo, item.product_name)"
                >
                  <img
                    :src="getImageSource(item.product_image || item.product?.photo)"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    @error="handleImageError"
                  />
                  <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <Maximize2Icon class="h-4 w-4 text-white" />
                  </div>
                </div>

                <div class="min-w-0">
                  <h5 class="text-sm font-bold text-slate-900 truncate uppercase tracking-tight">{{ item.product_name }}</h5>
                  <p class="text-[10px] font-black text-slate-400 mt-0.5 uppercase tracking-widest">SKU: {{ item.product?.sku || 'N/A' }}</p>
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
                <span class="text-xs font-black text-slate-700">₹{{ Number(item.unit_price).toFixed(2) }}</span>
              </div>

              <!-- Total -->
              <div class="col-span-4 md:col-span-2 text-center">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subtotal</label>
                <span class="text-sm font-black text-primary">₹{{ Number(item.total_price).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Right Column: Sidebar Style Summary -->
      <div class="w-full md:w-[360px] bg-slate-50/50 border-l border-slate-100 p-8 flex flex-col">
        <h4 class="text-sm font-bold text-slate-700 mb-8 ml-1">Order Summary</h4>

        <div class="flex-1 space-y-6">
          <!-- Totals Card -->
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-5">
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Subtotal</span>
              <span class="font-bold text-slate-900 tracking-tight">₹{{ subtotal.toFixed(2) }}</span>
            </div>

            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Discount</span>
              <span class="font-bold text-rose-500 tracking-tight">-₹{{ Number(order.discount || 0).toFixed(2) }}</span>
            </div>

            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Extra Charges</span>
              <span class="font-bold text-blue-500 tracking-tight">+₹{{ Number(order.extra_charges || 0).toFixed(2) }}</span>
            </div>

            <div v-if="dispatchExpense > 0" class="flex justify-between items-center text-sm">
              <span class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Courier Charge</span>
              <span class="font-bold text-emerald-500 tracking-tight">+₹{{ dispatchExpense.toFixed(2) }}</span>
            </div>

            <div class="pt-5 border-t border-slate-50 flex justify-between items-center">
              <span class="text-slate-900 font-black uppercase tracking-widest text-[10px] text-primary">Grand Total</span>
              <span class="text-2xl font-black text-slate-900 tracking-tighter">₹{{ total.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Status Indicators -->
          <div class="px-4 space-y-4">
            <div class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
              <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
              <span>Order Status: <b class="text-slate-900">{{ order.status }}</b></span>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">
              <div class="w-2 h-2 rounded-full" :class="order.payment_status === 'paid' ? 'bg-emerald-500' : 'bg-rose-500'"></div>
              <span>Payment: <b :class="order.payment_status === 'paid' ? 'text-emerald-600' : 'text-rose-600'">{{ order.payment_status }}</b></span>
            </div>
          </div>
        </div>

        <!-- Sticky Note Icon decoration at bottom of sidebar -->
        <div class="mt-auto pt-10 opacity-10 flex justify-center">
          <StickyNoteIcon class="h-20 w-20 text-slate-400 rotate-12" />
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <ImagePreviewModal
      :isOpen="isImagePreviewOpen"
      :imageSrc="previewSrc"
      :title="previewTitle"
      @close="isImagePreviewOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { User as UserIcon, Package as PackageIcon, StickyNote as StickyNoteIcon, Maximize2 as Maximize2Icon } from "lucide-vue-next";
import InfoItem from "../../ui/display/InfoItem.vue";
import ImagePreviewModal from "../../ui/modals/ImagePreviewModal.vue";

const props = defineProps({
  order: { type: Object, required: true },
});

// State for image preview
const isImagePreviewOpen = ref(false);
const previewSrc = ref("");
const previewTitle = ref("");

const subtotal = computed(() => {
  return (props.order.items || []).reduce(
    (acc, item) => acc + (parseFloat(item.unit_price) || 0) * item.quantity,
    0,
  );
});

const dispatchExpense = computed(() => {
  return parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;
});

const total = computed(() => {
  const discount = parseFloat(props.order.discount) || 0;
  const extraCharges = parseFloat(props.order.extra_charges) || 0;
  return Math.max(0, subtotal.value - discount + extraCharges + dispatchExpense.value);
});

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
};

const previewImage = (src, title) => {
  previewSrc.value = getImageSource(src);
  previewTitle.value = title;
  isImagePreviewOpen.value = true;
};
</script>
