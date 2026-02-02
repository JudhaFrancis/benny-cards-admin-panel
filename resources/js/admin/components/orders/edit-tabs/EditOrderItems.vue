<template>
  <div class="space-y-6">
    <!-- Current Items -->
    <div>
      <label
        class="text-sm font-bold text-slate-700 mb-3 block uppercase tracking-wider"
        >Current Items</label
      >
      <div class="space-y-3">
        <div
          v-for="item in order.items"
          :key="item.id"
          class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100 group transition-all duration-200 hover:border-primary/20"
        >
          <div
            class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden shrink-0"
          >
            <img
              v-if="item.image"
              :src="item.image"
              :alt="item.name"
              class="w-full h-full object-cover"
            />
            <PackageIcon v-else class="h-6 w-6 text-slate-300 m-4" />
          </div>
          <div class="flex-1">
            <p class="font-bold text-slate-900 leading-tight">
              {{ item.name }}
            </p>
            <p class="text-xs text-slate-500 font-medium">
              ${{ item.price.toFixed(2) }} each
            </p>
          </div>
          <div
            class="flex items-center gap-2 bg-white px-2 py-1 rounded-xl border border-slate-200"
          >
            <button
              @click="updateQuantity(item.id, -1)"
              class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-600 transition-colors"
            >
              <MinusIcon class="h-3 w-3" />
            </button>
            <span class="w-6 text-center text-sm font-bold text-slate-900">
              {{ item.quantity }}
            </span>
            <button
              @click="updateQuantity(item.id, 1)"
              class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-600 transition-colors"
            >
              <PlusIcon class="h-3 w-3" />
            </button>
          </div>
          <p class="font-bold text-slate-900 w-20 text-right">
            ${{ (item.price * item.quantity).toFixed(2) }}
          </p>
          <button
            @click="removeItem(item.id)"
            class="h-8 w-8 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-50 transition-colors"
          >
            <Trash2Icon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Add New Items -->
    <div>
      <label
        class="text-sm font-bold text-slate-700 mb-3 block uppercase tracking-wider"
        >Add New Item</label
      >
      <div class="relative group">
        <SearchIcon
          class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
        />
        <input
          v-model="searchTerm"
          placeholder="Search products..."
          class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
        />
      </div>

      <div
        v-if="searchTerm"
        class="mt-2 border border-slate-100 rounded-2xl bg-white shadow-xl max-h-56 overflow-y-auto z-20 custom-scrollbar"
      >
        <button
          v-for="product in filteredProducts"
          :key="product.id"
          class="w-full flex items-center gap-3 p-3 hover:bg-slate-50 transition-colors text-left group"
          @click="addItem(product)"
        >
          <div
            class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0"
          >
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <PackageIcon v-else class="h-4 w-4 text-slate-300 m-3" />
          </div>
          <div class="flex-1">
            <p class="text-sm font-bold text-slate-900">{{ product.name }}</p>
            <p class="text-xs text-slate-500 font-medium">
              ${{ product.price.toFixed(2) }}
            </p>
          </div>
          <PlusIcon
            class="h-4 w-4 text-primary opacity-0 group-hover:opacity-100 transition-opacity"
          />
        </button>
        <div v-if="filteredProducts.length === 0" class="p-8 text-center">
          <p class="text-sm text-slate-400 italic">No products found</p>
        </div>
      </div>
    </div>

    <!-- Totals -->
    <div
      class="space-y-3 p-6 rounded-[2rem] bg-slate-50 border border-slate-100"
    >
      <div class="flex justify-between items-center px-2">
        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest"
          >Subtotal</span
        >
        <span class="font-bold text-slate-900">${{ subtotal.toFixed(2) }}</span>
      </div>
      <div class="flex items-center justify-between gap-4 px-2">
        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest"
          >Discount</span
        >
        <div class="relative w-32">
          <span
            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold"
            >$</span
          >
          <input
            type="number"
            :value="order.discount || 0"
            @input="handleDiscountChange($event.target.value)"
            class="w-full pl-7 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-right text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            min="0"
          />
        </div>
      </div>
      <div
        class="pt-4 border-t border-slate-200 flex justify-between items-center px-2"
      >
        <span
          class="text-sm font-black text-slate-900 uppercase tracking-widest"
          >Total</span
        >
        <span class="text-2xl font-black text-primary"
          >${{ total.toFixed(2) }}</span
        >
      </div>
    </div>

    <button
      @click="$emit('save')"
      class="w-full bg-primary text-white hover:opacity-90 py-4 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all active:scale-[0.98]"
    >
      Save Changes
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import {
  Plus as PlusIcon,
  Minus as MinusIcon,
  Trash2 as Trash2Icon,
  Search as SearchIcon,
  Package as PackageIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order", "save"]);

const searchTerm = ref("");

const availableProducts = [
  {
    id: "prod-1",
    name: "Wireless Bluetooth Headphones",
    price: 89.99,
    image: null,
  },
  { id: "prod-2", name: "USB-C Cable (2 Pack)", price: 12.99, image: null },
  { id: "prod-3", name: "Smart Watch Pro", price: 199.99, image: null },
  {
    id: "prod-4",
    name: "Portable Charger 10000mAh",
    price: 49.99,
    image: null,
  },
  { id: "prod-5", name: "Wireless Mouse", price: 29.99, image: null },
  { id: "prod-6", name: "Mechanical Keyboard", price: 89.99, image: null },
];

const subtotal = computed(() => {
  return (props.order.items || []).reduce(
    (acc, item) => acc + item.price * item.quantity,
    0,
  );
});

const total = computed(() => {
  return Math.max(0, subtotal.value - (props.order.discount || 0));
});

const filteredProducts = computed(() => {
  if (!searchTerm.value) return [];
  return availableProducts.filter((p) =>
    p.name.toLowerCase().includes(searchTerm.value.toLowerCase()),
  );
});

const updateQuantity = (itemId, delta) => {
  const updatedItems = props.order.items.map((item) =>
    item.id === itemId
      ? { ...item, quantity: Math.max(1, item.quantity + delta) }
      : item,
  );

  updateOrderItems(updatedItems);
};

const removeItem = (itemId) => {
  const updatedItems = props.order.items.filter((item) => item.id !== itemId);
  updateOrderItems(updatedItems);
};

const addItem = (product) => {
  const existingItem = props.order.items.find(
    (item) => item.name === product.name,
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
      id: `item-${Date.now()}`,
      name: product.name,
      price: product.price,
      quantity: 1,
      image: product.image,
    };
    updatedItems = [...props.order.items, newItem];
  }

  updateOrderItems(updatedItems);
  searchTerm.value = "";
};

const handleDiscountChange = (value) => {
  const discount = parseFloat(value) || 0;
  emit("update:order", {
    ...props.order,
    discount: discount,
    amount: Math.max(0, subtotal.value - discount),
  });
};

const updateOrderItems = (items) => {
  const newSubtotal = items.reduce(
    (acc, item) => acc + item.price * item.quantity,
    0,
  );
  const discount = props.order.discount || 0;

  emit("update:order", {
    ...props.order,
    items: items,
    itemsCount: items.reduce((acc, item) => acc + item.quantity, 0),
    amount: Math.max(0, newSubtotal - discount),
  });
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
