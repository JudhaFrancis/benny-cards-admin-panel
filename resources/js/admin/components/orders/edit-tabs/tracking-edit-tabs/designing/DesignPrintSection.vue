<template>
  <div class="space-y-8">
    <p class="text-xs text-slate-400 italic">
      Please ✔ whichever given to print
    </p>

    <div class="space-y-4">
      <label class="text-xs font-medium text-slate-700"
        >Design Outputs <span class="text-red-500">*</span></label
      >
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <label
          v-for="output in designOutputs"
          :key="output"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            :value="output"
            v-model="designOutputsList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ output }}</span>
        </label>
      </div>
    </div>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label class="text-xs font-medium text-slate-700"
          >Print & Add-ons</label
        >
        <div class="flex items-center gap-2">
          <input
            v-model="newAddon"
            @keyup.enter="addAddon"
            type="text"
            placeholder="Add new addon..."
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 w-40 transition-all font-medium"
          />
          <button
            @click="addAddon"
            type="button"
            class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-all active:scale-95 shadow-sm shadow-primary/20"
          >
            Add
          </button>
        </div>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <label
          v-for="addon in allAddons"
          :key="addon"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            :value="addon"
            v-model="printAddonsList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ addon }}</span>
        </label>
      </div>
    </div>

    <!-- Sticker Design Image -->
    <div class="space-y-4 pt-4 border-t border-slate-100">
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
    <div
      v-if="designPrint._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(designPrint._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ designPrint._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { 
  Clock as ClockIcon, 
  PlusCircle as PlusCircleIcon,
  Image as ImageIcon,
  Upload as UploadIcon,
  Trash2 as Trash2Icon,
  X as XIcon
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const imagePreview = ref(null);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    imagePreview.value = URL.createObjectURL(file);
    // Attach the file to the designing stage object so the dialog can find it
    if (!props.order.designing) props.order.designing = {};
    props.order.designing.design_print_file = file;
  }
};

const removeImage = () => {
  imagePreview.value = null;
  if (props.order.designing) {
    props.order.designing.design_print_file = null;
    props.order.designing.sticker_image = null;
  }
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("blob:") || path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};
 
const designPrint = computed(() => {
  if (!props.order.designing) {
    props.order.designing = {};
  }
  if (!props.order.designing.design_print || Array.isArray(props.order.designing.design_print)) {
    props.order.designing.design_print = {};
  }
  return props.order.designing.design_print;
});

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

const designOutputsList = computed({
  get: () => {
    const val = designPrint.value?.design_outputs;
    return val ? val.split(",") : [];
  },
  set: (val) => {
    designPrint.value.design_outputs = val.join(",");
  },
});

const printAddonsList = computed({
  get: () => {
    const val = designPrint.value?.print_addons;
    return val ? val.split(",") : [];
  },
  set: (val) => {
    designPrint.value.print_addons = val.join(",");
  },
});

const designOutputs = [
  "Invitation in Draft",
  "Buttersheet / Master",
  "Gift Frame",
  "PDF",
];

const defaultAddons = [
  "Sticker",
  "Band",
  "Satin Ribbon",
  "Rope",
  "Corner Cutting",
  "Envelope",
  "Insert Leaf",
  "Buttersheet",
  "Tag",
  "Org. Ribbon",
  "Foiling",
  "Screen Printing",
  "UV",
  "Old Die",
  "New Die",
  "Dry Flower",
  "Ready Seal",
  "Special Paper",
  "Custom Seal",
];

const newAddon = ref("");
const customAddons = ref([]);

const allAddons = computed(() => {
  const selected = printAddonsList.value;
  // Get any selected options that are NOT in defaults and NOT in our tracked customAddons
  const legacyExtras = selected.filter(
    (s) => !defaultAddons.includes(s) && !customAddons.value.includes(s)
  );
  
  // Return unique set of everything
  return [...new Set([...defaultAddons, ...customAddons.value, ...legacyExtras])];
});

const addAddon = () => {
  const val = newAddon.value.trim();
  if (!val) return;

  // Prevent duplicates in the LIST of options
  if (
    allAddons.value.some((opt) => opt.toLowerCase() === val.toLowerCase()) 
  ) {
    if (!printAddonsList.value.includes(val)) {
       printAddonsList.value = [...printAddonsList.value, val];
    }
    newAddon.value = "";
    return;
  }

  // Add to custom options list so it persists
  customAddons.value.push(val);

  // Add to selected list
  printAddonsList.value = [...printAddonsList.value, val];
  newAddon.value = "";
};
</script>

