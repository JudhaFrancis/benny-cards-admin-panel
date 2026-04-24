<template>
  <Card
    class="bg-white border-slate-100 hover:shadow-lg transition-all duration-500 animate-in fade-in slide-in-from-bottom-4 group"
    :style="{ animationDelay: `${delay}ms` }"
  >
    <CardContent class="p-4 sm:p-5 lg:p-6">
      <div class="flex items-start justify-between">
        <div class="space-y-3">
          <p
            class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 leading-none"
          >
            {{ title }}
          </p>
          <p class="text-3xl font-bold text-slate-900 tracking-tight">
            {{ value }}
          </p>
          <div class="flex items-center gap-1.5">
            <span
              :class="
                cn(
                  'text-[10px] font-bold px-2 py-0.5 rounded-md border',
                  changeType === 'positive'
                    ? 'text-emerald-600 bg-emerald-50 border-emerald-100'
                    : 'text-rose-600 bg-rose-50 border-rose-100',
                )
              "
            >
              {{ change }}
            </span>
            <span
              class="text-[10px] font-medium text-slate-400 uppercase tracking-wider"
              >vs last month</span
            >
          </div>
        </div>
        <div
          :class="
            cn(
              'h-12 w-12 rounded-2xl flex items-center justify-center transition-all duration-300',
              'bg-slate-50 text-slate-400 group-hover:bg-primary group-hover:text-white group-hover:shadow-lg group-hover:shadow-primary/20 shadow-sm',
            )
          "
        >
          <component :is="icon" class="h-6 w-6" />
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import Card from "../ui/cards/Card.vue";
import CardContent from "../ui/cards/CardContent.vue";

const props = defineProps({
  title: String,
  value: String,
  change: String,
  changeType: String,
  icon: Object,
  delay: {
    type: Number,
    default: 0,
  },
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
