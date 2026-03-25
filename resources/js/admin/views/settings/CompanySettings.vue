<template>
  <div
    class="p-6 md:p-8 max-w-7xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700"
  >
    <!-- Header -->
    <PageHeader
      title="Company Settings"
      subtitle="Manage your business information and brand heritage."
      class="mb-12"
    >
      <template #actions>
        <button
          v-if="isSuperAdmin"
          @click="handleSave"
          :disabled="saving"
          class="inline-flex items-center justify-center gap-2 px-6 h-12 rounded-2xl bg-primary text-white font-bold shadow-xl shadow-primary/20 hover:opacity-95 active:scale-95 transition-all disabled:opacity-50 disabled:pointer-events-none group"
        >
          <Loader2Icon v-if="saving" class="h-5 w-5 animate-spin" />
          <SaveIcon
            v-else
            class="h-5 w-5 group-hover:scale-110 transition-transform"
          />
          <span>{{ saving ? "Saving Changes..." : "Save Configuration" }}</span>
        </button>
      </template>
    </PageHeader>

    <!-- Alert Messages -->
    <div
      v-if="successMessage"
      class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center gap-3 animate-in zoom-in-95"
    >
      <CheckCircleIcon class="h-5 w-5" />
      <span class="text-sm font-bold">{{ successMessage }}</span>
    </div>

    <div
      v-if="errorMessage"
      class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center gap-3 animate-in zoom-in-95"
    >
      <AlertCircleIcon class="h-5 w-5" />
      <span class="text-sm font-bold">{{ errorMessage }}</span>
    </div>

    <!-- Settings Forms -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Left Column: Identity & Social -->
      <div class="space-y-8">
        <!-- Brand Identity Card -->
        <div
          class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm space-y-8"
        >
          <div class="flex items-center gap-3 pb-6 border-b border-slate-50">
            <div
              class="w-10 h-10 rounded-2xl bg-primary/5 flex items-center justify-center text-primary"
            >
              <BuildingIcon class="h-5 w-5" />
            </div>
            <h2 class="text-lg font-bold text-slate-900">Brand Identity</h2>
          </div>

          <!-- Company Name -->
          <div class="group">
            <label
              class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
              >Company Name</label
            >
            <div class="relative">
              <BuildingIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
              />
              <input
                v-model="form.company_name"
                class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 text-slate-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all placeholder:text-slate-300"
                placeholder="e.g. Benny Cards"
              />
            </div>
          </div>

          <!-- Email & Phone -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group">
              <label
                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
                >Official Email</label
              >
              <div class="relative">
                <MailIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
                />
                <input
                  v-model="form.email"
                  class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all"
                  placeholder="hello@company.com"
                />
              </div>
            </div>
            <div class="group">
              <label
                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
                >Support Hotline</label
              >
              <div class="relative">
                <PhoneIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
                />
                <input
                  v-model="form.phone"
                  class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all"
                  placeholder="+1 (234) 567-890"
                />
              </div>
            </div>
          </div>

          <!-- Headquarters Address -->
          <div class="group">
            <label
              class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
              >Headquarters Address</label
            >
            <div class="relative">
              <MapPinIcon
                class="absolute left-4 top-4 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
              />
              <textarea
                v-model="form.address"
                rows="3"
                class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 text-slate-900 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all placeholder:text-slate-300 resize-none"
                placeholder="Street, City, Zip..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Social Presence -->
        <div
          class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm space-y-8"
        >
          <div class="flex items-center gap-3 pb-6 border-b border-slate-50">
            <div
              class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600"
            >
              <Share2Icon class="h-5 w-5" />
            </div>
            <h2 class="text-lg font-bold text-slate-900">Social Presence</h2>
          </div>

          <div class="space-y-6">
            <div class="group">
              <label
                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
                >Facebook</label
              >
              <div class="relative">
                <FacebookIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
                />
                <input
                  v-model="form.facebook_url"
                  class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all"
                  placeholder="facebook.com/..."
                />
              </div>
            </div>
            <div class="group">
              <label
                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1"
                >Instagram</label
              >
              <div class="relative">
                <InstagramIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300 group-focus-within:text-primary transition-colors"
                />
                <input
                  v-model="form.instagram_url"
                  class="w-full pl-12 pr-4 py-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/30 font-bold focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary outline-none transition-all"
                  placeholder="@username"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Visuals -->
      <div class="space-y-8">
        <!-- Logo Asset -->
        <div
          class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm space-y-6"
        >
          <div
            class="flex items-center gap-3 pb-6 border-b border-slate-50 uppercase tracking-[0.2em] text-[10px] font-black text-slate-400"
          >
            Official Brand Logo
          </div>

          <div class="space-y-4">
            <div
              class="relative group aspect-video rounded-[2rem] bg-slate-50/50 border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center transition-all hover:border-primary/50 hover:bg-white"
            >
              <img
                v-if="form.logo"
                :src="form.logo"
                class="w-full h-full object-contain p-8"
              />
              <div v-else class="text-center space-y-2">
                <ImageIcon class="h-8 w-8 text-slate-300 mx-auto" />
                <p
                  class="text-[9px] font-bold text-slate-400 tracking-widest uppercase"
                >
                  Click to Upload Logo
                </p>
              </div>
              <input
                type="file"
                @change="(e) => handleImageUpload(e, 'logo')"
                class="absolute inset-0 opacity-0 cursor-pointer"
                accept="image/*"
              />
            </div>
            <p
              class="text-[10px] text-slate-400 text-center italic tracking-wider uppercase font-bold"
            >
              Recommended: Transparent PNG (512x512)
            </p>
          </div>
        </div>

        <!-- Heritage Photo -->
        <div
          class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm space-y-6"
        >
          <div
            class="flex items-center gap-3 pb-6 border-b border-slate-50 uppercase tracking-[0.2em] text-[10px] font-black text-slate-400"
          >
            Heritage Media Asset
          </div>

          <div class="space-y-4">
            <div
              class="relative group aspect-video rounded-[2rem] bg-slate-50/50 border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center transition-all hover:border-primary/50 hover:bg-white"
            >
              <img
                v-if="form.photo"
                :src="form.photo"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-center space-y-2">
                <CameraIcon class="h-8 w-8 text-slate-300 mx-auto" />
                <p
                  class="text-[9px] font-bold text-slate-400 tracking-widest uppercase"
                >
                  Select Media Image
                </p>
              </div>
              <input
                type="file"
                @change="(e) => handleImageUpload(e, 'photo')"
                class="absolute inset-0 opacity-0 cursor-pointer"
                accept="image/*"
              />
            </div>
            <p
              class="text-[10px] text-slate-400 text-center italic tracking-wider uppercase font-bold"
            >
              Ideal for OG tags and landing banners
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";
import {
  Save as SaveIcon,
  Loader2 as Loader2Icon,
  Building as BuildingIcon,
  MapPin as MapPinIcon,
  Mail as MailIcon,
  Phone as PhoneIcon,
  Image as ImageIcon,
  Upload as UploadIcon,
  Camera as CameraIcon,
  CheckCircle as CheckCircleIcon,
  AlertCircle as AlertCircleIcon,
  Map as MapIcon,
  Share2 as Share2Icon,
  Facebook as FacebookIcon,
  Instagram as InstagramIcon,
} from "lucide-vue-next";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useSettings } from "../../composables/useSettings";

