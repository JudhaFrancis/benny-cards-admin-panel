<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-[100]" @close="close">
      <TransitionChild
        as="template"
        enter="ease-in-out duration-500"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-500"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full"
          >
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-500"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-500"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div
                  class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl custom-scrollbar"
                >
                  <!-- Header with Tabs -->
                  <div
                    class="px-4 py-4 sm:px-6 bg-slate-50 border-b border-slate-100"
                  >
                    <div class="flex items-center justify-between">
                      <div>
                        <DialogTitle
                          class="text-base font-bold leading-6 text-slate-900"
                        >
                          Notifications
                        </DialogTitle>
                      </div>
                      <div class="ml-3 flex h-7 items-center">
                        <button
                          type="button"
                          class="relative rounded-lg p-1 bg-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition-colors focus:outline-none"
                          @click.stop="close"
                        >
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                      </div>
                    </div>

                    <!-- Navigation Tabs (Hidden for now) -->
                    <div v-if="false" class="flex items-center gap-2 mt-4">
                      <button
                        @click="activeTab = 'notifications'"
                        :class="[
                          'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm',
                          activeTab === 'notifications'
                            ? 'bg-primary text-white shadow-primary/20'
                            : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
                        ]"
                      >
                        <BellIcon class="h-3.5 w-3.5" />
                        <span>Work Assignments</span>
                        <span
                          v-if="workUnreadCount > 0"
                          :class="[
                            'ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none transition-colors',
                            activeTab === 'notifications'
                              ? 'bg-white/25 text-white'
                              : 'bg-rose-50 text-rose-600 border border-rose-200'
                          ]"
                        >
                          {{ workUnreadCount }}
                        </span>
                      </button>

                      <!-- WhatsApp Logs Tab (Hidden for now) -->
                      <button
                        v-if="false"
                        @click="activeTab = 'whatsapp'"
                        :class="[
                          'flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm',
                          activeTab === 'whatsapp'
                            ? 'bg-primary text-white shadow-primary/20'
                            : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
                        ]"
                      >
                        <MessageSquareIcon class="h-3.5 w-3.5" />
                        <span>WhatsApp Logs</span>
                        <span
                          v-if="totalWhatsappCount > 0"
                          :class="[
                            'ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none transition-colors',
                            activeTab === 'whatsapp'
                              ? 'bg-white/25 text-white'
                              : 'bg-slate-100 text-slate-600 border border-slate-200'
                          ]"
                        >
                          {{ totalWhatsappCount }}
                        </span>
                      </button>
                    </div>
                  </div>

                  <!-- ============================================== -->
                  <!-- TAB 1: WORK ASSIGNMENT NOTIFICATIONS -->
                  <!-- ============================================== -->
                  <div v-if="activeTab === 'notifications'" class="flex-1 flex flex-col">
                    <!-- Search & Filter Bar -->
                    <div class="px-4 py-3 sm:px-6 border-b border-slate-100 bg-white space-y-2.5">
                      <div class="relative group">
                        <SearchIcon
                          class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                        />
                        <input
                          v-model="workSearch"
                          type="text"
                          placeholder="Search orders, stages, assigned by..."
                          class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all shadow-sm"
                        />
                      </div>

                      <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-1.5">
                          <button
                            v-for="filter in ['all', 'unread', 'read']"
                            :key="filter"
                            @click="workFilter = filter"
                            :class="[
                              'px-2.5 py-1 rounded-lg text-[11px] font-bold capitalize transition-all',
                              workFilter === filter
                                ? 'bg-slate-900 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            ]"
                          >
                            {{ filter }}
                          </button>
                        </div>

                        <button
                          v-if="workUnreadCount > 0"
                          @click="markAllAsRead"
                          :disabled="markingAllRead"
                          class="text-[11px] font-bold text-primary hover:underline flex items-center gap-1 disabled:opacity-50"
                        >
                          <CheckCheckIcon class="h-3.5 w-3.5" />
                          <span>Mark all read</span>
                        </button>
                      </div>
                    </div>

                    <!-- Work Notifications List -->
                    <div class="relative flex-1 px-4 py-4 sm:px-6">
                      <!-- Loading Skeleton -->
                      <div v-if="workLoading" class="space-y-3">
                        <div
                          v-for="i in 4"
                          :key="i"
                          class="animate-pulse bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-3"
                        >
                          <div class="flex justify-between items-center">
                            <div class="h-4 bg-slate-200 rounded w-24"></div>
                            <div class="h-4 bg-slate-200 rounded w-16"></div>
                          </div>
                          <div class="h-3 bg-slate-200 rounded w-full"></div>
                          <div class="h-2 bg-slate-200 rounded w-1/2"></div>
                        </div>
                      </div>

                      <!-- Empty State -->
                      <div
                        v-else-if="filteredWorkNotifications.length === 0"
                        class="text-center py-16 px-4"
                      >
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                          <BellOffIcon class="h-6 w-6" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">No Notifications</h4>
                        <p class="text-xs text-slate-400 mt-1">
                          {{ workFilter === 'unread' ? 'You have caught up with all assignments!' : 'No assignment records found.' }}
                        </p>
                      </div>

                      <!-- List -->
                      <ul v-else class="space-y-3 pb-20">
                        <li
                          v-for="item in filteredWorkNotifications"
                          :key="item.stage_key + '-' + item.id"
                          @click="openOrderStage(item)"
                          :class="[
                            'group relative rounded-xl p-3 border transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md hover:border-primary/30',
                            item.notification_status === 'unread'
                              ? 'bg-rose-50/25 border-rose-100/80 hover:bg-rose-50/40'
                              : 'bg-white border-slate-100 hover:bg-slate-50/50'
                          ]"
                        >
                          <!-- Card Header: Dot + Stage Badge on Left, Date on Right -->
                          <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                              <span
                                :class="[
                                  'h-2 w-2 rounded-full shrink-0',
                                  item.notification_status === 'unread'
                                    ? 'bg-rose-500 animate-pulse'
                                    : 'bg-emerald-500',
                                ]"
                              ></span>
                              <span
                                :class="[
                                  'px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wider',
                                  getStageBadgeClass(item.stage_key)
                                ]"
                              >
                                {{ item.stage_name }}
                              </span>
                            </div>

                            <span class="text-xs text-slate-400 shrink-0">
                              {{ item.assigned_date }}
                            </span>
                          </div>

                          <!-- Message Content -->
                          <p class="text-xs text-slate-600 mb-2 leading-relaxed">
                            {{ item.message }}
                          </p>

                          <!-- Card Footer: Assigner on Left, Read/Unread Pill at Bottom Right -->
                          <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100/80">
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                              <UserIcon class="h-3 w-3 text-slate-400" />
                              <span>By <strong class="text-slate-700 font-semibold">{{ item.assigned_by }}</strong></span>
                            </div>

                            <!-- Notification Status Pill -->
                            <button
                              type="button"
                              @click.stop="toggleWorkNotificationStatus(item)"
                              :class="[
                                'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border transition-colors cursor-pointer',
                                item.notification_status === 'unread'
                                  ? 'bg-rose-100 text-rose-700 border-rose-200 hover:bg-rose-200'
                                  : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                              ]"
                              :title="item.notification_status === 'unread' ? 'Click to mark as Read' : 'Click to mark as Unread'"
                            >
                              <span
                                class="w-1.5 h-1.5 rounded-full mr-1.5"
                                :class="item.notification_status === 'unread' ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'"
                              ></span>
                              {{ item.notification_status === 'unread' ? 'Unread' : 'Read' }}
                            </button>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <!-- ============================================== -->
                  <!-- TAB 2: WHATSAPP LOGS (EXISTING) -->
                  <!-- ============================================== -->
                  <div v-else class="flex-1 flex flex-col">
                    <!-- Filter & Search -->
                    <div class="px-4 py-4 sm:px-6 border-b border-slate-50 bg-white">
                      <div class="relative group">
                        <SearchIcon
                          class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                        />
                        <input
                          v-model="search"
                          type="text"
                          placeholder="Search WhatsApp logs..."
                          class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all shadow-sm"
                        />
                      </div>
                    </div>

                    <div class="relative mt-4 flex-1 px-4 sm:px-6">
                      <!-- Logs List -->
                      <div v-if="loading" class="space-y-4">
                        <div
                          v-for="i in 5"
                          :key="i"
                          class="animate-pulse flex space-x-4"
                        >
                          <div class="rounded-full bg-slate-200 h-10 w-10"></div>
                          <div class="flex-1 space-y-2 py-1">
                            <div class="h-2 bg-slate-200 rounded"></div>
                            <div class="space-y-1">
                              <div class="grid grid-cols-3 gap-4">
                                <div class="h-2 bg-slate-200 rounded col-span-2"></div>
                                <div class="h-2 bg-slate-200 rounded col-span-1"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div
                        v-else-if="logs.length === 0"
                        class="text-center py-10"
                      >
                        <p class="text-slate-500 text-sm">No WhatsApp logs found.</p>
                      </div>

                      <ul v-else class="space-y-4 pb-20">
                        <li
                          v-for="log in logs"
                          :key="log.id"
                          class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow"
                        >
                          <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-2">
                              <span
                                :class="[
                                  'h-2 w-2 rounded-full',
                                  log.status === 'sent'
                                    ? 'bg-emerald-500'
                                    : 'bg-rose-500',
                                ]"
                              ></span>
                              <span class="font-bold text-sm text-slate-800">{{
                                log.recipient_mobile_no
                              }}</span>
                            </div>
                            <span class="text-xs text-slate-400">{{
                              formatDate(log.updated_at)
                            }}</span>
                          </div>
                          <p class="text-xs text-slate-600 mb-2 line-clamp-2">
                            {{ log.message }}
                          </p>

                          <div
                            class="flex justify-between items-center mt-2 pt-2 border-t border-slate-50"
                          >
                            <span
                              class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded uppercase font-bold"
                              >{{ log.status }}</span
                            >
                            <button
                              @click="openResendConfirm(log)"
                              :disabled="resendingId === log.id"
                              class="text-primary text-xs font-semibold hover:underline disabled:opacity-50 flex items-center gap-1"
                            >
                              <span
                                v-if="resendingId === log.id"
                                class="animate-spin h-3 w-3 border-2 border-primary border-t-transparent rounded-full"
                              ></span>
                              <RefreshCwIcon v-else class="h-3 w-3" />
                              <span>Resend</span>
                            </button>
                          </div>
                        </li>
                      </ul>

                      <!-- Pagination Controls for WhatsApp Logs -->
                      <div
                        v-if="pagination && pagination.last_page > 1"
                        class="flex justify-between items-center py-4 border-t border-slate-100 bg-white"
                      >
                        <button
                          :disabled="pagination.current_page <= 1"
                          @click="fetchLogs(pagination.current_page - 1)"
                          class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 disabled:opacity-50 hover:bg-slate-50"
                        >
                          Previous
                        </button>
                        <span class="text-xs text-slate-500">
                          Page {{ pagination.current_page }} of {{ pagination.last_page }}
                        </span>
                        <button
                          :disabled="pagination.current_page >= pagination.last_page"
                          @click="fetchLogs(pagination.current_page + 1)"
                          class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 disabled:opacity-50 hover:bg-slate-50"
                        >
                          Next
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Resend Confirmation Modal -->
  <ConfirmationModal
    :is-open="isConfirmOpen"
    title="Resend WhatsApp Message"
    :message="`Are you sure you want to resend this message to ${logToResend?.recipient_mobile_no}?`"
    confirm-text="Resend"
    variant="primary"
    :loading="resendingId !== null"
    @close="isConfirmOpen = false"
    @confirm="confirmResend"
  />
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  Search as SearchIcon,
  RefreshCw as RefreshCwIcon,
  Bell as BellIcon,
  BellOff as BellOffIcon,
  MessageSquare as MessageSquareIcon,
  CheckCheck as CheckCheckIcon,
  Clock as ClockIcon,
  User as UserIcon,
} from "lucide-vue-next";
import ConfirmationModal from "../ui/modals/ConfirmationModal.vue";
import { useToast } from "../../composables/useToast";

