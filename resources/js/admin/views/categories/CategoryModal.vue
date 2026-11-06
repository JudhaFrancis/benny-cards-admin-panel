<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
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
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white text-left align-middle shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Header -->
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
                      <FolderIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <h3
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{ editCategory ? "Edit Category" : "Create Category" }}
                      </h3>
                      <p class="text-sm text-white/80">
                        {{
                          editCategory
                            ? "Update category information and settings"
                            : "Add a new category to organize your products"
                        }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click="closeModal"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Form -->
              <form
                @submit.prevent="handleSubmit"
                class="flex flex-col flex-1 overflow-hidden"
              >
                <div
                  class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar"
                >
                  <!-- Title -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      <div class="flex items-center gap-2">
                        <FolderIcon class="h-4 w-4 text-gray-400" />
                        Category Title
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <input
                      v-model="form.title"
                      type="text"
                      required
                      placeholder="Enter category name"
                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      style="color: #475569 !important"
                      @input="generateSlug"
                    />
                  </div>

                  <!-- Slug -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      <div class="flex items-center gap-2">
                        <LinkIcon class="h-4 w-4 text-gray-400" />
                        URL Slug
                      </div>
                    </label>
                    <input
                      v-model="form.slug"
                      type="text"
                      placeholder="auto-generated-from-title"
                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      style="color: #475569 !important"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">
                      Auto-generated from title. Edit if needed.
                    </p>
                  </div>

                  <!-- Summary -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      <div class="flex items-center gap-2">
                        <AlignLeftIcon class="h-4 w-4 text-gray-400" />
                        Description
                      </div>
                    </label>
                    <textarea
                      v-model="form.summary"
                      rows="3"
                      placeholder="Brief description of this category..."
                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                      style="color: #475569 !important"
                    ></textarea>
                  </div>

                  <!-- Photo Upload -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      <div class="flex items-center gap-2">
                        <ImageIcon class="h-4 w-4 text-gray-400" />
                        Category Image
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <div class="flex items-start gap-4">
                      <!-- Preview -->
                      <div
                        v-if="photoPreview || editCategory?.photo"
                        class="w-20 h-20 rounded-xl overflow-hidden border-2 border-gray-200 shrink-0"
                      >
                        <img
                          :src="
                            photoPreview || getImageSource(editCategory.photo)
                          "
                          class="w-full h-full object-cover"
                        />
                      </div>
                      <!-- Upload Button -->
                      <div class="flex-1">
                        <input
                          ref="photoInput"
                          type="file"
                          accept="image/*"
                          class="hidden"
                          @change="handlePhotoChange"
                        />
                        <button
                          type="button"
                          @click="$refs.photoInput.click()"
                          class="px-4 py-2.5 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary hover:bg-primary/5 text-sm font-medium text-gray-600 hover:text-primary transition-all"
                        >
                          {{ photoPreview ? "Change Image" : "Upload Image" }}
                        </button>
                        <p class="text-xs text-gray-500 mt-2">
                          JPG, PNG or GIF. Max 2MB.
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Category Type & Status (Single Row) -->
                  <div class="grid grid-cols-2 gap-4">
                    <!-- Status -->
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                      >
                        <div class="flex items-center gap-2">
                          <ActivityIcon class="h-4 w-4 text-gray-400" />
                          Status
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <ContextDropdown
                        v-model="form.status"
                        :options="statusOptions"
                        :icon="ActivityIcon"
                      />
                    </div>
                    <!-- Is Parent -->
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                      >
                        <div class="flex items-center gap-2">
                          <FolderTreeIcon class="h-4 w-4 text-gray-400" />
                          Category Type
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <ContextDropdown
                        v-model="form.is_parent"
                        :options="typeOptions"
                        :icon="FolderTreeIcon"
                      />
                    </div>
                  </div>

                  <!-- Parent Category (shown only if child, full width) -->
                  <div v-if="!form.is_parent">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      <div class="flex items-center gap-2">
                        <FolderIcon class="h-4 w-4 text-gray-400" />
                        Parent Category
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <ContextDropdown
                      v-model="form.parent_id"
                      :options="parentOptions"
                      :icon="FolderIcon"
                      searchable
                      placeholder="Select parent category..."
                    />
                  </div>
                </div>

                <!-- Actions -->
                <div
                  class="flex items-center justify-end gap-3 px-8 py-6 border-t border-gray-50 bg-gray-50/50 sticky bottom-0 z-10"
                >
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition-all active:scale-95"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2.5 rounded-xl bg-primary text-white font-semibold hover:opacity-90 transition-all shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 active:scale-95"
                  >
                    <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                    {{ editCategory ? "Update Category" : "Create Category" }}
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
import { computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";
import {
  X as XIcon,
  Folder as FolderIcon,
  FolderTree as FolderTreeIcon,
  Link as LinkIcon,
  Image as ImageIcon,
  AlignLeft as AlignLeftIcon,
  Activity as ActivityIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/dropdowns/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editCategory: Object,
});

const emit = defineEmits(["close", "refresh"]);

const { success: toastSuccess, error: toastError } = useToast();
const loading = ref(false);
const photoInput = ref(null);
const photoPreview = ref(null);
const photoFile = ref(null);
const parentCategories = ref([]);

const typeOptions = [
  {
    label: "Parent Category",
    value: true,
    description: "This category can have sub-categories.",
    badge: "Top Level",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Child Category",
    value: false,
    description: "This category belongs to a parent category.",
    badge: "Sub Level",
    badgeClass: "bg-purple-100 text-purple-700",
  },
];

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "Category is visible to customers.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Category is hidden from customers.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

const parentOptions = computed(() => {
  // Filter out the category itself to prevent self-parenting
  const filtered = parentCategories.value.filter(
    (cat) => !props.editCategory || cat.id !== props.editCategory.id,
  );

  return [
    { label: "Select parent...", value: "" },
    ...filtered.map((cat) => ({
      label: cat.title,
      value: cat.id,
      description: `Slug: ${cat.slug}`,
    })),
  ];
});

const form = reactive({
  title: "",
  slug: "",
  summary: "",
  is_parent: true,
  parent_id: "",
  status: "active",
});

const generateSlug = () => {
  if (!props.editCategory) {
    form.slug = form.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }
};

const handlePhotoChange = (e) => {
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

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  if (path.startsWith("http")) return path;
  return `/${path}`;
};

const fetchParentCategories = async () => {
  try {
    const response = await axios.get("/api/v1/categories", {
      params: { is_parent: 1, per_page: 100 },
    });
    if (response.data.success) {
      parentCategories.value = response.data.data.data;
    }
  } catch (e) {
    console.error("Failed to fetch parent categories", e);
  }
};

const handleSubmit = async () => {
  loading.value = true;

  try {
    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("slug", form.slug);
    formData.append("summary", form.summary || "");
    formData.append("is_parent", form.is_parent ? 1 : 0);
    formData.append("parent_id", form.parent_id || "");
    formData.append("status", form.status);

    if (photoFile.value) {
      formData.append("photo", photoFile.value);
    }

    let response;
    if (props.editCategory) {
      // For updates with files, we MUST use POST with _method spoofing
      // because PHP doesn't populate $_FILES for PUT/PATCH requests.
      formData.append("_method", "PUT");
      response = await axios.post(
        `/api/v1/categories/${props.editCategory.id}`,
        formData,
      );
    } else {
      response = await axios.post("/api/v1/categories", formData);
    }

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      closeModal();
    }
  } catch (error) {
    console.error("Failed to save category", error);

    // Extract Laravel validation errors
    if (error.response?.status === 422 && error.response.data.errors) {
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0][0];
      toastError(`Validation Error: ${firstError}`);
      console.table(errors); // Show all errors in a nice table in console
    } else {
      const message =
        error.response?.data?.message || "Failed to save category";
      toastError(message);
    }
  } finally {
    loading.value = false;
  }
};

const closeModal = () => {
  emit("close");
  resetForm();
};

const resetForm = () => {
  form.title = "";
  form.slug = "";
  form.summary = "";
  form.is_parent = true;
  form.parent_id = "";
  form.status = "active";
  photoPreview.value = null;
  photoFile.value = null;
};

watch(
  () => props.editCategory,
  (category) => {
    if (category) {
      form.title = category.title;
      form.slug = category.slug;
      form.summary = category.summary || "";
      form.is_parent = category.is_parent;
      form.parent_id = category.parent_id || "";
      form.status = category.status;
      photoPreview.value = null;
      photoFile.value = null;
    } else {
      resetForm();
    }
  },
  { immediate: true },
);

onMounted(() => {
  fetchParentCategories();
});
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

/* Autofill styling */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus,
select:-webkit-autofill,
select:-webkit-autofill:hover,
select:-webkit-autofill:focus {
  -webkit-text-fill-color: #475569 !important;
  -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
  box-shadow: 0 0 0px 1000px #ffffff inset !important;
  transition: background-color 5000s ease-in-out 0s;
}

input[type="text"],
input[type="email"],
input[type="password"],
textarea,
select {
  color: #475569 !important;
}
</style>
