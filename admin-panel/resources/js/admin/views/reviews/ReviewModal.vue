<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-[60]">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
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
              class="w-full max-w-2xl transform overflow-visible rounded-[2.5rem] bg-white text-left align-middle shadow-xl transition-all border border-gray-100 flex flex-col"
            >
              <!-- Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10 rounded-t-[2.5rem]"
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
                      <StarIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{ editReview ? "Edit Review" : "New Review" }}
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        {{
                          editReview
                            ? "Update review details and ratings."
                            : "Create a new product review."
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

              <!-- Form -->
              <div class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-6 pb-40">
                  <!-- Product Selection (Searchable) -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                      Product <span class="text-rose-500">*</span>
                    </label>
                    <Combobox v-model="form.product_id">
                      <div class="relative mt-1">
                        <div
                          class="relative w-full cursor-default overflow-hidden rounded-2xl bg-gray-50 border border-gray-200 text-left focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary transition-all font-semibold"
                        >
                          <ComboboxInput
                            class="w-full border-none py-4 pl-4 pr-10 text-sm leading-5 text-gray-700 focus:ring-0 outline-none bg-transparent"
                            :displayValue="
                              (id) =>
                                products.find((p) => p.id === id)?.title || ''
                            "
                            @change="productQuery = $event.target.value"
                            placeholder="Search product..."
                          />
                          <ComboboxButton
                            class="absolute inset-y-0 right-0 flex items-center pr-2"
                          >
                            <ChevronDownIcon
                              class="h-5 w-5 text-gray-400"
                              aria-hidden="true"
                            />
                          </ComboboxButton>
                        </div>
                        <TransitionRoot
                          leave="transition ease-in duration-100"
                          leaveFrom="opacity-100"
                          leaveTo="opacity-0"
                          @after-leave="productQuery = ''"
                        >
                          <ComboboxOptions
                            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                          >
                            <div
                              v-if="
                                filteredProducts.length === 0 &&
                                productQuery !== ''
                              "
                              class="relative cursor-default select-none py-2 px-4 text-gray-700"
                            >
                              Nothing found.
                            </div>
                            <ComboboxOption
                              v-for="product in filteredProducts"
                              as="template"
                              :key="product.id"
                              :value="product.id"
                              v-slot="{ selected, active }"
                            >
                              <li
                                class="relative cursor-default select-none py-2 pl-10 pr-4"
                                :class="{
                                  'bg-primary text-white': active,
                                  'text-gray-900': !active,
                                }"
                              >
                                <span
                                  class="block truncate"
                                  :class="{
                                    'font-medium': selected,
                                    'font-normal': !selected,
                                  }"
                                >
                                  {{ product.title }}
                                </span>
                                <span
                                  v-if="selected"
                                  class="absolute inset-y-0 left-0 flex items-center pl-3"
                                  :class="{
                                    'text-white': active,
                                    'text-primary': !active,
                                  }"
                                >
                                  <CheckIcon
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                  />
                                </span>
                              </li>
                            </ComboboxOption>
                          </ComboboxOptions>
                        </TransitionRoot>
                      </div>
                    </Combobox>
                  </div>

                  <!-- User Selection (Searchable) - Optional -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                      User
                    </label>
                    <Combobox v-model="form.user_id">
                      <div class="relative mt-1">
                        <div
                          class="relative w-full cursor-default overflow-hidden rounded-2xl bg-gray-50 border border-gray-200 text-left focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary transition-all font-semibold"
                        >
                          <ComboboxInput
                            class="w-full border-none py-4 pl-4 pr-10 text-sm leading-5 text-gray-700 focus:ring-0 outline-none bg-transparent"
                            :displayValue="
                              (id) => users.find((u) => u.id === id)?.name || ''
                            "
                            @change="userQuery = $event.target.value"
                            placeholder="Search user..."
                          />
                          <ComboboxButton
                            class="absolute inset-y-0 right-0 flex items-center pr-2"
                          >
                            <ChevronDownIcon
                              class="h-5 w-5 text-gray-400"
                              aria-hidden="true"
                            />
                          </ComboboxButton>
                        </div>
                        <TransitionRoot
                          leave="transition ease-in duration-100"
                          leaveFrom="opacity-100"
                          leaveTo="opacity-0"
                          @after-leave="userQuery = ''"
                        >
                          <ComboboxOptions
                            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                          >
                            <div
                              v-if="
                                filteredUsers.length === 0 && userQuery !== ''
                              "
                              class="relative cursor-default select-none py-2 px-4 text-gray-700"
                            >
                              Nothing found.
                            </div>
                            <ComboboxOption
                              v-for="user in filteredUsers"
                              as="template"
                              :key="user.id"
                              :value="user.id"
                              v-slot="{ selected, active }"
                            >
                              <li
                                class="relative cursor-default select-none py-2 pl-10 pr-4"
                                :class="{
                                  'bg-primary text-white': active,
                                  'text-gray-900': !active,
                                }"
                              >
                                <span
                                  class="block truncate"
                                  :class="{
                                    'font-medium': selected,
                                    'font-normal': !selected,
                                  }"
                                >
                                  {{ user.name }} ({{ user.email }})
                                </span>
                                <span
                                  v-if="selected"
                                  class="absolute inset-y-0 left-0 flex items-center pl-3"
                                  :class="{
                                    'text-white': active,
                                    'text-primary': !active,
                                  }"
                                >
                                  <CheckIcon
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                  />
                                </span>
                              </li>
                            </ComboboxOption>
                          </ComboboxOptions>
                        </TransitionRoot>
                      </div>
                    </Combobox>
                  </div>

                  <!-- Reviewer Name (if no user selected) -->
                  <div v-if="!form.user_id">
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Reviewer Name <span class="text-rose-500">*</span></label
                    >
                    <input
                      v-model="form.reviewer_name"
                      type="text"
                      required
                      class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                      placeholder="e.g. John Doe"
                    />
                  </div>

                  <!-- Title -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Review Title <span class="text-rose-500">*</span></label
                    >
                    <input
                      v-model="form.title"
                      type="text"
                      required
                      class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                      placeholder="e.g. Great Product!"
                    />
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Review Content
                      <span class="text-rose-500">*</span></label
                    >
                    <textarea
                      v-model="form.description"
                      rows="4"
                      required
                      class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600 resize-none"
                      placeholder="Write your review here..."
                    ></textarea>
                  </div>

                  <!-- Rating -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Rating <span class="text-rose-500">*</span></label
                    >
                    <div class="flex items-center gap-2">
                      <button
                        type="button"
                        v-for="star in 5"
                        :key="star"
                        @click="form.rating = star"
                        class="focus:outline-none transition-transform active:scale-90"
                      >
                        <StarIcon
                          class="h-8 w-8 transition-colors"
                          :class="
                            star <= form.rating
                              ? 'fill-amber-400 text-amber-400'
                              : 'text-gray-300 hover:text-amber-200'
                          "
                        />
                      </button>
                    </div>
                  </div>

                  <!-- Status -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Status</label
                    >
                    <select
                      v-model="form.status"
                      class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-semibold text-slate-600"
                    >
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>

                  <!-- Image Upload (Multiple) -->
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2"
                      >Images</label
                    >

                    <!-- Existing Images Preview -->
                    <div
                      v-if="editReview && existingImages.length > 0"
                      class="flex flex-wrap gap-4 mb-4"
                    >
                      <div
                        v-for="(img, index) in existingImages"
                        :key="index"
                        class="relative w-24 h-24 rounded-xl border border-gray-200 overflow-hidden group"
                      >
                        <img
                          :src="getImageSource(img)"
                          class="w-full h-full object-cover"
                        />
                        <button
                          type="button"
                          @click="removeExistingImage(index)"
                          class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                          <XIcon class="h-3 w-3" />
                        </button>
                      </div>
                    </div>

                    <!-- New Images Preview -->
                    <div
                      v-if="imagePreviews.length > 0"
                      class="flex flex-wrap gap-4 mb-4"
                    >
                      <div
                        v-for="(preview, index) in imagePreviews"
                        :key="index"
                        class="relative w-24 h-24 rounded-xl border border-gray-200 overflow-hidden group"
                      >
                        <img
                          :src="preview"
                          class="w-full h-full object-cover"
                        />
                        <button
                          type="button"
                          @click="removeNewImage(index)"
                          class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                          <XIcon class="h-3 w-3" />
                        </button>
                      </div>
                    </div>

                    <div
                      class="border-2 border-dashed border-gray-300 rounded-2xl p-8 flex flex-col items-center justify-center text-center hover:border-primary hover:bg-primary/5 transition-all cursor-pointer"
                      @click="$refs.fileInput.click()"
                    >
                      <input
                        ref="fileInput"
                        type="file"
                        multiple
                        accept="image/*"
                        class="hidden"
                        @change="handleFileChange"
                      />
                      <ImageIcon class="h-10 w-10 text-gray-400 mb-2" />
                      <p class="text-sm font-semibold text-gray-600">
                        Click to upload images
                      </p>
                      <p class="text-xs text-gray-400">
                        PNG, JPG, GIF up to 5MB
                      </p>
                    </div>
                  </div>

                  <div class="flex gap-4 pt-4">
                    <button
                      type="button"
                      @click="handleClose"
                      class="flex-1 py-4 border border-gray-200 text-gray-500 rounded-2xl font-bold hover:bg-gray-50 transition-all"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="flex-1 py-4 bg-primary text-white rounded-2xl font-bold hover:shadow-xl hover:shadow-primary/20 transition-all disabled:opacity-50"
                    >
                      {{
                        isSubmitting
                          ? "Saving..."
                          : editReview
                            ? "Update Review"
                            : "Create Review"
                      }}
                    </button>
                  </div>
                </form>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
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
  X as XIcon,
  Star as StarIcon,
  Image as ImageIcon,
  ChevronDown as ChevronDownIcon,
  Check as CheckIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";

