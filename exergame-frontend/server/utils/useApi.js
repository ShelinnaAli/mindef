export const useApi = (event) => {
	const config = useRuntimeConfig();

	// Get the Authorization header from the client request
	const authHeader = getHeader(event, "authorization");

	const apiFetch = $fetch.create({
		baseURL: config.public.apiBaseUrl,
		onRequest({ options }) {
			if (authHeader) {
				options.headers = {
					...options.headers,
					Authorization: authHeader,
				};
			}
		},
	});

	// Default options for methods that send data
	const defaultDataOptions = {
		headers: {
			"Content-Type": "application/json",
		},
	};

	// Create method helpers
	const $api = {
		get: (url, options = {}) =>
			apiFetch(url, {
				method: "GET",
				...options,
			}),

		post: (url, body = null, options = {}) => {
			return apiFetch(url, {
				method: "POST",
				body,
				...defaultDataOptions,
				...options,
				headers: {
					...defaultDataOptions.headers,
					...options.headers,
				},
			});
		},

		put: (url, body = null, options = {}) => {
			return apiFetch(url, {
				method: "PUT",
				body,
				...defaultDataOptions,
				...options,
				headers: {
					...defaultDataOptions.headers,
					...options.headers,
				},
			});
		},

		patch: (url, body = null, options = {}) => {
			return apiFetch(url, {
				method: "PATCH",
				body,
				...defaultDataOptions,
				...options,
				headers: {
					...defaultDataOptions.headers,
					...options.headers,
				},
			});
		},

		delete: (url, options = {}) =>
			apiFetch(url, {
				method: "DELETE",
				...options,
			}),
	};

	return { $api };
};
