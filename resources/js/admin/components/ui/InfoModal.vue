<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-[60]">
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
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
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
              :class="
                cn(
                  'w-full transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh]',
                  maxWidthClass,
                )
              "
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
                  class="relative px-8 py-6 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      v-if="icon"
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <component :is="icon" class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{ title }}
                      </DialogTitle>
                      <p v-if="subtitle" class="text-sm text-white/80">
                        {{ subtitle }}
                      </p>
                      <div v-if="$slots['header-extra']" class="mt-2 flex items-center gap-3">
                        <slot name="header-extra" />
                      </div>
                    </div>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <slot />
              </div>

              <!-- Footer -->
              <div
                v-if="$slots.footer"
                class="px-8 py-6 border-t border-slate-50 bg-slate-50/50 sticky bottom-0 z-10 flex justify-end gap-3"
              >
                <slot name="footer" />
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
  DialogTitle,
} from "@headlessui/vue";
import { X as XIcon } from "lucide-vue-next";
import { computed } from "vue";

const props = defineProps({
  isOpen: Boolean,
  title: String,
  subtitle: String,
  icon: [Object, Function],
  maxWidth: {
    type: String,
    default: "900px",
  },
});

defineEmits(["close"]);

const maxWidthClass = computed(() => {
  switch (props.maxWidth) {
    case "sm":
      return "max-w-md";
    case "md":
      return "max-w-2xl";
    case "lg":
      return "max-w-4xl";
    case "xl":
      return "max-w-5xl";
    case "700px":
      return "max-w-[700px]";
    case "800px":
      return "max-w-[800px]";
    case "900px":
      return "max-w-[900px]";
    default:
      return "max-w-3xl";
  }
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
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
