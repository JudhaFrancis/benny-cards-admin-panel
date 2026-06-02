<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
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
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
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
              class="w-full max-w-4xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Sticky Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10"
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
                      <BoxIcon v-if="!editProduct" class="h-7 w-7 text-white" />
                      <Edit3Icon v-else class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{ editProduct ? "Edit Product" : "Add New Product" }}
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        {{
                          editProduct
                            ? "Update product inventory and descriptions."
                            : "Create a new entry in your digital catalog."
                        }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click="handleClose"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Scrollable Form Content -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-10">
                  <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                    <!-- Left Column: Visuals & Core Info -->
                    <div class="md:col-span-4 space-y-8">
                      <!-- Main Image Upload -->
                      <div>
                        <label
                          class="block text-xs font-bold text-gray-700 mb-3 ml-1"
                          >Main Photo
                          <span class="text-rose-500">*</span></label
                        >
                        <div
                          class="aspect-square rounded-[2rem] border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center transition-all cursor-pointer group relative overflow-hidden active:scale-95 shadow-sm"
                          @click="$refs.fileInput.click()"
                        >
                          <input
                            type="file"
                            ref="fileInput"
                            class="hidden"
                            accept="image/*"
                            @change="handleFileUpload"
                          />

                          <div
                            v-if="photoPreview || editProduct?.photo"
                            class="w-full h-full relative z-10"
                          >
                            <img
                              :src="
                                photoPreview ||
                                getImageSource(editProduct.photo)
                              "
                              class="w-full h-full object-cover"
                              @error="handleImageError"
                            />
                            <div
                              class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                            >
                              <span
                                class="text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2"
                              >
                                <UploadIcon class="h-4 w-4" />
                                Change Photo
                              </span>
                            </div>
                          </div>
                          <div
                            v-else
                            class="flex flex-col items-center gap-2 text-gray-400 group-hover:text-primary transition-colors px-6 text-center"
                          >
                            <ImageIcon class="h-10 w-10 mb-1" />
                            <span
                              class="text-xs font-bold uppercase tracking-widest leading-tight"
                              >Upload Main</span
                            >
                            <span class="text-[10px] opacity-60"
                              >JGP, PNG, WebP up to 1MB</span
                            >
                          </div>
                        </div>
                      </div>

                      <!-- Basic Meta -->
                      <div class="space-y-6">
                        <div>
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5 ml-1"
                            >Visibility</label
                          >
                          <ContextDropdown
                            v-model="form.status"
                            :options="statusOptions"
                            :icon="ActivityIcon"
                          />
                        </div>
                        <div>
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5 ml-1"
                            >Type</label
                          >
                          <ContextDropdown
                            v-model="form.type"
                            :options="typeOptions"
                            :icon="LayersIcon"
                          />
                        </div>
                        <div>
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5 ml-1"
                            >Condition</label
                          >
                          <ContextDropdown
                            v-model="form.condition"
                            :options="conditionOptions"
                            :icon="StarIcon"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Right Column: Details & Gallery -->
                    <div class="md:col-span-8 space-y-10">
                      <!-- Title & Slug -->
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                          <label
                            class="block text-xs font-bold text-gray-700 mb-2.5 ml-1"
                            >Product Title
                            <span class="text-rose-500">*</span></label
                          >
                          <input
                            v-model="form.title"
                            type="text"
                            placeholder="e.g. Wedding Invitation v1.0"
                            class="w-full px-5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-semibold"
                            required
                            @input="generateSlug"
                          />
                        </div>
                        <div class="md:col-span-2">
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5 ml-1"
                            >URL Slug</label
                          >
                          <input
                            v-model="form.slug"
                            type="text"
                            placeholder="auto-generated-slug"
                            class="w-full px-5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-400 text-xs font-mono focus:outline-none focus:border-primary transition-all"
                          />
                        </div>
                      </div>

                      <!-- Gallery Management -->
                      <div>
                        <div
                          class="flex items-center justify-between mb-4 px-1"
                        >
                          <label
                            class="text-xs font-bold text-gray-700 flex items-center gap-2"
                          >
                            <ImagesIcon class="h-4 w-4 text-primary" />
                            Product Gallery
                          </label>
                          <span
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                            >Max 1MB per image</span
                          >
                        </div>

                        <div class="grid grid-cols-4 gap-4">
                          <!-- Existing & Preview Images -->
                          <template
                            v-for="(img, index) in combinedGallery"
                            :key="index"
                          >
                            <div
                              v-if="!isDeleted(img)"
                              class="relative aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm group"
                            >
                              <img
                                :src="img.preview"
                                class="w-full h-full object-cover"
                                @error="handleImageError"
                              />
                              <button
                                type="button"
                                @click="removeGalleryItem(img)"
                                class="absolute top-1.5 right-1.5 h-6 w-6 bg-rose-500 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:scale-110 shadow-lg active:scale-95"
                              >
                                <XIcon class="h-3 w-3" />
                              </button>
                              <div
                                v-if="img.isNew"
                                class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 bg-primary/90 text-white text-[8px] font-bold uppercase rounded shadow-sm"
                              >
                                New
                              </div>
                            </div>
                          </template>

                          <!-- Upload Trigger -->
                          <button
                            type="button"
                            @click="$refs.galleryInput.click()"
                            class="aspect-square rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 flex flex-col items-center justify-center gap-1.5 text-gray-400 hover:text-primary hover:border-primary hover:bg-primary/5 transition-all text-center active:scale-95"
                          >
                            <PlusIcon class="h-5 w-5" />
                            <span
                              class="text-[9px] font-bold uppercase tracking-widest"
                              >Add More</span
                            >
                            <input
                              type="file"
                              ref="galleryInput"
                              class="hidden"
                              accept="image/*"
                              multiple
                              @change="handleGalleryUpload"
                            />
                          </button>
                        </div>
                      </div>

                      <!-- Relations: Category & Brand -->
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                          <label
                            class="block text-xs font-bold text-gray-700 mb-2.5 ml-1"
                            >Category
                            <span class="text-rose-500">*</span></label
                          >
                          <ContextDropdown
                            v-model="form.cat_id"
                            :options="categoryOptions"
                            :icon="FolderIcon"
                            placeholder="Select Category"
                            @update:modelValue="handleCategoryChange"
                          />
                        </div>
                        <div>
                          <label
                            class="block text-xs font-bold text-gray-700 mb-2.5 ml-1 text-gray-400"
                            >Sub-Category</label
                          >
                          <ContextDropdown
                            v-model="form.child_cat_id"
                            :options="subCategoryOptions"
                            :icon="FolderIcon"
                            placeholder="No sub-category"
                            :class="{ 'opacity-50 pointer-events-none': !form.cat_id || !filteredSubCategories.length }"
                          />
                        </div>
                        <div class="md:col-span-2">
                          <label
                            class="block text-xs font-bold text-gray-700 mb-2.5 ml-1"
                            >Brand</label
                          >
                          <ContextDropdown
                            v-model="form.brand_id"
                            :options="brandOptions"
                            :icon="TagIcon"
                            placeholder="Select Brand (Optional)"
                          />
                        </div>
                      </div>

                      <!-- Pricing -->
                      <div
                        class="p-8 rounded-[2.5rem] bg-gray-50/50 border border-gray-100 flex flex-col md:flex-row gap-8 shadow-inner"
                      >
                        <div class="flex-1">
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5"
                            >Price (₹)
                            <span class="text-rose-500">*</span></label
                          >
                          <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            class="w-full bg-transparent border-b-2 border-gray-200 focus:border-primary focus:outline-none py-2 text-2xl font-black text-gray-700 transition-all"
                            placeholder="0.00"
                            required
                          />
                        </div>
                        <div class="flex-1">
                          <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2.5"
                            >Discount (%)</label
                          >
                          <input
                            v-model="form.discount"
                            type="number"
                            step="0.1"
                            class="w-full bg-transparent border-b-2 border-gray-200 focus:border-primary focus:outline-none py-2 text-2xl font-black text-emerald-500 transition-all"
                            placeholder="0"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Full Width: Rich Text Editors -->
                    <div class="md:col-span-12 space-y-10 pt-4">
                      <div class="space-y-4">
                        <label
                          class="block text-xs font-bold text-gray-700 flex items-center gap-2 ml-1"
                        >
                          <FileIcon class="h-4 w-4 text-gray-400" />
                          Short Summary / Intro
                          <span class="text-rose-500">*</span>
                        </label>
                        <div
                          class="quill-wrapper rounded-3xl border border-gray-200 overflow-hidden shadow-inner bg-white"
                        >
                          <QuillEditor
                            theme="snow"
                            v-model:content="form.summary"
                            contentType="html"
                            placeholder="Briefly describe the product..."
                          />
                        </div>
                      </div>

                      <div class="space-y-4">
                        <label
                          class="block text-xs font-bold text-gray-700 flex items-center gap-2 ml-1"
                        >
                          <AlignLeftIcon class="h-4 w-4 text-gray-400" />
                          Detailed Description
                        </label>
                        <div
                          class="quill-wrapper rounded-3xl border border-gray-200 overflow-hidden shadow-inner bg-white"
                        >
                          <QuillEditor
                            theme="snow"
                            v-model:content="form.description"
                            contentType="html"
                            placeholder="Enter full technical details, features, and specs..."
                          />
                        </div>
                      </div>

                      <div
                        class="flex items-center gap-4 p-6 bg-primary/5 rounded-3xl border border-primary/10"
                      >
                        <div class="flex-1">
                          <h5 class="text-xs font-bold text-primary">
                            Featured Product
                          </h5>
                          <p class="text-[11px] text-gray-500">
                            Highlight this item on the homepage and special
                            promotions.
                          </p>
                        </div>
                        <button
                          type="button"
                          @click="form.is_featured = !form.is_featured"
                          :class="[
                            'w-14 h-8 rounded-full transition-all flex items-center px-1',
                            form.is_featured ? 'bg-primary' : 'bg-gray-200',
                          ]"
                        >
                          <div
                            :class="[
                              'w-6 h-6 bg-white rounded-full shadow-md transition-all',
                              form.is_featured
                                ? 'translate-x-6'
                                : 'translate-x-0',
                            ]"
                          ></div>
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>

              <!-- Sticky Footer -->
              <div
                class="px-8 py-6 border-t border-gray-50 bg-gray-50 flex justify-end gap-3 sticky bottom-0 z-10"
              >
                <button
                  type="button"
                  @click="handleClose"
                  class="px-6 py-3 rounded-2xl text-gray-600 font-semibold hover:bg-gray-100 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="loading"
                  class="px-10 py-3 bg-primary text-white rounded-2xl font-bold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-xl shadow-primary/30 flex items-center gap-2 active:scale-95"
                >
                  <span
                    v-if="loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                  ></span>
                  {{ editProduct ? "Update Product" : "Create Product" }}
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
import { ref, watch, reactive, computed } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Box as BoxIcon,
  Edit3 as Edit3Icon,
  Upload as UploadIcon,
  Image as ImageIcon,
  Images as ImagesIcon,
  Activity as ActivityIcon,
  Layers as LayersIcon,
  Star as StarIcon,
  Plus as PlusIcon,
  FileText as FileIcon,
  AlignLeft as AlignLeftIcon,
  Folder as FolderIcon,
  Tag as TagIcon,
} from "lucide-vue-next";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/dropdowns/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editProduct: Object,
  defaultType: String,
  options: Object,
});