const toast = useToast();
const router = useRouter();

const props = defineProps({
  isOpen: Boolean,
  whatsappCount: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(["close", "refresh-count"]);

// Active Tab
const activeTab = ref("notifications"); // 'notifications' | 'whatsapp'

const totalWhatsappCount = computed(() => {
  return pagination.value?.total ?? props.whatsappCount ?? 0;
});

// ==========================================
// WORK ASSIGNMENTS STATE & LOGIC
// ==========================================
const workNotifications = ref([]);
const workUnreadCount = ref(0);
const workLoading = ref(false);
const workSearch = ref("");
const workFilter = ref("all"); // 'all' | 'unread' | 'read'
const markingAllRead = ref(false);

const fetchWorkNotifications = async () => {
  workLoading.value = true;
  try {
    const response = await axios.get("/api/v1/work-notifications?limit=30");
    if (response.data.success) {
      workNotifications.value = response.data.data.notifications || [];
      workUnreadCount.value = response.data.data.unread_count || 0;
      emit("refresh-count", workUnreadCount.value);
    }
  } catch (error) {
    console.error("Failed to fetch work notifications", error);
    toast.error(error.response?.data?.message || "Failed to load notifications");
  } finally {
    workLoading.value = false;
  }
};

const filteredWorkNotifications = computed(() => {
  let list = workNotifications.value;

  // Filter by read/unread
  if (workFilter.value === "unread") {
    list = list.filter((n) => n.notification_status === "unread");
  } else if (workFilter.value === "read") {
    list = list.filter((n) => n.notification_status === "read");
  }

  // Filter by search query
  if (workSearch.value.trim()) {
    const q = workSearch.value.toLowerCase();
    list = list.filter(
      (n) =>
        n.order_number?.toLowerCase().includes(q) ||
        n.stage_name?.toLowerCase().includes(q) ||
        n.assigned_by?.toLowerCase().includes(q) ||
        n.message?.toLowerCase().includes(q)
    );
  }

  return list;
});

const toggleWorkNotificationStatus = async (item) => {
  const previousStatus = item.notification_status || "unread";
  const newStatus = previousStatus === "read" ? "unread" : "read";
  item.notification_status = newStatus;

  if (newStatus === "read" && workUnreadCount.value > 0) {
    workUnreadCount.value--;
  } else if (newStatus === "unread") {
    workUnreadCount.value++;
  }
  emit("refresh-count", workUnreadCount.value);

  try {
    const res = await axios.post(`/api/v1/work-notifications/${item.stage_key}/${item.id}/${newStatus}`);
    if (res.data?.success) {
      toast.success(newStatus === "read" ? "Marked as read" : "Marked as unread");
    } else {
      throw new Error(res.data?.message || "Failed to update status");
    }
  } catch (e) {
    console.error("Failed to toggle notification status", e);
    // Revert optimistic update
    item.notification_status = previousStatus;
    if (newStatus === "read") {
      workUnreadCount.value++;
    } else {
      workUnreadCount.value = Math.max(0, workUnreadCount.value - 1);
    }
    emit("refresh-count", workUnreadCount.value);
    toast.error(e.response?.data?.message || "Failed to update notification status");
  }
};

const markAllAsRead = async () => {
  markingAllRead.value = true;
  try {
    const response = await axios.post("/api/v1/work-notifications/mark-all-read");
    if (response.data.success) {
      workNotifications.value.forEach((n) => (n.notification_status = "read"));
      workUnreadCount.value = 0;
      emit("refresh-count", 0);
      toast.success("All assignments marked as read!");
    } else {
      toast.error(response.data?.message || "Failed to mark all as read");
    }
  } catch (e) {
    console.error("Failed to mark all as read", e);
    toast.error(e.response?.data?.message || "Failed to mark all as read");
  } finally {
    markingAllRead.value = false;
  }
};

const openOrderStage = async (item) => {
  // Mark as read if currently unread
  if (item.notification_status === "unread") {
    item.notification_status = "read";
    if (workUnreadCount.value > 0) {
      workUnreadCount.value--;
      emit("refresh-count", workUnreadCount.value);
    }
    axios.post(`/api/v1/work-notifications/${item.stage_key}/${item.id}/read`).catch(() => {});
  }

  // Close drawer and navigate directly to this specific order
  close();
  if (item.route) {
    router.push({
      path: item.route,
      query: { search: item.order_number }
    });
  }
};

const getStageBadgeClass = (stageKey) => {
  switch (stageKey) {
    case "client_information":
      return "bg-teal-50 text-teal-700 border-teal-200";
    case "designing":
      return "bg-blue-50 text-blue-700 border-blue-200";
    case "printing":
      return "bg-amber-50 text-amber-700 border-amber-200";
    case "packaging":
      return "bg-purple-50 text-purple-700 border-purple-200";
    case "delivery":
      return "bg-emerald-50 text-emerald-700 border-emerald-200";
    default:
      return "bg-slate-50 text-slate-700 border-slate-200";
  }
};

// ==========================================
// WHATSAPP LOGS STATE & LOGIC (EXISTING)
// ==========================================
const logs = ref([]);
const loading = ref(false);
const search = ref("");
const pagination = ref(null);
const resendingId = ref(null);
const isConfirmOpen = ref(false);
const logToResend = ref(null);
const searchTimer = ref(null);

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/whatsapp-logs", {
      params: {
        page,
        search: search.value,
      },
    });
    if (response.data.success) {
      logs.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch whatsapp logs", error);
  } finally {
    loading.value = false;
  }
};

// Search watcher with debounce for whatsapp
watch(search, () => {
  clearTimeout(searchTimer.value);
  searchTimer.value = setTimeout(() => {
    fetchLogs(1);
  }, 500);
});

// Fetch both when opened
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      fetchWorkNotifications();
      fetchLogs();
    }
  },
  { immediate: true }
);

const openResendConfirm = (log) => {
  logToResend.value = log;
  isConfirmOpen.value = true;
};

const confirmResend = async () => {
  if (!logToResend.value) return;

  const log = logToResend.value;
  resendingId.value = log.id;

  try {
    const response = await axios.post(`/api/v1/whatsapp-logs/resend/${log.id}`);
    if (response.data.success) {
      await fetchLogs(pagination.value?.current_page || 1);
      toast.success(response.data.message || "Message resent successfully");
    } else {
      toast.error(response.data.message || "Failed to resend message.");
    }
  } catch (error) {
    console.error("Failed to resend log", error);
    toast.error("An error occurred while resending the message.");
  } finally {
    isConfirmOpen.value = false;
    resendingId.value = null;
    logToResend.value = null;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.4);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 0.6);
}
</style>
