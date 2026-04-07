export default defineEventHandler(async (event) => {
	const config = useRuntimeConfig();
	const flag = getRouterParam(event, 'flag');

	try {
		const url = flag
			? `${config.apiInternalBaseUrl}/app-settings/${flag}`
			: `${config.apiInternalBaseUrl}/app-settings`;

		const response = await $fetch(url, {
			method: "GET",
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json",
			},
		});

		return response;
	} catch (error) {
		throw createError({
			statusCode: error.response?.status || 500,
			statusMessage: error.message || "Failed to fetch app settings",
			data: error.data,
		});
	}
});
