<template>
  <div class="space-y-8">
    <!-- Current Items -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label
          class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2"
        >
          <PackageIcon class="h-3 w-3" /> Current Items
        </label>
        <span
          class="px-2 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-500"
        >
          {{ order.items?.length || 0 }} Items
        </span>
      </div>

      <div class="space-y-4">
        <TransitionGroup
          enter-active-class="transform transition duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transform transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-for="item in order.items"
            :key="item.id"
            class="group flex items-center gap-6 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm hover:border-primary/20 hover:shadow-md transition-all"
          >
            <!-- Image -->
            <div
              class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform"
            >
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.product_name"
                class="w-full h-full object-cover"
              />
              <PackageIcon v-else class="h-6 w-6 text-slate-300" />
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-bold text-slate-900 truncate text-sm mb-1">
                {{ item.product_name }}
              </p>
              <div class="flex items-center gap-4">
                <div class="relative w-32">
                  <span
                    class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-[10px]"
                    >₹</span
                  >
                  <input
                    type="number"
                    :value="item.unit_price"
                    @input="updateUnitPriceValue(item.id, $event.target.value)"
                    class="w-full pl-6 pr-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-primary transition-all font-bold text-xs appearance-none [-moz-appearance:_textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    step="0.01"
                    min="0"
                    @focus="$event.target.select()"
                  />
                </div>
                <p class="text-xs text-slate-500 font-bold">
                  Subtotal: ₹{{ Number(item.total_price || 0).toFixed(2) }}
                </p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4">
              <!-- Quantity Control -->
              <div
                class="flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200"
              >
                <button
                  @click="updateQuantity(item.id, -1)"
                  class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all shadow-sm active:scale-95 disabled:opacity-50"
                  :disabled="item.quantity <= 1"
                >
                  <MinusIcon class="h-3.5 w-3.5" />
                </button>
                <div class="w-12 text-center">
                  <input
                    type="number"
                    :value="item.quantity"
                    @input="updateQuantityValue(item.id, $event.target.value)"
                    class="w-full text-center text-xs font-black text-slate-900 bg-transparent border-none focus:ring-0 p-0 appearance-none [-moz-appearance:_textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    @keypress="isNumber($event)"
                    min="1"
                    @focus="$event.target.select()"
                  />
                </div>
                <button
                  @click="updateQuantity(item.id, 1)"
                  class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary/20 hover:bg-primary/5 transition-all shadow-sm active:scale-95"
                >
                  <PlusIcon class="h-3.5 w-3.5" />
                </button>
              </div>

              <!-- Delete -->
              <button
                @click="removeItem(item.id)"
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all active:scale-90"
              >
                <Trash2Icon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </TransitionGroup>

        <div
          v-if="!order.items?.length"
          class="text-center py-12 px-6 rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50"
        >
          <div
            class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <PackageIcon class="h-8 w-8 text-slate-300" />
          </div>
          <p class="text-slate-500 font-medium mb-1">No items in this order</p>
          <p class="text-xs text-slate-400">
            Search and add products below to get preliminary details.
          </p>
        </div>
      </div>
    </div>

    <!-- Add New Items -->
    <div class="space-y-4">
      <label
        class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2"
      >
        <SearchIcon class="h-3 w-3" /> Add Product
      </label>

      <div class="relative group z-20">
        <Combobox
          v-model="selectedProduct"
          @update:modelValue="handleAddProduct"
        >
          <div class="relative mt-1">
            <div
              class="relative w-full cursor-default overflow-hidden rounded-2xl bg-white border border-slate-200 text-left focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary transition-all font-semibold shadow-sm"
            >
              <ComboboxInput
                class="w-full border-none py-3 pl-12 pr-10 text-xs leading-5 text-slate-900 focus:ring-0 outline-none bg-transparent placeholder:text-slate-400"
                :displayValue="(product) => query"
                @change="query = $event.target.value"
                placeholder="Search for products to add..."
              />
              <div
                class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none"
              >
                <SearchIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
              </div>
              <ComboboxButton
                class="absolute inset-y-0 right-0 flex items-center pr-4"
              >
                <ChevronDownIcon
                  class="h-5 w-5 text-slate-400"
                  aria-hidden="true"
                />
              </ComboboxButton>
            </div>
            <transition
              leave-active-class="transition ease-in duration-100"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
              @after-leave="query = ''"
            >
              <ComboboxOptions
                class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm custom-scrollbar text-left"
              >
                <div
                  v-if="filteredProducts.length === 0 && query !== ''"
                  class="relative cursor-default select-none py-10 px-4 text-slate-400 text-center"
                >
                  <PackageIcon class="h-8 w-8 mx-auto mb-2 opacity-20" />
                  <p class="font-medium">No products found</p>
                </div>

                <ComboboxOption
                  v-for="product in filteredProducts"
                  as="template"
                  :key="product.id"
                  :value="product"
                  v-slot="{ selected, active }"
                >
                  <li
                    class="relative cursor-default select-none py-3 pl-3 pr-4 transition-all rounded-2xl mb-1 last:mb-0"
                    :class="{
                      'bg-primary/5 text-primary': active,
                      'text-slate-900': !active,
                    }"
                  >
                    <div class="flex items-center gap-4">
                      <!-- Product Thumbnail -->
                      <div
                        class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden flex-shrink-0 flex items-center justify-center"
                      >
                        <img
                          v-if="product.image"
                          :src="getImageSource(product.image)"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                        <PackageIcon v-else class="h-5 w-5 text-slate-300" />
                      </div>

                      <div class="flex-1 min-w-0">
                        <p
                          class="text-sm font-bold truncate leading-tight"
                          :class="{
                            'text-primary': active,
                            'text-slate-900': !active,
                          }"
                        >
                          {{ product.title }}
                        </p>
                        <p
                          class="text-[11px] font-bold text-slate-400 mt-0.5 tracking-tight uppercase"
                        >
                          SKU: {{ product.product_id || product.sku || "N/A" }}
                        </p>
                      </div>

                      <div class="text-right">
                        <p
                          class="text-sm font-black"
                          :class="active ? 'text-primary' : 'text-slate-900'"
                        >
                          ₹{{ parseFloat(product.price).toFixed(2) }}
                        </p>
                      </div>
                    </div>

                    <span
                      v-if="selected"
                      class="absolute inset-y-0 right-3 flex items-center text-primary"
                    >
                      <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    </span>
                  </li>
                </ComboboxOption>
              </ComboboxOptions>
            </transition>
          </div>
        </Combobox>
      </div>
    </div>

    <!-- Totals & Summary -->
    <div class="mt-8 border-t border-slate-100 pt-8">
      <div class="flex flex-col md:flex-row gap-8 justify-end">
        <!-- Discount & Extra Charges Section -->
        <div class="w-full md:w-72 space-y-4">
          <div>
            <label
              class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 block"
            >
              Discount & Adjustment
            </label>
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
              >
                <span class="text-slate-400 font-bold">₹</span>
              </div>
              <input
                type="number"
                :value="order.discount || 0"
                @input="handleDiscountChange($event.target.value)"
                class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all"
                placeholder="0.00"
                min="0"
                step="0.01"
              />
            </div>
          </div>

          <div>
            <label
              class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 block"
            >
              Extra Charges
            </label>
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
              >
                <span class="text-slate-400 font-bold">₹</span>
              </div>
              <input
                type="number"
                :value="order.extra_charges || 0"
                @input="handleExtraChargesChange($event.target.value)"
                class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-bold focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all"
                placeholder="0.00"
                min="0"
                step="0.01"
              />
            </div>
          </div>


        </div>

        <!-- Totals Section -->
        <div
          class="w-full md:w-80 bg-slate-50 rounded-2xl p-6 border border-slate-100"
        >
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Subtotal</span>
              <span class="text-sm font-bold text-slate-900"
                >₹{{ subtotal.toFixed(2) }}</span
              >
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Discount</span>
              <span class="text-sm font-bold text-emerald-500"
                >-₹{{ Number(order.discount || 0).toFixed(2) }}</span
              >
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Extra Charges</span>
              <span class="text-sm font-bold text-slate-900"
                >+₹{{ Number(order.extra_charges || 0).toFixed(2) }}</span
              >
            </div>

            <div v-if="dispatchExpense > 0" class="flex justify-between items-center">
              <span class="text-sm font-bold text-slate-500">Courier Charge</span>
              <span class="text-sm font-bold text-slate-900"
                >+₹{{ dispatchExpense.toFixed(2) }}</span
              >
            </div>

            <div class="h-px bg-slate-200 my-4"></div>

            <div class="flex justify-between items-center">
              <span
                class="text-base font-black text-slate-900 uppercase tracking-wide"
                >Total Due</span
              >
              <span class="text-2xl font-black text-primary"
                >₹{{ total.toFixed(2) }}</span
              >
            </div>


          </div>
        </div>
      </div>
    </div>

    <!-- Footer Actions -->
    <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
      <button
        @click="$emit('cancel')"
        class="px-8 py-3.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all active:scale-95 text-sm"
      >
        Cancel Changes
      </button>
      <button
        @click="handleSaveSection"
        :disabled="isSaving"
        class="px-8 py-3.5 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95 text-sm disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <Loader2Icon v-if="isSaving" class="h-4 w-4 animate-spin" />
        {{ isSaving ? "Saving..." : "Save Order Items" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
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
} from "@headlessui/vue";
import {
  Plus as PlusIcon,
  Minus as MinusIcon,
  Trash2 as Trash2Icon,
  Search as SearchIcon,
  Package as PackageIcon,
  Tag as TagIcon,
  Check as CheckIcon,
  ChevronDown as ChevronDownIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";

import axios from "axios";
import { useToast } from "../../../composables/useToast";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order", "save", "cancel"]);
const toast = useToast();

const query = ref("");
const selectedProduct = ref(null);
const availableProducts = ref([]);

const fetchProducts = async () => {
  try {
    const response = await axios.get("/api/v1/products/list");
    if (response.data.success && Array.isArray(response.data.data)) {
      availableProducts.value = response.data.data;
    } else {
      availableProducts.value = [];
    }
  } catch (error) {
    console.error("Failed to fetch products", error);
    availableProducts.value = [];
  }
};

onMounted(() => {
  fetchProducts();
});

const dispatchExpense = computed(() => {
  return parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;
});

const subtotal = computed(() => {
  return (props.order.items || []).reduce(
    (acc, item) => acc + (parseFloat(item.unit_price) || 0) * item.quantity,
    0,
  );
});

const total = computed(() => {
  const discount = parseFloat(props.order.discount) || 0;
  const extraCharges = parseFloat(props.order.extra_charges) || 0;
  return Math.max(0, subtotal.value - discount + extraCharges + dispatchExpense.value);
});

const filteredProducts = computed(() => {
  if (!Array.isArray(availableProducts.value)) return [];
  const search = query.value.toLowerCase();
  if (!search) return availableProducts.value;
  return availableProducts.value.filter((p) =>
    (p.title || "").toLowerCase().includes(search),
  );
});

const updateQuantity = (itemId, delta) => {
  const item = props.order.items.find((i) => i.id === itemId);
  if (!item) return;
  const newQuantity = Math.max(1, item.quantity + delta);
  updateQuantityValue(itemId, String(newQuantity));
};

const updateQuantityValue = (itemId, value) => {
  // Remove any non-numeric characters
  const cleanedValue = value.replace(/\D/g, "");

  // If empty, return to prevent state update until valid number
  if (cleanedValue === "") return;

  const newQuantity = parseInt(cleanedValue);
  
  if (isNaN(newQuantity) || newQuantity < 1) return;

  // We need to emit both the item update AND recalculate totals
  const updatedItems = props.order.items.map((item) => {
    if (item.id === itemId) {
      return { 
        ...item, 
        quantity: newQuantity,
        total_price: (parseFloat(item.unit_price) || 0) * newQuantity
      };
    }
    return item;
  });

  // Calculate new subtotal
  const newSubtotal = updatedItems.reduce(
    (acc, item) => acc + (parseFloat(item.total_price) || 0),
    0
  );
  
  const discount = props.order.discount || 0;
  // Use current dispatch expense
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;

  // Emit updated order
  emit("update:order", {
    ...props.order,
    items: updatedItems,
    total_amount: Math.max(0, newSubtotal - discount + expense)
  });
};

const updateUnitPriceValue = (itemId, value) => {
  const newPrice = parseFloat(value) || 0;
  
  const updatedItems = props.order.items.map((item) => {
    if (item.id === itemId) {
      return { 
        ...item, 
        unit_price: newPrice,
        total_price: newPrice * item.quantity
      };
    }
    return item;
  });

  const newSubtotal = updatedItems.reduce(
    (acc, item) => acc + (parseFloat(item.total_price) || 0),
    0
  );
  
  const discount = props.order.discount || 0;
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;

  emit("update:order", {
    ...props.order,
    items: updatedItems,
    total_amount: Math.max(0, newSubtotal - discount + expense)
  });
};

const removeItem = (itemId) => {
  const updatedItems = props.order.items.filter((item) => item.id !== itemId);
  
  // Recalculate totals after removal
  const newSubtotal = updatedItems.reduce(
    (acc, item) => acc + (parseFloat(item.unit_price) || 0) * item.quantity,
    0
  );
  const discount = props.order.discount || 0;
  const extraCharges = props.order.extra_charges || 0;
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;

  emit("update:order", {
     ...props.order,
     items: updatedItems,
     total_amount: Math.max(0, newSubtotal + extraCharges - discount + expense)
  });
};

const handleAddProduct = (product) => {
  if (!product) return;
  addItem(product);
  // Reset selection
  selectedProduct.value = null;
  query.value = "";
};

const addItem = (product) => {
  const existingItem = props.order.items.find(
    (item) => item.product_id === product.id,
  );
  let updatedItems;

  if (existingItem) {
    updatedItems = props.order.items.map((item) =>
      item.id === existingItem.id
        ? { ...item, quantity: item.quantity + 1 }
        : item,
    );
  } else {
    const newItem = {
      id: `new-${Date.now()}`,
      product_id: product.id,
      product_name: product.title,
      unit_price: parseFloat(product.price),
      quantity: 1,
      image: product.image,
    };
    updatedItems = [...props.order.items, newItem];
  }

  updateOrderItems(updatedItems);
};

const handleDiscountChange = (value) => {
  const discount = parseFloat(value) || 0;
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;
  const extraCharges = parseFloat(props.order.extra_charges) || 0;
  emit("update:order", {
    ...props.order,
    discount: discount,
    total_amount: Math.max(0, subtotal.value + extraCharges - discount + expense),
  });
};

const handleExtraChargesChange = (value) => {
  const extraCharges = parseFloat(value) || 0;
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;
  const discount = parseFloat(props.order.discount) || 0;
  emit("update:order", {
    ...props.order,
    extra_charges: extraCharges,
    total_amount: Math.max(0, subtotal.value + extraCharges - discount + expense),
  });
};



const updateOrderItems = (items) => {
  const newSubtotal = items.reduce(
    (acc, item) => acc + (parseFloat(item.unit_price) || 0) * item.quantity,
    0,
  );
  const discount = props.order.discount || 0;
  const extraCharges = props.order.extra_charges || 0;
  const expense = parseFloat(props.order.dispatch_delivery?.dispatch_mode?.expense) || 0;

  emit("update:order", {
    ...props.order,
    items: items.map((item) => ({
      ...item,
      total_price: (parseFloat(item.unit_price) || 0) * item.quantity,
    })),
    // Update top level total amount
    total_amount: Math.max(0, newSubtotal + extraCharges - discount + expense),
  });
};

const handleSaveSection = () => {
  emit("save");
};

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
const isNumber = (evt) => {
  const keys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab'];
  if (keys.includes(evt.key)) return true;
  
  // Create a regex that only allows digits 0-9
  const regex = /^[0-9]$/;
  if (!regex.test(evt.key)) {
    evt.preventDefault();
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
</style>