const props = defineProps({
  isOpen: Boolean,
  editReview: Object,
});

const emit = defineEmits(["close", "refresh"]);
const toast = useToast();

const isSubmitting = ref(false);
const fileInput = ref(null);
const imagePreviews = ref([]);
const existingImages = ref([]);
const newImages = ref([]); // File objects

const products = ref([]);
const users = ref([]);
const productQuery = ref("");
const userQuery = ref("");

const form = reactive({
  product_id: null,
  user_id: null,
  reviewer_name: "",
  title: "",
  description: "",
  rating: 5,
  status: "active",
});

// Fetch Data for Dropdowns
const fetchProducts = async () => {
  try {
    const res = await axios.get("/api/v1/products/list");
    if (res.data.success && Array.isArray(res.data.data)) {
      products.value = res.data.data;
    } else {
      products.value = [];
    }
  } catch (e) {
    console.error(e);
  }
};

const fetchUsers = async () => {
  try {
    const res = await axios.get("/api/v1/users");
    if (res.data.success) {
      users.value = res.data.data.data || res.data.data;
    }
  } catch (e) {
    console.error(e);
  }
};

onMounted(() => {
  fetchProducts();
  fetchUsers();
});

const filteredProducts = computed(() =>
  productQuery.value === ""
    ? products.value
    : products.value.filter((product) =>
        product.title
          .toLowerCase()
          .replace(/\s+/g, "")
          .includes(productQuery.value.toLowerCase().replace(/\s+/g, "")),
      ),
);

