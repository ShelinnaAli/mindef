import { defineStore } from 'pinia'
import { decrypt } from '~/utils/crypto'

export const useAppSettingsStore = defineStore('appSettings', () => {
    const isLoading = ref(false);
    const appSettings = ref([]);
    const error = ref(null);
    const runtimeConfig = useRuntimeConfig()

    // Fetch all app settings
    const fetchAppSettings = async (flag, options = {}) => {
        isLoading.value = true;
        error.value = null;
        appSettings.value = [];

        try {
            let url = flag ? `/api/app-settings/${flag}` : "/api/app-settings";
            const response = await $fetch(url, {
                method: "GET",
                query: options,
            });

            if (response.success === false) {
                throw new Error(response.message || "Failed to fetch app settings");
            }

            appSettings.value = decrypt(response.data) || [];
        } catch (err) {
            error.value = err?.data?.message || "Failed to fetch app settings";
            console.error("Failed to fetch app settings:", error.value);
        } finally {
            isLoading.value = false;
        }
    };

    // Update an app setting
    const updateAppSetting = async (flag, settingData) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await $fetch(`/api/app-settings/${flag}`, {
                method: "PUT",
                body: {
                  settings: settingData
                },
            });
            if (!response.success) {
                throw new Error(response.message || "Failed to update app setting");
            }

            // Update the store with the new data
            const updatedSettings = response.data;
            appSettings.value = appSettings.value.map((s) => {
                const updatedSetting = updatedSettings.find((item) => item.key === s.key && item.flag === s.flag);
                return updatedSetting || s;
            });

            return response;
        } catch (error) {
            error.value = error.data?.message || "Failed to update app setting";
            console.error("app setting update error:", error.value, error?.data);
            throw error?.data;
        } finally {
            isLoading.value = false;
        }
    };

    // Computed getters for specific setting types
    const webSettings = computed(() => appSettings.value.filter(s => s.flag === 'WEB'));
    const smtpSettings = computed(() => appSettings.value.filter(s => s.flag === 'SMTP'));

    // web settings
    const APP_NAME = computed(() => {
      return appSettings.value
        .filter(s => s.flag === 'WEB')
        .find(s => s.key === 'APP_NAME')?.value || runtimeConfig.public.APP_NAME || 'FITCOMMAND';
    });
    const ADVERTISEMENT_MSG = computed(() => {
      return appSettings.value
        .filter(s => s.flag === 'WEB')
        .find(s => s.key === 'ADVERTISEMENT_MSG')?.value || '';
    });
    const LOGIN_BACKGROUND_MEDIA_URL = computed(() => {
      return appSettings.value
        .filter(s => s.flag === 'WEB')
        .find(s => s.key === 'LOGIN_BACKGROUND_MEDIA_URL')?.value || '';
    });

    return {
        appSettings,
        isLoading,
        error,
        webSettings,
        smtpSettings,

        // web settings
        APP_NAME,
        ADVERTISEMENT_MSG,
        LOGIN_BACKGROUND_MEDIA_URL,

        fetchAppSettings,
        updateAppSetting,
    };
});