const emit = defineEmits(["close", "refresh"]);
const { success: toastSuccess, error: toastError } = useToast();

const loading = ref(false);
const fileInput = ref(null);
const galleryInput = ref(null);
const photoPreview = ref(null);
const photoFile = ref(null);

const galleryPreviews = ref([]); // { file: File, preview: string, isNew: true }
const deletedImageIds = ref([]); // IDs of existing images to delete

const form = reactive({
  title: "",
  slug: "",
  summary: "",
  description: "",
  condition: "default",
  status: "active",
  price: 0,
  discount: 0,
  is_featured: false,
  type: "card",
  cat_id: "",
  child_cat_id: "",
  brand_id: "",
});

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "Customer can purchase it.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Save as draft.",
    badge: "Draft",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

const typeOptions = [
  {
    label: "Invitation Card",
    value: "card",
    description: "Standard cards.",
    badge: "Card",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Gift Accessory",
    value: "gift",
    description: "Gift items.",
    badge: "Gift",
    badgeClass: "bg-purple-100 text-purple-700",
  },
];

const conditionOptions = [
  { label: "Standard", value: "default", description: "Regular item." },
  {
    label: "New Arrival",
    value: "new",
    description: "Latest collection.",
    badge: "Fresh",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Hot Trending",
    value: "hot",
    description: "Best sellers.",
    badge: "Hot",
    badgeClass: "bg-rose-100 text-rose-700",
  },
];

