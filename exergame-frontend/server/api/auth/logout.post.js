export default defineEventHandler(async (event) => {
	const config = useRuntimeConfig();
	const token = getCookie(event, "auth-token");

	if (!token) {
		throw createError({
			statusCode: 401,
			statusMessage: "Unauthorized",
		});
	}

	try {
		const response = await $fetch(`${config.apiInternalBaseUrl}/api/auth/logout`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json",
				Authorization: `Bearer ${token}`,
			},
		});

		return response;
	} catch (error) {
		throw createError({
			statusCode: error.response?.status || 500,
			statusMessage: error.message || "Logout failed",
			data: error.data,
		});
	}
});
