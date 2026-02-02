<template>
  <div
    class="min-h-screen flex bg-[#FDFCFB] text-slate-900 overflow-hidden font-sans"
  >
    <!-- Left Side: Visual Hero (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-slate-900">
      <img
        src="https://images.unsplash.com/photo-1607344645866-009c320b63e0?q=80&w=2000&auto=format&fit=crop"
        alt="SaaS Order Management"
        class="absolute inset-0 w-full h-full object-cover opacity-80"
      />
      <div
        class="absolute inset-0 bg-gradient-to-tr from-slate-900/80 via-transparent to-transparent"
      ></div>

      <div
        class="relative z-10 flex flex-col justify-end p-16 h-full text-white"
      >
        <div
          class="max-w-md animate-in fade-in slide-in-from-left-8 duration-1000"
        >
          <div class="w-12 h-1 bg-primary mb-8 rounded-full"></div>
          <h2 class="text-5xl font-serif font-light leading-tight mb-6">
            The Art of <br />
            <span class="italic text-primary-light font-medium text-primary"
              >Management</span
            >
          </h2>
          <p class="text-slate-300 text-lg font-light leading-relaxed">
            Elevating order management for owners and staff. Seamlessly handle
            operations with elegance and precision.
          </p>
        </div>
      </div>
    </div>

    <!-- Right Side: Login Form -->
    <div
      class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-12 lg:p-16 relative"
    >
      <!-- Animated background shapes for mobile/subtle desktop feel -->
      <div
        class="absolute inset-0 z-0 overflow-hidden pointer-events-none opacity-40"
      >
        <div
          class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] rounded-full blur-[100px] bg-primary/5"
        ></div>
        <div
          class="absolute -bottom-[10%] -left-[10%] w-[400px] h-[400px] rounded-full blur-[80px] bg-primary/5"
        ></div>
      </div>

      <div class="w-full max-w-[420px] relative z-10">
        <!-- Brand Header -->
        <div
          class="mb-12 animate-in fade-in slide-in-from-bottom-4 duration-700"
        >
          <div
            class="flex flex-col items-center text-center lg:text-left gap-4 mb-8"
          >
            <div
              class="p-4 rounded-[2rem] bg-white border border-slate-100 shadow-xl shadow-primary/5 overflow-hidden flex items-center justify-center w-24 h-24 mb-2 group hover:scale-105 transition-transform duration-500"
            >
              <img
                v-if="companyLogo"
                :src="getImageSource(companyLogo)"
                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700"
                alt="Logo"
              />
              <ScrollTextIcon
                v-else
                class="h-10 w-10 text-primary group-hover:rotate-12 transition-transform duration-500"
                style="color: #3cc0c2"
              />
            </div>
            <div class="flex flex-col items-center w-full">
              <span
                class="text-sm font-bold uppercase tracking-[0.3em] text-primary mb-1 block"
              >
                {{ companyName || "Agoo Order" }}
              </span>
              <h1 class="text-4xl font-bold text-slate-900 leading-tight">
                Management Portal
              </h1>
            </div>
          </div>
        </div>

        <!-- Login Form -->
        <form
          @submit.prevent="handleLogin"
          class="space-y-7 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-150"
        >
          <div
            v-if="errorMessage"
            class="bg-rose-50 border border-rose-100 text-rose-600 text-sm py-4 px-5 rounded-2xl flex items-center gap-3 shadow-sm"
          >
            <AlertCircleIcon class="h-5 w-5 shrink-0" />
            {{ errorMessage }}
          </div>

          <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700 ml-1"
              >Email Address</label
            >
            <div class="relative group">
              <MailIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors duration-300"
              />
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="Enter your email"
                class="w-full rounded-2xl py-4 pl-12 pr-4 text-slate-900 bg-white border border-slate-200 shadow-sm outline-none transition-all duration-300 focus:border-primary focus:ring-4 focus:ring-primary/5 placeholder:text-slate-400"
              />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center ml-1">
              <label class="text-sm font-semibold text-slate-700"
                >Password</label
              >
            </div>
            <div class="relative group">
              <LockIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors duration-300"
              />
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="Enter your password"
                class="w-full rounded-2xl py-4 pl-12 pr-4 text-slate-900 bg-white border border-slate-200 shadow-sm outline-none transition-all duration-300 focus:border-primary focus:ring-4 focus:ring-primary/5 placeholder:text-slate-400"
              />
            </div>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-slate-900 hover:bg-slate-800 text-white h-16 rounded-2xl font-bold text-lg shadow-xl shadow-slate-200 active:scale-[0.99] transition-all duration-300 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-3 relative overflow-hidden group"
            >
              <div
                class="absolute inset-0 w-1/2 h-full bg-white/5 skew-x-[-20deg] -translate-x-full group-hover:translate-x-[250%] transition-transform duration-1000"
              ></div>
              <Loader2Icon v-if="loading" class="h-6 w-6 animate-spin" />
              <span class="relative z-10">{{
                loading ? "Accessing Portal..." : "Sign In to Dashboard"
              }}</span>
              <ArrowRightIcon
                v-if="!loading"
                class="h-5 w-5 group-hover:translate-x-1 transition-transform"
              />
            </button>
          </div>
        </form>

        <!-- Footer -->
        <div
          class="mt-16 pt-8 border-t border-slate-100 text-center lg:text-left"
        >
          <p class="text-sm text-slate-400 font-medium">
            &copy; 2026 Benny Cards <span class="mx-2">•</span> Management
            Console
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import {
  Mail as MailIcon,
  Lock as LockIcon,
  Loader2 as Loader2Icon,
  ScrollText as ScrollTextIcon,
  AlertCircle as AlertCircleIcon,
  ArrowRight as ArrowRightIcon,
} from "lucide-vue-next";
import { useAuth } from "../../composables/useAuth";