const filteredSubCategories = computed(() => {
  if (!form.cat_id) return [];
  const parent = props.options.categories.find((c) => c.id === form.cat_id);
  return parent ? parent.children : [];
});

const categoryOptions = computed(() => {
  return props.options?.categories?.map((c) => ({
    label: c.title,
    value: c.id,
  })) || [];
});

const subCategoryOptions = computed(() => {
  return filteredSubCategories.value.map((c) => ({
    label: c.title,
    value: c.id,
  }));
});

const brandOptions = computed(() => {
  return props.options?.brands?.map((b) => ({
    label: b.title,
    value: b.id,
  })) || [];
});

const combinedGallery = computed(() => {
  const existing = (props.editProduct?.images || []).map((img) => ({
    id: img.id,
    preview: getImageSource(img.image_path),
    isNew: false,
  }));
  return [...existing, ...galleryPreviews.value];
});

const isDeleted = (img) => !img.isNew && deletedImageIds.value.includes(img.id);

const generateSlug = () => {
  if (!props.editProduct) {
    form.slug = form.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }
};

const handleCategoryChange = () => {
  form.child_cat_id = "";
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  return `/${path}`;
};

const handleImageError = (e) => {
  const fallback =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='3' rx='2' ry='2'%3E%3C/rect%3E%3Ccircle cx='9' cy='9' r='2'%3E%3C/circle%3E%3Cpath d='m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21'%3E%3C/path%3E%3C/svg%3E";
  if (e.target.src === fallback) return;
  e.target.src = fallback;
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    photoFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleGalleryUpload = (e) => {
  const files = Array.from(e.target.files);
  files.forEach((file) => {
    const reader = new FileReader();
    reader.onload = (re) => {
      galleryPreviews.value.push({
        file: file,
        preview: re.target.result,
        isNew: true,
      });
    };
    reader.readAsDataURL(file);
  });
};

const removeGalleryItem = (img) => {
  if (img.isNew) {
    galleryPreviews.value = galleryPreviews.value.filter(
      (item) => item !== img,
    );
  } else {
    deletedImageIds.value.push(img.id);
  }
};

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      if (props.editProduct) {
        Object.keys(form).forEach((key) => {
          form[key] = props.editProduct[key] ?? "";
        });
        form.is_featured = !!props.editProduct.is_featured;
        photoPreview.value = null;
        photoFile.value = null;
        galleryPreviews.value = [];
        deletedImageIds.value = [];
      } else {
        Object.assign(form, {
          title: "",
          slug: "",
          summary: "",
          description: "",
          condition: "default",
          status: "active",
          price: 0,
          discount: 0,
          is_featured: false,
          type: props.defaultType || "card",
          cat_id: "",
          child_cat_id: "",
          brand_id: "",
        });
        photoPreview.value = null;
        photoFile.value = null;
        galleryPreviews.value = [];
        deletedImageIds.value = [];
      }
    }
  },
);

