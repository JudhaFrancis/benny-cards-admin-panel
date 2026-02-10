<template>
  <div class="space-y-2">
    <!-- Label -->
    <label v-if="label" class="text-sm font-bold text-slate-700 ml-1 block">
      {{ label }}
    </label>

    <div class="relative">
      <Listbox
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        v-slot="{ open, value }"
      >
        <div class="relative" :class="{ 'z-20': open }">
          <!-- Trigger Button -->
          <ListboxButton
            :class="
              cn(
                'relative w-full text-left py-3 bg-slate-50 border border-transparent rounded-xl cursor-pointer outline-none transition-all',
                'focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5',
                open ? 'bg-white border-primary ring-4 ring-primary/5' : '',
                props.icon ? 'pl-12 pr-10' : 'pl-4 pr-10',
              )
            "
          >
            <!-- Leading Icon -->
            <span
              v-if="props.icon"
              class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"
            >
              <component :is="props.icon" class="h-5 w-5 text-slate-400" />
            </span>

            <span
              class="block truncate text-sm font-semibold"
              :class="value ? 'text-slate-900' : 'text-slate-400'"
            >
              {{ selectedOption?.label || placeholder }}
            </span>

            <span
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4"
            >
              <ChevronDownIcon
                class="h-4 w-4 text-slate-400 transition-transform duration-300"
                :class="{ 'rotate-180': open }"
              />
            </span>
          </ListboxButton>

          <!-- Options Panel -->
          <transition
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <ListboxOptions
              class="absolute mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-50 custom-scrollbar"
            >
              <ListboxOption
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                as="template"
                v-slot="{ active, selected }"
              >
                <li
                  :class="[
                    active ? 'bg-slate-50 text-primary' : 'text-slate-900',
                    'relative cursor-pointer select-none py-2.5 pl-10 pr-4',
                  ]"
                >
                  <span
                    :class="[
                      selected ? 'font-bold' : 'font-medium',
                      'block truncate',
                    ]"
                  >
                    {{ option.label }}
                  </span>
                  <span
                    v-if="selected"
                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary"
                  >
                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                  </span>
                </li>
              </ListboxOption>
            </ListboxOptions>
          </transition>
        </div>
      </Listbox>

      <!-- Contextual Details Section -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div v-if="selectedOption?.description" class="mt-3 relative">
          <div
            class="absolute left-6 -top-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-b-[6px] border-b-slate-100 dark:border-b-slate-800"
          ></div>

          <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100">
            <div class="flex items-start gap-3">
              <div v-if="selectedOption.badge" class="mt-0.5">
                <span
                  :class="
                    cn(
                      'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                      selectedOption.badgeClass ||
                        'bg-slate-200 text-slate-600',
                    )
                  "
                >
                  {{ selectedOption.badge }}
                </span>
              </div>
              <div class="flex-1 space-y-1">
                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                  {{ selectedOption.description }}
                </p>
                <div
                  v-if="selectedOption.metadata"
                  class="pt-2 flex items-center gap-4 border-t border-slate-100/50 mt-2"
                >
                  <div
                    v-for="(meta, index) in selectedOption.metadata"
                    :key="index"
                    class="flex items-center gap-1.5 text-[10px] text-slate-400 font-bold uppercase tracking-wide"
                  >
                    <component
                      :is="meta.icon"
                      v-if="meta.icon"
                      class="h-3 w-3"
                    />
                    {{ meta.text }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";
import {
  Check as CheckIcon,
  ChevronDown as ChevronDownIcon,
} from "lucide-vue-next";

const props = defineProps({
  modelValue: [String, Number, Object],
  options: {
    type: Array, // [{ label, value, description, badge, badgeClass, metadata: [{icon, text}] }]
    default: () => [],
  },
  label: String,
  placeholder: {
    type: String,
    default: "Select an option",
  },
  icon: [Object, Function],
});

defineEmits(["update:modelValue"]);

const selectedOption = computed(() => {
  return props.options.find((opt) => opt.value === props.modelValue);
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 20px;
}
</style>
