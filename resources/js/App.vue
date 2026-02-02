<template>
  <router-view></router-view>
</template>

<script setup>
import { onMounted } from "vue";
import axios from "axios";

const updateBranding = async () => {
  try {
    const response = await axios.get("/api/v1/settings/logo");
    if (response.data.success && response.data.data) {
      const { logo, company_name } = response.data.data;

      // Update Title
      if (company_name) {
        document.title = `${company_name} Admin`;
      }

      // Update Favicon & Icons
      if (logo) {
        const updateLink = (rel, href) => {
          let link = document.querySelector(`link[rel~='${rel}']`);
          if (!link) {
            link = document.createElement("link");
            link.rel = rel;
            document.getElementsByTagName("head")[0].appendChild(link);
          }
          link.href = href;
        };

        updateLink("icon", logo);
        updateLink("shortcut icon", logo);
        updateLink("apple-touch-icon", logo);
      }
    }
  } catch (error) {
    console.error("Failed to load branding settings:", error);
  }
};

onMounted(() => {
  updateBranding();
});
</script>
