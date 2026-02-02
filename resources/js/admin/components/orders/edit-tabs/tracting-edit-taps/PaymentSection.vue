<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        class="p-6 rounded-[2rem] bg-slate-50 border border-slate-100 text-center"
      >
        <p
          class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2"
        >
          Total Amount
        </p>
        <p class="text-2xl font-black text-slate-900">
          ${{ order.amount?.toFixed(2) || "0.00" }}
        </p>
      </div>
      <div
        class="p-6 rounded-[2rem] bg-emerald-50 border border-emerald-100 text-center"
      >
        <p
          class="text-[10px] font-bold text-emerald-600/60 uppercase tracking-[0.2em] mb-2"
        >
          Advance
        </p>
        <p class="text-2xl font-black text-emerald-600">
          ${{ order.paidAmount?.toFixed(2) || "0.00" }}
        </p>
      </div>
      <div
        class="p-6 rounded-[2rem] bg-rose-50 border border-rose-100 text-center"
      >
        <p
          class="text-[10px] font-bold text-rose-600/60 uppercase tracking-[0.2em] mb-2"
        >
          Balance
        </p>
        <p class="text-2xl font-black text-rose-600">
          ${{ ((order.amount || 0) - (order.paidAmount || 0)).toFixed(2) }}
        </p>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Payment Via</label
      >
      <select
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
      >
        <option value="">Select payment method</option>
        <option value="cash">Cash</option>
        <option value="upi">UPI / GPay</option>
        <option value="card">Card Payment</option>
        <option value="bank">Bank Transfer</option>
      </select>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Transaction ID</label
      >
      <input
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        placeholder="Reference #"
      />
    </div>

    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Signature & Name</label
      >
      <input
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        placeholder="Enter name"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);
</script>
