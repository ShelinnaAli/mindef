export default defineEventHandler(async (event) => {
	const config = useRuntimeConfig();
	const { slug } = getRouterParams(event);
	const query = getQuery(event);
	const body = await readBody(event).catch(() => ({}));

	const laravelUrl = `${config.apiInternalBaseUrl}/api/${Array.isArray(slug) ? slug.join("/") : slug}`;

	// Get the authorization token from cookies or headers
	const authToken =
		getCookie(event, "auth-token") || getHeader(event, "authorization");

	try {
		const headers: Record<string, string> = {
			"Content-Type": "application/json",
			Accept: "application/json",
			...getHeaders(event),
		};

		// Add authorization header if token exists
		if (authToken) {
			headers.Authorization = authToken.startsWith("Bearer ")
				? authToken
				: `Bearer ${authToken}`;
		}

		const response = await $fetch(laravelUrl, {
			method: getMethod(event),
			query,
			body: ["GET", "HEAD"].includes(getMethod(event)) ? undefined : body,
			headers,
		});

		return response;
	} catch (error: unknown) {
		const fetchError = error as {
			response?: {
				status?: number;
				statusText?: string;
				_data?: unknown;
			};
			message?: string;
		};
		throw createError({
			statusCode: fetchError.response?.status || 500,
			statusMessage:
				fetchError.response?.statusText || "Internal Server Error",
			data: fetchError.response?._data || fetchError.message,
		});
	}
});
