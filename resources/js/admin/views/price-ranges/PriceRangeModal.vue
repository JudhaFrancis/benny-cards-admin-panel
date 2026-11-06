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
                      <DollarSignIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <h3
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{
                          editRange ? "Edit Price Range" : "Create Price Range"
                        }}
                      </h3>
                      <p class="text-sm text-white/80">
                        {{
                          editRange
                            ? "Update range information and pricing settings"
                            : "Add a new price range to filter your collection"
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
                        <TagIcon class="h-4 w-4 text-gray-400" />
                        Range Title
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <input
                      v-model="form.title"
                      type="text"
                      required
                      placeholder="e.g. Under ₹100"
                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
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
                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
                      style="color: #475569 !important"
                    />
                  </div>

                  <!-- Pricing Configuration -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                      >
                        <div class="flex items-center gap-2">
                          <DollarSignIcon class="h-4 w-4 text-gray-400" />
                          Min Price
                        </div>
                      </label>
                      <input
                        v-model="form.min_price"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
                        style="color: #475569 !important"
                      />
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                      >
                        <div class="flex items-center gap-2">
                          <DollarSignIcon class="h-4 w-4 text-gray-400" />
                          Max Price
                        </div>
                      </label>
                      <input
                        v-model="form.max_price"
                        type="number"
                        step="0.01"
                        placeholder="∞"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
                        style="color: #475569 !important"
                      />
                    </div>
                  </div>

                  <!-- Image & Status Row -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                    <!-- Photo Upload -->
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                      >
                        <div class="flex items-center gap-2">
                          <ImageIcon class="h-4 w-4 text-gray-400" />
                          Range Image
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <div class="flex items-center gap-4">
                        <!-- Preview -->
                        <div
                          v-if="photoPreview || editRange?.photo"
                          class="w-12 h-12 rounded-xl overflow-hidden border border-gray-200 shrink-0"
                        >
                          <img
                            :src="
                              photoPreview || getImageSource(editRange.photo)
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
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 hover:border-primary hover:bg-primary/5 text-sm font-medium text-gray-600 hover:text-primary transition-all flex items-center justify-center gap-2"
                          >
                            <ImageIcon class="h-4 w-4" />
                            {{ photoPreview ? "Change" : "Upload" }}
                          </button>
                        </div>
                      </div>
                    </div>

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
                    {{ editRange ? "Update Range" : "Create Range" }}
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
import { ref, reactive, watch } from "vue";
import { computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";
import {
  X as XIcon,
  Tag as TagIcon,
  Link as LinkIcon,
  DollarSign as DollarSignIcon,
  Image as ImageIcon,
  Activity as ActivityIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/dropdowns/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editRange: Object,
});

const emit = defineEmits(["close", "refresh"]);

const { success: toastSuccess, error: toastError } = useToast();
const loading = ref(false);
const photoInput = ref(null);
const photoPreview = ref(null);
const photoFile = ref(null);

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "Range is visible to customers.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Range is hidden from customers.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

const form = reactive({
  title: "",
  slug: "",
  min_price: "",
  max_price: "",
  status: "active",
});

const generateSlug = () => {
  if (!props.editRange) {
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

const handleSubmit = async () => {
  loading.value = true;

  try {
    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("slug", form.slug);
    formData.append("min_price", form.min_price || "");
    formData.append("max_price", form.max_price || "");
    formData.append("status", form.status);

    if (photoFile.value) {
      formData.append("photo", photoFile.value);
    }

    let response;
    if (props.editRange) {
      formData.append("_method", "PUT");
      response = await axios.post(
        `/api/v1/price-ranges/${props.editRange.id}`,
        formData,
      );
    } else {
      response = await axios.post("/api/v1/price-ranges", formData);
    }

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      closeModal();
    }
  } catch (error) {
    console.error("Failed to save price range", error);
    if (error.response?.status === 422 && error.response.data.errors) {
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0][0];
      toastError(`Validation Error: ${firstError}`);
    } else {
      const message =
        error.response?.data?.message || "Failed to save price range";
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
  form.min_price = "";
  form.max_price = "";
  form.status = "active";
  photoPreview.value = null;
  photoFile.value = null;
};

watch(
  () => props.editRange,
  (range) => {
    if (range) {
      form.title = range.title;
      form.slug = range.slug;
      form.min_price = range.min_price || "";
      form.max_price = range.max_price || "";
      form.status = range.status;
      photoPreview.value = null;
      photoFile.value = null;
    } else {
      resetForm();
    }
  },
  { immediate: true },
);
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
