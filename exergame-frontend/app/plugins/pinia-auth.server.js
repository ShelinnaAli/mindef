export default defineNuxtPlugin(async () => {
	if (import.meta.server) {
		// Server-side: load auth state from cookies
		const { useAuthStore } = await import("~/stores/useAuthStore");
		const authStore = useAuthStore();

		// Load session from cookies during SSR
		authStore.loadSessionFromCookies();
	}
});
