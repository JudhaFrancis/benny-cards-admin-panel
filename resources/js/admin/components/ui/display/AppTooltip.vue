<template>
  <TooltipProvider :delayDuration="100">
    <TooltipRoot>
      <TooltipTrigger as-child>
        <slot name="trigger">
          <InfoIcon class="h-4 w-4 text-slate-400 hover:text-slate-600 transition-colors cursor-help outline-none" />
        </slot>
      </TooltipTrigger>
      <TooltipPortal>
        <TooltipContent
          :side="position"
          class="z-[var(--z-popover)] bg-slate-800 text-white text-xs rounded-lg py-1.5 px-2.5 shadow-xl min-w-max max-w-xs whitespace-pre-wrap leading-relaxed will-change-[transform,opacity] data-[state=delayed-open]:data-[side=top]:animate-in data-[state=delayed-open]:data-[side=bottom]:animate-in fade-in zoom-in-95"
          :sideOffset="5"
        >
          <slot>{{ content }}</slot>
          <TooltipArrow class="fill-slate-800" :width="8" />
        </TooltipContent>
      </TooltipPortal>
    </TooltipRoot>
  </TooltipProvider>
</template>

<script setup>
import { 
  TooltipContent, 
  TooltipPortal, 
  TooltipProvider, 
  TooltipRoot, 
  TooltipTrigger,
  TooltipArrow 
} from 'radix-vue';
import { Info as InfoIcon } from "lucide-vue-next";

const props = defineProps({
  content: {
    type: String,
    default: "",
  },
  position: {
    type: String,
    default: "top", // top, bottom, left, right
  },
});
</script>
