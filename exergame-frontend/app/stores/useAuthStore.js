import { defineStore } from "pinia";
// import { encrypt, decrypt } from "~/utils/crypto";   // ← comment dulu

export const useAuthStore = defineStore("auth", () => {
	// State
	const user = ref(null);
	const accessToken = ref(null);
	const isLoading = ref(false);
	const isAuthenticated = computed(() => !!user.value && !!accessToken.value);

	// Cookies
	const tokenCookie = useCookie("auth-token", {
		default: () => null,
		maxAge: 60 * 60 * 24 * 14, // 14 days
	});

	const userCookie = useCookie("auth-user", {
		default: () => null,
		maxAge: 60 * 60 * 24 * 14,
	});

	// Set session data (tanpa encrypt)
	const setSession = (userData, token) => {
		user.value = userData;
		accessToken.value = token;
		tokenCookie.value = token;
		userCookie.value = userData;        // simpan langsung tanpa encrypt
	};

	// Clear session
	const clearSession = () => {
		user.value = null;
		accessToken.value = null;
		tokenCookie.value = null;
		userCookie.value = null;
	};

	// Load session from cookies
	const loadSessionFromCookies = () => {
		if (tokenCookie.value && userCookie.value) {
			try {
				accessToken.value = tokenCookie.value;
				user.value = userCookie.value;     // ambil langsung
				return true;
			} catch (error) {
				console.error("Failed to load user session:", error);
				clearSession();
				return false;
			}
		}
		return false;
	};

	// Login action - TANPA ENKRIPSI
	// Login action - VERSI DEBUG
	const login = async (credentials) => {
		isLoading.value = true;

		try {
			console.log("🔍 Sending credentials:", credentials);

			const response = await $fetch("/api/auth/login", {
				method: "POST",
				body: credentials,        // ← Kirim langsung object, BUKAN { data: credentials }
			});

			console.log("✅ Response dari backend:", response);

			if (response.success === false) {
				throw new Error(response.message || "Login failed");
			}

			const sessionData = response.data || response;
			setSession(sessionData.user || sessionData, sessionData.accessToken || sessionData.token);

			return { success: true };
		} catch (error) {
			console.error("❌ Full login error:", error);
			throw error?.data || error;
		} finally {
			isLoading.value = false;
		}
	};

	// Logout action
	const logout = async () => {
		isLoading.value = true;

		try {
			const response = await $fetch("/api/auth/logout", {
				method: "POST",
			});

			if (response.success === false) {
				throw new Error(response.message || "Logout failed");
			}

			return { success: true };
		} catch (error) {
			console.error("Logout error:", error);
			throw error?.data;
		} finally {
			clearSession();
			isLoading.value = false;
		}
	};

	return {
		user,
		accessToken,
		isLoading,
		isAuthenticated,

		login,
		logout,
		setSession,
		clearSession,
		loadSessionFromCookies,
	};
});