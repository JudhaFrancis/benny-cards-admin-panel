<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-[#475569]/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translateY(20px)"
            enter-to="opacity-100 scale-100 translateY(0)"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translateY(0)"
            leave-to="opacity-0 scale-95 translateY(20px)"
          >
            <DialogPanel
              class="w-full max-w-xl transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all border border-slate-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Sticky Header -->
              <div
                class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-white sticky top-0 z-10"
              >
                <DialogTitle
                  as="h3"
                  class="text-2xl font-bold text-[#475569] tracking-tight"
                >
                  {{ isEdit ? "Edit User" : "Add New User" }}
                </DialogTitle>
                <button
                  @click="handleClose"
                  class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 transition-all active:scale-95"
                >
                  <XIcon class="h-6 w-6" />
                </button>
              </div>

              <!-- Scrollable Content -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form
                  @submit.prevent="handleSubmit"
                  id="userForm"
                  class="space-y-10"
                >
                  <!-- Validation Errors -->
                  <div
                    v-if="errors"
                    class="bg-rose-50 p-4 rounded-2xl border border-rose-100 animate-in fade-in zoom-in duration-300"
                  >
                    <ul
                      class="list-disc list-inside text-sm text-rose-600 space-y-1"
                    >
                      <li v-for="(error, field) in errors" :key="field">
                        {{ error[0] }}
                      </li>
                    </ul>
                  </div>

                  <InfoSection title="Primary Identity" columns="1">
                    <div class="space-y-6">
                      <!-- Name -->
                      <div class="space-y-2">
                        <label
                          class="text-[11px] font-bold tracking-wider text-slate-500 uppercase ml-1"
                          >Full Name <span class="text-rose-500">*</span></label
                        >
                        <div class="relative group">
                          <UserIcon
                            class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors"
                          />
                          <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="John Doe"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-transparent focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 rounded-xl outline-none transition-all text-sm font-medium"
                            style="color: #000000"
                          />
                        </div>
                      </div>

                      <!-- Email -->
                      <div class="space-y-2">
                        <label
                          class="text-[11px] font-bold tracking-wider text-slate-500 uppercase ml-1"
                          >Email Address <span class="text-rose-500">*</span></label
                        >
                        <div class="relative group">
                          <MailIcon
                            class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors"
                          />
                          <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="john@example.com"
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-transparent focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 rounded-xl outline-none transition-all text-sm font-medium"
                            style="color: #000000"
                          />
                        </div>
                      </div>
                    </div>
                  </InfoSection>

                  <InfoSection title="Access & Status" columns="2">
                    <!-- Role -->
                    <div class="space-y-2">
                      <ContextDropdown
                        v-model="form.role_id"
                        label="System Role *"
                        :icon="ShieldIcon"
                        :options="roleOptions"
                      />
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                      <ContextDropdown
                        v-model="form.status"
                        label="Current Status"
                        :icon="ActivityIcon"
                        :options="statusOptions"
                      />
                    </div>
                  </InfoSection>

                  <!-- Branch access: which branches' enquiries and orders this user sees -->
                  <InfoSection v-if="!isCustomerRole" title="Branch Access" columns="1">
                    <div class="space-y-3">
                      <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold tracking-wider text-slate-500 uppercase ml-1">
                          Assigned Branches
                          <span v-if="!isEdit" class="text-rose-500">*</span>
                          <span class="text-[10px] text-slate-400 ml-1 normal-case">({{ form.branch_ids.length }} of {{ branchOptions.length }})</span>
                        </label>
                        <button type="button" class="text-xs font-semibold text-primary hover:underline" @click="toggleAllBranches">
                          {{ form.branch_ids.length === branchOptions.length ? "Clear all" : "Select all" }}
                        </button>
                      </div>
                      <div class="flex flex-wrap gap-2">
                        <button
                          v-for="b in branchOptions"
                          :key="b.value"
                          type="button"
                          @click="toggleBranch(b.value)"
                          class="px-3.5 py-2 rounded-xl border text-sm font-semibold transition-all"
                          :class="form.branch_ids.includes(b.value)
                            ? 'bg-primary text-white border-primary shadow-sm'
                            : branchError
                              ? 'bg-rose-50 text-slate-600 border-rose-300'
                              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300'"
                        >
                          {{ b.label }}
                        </button>
                      </div>
                      <p v-if="!form.branch_ids.length" class="text-xs text-rose-500 ml-1" :class="branchError ? 'font-semibold' : ''">
                        {{ isEdit ? "No branch selected: this user will see no enquiries or orders." : "Select at least one branch." }}
                      </p>
                    </div>
                  </InfoSection>

                  <InfoSection title="Security Credentials" columns="2">
                    <!-- Password -->
                    <div class="space-y-2">
                      <label
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase ml-1"
                      >
                        {{ isEdit ? "New Password" : "Password" }}
                        <span
                          v-if="isEdit"
                          class="text-[10px] text-slate-400 ml-1 text-nowrap"
                          >(Optional)</span
                        >
                      </label>
                      <div class="relative group">
                        <LockIcon
                          class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors"
                        />
                        <input
                          v-model="form.password"
                          type="password"
                          :required="!isEdit || !!form.password_confirmation"
                          placeholder="••••••••"
                          class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-transparent focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 rounded-xl outline-none transition-all text-sm font-medium"
                          style="color: #000000"
                        />
                      </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-2">
                      <label
                        class="text-[11px] font-bold tracking-wider text-slate-500 uppercase ml-1 text-nowrap"
                        >Confirm Password</label
                      >
                      <div class="relative group">
                        <LockIcon
                          class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors"
                        />
                        <input
                          v-model="form.password_confirmation"
                          type="password"
                          :required="!isEdit || !!form.password"
                          placeholder="••••••••"
                          class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-transparent focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 rounded-xl outline-none transition-all text-sm font-medium"
                          style="color: #000000"
                        />
                      </div>
                    </div>
                  </InfoSection>
                </form>
              </div>

              <!-- Sticky Footer -->
              <div
                class="px-8 py-6 border-t border-slate-50 bg-slate-50/50 sticky bottom-0 z-10 flex justify-end gap-3"
              >
                <button
                  type="button"
                  @click="handleClose"
                  class="px-8 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  form="userForm"
                  type="submit"
                  :disabled="loading"
                  class="px-8 py-3.5 rounded-2xl bg-primary text-white font-bold hover:opacity-90 transition-all active:scale-95 shadow-xl shadow-primary/20 flex items-center gap-2 disabled:opacity-50"
                >
                  <Loader2Icon v-if="loading" class="h-5 w-5 animate-spin" />
                  {{
                    loading
                      ? "Saving..."
                      : isEdit
                        ? "Update User"
                        : "Create User"
                  }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";
import {
  X as XIcon,
  User as UserIcon,
  Mail as MailIcon,
  Lock as LockIcon,
  Loader2 as Loader2Icon,
  Shield as ShieldIcon,
  Activity as ActivityIcon,
} from "lucide-vue-next";
import axios from "axios";
import InfoSection from "../../components/ui/display/InfoSection.vue";
import ContextDropdown from "../../components/ui/dropdowns/ContextDropdown.vue";
import { useToast } from "../../composables/useToast";
import { useBranches } from "../../composables/useBranches";

const { branchOptions: allBranchOptions, fetchBranches } = useBranches();
// Ids as numbers so they match users.branch_ids
const branchOptions = computed(() => allBranchOptions.value.map((b) => ({ ...b, value: Number(b.value) })));

const props = defineProps({
  isOpen: Boolean,
  editUser: Object,
});

const emit = defineEmits(["close", "refresh"]);

const { success, error: toastError } = useToast();
const loading = ref(false);
const errors = ref(null);
const isEdit = computed(() => !!props.editUser);
const roleOptions = ref([]);

const form = ref({
  name: "",
  email: "",
  role_id: null,
  status: "active",
  password: "",
  password_confirmation: "",
  photo: null,
});

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      errors.value = null;
      if (props.editUser) {
        form.value = {
          name: props.editUser.name,
          email: props.editUser.email,
          role_id: props.editUser.role_id,
          status: props.editUser.status,
          branch_ids: (props.editUser.branch_ids || []).map(Number),
          password: "",
          password_confirmation: "",
          photo: props.editUser.photo,
        };
      } else {
        form.value = {
          name: "",
          email: "",
          role_id: null,
          status: "active",
          branch_ids: [],
          password: "",
          password_confirmation: "",
          photo: null,
        };
      }
    }
  },
  { immediate: true },
);

