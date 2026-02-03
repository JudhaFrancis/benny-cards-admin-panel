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
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Sticky Header -->
              <div
                class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-white sticky top-0 z-10"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shadow-inner"
                  >
                    <ImageIcon v-if="!editBanner" class="h-6 w-6" />
                    <Edit3Icon v-else class="h-6 w-6" />
                  </div>
                  <div>
                    <DialogTitle
                      as="h3"
                      class="text-2xl font-bold text-gray-800 tracking-tight"
                    >
                      {{ editBanner ? "Edit Banner" : "Add New Banner" }}
                    </DialogTitle>
                    <p class="text-sm text-gray-500 mt-0.5">
                      {{
                        editBanner
                          ? "Update global promotional banner content."
                          : "Create a new highlight banner for the site."
                      }}
                    </p>
                  </div>
                </div>
                <button
                  @click="handleClose"
                  class="p-3 rounded-2xl hover:bg-gray-100 text-gray-400 transition-all active:scale-95"
                >
                  <XIcon class="h-6 w-6" />
                </button>
              </div>

              <!-- Scrollable Form Content -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-8">
                  <!-- Image Upload Section -->
                  <div>
                    <label
                      class="block text-sm font-semibold text-gray-700 mb-4 ml-1"
                    >
                      <div class="flex items-center gap-2">
                        <ImageIcon class="h-4 w-4 text-gray-400" />
                        Banner Image
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <div
                      class="flex flex-col items-center p-8 rounded-[2rem] border-2 border-dashed border-gray-200 bg-gray-50/50 hover:bg-gray-50 hover:border-primary/30 transition-all cursor-pointer group relative overflow-hidden"
                      @click="$refs.fileInput.click()"
                    >
                      <input
                        type="file"
                        ref="fileInput"
                        class="hidden"
                        accept="image/*"
                        @change="handleFileUpload"
                      />

                      <!-- Preview -->
                      <div
                        v-if="photoPreview || editBanner?.photo"
                        class="w-full h-48 rounded-2xl overflow-hidden shadow-lg border-2 border-white mb-4 relative z-10"
                      >
                        <img
                          :src="
                            photoPreview || getImageSource(editBanner.photo)
                          "
                          class="w-full h-full object-cover"
                        />
                        <div
                          class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                        >
                          <span
                            class="text-white text-sm font-bold flex items-center gap-2"
                          >
                            <UploadIcon class="h-5 w-5" />
                            Click to Change
                          </span>
                        </div>
                      </div>

                      <div v-else class="flex flex-col items-center gap-3">
                        <div
                          class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-gray-300 group-hover:text-primary transition-colors"
                        >
                          <UploadIcon class="h-7 w-7" />
                        </div>
                        <div class="text-center">
                          <p class="text-sm font-bold text-gray-600">
                            Select Banner Image
                          </p>
                          <p class="text-xs text-gray-400 mt-1">
                            Recommended: 1920x1080 (HD)
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Title -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        Title <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.title"
                        type="text"
                        placeholder="Enter banner headline"
                        class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all"
                        required
                        @input="generateSlug"
                      />
                    </div>

                    <!-- Slug -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        Slug (URL)
                      </label>
                      <input
                        v-model="form.slug"
                        type="text"
                        placeholder="auto-generated-slug"
                        class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-500 text-sm focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all font-mono"
                      />
                    </div>

                    <!-- Subtitle/Text -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        Subtitle / Accent Text
                      </label>
                      <input
                        v-model="form.text"
                        type="text"
                        placeholder="Short text appearing below title"
                        class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all"
                      />
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        Detailed Description
                      </label>
                      <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Detailed information for internal review or accessibility..."
                        class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all resize-none"
                      ></textarea>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        Visibility Status
                      </label>
                      <ContextDropdown
                        v-model="form.status"
                        :options="statusOptions"
                        :icon="ActivityIcon"
                      />
                    </div>
                  </div>
                </form>
              </div>

              <!-- Sticky Footer -->
              <div
                class="px-8 py-6 border-t border-gray-50 bg-gray-50/50 flex justify-end gap-3 sticky bottom-0 z-10"
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
                  class="px-8 py-3 bg-primary text-white rounded-2xl font-bold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/25 flex items-center gap-2 active:scale-95"
                >
                  <span
                    v-if="loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                  ></span>
                  {{ editBanner ? "Update Banner" : "Create Banner" }}
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
import { ref, watch, reactive } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Image as ImageIcon,
  Edit3 as Edit3Icon,
  Activity as ActivityIcon,
  Upload as UploadIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editBanner: Object,
});

const emit = defineEmits(["close", "refresh"]);
const { success: toastSuccess, error: toastError } = useToast();

const loading = ref(false);
const fileInput = ref(null);
const photoPreview = ref(null);
const photoFile = ref(null);

const form = reactive({
  title: "",
  slug: "",
  text: "",
  description: "",
  status: "active",
});

const statusOptions = [
  {
    label: "Active / Live",
    value: "active",
    description: "Visible on the website front-page.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive / Hidden",
    value: "inactive",
    description: "Hide from customers temporarily.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

const generateSlug = () => {
  if (!props.editBanner) {
    form.slug = form.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }
};

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  return `/${path}`;
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

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      if (props.editBanner) {
        form.title = props.editBanner.title;
        form.slug = props.editBanner.slug;
        form.text = props.editBanner.text || "";
        form.description = props.editBanner.description || "";
        form.status = props.editBanner.status;
        photoPreview.value = null;
        photoFile.value = null;
      } else {
        form.title = "";
        form.slug = "";
        form.text = "";
        form.description = "";
        form.status = "active";
        photoPreview.value = null;
        photoFile.value = null;
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
  // Validation
  if (!props.editBanner && !photoFile.value) {
    toastError("Banner image is required");
    return;
  }

  loading.value = true;
  try {
    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("slug", form.slug);
    formData.append("text", form.text);
    formData.append("description", form.description);
    formData.append("status", form.status);

    if (photoFile.value) {
      formData.append("photo", photoFile.value);
    }

    let response;
    if (props.editBanner) {
      formData.append("_method", "PUT");
      response = await axios.post(
        `/api/v1/banners/${props.editBanner.id}`,
        formData,
      );
    } else {
      response = await axios.post("/api/v1/banners", formData);
    }

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      loading.value = false;
      handleClose();
    }
  } catch (error) {
    console.error("Banner submission failed", error);
    toastError(error.response?.data?.message || "Failed to save banner");
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
</style>
