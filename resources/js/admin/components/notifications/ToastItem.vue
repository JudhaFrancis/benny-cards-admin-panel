<template>
  <div
    class="flex items-start gap-4 p-4 rounded-2xl shadow-xl border backdrop-blur-md transition-all duration-500 ease-out transform cursor-pointer"
    :class="[
      type === 'success'
        ? 'bg-emerald-50/95 border-emerald-100 ring-1 ring-emerald-500/10'
        : type === 'info'
        ? 'bg-blue-50/95 border-blue-100 ring-1 ring-blue-500/10'
        : 'bg-rose-50/95 border-rose-100 ring-1 ring-rose-500/10',
    ]"
  >
    <div
      class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center flex-none"
      :class="[
        type === 'success'
          ? 'bg-emerald-100 text-emerald-600'
          : type === 'info'
          ? 'bg-blue-100 text-blue-600'
          : 'bg-rose-100 text-rose-600',
      ]"
    >
      <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5" />
      <BellIcon v-else-if="type === 'info'" class="h-5 w-5" />
      <AlertTriangleIcon v-else class="h-5 w-5" />
    </div>

    <div class="flex-1 pt-0.5 min-w-0">
      <h3
        class="text-sm font-bold tracking-tight"
        :class="
          type === 'success'
            ? 'text-emerald-900'
            : type === 'info'
            ? 'text-blue-900'
            : 'text-rose-900'
        "
      >
        {{ type === "success" ? "Success" : type === "info" ? "Work Notification" : "Error" }}
      </h3>
      <p
        class="mt-1 text-sm font-medium leading-relaxed"
        :class="
          type === 'success'
            ? 'text-emerald-700'
            : type === 'info'
            ? 'text-blue-700'
            : 'text-rose-700'
        "
      >
        {{ message }}
      </p>
    </div>

    <button
      @click.stop="$emit('close')"
      class="flex-shrink-0 -mr-1 -mt-1 p-2 rounded-lg transition-colors flex-none"
      :class="
        type === 'success'
          ? 'text-emerald-500 hover:bg-emerald-100'
          : type === 'info'
          ? 'text-blue-500 hover:bg-blue-100'
          : 'text-rose-500 hover:bg-rose-100'
      "
    >
      <XIcon class="h-4 w-4" />
    </button>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import {
  CheckCircle as CheckCircleIcon,
  AlertTriangle as AlertTriangleIcon,
  Bell as BellIcon,
  X as XIcon,
} from "lucide-vue-next";

defineProps({
  message: String,
  type: {
    type: String, // 'success' | 'error' | 'info'
    default: "success",
  },
});

defineEmits(["close"]);
</script>