const router = useRouter();
const { setUser } = useAuth();
const loading = ref(false);
const errorMessage = ref(null);
const companyName = ref(null);
const companyLogo = ref(null);

const getImageSource = (path) => {
  if (!path) return "";
  if (path.startsWith("data:image") || path.startsWith("http")) return path;
  return `/${path}`;
};

const form = reactive({
  email: "",
  password: "",
});

const fetchLogo = async () => {
  try {
    const response = await axios.get("/api/v1/settings/logo");
    if (response.data.success && response.data.data) {
      companyLogo.value = response.data.data.logo;
      companyName.value = response.data.data.company_name;
    }
  } catch (e) {
    console.error("Login: Failed to fetch logo", e);
  }
};

onMounted(() => {
  document.documentElement.classList.remove("dark");
  fetchLogo();
});

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = null;

  try {
    await axios.get("/sanctum/csrf-cookie");
    const response = await axios.post("/api/v1/login", form);

    if (response.data.success) {
      localStorage.setItem("auth_token", response.data.data.token);
      setUser(response.data.data.user);
      axios.defaults.headers.common["Authorization"] =
        `Bearer ${response.data.data.token}`;
      axios.defaults.headers.common["Authorization"] =
        `Bearer ${response.data.data.token}`;
      router.push("/");
    }
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value =
        "Invalid credentials. Please verify your email and password.";
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        "Connection lost. Please check your internet and try again.";
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap");

.font-serif {
  font-family: "Playfair Display", serif;
}

.animate-in {
  animation-fill-mode: both;
}

.fade-in {
  animation: fade-in 0.8s ease-out;
}

.slide-in-from-left-8 {
  animation: slide-in-left 1s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-in-from-bottom-8 {
  animation: slide-in-bottom 1s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-in-from-bottom-4 {
  animation: slide-in-bottom-small 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.delay-150 {
  animation-delay: 150ms;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slide-in-left {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slide-in-bottom {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-in-bottom-small {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
