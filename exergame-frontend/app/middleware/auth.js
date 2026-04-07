import { useAuthStore } from "~/stores/useAuthStore";

export default defineNuxtRouteMiddleware(async () => {
	// Server-side and client-side compatible check
	if (import.meta.client) {
		// Client-side: use Pinia store
		const authStore = useAuthStore();

		// Load session if not already loaded
		if (!authStore.isAuthenticated) {
			authStore.loadSessionFromCookies();
		}

		// If still not authenticated after loading cookies, redirect to login
		if (!authStore.isAuthenticated) {
			return navigateTo("/");
		}
	} else {
		// Server-side fallback - check cookies directly
		const tokenCookie = useCookie("auth-token");
		const userCookie = useCookie("auth-user");

		if (!tokenCookie.value || !userCookie.value) {
			return navigateTo("/");
		}
	}
});
