<template>
  <div class="space-y-8">
    <!-- Status Badge -->
    <div class="flex justify-center -mb-4">
      <div
        :class="[
          'px-6 py-2 rounded-2xl border-2 flex items-center gap-2 transition-all shadow-sm',
          isFullyPaid
            ? 'bg-emerald-50 border-emerald-500 text-emerald-700'
            : 'bg-amber-50 border-amber-500 text-amber-700',
        ]"
      >
        <div
          :class="[
            'w-2.5 h-2.5 rounded-full animate-pulse',
            isFullyPaid ? 'bg-emerald-500' : 'bg-amber-500',
          ]"
        ></div>
        <span class="text-sm font-black uppercase tracking-widest">{{
          isFullyPaid ? "Completed" : "Pending"
        }}</span>
      </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        class="relative overflow-hidden p-6 rounded-2xl bg-white border border-slate-200 shadow-sm transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-slate-50 text-slate-900 rounded-2xl shadow-inner">
            <BanknoteIcon class="h-6 w-6 text-slate-500" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5"
            >
              Total Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.total_amount || 0).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="relative overflow-hidden p-6 rounded-2xl bg-white border border-slate-200 shadow-sm transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-emerald-50 rounded-2xl shadow-inner">
            <CheckCircleIcon class="h-6 w-6 text-emerald-600" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-emerald-600/60 uppercase tracking-widest mb-0.5"
            >
              Paid Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.paid_amount || 0).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="relative overflow-hidden p-6 rounded-2xl bg-white border border-slate-200 shadow-sm transition-all hover:shadow-md hover:-translate-y-1"
      >
        <div class="relative z-10 flex items-center gap-4">
          <div class="p-3 bg-rose-50 rounded-2xl shadow-inner">
            <AlertCircleIcon class="h-6 w-6 text-rose-600" />
          </div>
          <div>
            <p
              class="text-[10px] font-black text-rose-600/60 uppercase tracking-widest mb-0.5"
            >
              Due Amount
            </p>
            <p class="text-2xl font-black text-slate-900 leading-tight">
              ₹{{ parseFloat(order.balance_due || 0).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments List -->
    <div class="space-y-6">
      <h4
        class="text-sm font-black uppercase tracking-widest text-slate-400 pl-2"
      >
        Payment Records
      </h4>

      <div v-if="paymentList.length > 0" class="grid grid-cols-1 gap-4">
        <div
          v-for="(payment, index) in paymentList"
          :key="index"
          class="relative group bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all hover:border-primary/20"
        >
          <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-6"
          >
            <div class="flex items-center gap-4">
              <div
                class="p-3 bg-slate-50 text-slate-400 rounded-2xl group-hover:bg-primary/5 group-hover:text-primary transition-colors"
              >
                <component
                  :is="getMethodIcon(payment.payment_method)"
                  class="h-6 w-6"
                />
              </div>
              <div class="space-y-1">
                <div class="flex items-center gap-3">
                  <span class="text-lg font-black text-slate-900"
                    >₹{{ parseFloat(payment.amount || 0).toFixed(2) }}</span
                  >
                  <span
                    class="px-2.5 py-0.5 bg-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-500 rounded-full"
                  >
                    {{ payment.payment_method || "N/A" }}
                  </span>
                </div>
                <div
                  class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider"
                >
                  <span class="flex items-center gap-1.5">
                    <CalendarIcon class="h-3.5 w-3.5" />
                    {{ formatDate(payment.payment_date) || "—" }}
                  </span>
                  <span
                    v-if="payment.transaction_id"
                    class="flex items-center gap-1.5"
                  >
                    <HashIcon class="h-3.5 w-3.5" />
                    {{ payment.transaction_id }}
                  </span>
                </div>
              </div>
            </div>

            <div
              v-if="payment.signature_name"
              class="flex items-center gap-3 px-4 py-2 bg-slate-50/50 rounded-xl border border-slate-100/50"
            >
              <UserIcon class="h-4 w-4 text-slate-400" />
              <div>
                <p
                  class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5"
                >
                  Signed By
                </p>
                <p class="text-xs font-bold text-slate-700">
                  {{ payment.signature_name }}
                </p>
              </div>
            </div>
          </div>

          <!-- Individual Audit info -->
          <div
            v-if="payment._audit"
            class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-end text-[10px] text-slate-400"
          >
            <span class="flex items-center gap-2">
              <ClockIcon class="h-3.5 w-3.5" />
              Last updated by
              <strong class="text-slate-600">{{
                payment._audit.updated_by
              }}</strong>
              on
              <span class="font-medium bg-slate-50 px-2 py-0.5 rounded-full">{{
                formatAuditDate(payment._audit.updated_at)
              }}</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="py-16 flex flex-col items-center justify-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200"
      >
        <div class="p-5 bg-white rounded-full shadow-sm mb-4">
          <BanknoteIcon class="h-10 w-10 text-slate-300" />
        </div>
        <p class="text-base font-bold text-slate-500">
          No payment records found in tracking.
        </p>
        <p class="text-sm text-slate-400 mt-1">
          Payment information will appear here once tracked.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Banknote as BanknoteIcon,
  CheckCircle as CheckCircleIcon,
  AlertCircle as AlertCircleIcon,
  CreditCard as CreditCardIcon,
  Hash as HashIcon,
  User as UserIcon,
  Calendar as CalendarIcon,
  Smartphone as SmartphoneIcon,
  Building2 as Building2Icon,
  QrCode as QrCodeIcon,
  ClipboardCheck as ClipboardCheckIcon,
  Wallet as WalletIcon,
  Globe as GlobeIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const paymentList = computed(() => {
  const info = props.order.tracking?.payment_info;
  if (!info) return [];

  // Check for new standard structure
  if (info.payments && Array.isArray(info.payments)) {
    return info.payments;
  }

  return Array.isArray(info) ? info : [];
});

const isFullyPaid = computed(() => {
  const total = parseFloat(props.order.total_amount || 0);
  const paid = parseFloat(props.order.paid_amount || 0);
  return total > 0 && Math.abs(total - paid) < 0.01;
});

const paymentMethods = [
  { value: "cash", icon: BanknoteIcon },
  { value: "card", icon: CreditCardIcon },
  { value: "upi", icon: SmartphoneIcon },
  { value: "net_banking", icon: GlobeIcon },
  { value: "qr_code", icon: QrCodeIcon },
  { value: "bank_transfer", icon: Building2Icon },
  { value: "cheque", icon: ClipboardCheckIcon },
  { value: "wallet", icon: WalletIcon },
];

const getMethodIcon = (methodValue) => {
  return (
    paymentMethods.find((m) => m.value === methodValue)?.icon || CreditCardIcon
  );
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>
