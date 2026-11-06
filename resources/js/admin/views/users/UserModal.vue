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
                          >Full Name</label
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
                          >Email Address</label
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
                        label="System Role"
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

const props = defineProps({
  isOpen: Boolean,
  editUser: Object,
});

const emit = defineEmits(["close", "refresh"]);

const { success, error: toastError } = useToast();
const loading = ref(false);
const errors = ref(null);
const isEdit = computed(() => !!props.editUser);

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
          password: "",
          password_confirmation: "",
          photo: null,
        };
      }
    }
  },
  { immediate: true },
);

const handleClose = () => {
  if (!loading.value) {
    emit("close");
  }
};

const handleSubmit = async () => {
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

const roleOptions = ref([]);

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

onMounted(fetchRoles);

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