const roleName = computed(() =>
  (roleOptions.value.find((r) => r.value === form.value.role_id)?.label || "").toLowerCase(),
);
// The customer "User" role never has branches
const isCustomerRole = computed(() => roleName.value === "user");

// New user with Admin / Super-admin role: start with every branch selected (can still be changed).
// Editing never changes branches on a role switch.
watch(roleName, (name) => {
  if (!isEdit.value && ["admin", "super-admin"].includes(name) && !form.value.branch_ids.length) {
    form.value.branch_ids = branchOptions.value.map((b) => b.value);
  }
});

const branchError = ref(false);
watch(() => form.value.branch_ids.length, (n) => { if (n) branchError.value = false; });
watch(() => props.isOpen, () => { branchError.value = false; });

const toggleBranch = (id) => {
  const ids = form.value.branch_ids;
  form.value.branch_ids = ids.includes(id) ? ids.filter((x) => x !== id) : [...ids, id];
};
const toggleAllBranches = () => {
  form.value.branch_ids = form.value.branch_ids.length === branchOptions.value.length
    ? []
    : branchOptions.value.map((b) => b.value);
};

const handleClose = () => {
  if (!loading.value) {
    emit("close");
  }
};

const handleSubmit = async () => {
  if (!form.value.name?.trim() || !form.value.email?.trim()) {
    toastError("Full name and email address are required.");
    return;
  }
  if (!form.value.role_id) {
    toastError("Select a system role.");
    return;
  }
  // New users (except the customer "User" role) need at least one branch
  if (!isEdit.value && !isCustomerRole.value && !form.value.branch_ids.length) {
    branchError.value = true;
    toastError("Select at least one branch.");
    return;
  }

  loading.value = true;
  errors.value = null;

  try {
    if (isEdit.value) {
      await axios.put(`/api/v1/users/${props.editUser.id}`, form.value);
    } else {
      await axios.post("/api/v1/users", form.value);
    }

    success(
      isEdit.value ? "User updated successfully" : "User created successfully",
    );
    emit("refresh");
    emit("close");
  } catch (e) {
    if (e.response?.data?.errors) {
      errors.value = e.response.data.errors;
      toastError("Please correct the errors in the form.");
    } else {
      console.error("Save failed", e);
      toastError(
        e.response?.data?.message || "Something went wrong. Please try again.",
      );
    }
    emit("close");
  } finally {
    loading.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get("/api/v1/users/roles");
    if (response.data.success) {
      roleOptions.value = response.data.data.map((role) => {
        // Map to existing rich styles if matching names
        const name = role.name.toLowerCase();
        let richProps = {};

        if (name === "super-admin" || name === "admin") {
          richProps = {
            description:
              "Full access to all system settings and user management.",
            badge: "High Privilege",
            badgeClass: "bg-rose-100 text-rose-600",
          };
        } else if (name === "staff") {
          richProps = {
            description:
              "Can manage orders, view customer data, and process shipments.",
            badge: "Operational",
            badgeClass: "bg-blue-100 text-blue-600",
          };
        } else if (name === "moderator") {
          richProps = {
            description: "Can manage user comments and community interactions.",
            badge: "Limited",
            badgeClass: "bg-amber-100 text-amber-600",
          };
        } else if (name === "user") {
          richProps = {
            description: "Basic account with access to personal profile.",
            badge: "Default",
            badgeClass: "bg-slate-100 text-slate-600",
          };
        }

        return {
          label: role.name,
          value: role.id,
          ...richProps,
        };
      });
    }
  } catch (e) {
    console.error("Failed to fetch roles", e);
  }
};

onMounted(() => {
  fetchRoles();
  fetchBranches();
});

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "User can access the system and perform all assigned tasks.",
    badge: "Enabled",
    badgeClass: "bg-emerald-100 text-emerald-600",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "User account is suspended and cannot access the system.",
    badge: "Disabled",
    badgeClass: "bg-slate-100 text-slate-600",
  },
];
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}

/* Fix autofill text color visibility */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-text-fill-color: #475569 !important;
  -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
  transition: background-color 5000s ease-in-out 0s;
}

input:focus:-webkit-autofill,
input:focus:-webkit-autofill:hover,
input:focus:-webkit-autofill:focus,
input:focus:-webkit-autofill:active {
  -webkit-text-fill-color: #475569 !important;
  -webkit-box-shadow: 0 0 0 30px #ffffff inset !important;
}

/* Force input text color */
input[type="text"],
input[type="email"],
input[type="password"] {
  color: #475569 !important;
}
</style>