const { isSuperAdmin } = usePermissions();
const { settings, fetchSettings: loadSettings, setSettings } = useSettings();
const saving = ref(false);
const successMessage = ref(null);
const errorMessage = ref(null);

const form = reactive({
  company_name: "",
  description: "",
  short_des: "",
  address: "",
  email: "",
  phone: "",
  logo: null,
  photo: null,
  facebook_url: "",
  instagram_url: "",
});

const fetchSettings = async () => {
  await loadSettings();
  if (settings.value) {
    Object.assign(form, settings.value);
  }
};

const handleImageUpload = (event, field) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    // 2MB limit
    errorMessage.value = "Image size should not exceed 2MB.";
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    form[field] = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handleSave = async () => {
  saving.value = true;
  successMessage.value = null;
  errorMessage.value = null;

  try {
    const response = await axios.put("/api/v1/settings", form);
    if (response.data.success) {
      setSettings(form); // Update global state
      successMessage.value = "Configuration updated successfully.";
      setTimeout(() => {
        successMessage.value = null;
      }, 5000);
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      "Failed to update configuration. Please try again.";
  } finally {
    saving.value = false;
  }
};

onMounted(fetchSettings);
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap");

.font-serif {
  font-family: "Playfair Display", serif;
}

.animate-in {
  animation-fill-mode: both;
}

.fade-in {
  animation: fade-in 0.8s ease-out;
}

.slide-in-from-bottom-4 {
  animation: slide-in-bottom 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.zoom-in-95 {
  animation: zoom-in-95 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
@keyframes slide-in-bottom {
  from {
    transform: translateY(15px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
@keyframes zoom-in-95 {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
