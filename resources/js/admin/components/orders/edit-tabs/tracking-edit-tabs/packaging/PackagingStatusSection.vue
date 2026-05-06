<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Packed By -->
    <div v-if="showPackedBy" class="space-y-2">
      <label class="text-xs font-medium text-slate-700">Packed By <span class="text-red-500">*</span></label>
      <SearchableDropdown :model-value="packagingStatus.packed_by"
        @update:model-value="(val) => updateSection('packed_by', val)" :options="staffOptions"
        placeholder="Select staff" :icon="UserIcon" />
    </div>

    <!-- Gift Option Section (Moved here from Logistics) -->
    <div v-if="showGiftOption" class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 mt-6 relative z-[15]">
      <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
        <GiftIcon class="h-4 w-4" /> Gift Option
      </h3>
      <div class="flex flex-wrap gap-4">
        <!-- With Gift Option -->
        <div @click="updateSection('gift_type', packagingStatus.gift_type === 'with_gift' ? null : 'with_gift')"
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="packagingStatus.gift_type === 'with_gift' ? 'border-primary bg-primary/5 ring-4 ring-primary/5' : 'border-slate-200 hover:border-slate-300'">
          <div class="p-2 rounded-lg transition-colors"
            :class="packagingStatus.gift_type === 'with_gift' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <GiftIcon class="h-4 w-4" />
          </div>
          <div class="flex-1 pointer-events-none">
            <p class="text-xs font-bold text-slate-900 leading-none">With Gift</p>
            <input type="radio" value="with_gift" :checked="packagingStatus.gift_type === 'with_gift'" class="sr-only" readonly />
          </div>
          <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all pointer-events-none"
            :class="packagingStatus.gift_type === 'with_gift' ? 'border-primary bg-primary scale-110' : 'border-slate-200'">
            <CheckIcon v-show="packagingStatus.gift_type === 'with_gift'" class="h-3 w-3 text-white" />
          </div>
        </div>

        <!-- Without Gift Option -->
        <div @click="updateSection('gift_type', packagingStatus.gift_type === 'without_gift' ? null : 'without_gift')"
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="packagingStatus.gift_type === 'without_gift' ? 'border-primary bg-primary/5 ring-4 ring-primary/5' : 'border-slate-200 hover:border-slate-300'">
          <div class="p-2 rounded-lg transition-colors"
            :class="packagingStatus.gift_type === 'without_gift' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <PackageIcon class="h-4 w-4" />
          </div>
          <div class="flex-1 pointer-events-none">
            <p class="text-xs font-bold text-slate-900 leading-none">Without Gift</p>
            <input type="radio" value="without_gift" :checked="packagingStatus.gift_type === 'without_gift'" class="sr-only" readonly />
          </div>
          <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all pointer-events-none"
            :class="packagingStatus.gift_type === 'without_gift' ? 'border-primary bg-primary scale-110' : 'border-slate-200'">
            <CheckIcon v-show="packagingStatus.gift_type === 'without_gift'" class="h-3 w-3 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Sticker Design Image -->
    <div v-if="showSticker" class="space-y-4 pt-6 border-t border-slate-100 mt-6">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
        <ImageIcon class="h-3 w-3" /> Sticker Design Image (Optional)
      </label>
      
      <div class="flex items-start gap-6">
        <div v-if="imagePreview || props.order.designing?.sticker_image" class="relative group w-32 h-32 rounded-2xl border border-slate-200 overflow-hidden bg-slate-50 shadow-sm">
          <img :src="getImageSource(imagePreview || props.order.designing?.sticker_image)" class="w-full h-full object-cover" />
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <button @click="removeImage" class="p-2 bg-rose-500 text-white rounded-xl hover:bg-rose-600 transition-all active:scale-90" title="Remove image">
              <Trash2Icon class="h-4 w-4" />
            </button>
            <label class="p-2 bg-white text-slate-900 rounded-xl hover:bg-slate-50 transition-all active:scale-90 cursor-pointer" title="Change image">
              <UploadIcon class="h-4 w-4" />
              <input type="file" class="hidden" accept="image/*" @change="handleFileChange" />
            </label>
          </div>
        </div>
        
        <label v-else class="w-32 h-32 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-3 cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-all text-slate-400 hover:text-primary group bg-slate-50/50">
          <div class="p-3 rounded-full bg-white shadow-sm group-hover:scale-110 transition-transform">
            <UploadIcon class="h-6 w-6" />
          </div>
          <div class="text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider block">Upload Sticker</span>
            <span class="text-[9px] font-medium opacity-60">JPG, PNG or WEBP</span>
          </div>
          <input type="file" class="hidden" accept="image/*" @change="handleFileChange" />
        </label>

        <div class="flex-1 space-y-2">
          <p class="text-xs text-slate-500 leading-relaxed pt-2">
            Upload any sticker design or reference image for this order. This image will be accessible in the design and printing stages.
          </p>
          <ul class="text-[10px] text-slate-400 space-y-1">
            <li class="flex items-center gap-1.5"><div class="w-1 h-1 rounded-full bg-slate-300"></div> Max size: 2MB</li>
            <li class="flex items-center gap-1.5"><div class="w-1 h-1 rounded-full bg-slate-300"></div> Required for custom sticker jobs</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Audit Footer -->
    <div v-if="packagingStatus._audit && !hideAudit && showSticker"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400">
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600">{{
          formatAuditDate(packagingStatus._audit.updated_at) }}</span>
        by
        <span class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2">{{
          packagingStatus._audit.updated_by }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { 
  User as UserIcon, 
  Clock as ClockIcon,
  Image as ImageIcon,
  Upload as UploadIcon,
  Trash2 as Trash2Icon,
  Gift as GiftIcon,
  Package as PackageIcon,
  Check as CheckIcon
} from "lucide-vue-next";
import SearchableDropdown from "../../../../ui/dropdowns/SearchableDropdown.vue";


const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
  showPackedBy: {
    type: Boolean,
    default: true,
  },
  showGiftOption: {
    type: Boolean,
    default: true,
  },
  showSticker: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["update:order"]);

const packagingStatus = computed(() => {
  if (!props.order.dispatch_delivery) {
    props.order.dispatch_delivery = { dispatch_mode: {} };
  }
  if (!props.order.dispatch_delivery.dispatch_mode || Array.isArray(props.order.dispatch_delivery.dispatch_mode)) {
    props.order.dispatch_delivery.dispatch_mode = {};
  }
  return props.order.dispatch_delivery.dispatch_mode;
});

const imagePreview = ref(null);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    imagePreview.value = URL.createObjectURL(file);
    const newOrder = {
      ...props.order,
      packaging: {
        ...(props.order.packaging || {}),
        design_print_file: file
      }
    };
    emit("update:order", newOrder);
  }
};

const removeImage = () => {
  imagePreview.value = null;
  const newOrder = {
    ...props.order,
    packaging: {
      ...(props.order.packaging || {}),
      design_print_file: null
    },
    designing: {
      ...(props.order.designing || {}),
      sticker_image: null
    }
  };
  emit("update:order", newOrder);
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("blob:") || path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};


const updateSection = (key, value) => {
  const newOrder = { 
    ...props.order,
    dispatch_delivery: {
      ...(props.order.dispatch_delivery || {}),
      dispatch_mode: {
        ...(props.order.dispatch_delivery?.dispatch_mode || {})
      }
    }
  };

  newOrder.dispatch_delivery.dispatch_mode[key] = value;
  emit("update:order", newOrder);
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const d = date.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};
</script>