const filteredUsers = computed(() =>
  userQuery.value === ""
    ? users.value
    : users.value.filter((user) =>
        user.name
          .toLowerCase()
          .replace(/\s+/g, "")
          .includes(userQuery.value.toLowerCase().replace(/\s+/g, "")),
      ),
);

const resetForm = () => {
  form.product_id = null;
  form.user_id = null;
  form.reviewer_name = "";
  form.title = "";
  form.description = "";
  form.rating = 5;
  form.status = "active";
  existingImages.value = [];
  newImages.value = [];
  imagePreviews.value = [];
};

watch(
  () => props.editReview,
  (val) => {
    if (val) {
      form.product_id = val.product_id;
      form.user_id = val.user_id;
      form.reviewer_name = val.reviewer_name || ""; // if user_id is null
      form.title = val.title;
      form.description = val.description;
      form.rating = val.rating;
      form.status = val.status;

      existingImages.value = Array.isArray(val.image)
        ? [...val.image]
        : val.image
          ? [val.image]
          : [];
    } else {
      resetForm();
    }
  },
  { immediate: true },
);

const handleFileChange = (event) => {
  const files = Array.from(event.target.files);
  files.forEach((file) => {
    newImages.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => imagePreviews.value.push(e.target.result);
    reader.readAsDataURL(file);
  });
  // Reset input to allow re-selecting same file if cleared
  event.target.value = "";
};

const removeNewImage = (index) => {
  newImages.value.splice(index, 1);
  imagePreviews.value.splice(index, 1);
};

const removeExistingImage = (index) => {
  existingImages.value.splice(index, 1);
};

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  if (path.startsWith("http")) return path;
  return `/${path}`;
};

const handleClose = () => {
  resetForm();
  emit("close");
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    formData.append("product_id", form.product_id);
    if (form.user_id) formData.append("user_id", form.user_id);
    if (!form.user_id) formData.append("reviewer_name", form.reviewer_name);
    formData.append("title", form.title);
    formData.append("description", form.description);
    formData.append("rating", form.rating);
    formData.append("status", form.status);

    // Append new images
    newImages.value.forEach((file) => {
      formData.append("images[]", file);
    });

    // Append existing images (for update)
    existingImages.value.forEach((path) => {
      formData.append("existing_images[]", path);
    });

    let url = "/api/v1/reviews";

    if (props.editReview) {
      url += `/${props.editReview.id}`;
      // Put method with FormData requires _method spoofing in Laravel usually,
      // or simply use Post with _method=PUT.
      // Axios put with FormData often fails to parse files. Safe bet is POST with _method=PUT
      formData.append("_method", "PUT");
    }

    const res = await axios.post(url, formData, {
      headers: { "Content-Type": "multipart/form-data" }, // Optional, axios sets it automatically for FormData
    });

    if (res.data.success) {
      toast.success(props.editReview ? "Review updated" : "Review added");
      emit("refresh");
      handleClose();
    }
  } catch (e) {
    console.error(e);
    toast.error("Failed to save review");
  } finally {
    isSubmitting.value = false;
  }
};
</script>
