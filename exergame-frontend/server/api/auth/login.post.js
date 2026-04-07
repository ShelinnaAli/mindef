export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig();
  const body = await readBody(event);

  try {
    const response = await $fetch(`${config.apiInternalBaseUrl}/api/auth/login`, {
      method: "POST",
      body: JSON.stringify(body), // 🔥 WAJIB stringify
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });

    return response;
  } catch (error) {
    throw createError({
      statusCode: error.response?.status || 500,
      statusMessage: error.message || "Login failed",
      data: error.data || error.response?.data,
    });
  }
});