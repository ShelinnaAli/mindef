export default defineNuxtPlugin(async () => {
	// This will run after Pinia is initialized
	await nextTick();

	if (import.meta.client) {
		// Import the store here to avoid issues with SSR
		const { useAuthStore } = await import("~/stores/useAuthStore");
		const authStore = useAuthStore();
		authStore.loadSessionFromCookies();
	}
});
