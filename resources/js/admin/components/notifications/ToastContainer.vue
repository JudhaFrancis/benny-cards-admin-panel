<template>
  <div
    class="fixed top-20 right-8 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none"
  >
    <TransitionGroup
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-x-8 opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition ease-in duration-200 absolute w-full"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 scale-95"
      move-class="transition-all duration-300 ease-in-out"
    >
      <div v-for="toast in toasts" :key="toast.id" class="pointer-events-auto">
        <ToastItem
          :message="toast.message"
          :type="toast.type"
          @close="removeToast(toast.id)"
        />
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from "../../composables/useToast";
import ToastItem from "./ToastItem.vue";

const { toasts, removeToast } = useToast();
</script>
