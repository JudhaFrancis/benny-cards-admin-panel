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
              <div class="relative bg-primary px-8 py-6 text-white shrink-0 flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/10">
                    <ClipboardListIcon class="h-6 w-6 text-white" />
                  </div>
                  <div>
                    <DialogTitle as="h3" class="text-xl font-bold tracking-tight text-white">
                      New Order Enquiry
                    </DialogTitle>
                    <p class="text-xs text-white/80">
                      Record walk-in, Instagram, phone, or WhatsApp lead details
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
              <form @submit.prevent="handleSubmit" class="overflow-y-auto p-8 space-y-6 flex-1">
                <!-- Section 1: Customer Info & Source -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Customer Information
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Customer Name <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.customer_name"
                        type="text"
                        required
                        placeholder="e.g. Anand & Priya"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Phone Number <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.customer_phone"
                        type="text"
                        required
                        placeholder="e.g. 9876543210"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Email Address
                      </label>
                      <input
                        v-model="form.customer_email"
                        type="email"
                        placeholder="customer@email.com"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Lead Source
                      </label>
                      <SearchableDropdown
                        v-model="form.source"
                        :options="SOURCE_OPTIONS"
                        placeholder="Select Lead Source"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Occasion
                      </label>
                      <input
                        v-model="form.occasion"
                        type="text"
                        placeholder="Wedding, Reception, Puberty, etc."
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Branch
                      </label>
                      <input
                        v-model="form.order_placed_in"
                        type="text"
                        placeholder="e.g. Chennai, Madurai"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                      Address
                    </label>
                    <textarea
                      v-model="form.customer_address"
                      rows="2"
                      placeholder="Street, City, Pincode"
                      class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    ></textarea>
                  </div>
                </div>

                <!-- Section 2: Card Specs & Requirements -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Card Requirements & Timeline
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Card Type
                      </label>
                      <SearchableDropdown
                        v-model="form.card_type"
                        :options="CARD_TYPE_OPTIONS"
                        placeholder="Select Card Type"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Expected Delivery Date
                      </label>
                      <DatePicker
                        v-model="form.expected_delivery_date"
                        placeholder="Select Delivery Date"
                        custom-class="py-2.5 text-xs rounded-xl"
                      >
                        <template #leading>
                          <CalendarIcon class="h-4 w-4 text-slate-400" />
                        </template>
                      </DatePicker>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Priority
                      </label>
                      <SearchableDropdown
                        v-model="form.priority"
                        :options="PRIORITY_OPTIONS"
                        placeholder="Select Priority"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Next Follow-up Date
                      </label>
                      <DatePicker
                        v-model="form.next_follow_up_at"
                        placeholder="Select Follow-up Date"
                        custom-class="py-2.5 text-xs rounded-xl"
                      >
                        <template #leading>
                          <CalendarIcon class="h-4 w-4 text-slate-400" />
                        </template>
                      </DatePicker>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Card Specifications (Size, Lamination, GSM)
                      </label>
                      <input
                        v-model="form.specifications"
                        type="text"
                        placeholder="e.g. A4 Trifold, 300 GSM, Matt lamination"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Assigned Staff
                      </label>
                      <input
                        v-model="form.assigned_to"
                        type="text"
                        placeholder="Staff member handling this enquiry"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                      Notes / Customer Message
                    </label>
                    <textarea
                      v-model="form.message"
                      rows="2"
                      placeholder="Special requirements, foil stamping notes, etc."
                      class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    ></textarea>
                  </div>
                </div>

                <!-- Section 3: Cards / Items -->
                <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-100 space-y-4">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                      Card / Product Items
                    </h4>
                    <button
                      type="button"
                      @click="addItem"
                      class="text-xs font-bold text-primary hover:text-primary/80 flex items-center gap-1"
                    >
                      <PlusIcon class="h-3.5 w-3.5" /> Add Another Card
                    </button>
                  </div>

                  <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3"
                  >
                    <!-- Quick catalog selector -->
                    <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-100">
                      <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        Card Item #{{ index + 1 }}
                      </span>
                      <div class="flex items-center gap-2">
                        <select
                          v-if="products.length > 0"
                          @change="onProductSelect(index, $event.target.value)"
                          class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-slate-700 bg-slate-50 focus:outline-none focus:border-primary cursor-pointer"
                        >
                          <option value="">— Pick from Catalog (Auto-fill) —</option>
                          <option v-for="p in products" :key="p.id" :value="p.id">
                            {{ p.title }} (₹{{ p.price }})
                          </option>
                        </select>
                        <button
                          v-if="form.items.length > 1"
                          type="button"
                          @click="removeItem(index)"
                          class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors"
                          title="Remove Item"
                        >
                          <Trash2Icon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                      <!-- Image Thumbnail / Upload Box -->
                      <div class="relative shrink-0">
                        <input
                          type="file"
                          :id="'item-img-' + index"
                          accept="image/*"
                          class="hidden"
                          @change="handleImageUpload($event, index)"
                        />
                        <div
                          v-if="item.product_image"
                          class="w-16 h-16 rounded-2xl border border-slate-200 overflow-hidden relative group bg-slate-50"
                        >
                          <img
                            :src="item.product_image"
                            alt="Card Image"
                            class="w-full h-full object-cover"
                          />
                          <button
                            type="button"
                            @click="clearItemImage(index)"
                            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-md hover:bg-rose-600 transition-colors"
                            title="Remove Image"
                          >
                            <XIcon class="h-3 w-3" />
                          </button>
                        </div>
                        <label
                          v-else
                          :for="'item-img-' + index"
                          class="w-16 h-16 rounded-2xl border-2 border-dashed border-slate-200 hover:border-primary/50 bg-slate-50 hover:bg-primary/5 flex flex-col items-center justify-center cursor-pointer transition-colors text-slate-400 hover:text-primary"
                          title="Click to attach card sample image"
                        >
                          <ImageIcon class="h-5 w-5 mb-0.5" />
                          <span class="text-[9px] font-bold">Add Image</span>
                        </label>
                      </div>

                      <!-- Details Grid -->
                      <div class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 w-full">
                        <div class="sm:col-span-6">
                          <label class="block text-xs font-semibold text-slate-700 mb-1">Card / Product Name *</label>
                          <input
                            v-model="item.product_name"
                            type="text"
                            required
                            placeholder="e.g. Royal Peacock Trifold"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                          />
                        </div>
                        <div class="sm:col-span-3">
                          <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                          <input
                            v-model.number="item.quantity"
                            type="number"
                            min="1"
                            required
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                          />
                        </div>
                        <div class="sm:col-span-3">
                          <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price (₹)</label>
                          <input
                            v-model.number="item.unit_price"
                            type="number"
                            min="0"
                            step="0.5"
                            placeholder="0.00"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer Buttons -->
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
                    class="px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary/95 active:scale-95 transition-all disabled:opacity-50"
                  >
                    {{ loading ? "Saving..." : "Create Enquiry" }}
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
import { ref, reactive, watch, onMounted } from "vue";
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
  Plus as PlusIcon,
  Trash2 as Trash2Icon,
  Calendar as CalendarIcon,
  Image as ImageIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import SearchableDropdown from "../ui/dropdowns/SearchableDropdown.vue";
