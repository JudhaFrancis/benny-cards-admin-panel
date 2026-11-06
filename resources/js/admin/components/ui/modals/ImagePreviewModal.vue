<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-[100]">
      <!-- Backdrop with heavy blur for focus -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-md" />
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
              class="relative transform transition-all max-w-2xl w-full mx-4"
            >
              <!-- Close Button -->
              <button
                @click="$emit('close')"
                class="absolute -top-14 right-0 md:-right-12 p-2 text-white/80 hover:text-white transition-all hover:scale-110 active:scale-90"
                title="Close Preview"
              >
                <XIcon class="h-9 w-9" />
              </button>

              <!-- Image Container -->
              <div
                class="bg-white p-3 rounded-[2.5rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden border-4 border-white/10"
              >
                <img
                  :src="imageSrc"
                  class="w-full h-auto max-h-[75vh] object-contain rounded-[1.8rem]"
                  :alt="title || 'Image Preview'"
                />
              </div>

              <!-- Title (Optional) -->
              <div v-if="title" class="mt-4 text-center">
                <h4 class="text-xl font-bold text-white tracking-tight">
                  {{ title }}
                </h4>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
} from "@headlessui/vue";
import { X as XIcon } from "lucide-vue-next";

defineProps({
  isOpen: Boolean,
  imageSrc: String,
  title: String,
});

defineEmits(["close"]);
</script>
