<template>
	<NuxtLoadingIndicator />
	<NuxtLayout>
		<NuxtPage />
	</NuxtLayout>
</template>

<script setup>
import { useAppSettingsStore } from '~/stores/useAppSettingsStore';

const appSettingsStore = useAppSettingsStore();
const {  APP_NAME  } = storeToRefs(appSettingsStore);

useHead({
	titleTemplate: computed(() => (titleChunk) => {
		return titleChunk ? `${titleChunk} - ${APP_NAME.value}` : APP_NAME.value;
	}),
});

// Initialize app settings once on client side
onMounted(async () => {
	if (import.meta.client) {
		if (appSettingsStore.appSettings.length === 0) {
			try {
				await appSettingsStore.fetchAppSettings('WEB');
			} catch (error) {
				console.error('Failed to load app settings:', error);
			}
		}
	}
});
</script>