import DatePicker from "../ui/pickers/DatePicker.vue";
import { PRIORITY_OPTIONS } from "../../constants/orderPriorities";

const SOURCE_OPTIONS = [
  { value: "walk_in", label: "Walk-In (In-Store)" },
  { value: "instagram", label: "Instagram" },
  { value: "phone", label: "Phone Call" },
  { value: "whatsapp", label: "WhatsApp" },
  { value: "website", label: "Website" },
  { value: "other", label: "Other" },
];

const CARD_TYPE_OPTIONS = [
  { value: "customize", label: "Custom Wedding Card" },
  { value: "ready_made", label: "Ready-Made Card" },
];

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["close", "created"]);
const toast = useToast();
const loading = ref(false);
const products = ref([]);

const fetchProducts = async () => {
  try {
    const res = await axios.get("/api/v1/products/list");
    if (res.data?.success && Array.isArray(res.data?.data)) {
      products.value = res.data.data;
    }
  } catch (err) {
    console.error("Failed to load products:", err);
  }
};

const onProductSelect = (index, productId) => {
  if (!productId) return;
  const prod = products.value.find((p) => String(p.id) === String(productId));
  if (prod) {
    form.items[index].product_id = prod.id;
    form.items[index].product_name = prod.title;
    form.items[index].unit_price = Number(prod.price) || 0;
    form.items[index].product_image = prod.photo || prod.image || "";
  }
};

const handleImageUpload = (e, index) => {
  const file = e.target.files?.[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) {
    toast.error("Image size must be less than 5MB");
    return;
  }
  const reader = new FileReader();
  reader.onload = (event) => {
    form.items[index].product_image = event.target.result;
  };
  reader.readAsDataURL(file);
};

const clearItemImage = (index) => {
  form.items[index].product_image = "";
  form.items[index].product_id = null;
};

const form = reactive({
  customer_name: "",
  customer_phone: "",
  customer_email: "",
  customer_address: "",
  occasion: "wedding",
  order_placed_in: "Chennai",
  source: "walk_in",
  card_type: "customize",
  specifications: "",
  expected_delivery_date: "",
  priority: "P4",
  next_follow_up_at: "",
  assigned_to: "",
  message: "",
  items: [
    {
      product_id: null,
      product_name: "Custom Wedding Card",
      product_image: "",
      quantity: 100,
      unit_price: 0,
    },
  ],
});

const addItem = () => {
  form.items.push({
    product_id: null,
    product_name: "",
    product_image: "",
    quantity: 100,
    unit_price: 0,
  });
};

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
};

const resetForm = () => {
  form.customer_name = "";
  form.customer_phone = "";
  form.customer_email = "";
  form.customer_address = "";
  form.occasion = "wedding";
  form.order_placed_in = "Chennai";
  form.source = "walk_in";
  form.card_type = "customize";
  form.specifications = "";
  form.expected_delivery_date = "";
  form.priority = "P4";
  form.next_follow_up_at = "";
  form.assigned_to = "";
  form.message = "";
  form.items = [
    {
      product_id: null,
      product_name: "Custom Wedding Card",
      product_image: "",
      quantity: 100,
      unit_price: 0,
    },
  ];
};

onMounted(() => {
  fetchProducts();
});

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      resetForm();
      if (!products.value.length) {
        fetchProducts();
      }
    }
  }
);

const handleClose = () => {
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    const res = await axios.post("/api/v1/enquiries", form);
    if (res.data.success) {
      toast.success(res.data.message || "Enquiry created successfully!");
      emit("created", res.data.data);
      handleClose();
    }
  } catch (err) {
    console.error("Failed to create enquiry", err);
    toast.error(err.response?.data?.message || "Failed to create enquiry. Please check fields.");
  } finally {
    loading.value = false;
  }
};
</script>

