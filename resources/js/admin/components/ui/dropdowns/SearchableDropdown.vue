<template>
  <div class="space-y-2">
    <!-- Label -->
    <label v-if="label" class="text-sm font-bold text-slate-700 ml-1 block">
      {{ label }}
    </label>

    <div class="relative">
      <Combobox
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        :multiple="multiple"
      >
        <div class="relative">
          <!-- Trigger / Input -->
          <div class="relative w-full">
            <!-- Leading Icon -->
            <span
              v-if="props.icon"
              class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10"
            >
              <component :is="props.icon" class="h-5 w-5 text-slate-400" />
            </span>

            <ComboboxInput
              @change="query = $event.target.value"
              @blur="commitCustom"
              @keydown.enter="commitCustom"
              :display-value="displayValue"
              :placeholder="placeholder"
              :class="
                cn(
                  'w-full text-left py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all font-semibold text-xs text-slate-900',
                  'focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5',
                  props.icon ? 'pl-12 pr-10' : 'pl-4 pr-10',
                )
              "
            />

            <ComboboxButton
              class="absolute inset-y-0 right-0 flex items-center pr-4"
            >
              <ChevronDownIcon
                class="h-4 w-4 text-slate-400 transition-transform duration-300"
              />
            </ComboboxButton>
          </div>

          <!-- Options Panel -->
          <transition
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            @after-leave="query = ''"
          >
            <ComboboxOptions
              class="absolute mt-1 max-h-56 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-2xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-[110] custom-scrollbar text-left"
            >
              <!-- allowCustom: use the typed text as the value -->
              <ComboboxOption
                v-if="customValue"
                :value="customValue"
                as="template"
                v-slot="{ active }"
              >
                <li
                  :class="[
                    active ? 'bg-slate-50 text-primary' : 'text-slate-900',
                    'relative cursor-pointer select-none py-2.5 pl-10 pr-4 border-b border-slate-100',
                  ]"
                >
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary">
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                  </span>
                  <span class="block truncate font-medium">Use "{{ customValue }}"</span>
                </li>
              </ComboboxOption>

              <div
                v-if="filteredOptions.length === 0 && query !== '' && !customValue"
                class="relative cursor-default select-none py-4 px-4 text-slate-500 text-xs italic"
              >
                No results found for "{{ query }}"
              </div>

              <ComboboxOption
                v-for="option in filteredOptions"
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
              </ComboboxOption>
            </ComboboxOptions>
          </transition>
        </div>
      </Combobox>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from "@headlessui/vue";
import {
  Check as CheckIcon,
  ChevronDown as ChevronDownIcon,
  Plus as PlusIcon,
} from "lucide-vue-next";

const props = defineProps({
  modelValue: [String, Number, Array, Object],
  options: {
    type: Array,
    default: () => [],
  },
  label: String,
  placeholder: {
    type: String,
    default: "Select an option",
  },
  multiple: {
    type: Boolean,
    default: false
  },
  icon: [Object, Function],
  // Let the user type a value that isn't in the options (single select only).
  // The typed text itself becomes the value.
  allowCustom: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const query = ref("");

// Typed text offered as "Use …" when it doesn't exactly match an existing option
const customValue = computed(() => {
  if (!props.allowCustom || props.multiple) return "";
  const typed = query.value.trim();
  if (!typed) return "";
  const lower = typed.toLowerCase();
  const exists = props.options.some(
    (o) => String(o.label).toLowerCase() === lower || String(o.value).toLowerCase() === lower
  );
  return exists ? "" : typed;
});

// allowCustom: keep a typed name that isn't in the list when the user presses Enter
// or leaves the field, without needing to click "Use …"
const commitCustom = () => {
  if (customValue.value && customValue.value !== props.modelValue) {
    emit("update:modelValue", customValue.value);
  }
};

const filteredOptions = computed(() =>
  query.value === ""
    ? props.options
    : props.options.filter((option) => {
        return option.label.toLowerCase().includes(query.value.toLowerCase());
      })
);

const displayValue = (val) => {
  if (props.multiple) {
    if (!Array.isArray(val) || val.length === 0) return '';
    return val.map(v => props.options.find(opt => opt.value === v)?.label || v).join(', ');
  }
  return props.options.find(opt => opt.value === val)?.label || val || '';
};

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
