import { useAuthStore } from "~/stores/useAuthStore";

export default defineNuxtRouteMiddleware(() => {
	if (import.meta.client) {
		const authStore = useAuthStore();
		if (authStore.isAuthenticated) {
			return navigateTo("/dashboard");
		}
	} else {
		// SSR fallback: check token cookie directly
		const token = useCookie("auth-token");
		if (token.value) {
			return navigateTo("/dashboard");
		}
	}
});
