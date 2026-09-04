<template>
  <div class="space-y-2 w-full">
    <div class="flex items-center gap-2">
      <input
        type="text"
        v-model="inputValue"
        @keydown.enter.prevent="addTag"
        :placeholder="placeholder"
        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        :disabled="disabled"
      />
      <button
        type="button"
        @click="addTag"
        :disabled="!inputValue.trim() || disabled"
        class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold shadow-sm hover:bg-primary/90 focus:ring-2 focus:ring-primary/20 transition-all disabled:opacity-50 flex items-center justify-center shrink-0"
      >
        <PlusIcon class="h-4 w-4" />
        <span class="ml-1 md:inline hidden">Add</span>
      </button>
    </div>
    
    <div v-if="modelValue && modelValue.length > 0" class="flex flex-wrap gap-2 pt-1">
      <span
        v-for="(tag, index) in modelValue"
        :key="index"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary/5 text-primary border border-primary/20 text-xs font-bold"
      >
        {{ tag }}
        <button
          type="button"
          @click="removeTag(index)"
          class="text-primary/60 hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/30 rounded-full"
          :disabled="disabled"
        >
          <XIcon class="h-3.5 w-3.5" />
        </button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Plus as PlusIcon, X as XIcon } from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Add item...'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);
const inputValue = ref('');

const addTag = () => {
  const val = inputValue.value.trim();
  if (!val || props.disabled) return;

  // Prevent duplicates (case-insensitive)
  const currentTags = Array.isArray(props.modelValue) ? props.modelValue : [];
  const exists = currentTags.some(tag => tag.toLowerCase() === val.toLowerCase());
  
  if (!exists) {
    emit('update:modelValue', [...currentTags, val]);
  }
  
  inputValue.value = '';
};

const removeTag = (index) => {
  if (props.disabled) return;
  const newTags = [...(props.modelValue || [])];
  newTags.splice(index, 1);
  emit('update:modelValue', newTags);
};
</script>