const handleClose = () => {
  if (!loading.value) {
    emit("close");
  }
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    // Client-side validation: Required Fields
    if (!props.editProduct && !photoFile.value) {
      toastError("Main product photo is required");
      loading.value = false;
      return;
    }

    if (
      !form.summary ||
      form.summary.trim() === "<p></p>" ||
      form.summary.trim() === ""
    ) {
      toastError("Short Summary is required");
      loading.value = false;
      return;
    }

    // Client-side validation: Main Photo Size
    // Client-side validation: Main Photo
    if (photoFile.value && photoFile.value.size > 1024 * 1024) {
      toastError("Main photo must be below 1MB");
      loading.value = false;
      return;
    }

    // Client-side validation: Gallery
    for (const item of galleryPreviews.value) {
      if (item.isNew && item.file.size > 1024 * 1024) {
        toastError(`Gallery image '${item.file.name}' must be below 1MB`);
        loading.value = false;
        return;
      }
    }

    const formData = new FormData();
    Object.keys(form).forEach((key) => {
      if (form[key] !== null) {
        let value = form[key];
        // Cast boolean to 1/0 for FormData
        if (typeof value === "boolean") {
          value = value ? 1 : 0;
        }
        formData.append(key, value);
      }
    });

    if (photoFile.value) formData.append("photo", photoFile.value);

    // Gallery Append
    galleryPreviews.value.forEach((item, index) => {
      formData.append(`images[${index}]`, item.file);
    });

    if (deletedImageIds.value.length > 0) {
      deletedImageIds.value.forEach((id, index) => {
        formData.append(`deleted_images[${index}]`, id);
      });
    }

    let response;
    if (props.editProduct) {
      formData.append("_method", "PUT");
      response = await axios.post(
        `/api/v1/products/${props.editProduct.id}`,
        formData,
      );
    } else {
      response = await axios.post("/api/v1/products", formData);
    }

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      loading.value = false;
      handleClose();
    }
  } catch (error) {
    console.error("Product submission failed", error);
    toastError(error.response?.data?.message || "Failed to save product");
  } finally {
    loading.value = false;
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

.quill-wrapper :deep(.ql-toolbar.ql-snow) {
  @apply border-none bg-gray-50 px-4 py-3;
}
.quill-wrapper :deep(.ql-container.ql-snow) {
  @apply border-none min-h-[150px] text-gray-700 text-sm;
}
.quill-wrapper :deep(.ql-editor) {
  @apply px-5 py-4;
}
</style>
