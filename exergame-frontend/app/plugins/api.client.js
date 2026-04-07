export default defineNuxtPlugin(() => {
	const config = useRuntimeConfig();
	const authStore = useAuthStore();

	// Define which API routes should be proxied through Nuxt server
	const PROXIED_ROUTES = [
		// '/api/auth/',
		// '/api/user/',
		// '/api/users/'
	];

	// Create a custom $fetch instance for direct Laravel backend calls
	const apiFetch = $fetch.create({
		baseURL: config.public.apiBaseUrl,
		onRequest({ options }) {
			const token = useCookie("auth-token");
			options.headers = {
				...options.headers,
				Accept: "application/json",
			};
			if (token.value) {
				options.headers.Authorization = `Bearer ${token.value}`;
			}
		},

		onResponseError({ response }) {
			response._data = response._data?.data || response._data || {};
			if (response._data?.errors) {
				const formattedErrors = formatValidationErrors(
					response._data.message,
					response._data.errors,
				);
				if (formattedErrors) {
					response._data.message = `<strong>Validation failed.</strong><br>${formattedErrors}`;
				}
			}

			if (response.status === 401) {
				authStore.clearSession();
				navigateTo("/");
        // const { $notify } = useNuxtApp();
        // $notify.error('Session expired.');
			}
		},
	});

	// Create a custom $fetch instance for Nuxt server proxy calls
	const serverFetch = $fetch.create({
		// Don't set baseURL here - let it use the current domain for proxy routes
		onRequest({ options }) {
			const token = useCookie("auth-token");
			options.headers = {
				...options.headers,
				Accept: "application/json",
			};
			if (token.value) {
				options.headers.Authorization = `Bearer ${token.value}`;
			}
		},

		onResponseError({ response }) {
			response._data = response._data?.data || response._data;
			if (response._data?.errors) {
				const formattedErrors = formatValidationErrors(
					response._data.message,
					response._data.errors,
				);
				if (formattedErrors) {
					response._data.message += `<strong>Validation failed.</strong><br>${formattedErrors}`;
				}
			}

			if (response.status === 401) {
				authStore.clearSession();
				navigateTo("/");
        // const { $notify } = useNuxtApp();
        // $notify.error('Session expired.');
			}
		},
	});

	// Store the original $fetch
	const originalFetch = globalThis.$fetch;

	// Override global $fetch selectively
	globalThis.$fetch = (request, options = {}) => {
		if (typeof request === "string" && request.startsWith("/api")) {
			// Check if this should be proxied through Nuxt server
			const shouldProxy = PROXIED_ROUTES.some((route) =>
				request.startsWith(route),
			);

			if (shouldProxy) {
				// Use Nuxt server proxy
				return serverFetch(request, options);
			} else {
				// Use direct Laravel backend
				return apiFetch(request, options);
			}
		}
		// Otherwise use the original $fetch (for Nuxt internal requests)
		return originalFetch(request, options);
	};

	const formatValidationErrors = (message, errors) => {
		if (!errors || typeof errors !== "object") {
			return "";
		}

		const formattedErrors = [];

		for (const field in errors) {
			if (Array.isArray(errors[field])) {
				errors[field].forEach((error) => {
					if (message !== error) {
						formattedErrors.push(`- ${error}`);
					}
				});
			}
		}

		if (formattedErrors.length) {
			return formattedErrors.join(`<br>`);
		} else {
			return "";
		}
	};
});
