<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-50">
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
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white text-left align-middle shadow-xl transition-all border border-gray-100"
            >
              <div class="relative bg-white p-8">
                <button
                  @click="$emit('close')"
                  class="absolute top-6 right-6 p-2 rounded-full hover:bg-gray-100 transition-colors"
                >
                  <XIcon class="h-6 w-6 text-gray-500" />
                </button>

                <div class="flex items-start gap-6">
                  <!-- Rating Badge -->
                  <div
                    class="flex-shrink-0 flex flex-col items-center justify-center w-20 h-20 rounded-2xl bg-amber-50 text-amber-500 font-bold text-3xl"
                  >
                    <span>{{ review.rating }}</span>
                    <StarIcon class="h-5 w-5 mt-1 fill-amber-500" />
                  </div>

                  <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-800">
                      {{ review.title }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                      by
                      <span class="font-semibold text-gray-700">{{
                        review.user?.name || review.reviewer_name || "Guest"
                      }}</span>
                      on {{ new Date(review.created_at).toLocaleDateString() }}
                    </p>

                    <!-- Product Info -->
                    <div
                      class="mt-4 p-4 bg-gray-50 rounded-xl flex items-center gap-4"
                    >
                      <div
                        class="w-12 h-12 rounded-lg bg-gray-200 overflow-hidden flex-shrink-0"
                      >
                        <!-- If product has image -->
                        <img
                          v-if="review.product?.image"
                          :src="`/${review.product.image}`"
                          class="w-full h-full object-cover"
                        />
                        <div
                          v-else
                          class="w-full h-full flex items-center justify-center text-gray-400"
                        >
                          <PackageIcon class="h-6 w-6" />
                        </div>
                      </div>
                      <div>
                        <p
                          class="text-xs text-gray-400 uppercase font-bold tracking-wider"
                        >
                          Product
                        </p>
                        <p class="text-sm font-semibold text-gray-700">
                          {{ review.product?.title || "Unknown Product" }}
                        </p>
                      </div>
                    </div>

                    <!-- Review Body -->
                    <div class="mt-6 prose prose-sm text-gray-600">
                      <p>{{ review.description }}</p>
                    </div>

                    <!-- Images Gallery -->
                    <div
                      v-if="review.image && review.image.length > 0"
                      class="mt-6"
                    >
                      <h4 class="text-sm font-bold text-gray-700 mb-3">
                        Attached Images
                      </h4>
                      <div class="flex flex-wrap gap-2">
                        <div
                          v-for="(img, idx) in Array.isArray(review.image)
                            ? review.image
                            : [review.image]"
                          :key="idx"
                          class="w-24 h-24 rounded-xl border border-gray-100 overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                          @click="openPreview(img)"
                        >
                          <img
                            :src="getImageSource(img)"
                            class="w-full h-full object-cover"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <ImagePreviewModal
    :isOpen="isPreviewOpen"
    :imageSrc="previewImage"
    title="Review Image"
    @close="isPreviewOpen = false"
  />
</template>

<script setup>
import { ref } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
} from "@headlessui/vue";
import {
  X as XIcon,
  Star as StarIcon,
  Package as PackageIcon,
} from "lucide-vue-next";
import ImagePreviewModal from "../ui/modals/ImagePreviewModal.vue";

const props = defineProps({
  isOpen: Boolean,
  review: Object,
});

defineEmits(["close"]);

const isPreviewOpen = ref(false);
const previewImage = ref("");

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image")) return path;
  if (path.startsWith("http")) return path;
  return `/${path}`;
};

const openPreview = (img) => {
  previewImage.value = getImageSource(img);
  isPreviewOpen.value = true;
};
</script>
