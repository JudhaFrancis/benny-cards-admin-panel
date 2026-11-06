import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './admin/router';
import axios from 'axios';
import { branchScopeHeader } from './admin/composables/useBranchScope';

// Set default base URL for axios
axios.defaults.baseURL = window.location.origin;
axios.defaults.withCredentials = true;

// Add auth token if exists
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Send the header branch pick with every request (server limits it to the user's branches)
axios.interceptors.request.use((config) => {
    config.headers = config.headers || {};
    config.headers['X-Branch-Ids'] = branchScopeHeader();
    return config;
});

// Add a response interceptor to handle session expiration
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Token expired or invalid
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];

            // Redirect to login if not already there
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

import VueApexCharts from "vue3-apexcharts";

const app = createApp(App);

app.use(router);
app.use(VueApexCharts);

app.mount('#app');
