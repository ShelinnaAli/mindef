export const useUserSchemes = () => {
	const schemes = ref([]);
	const isLoading = ref(false);

	const fetchSchemes = async () => {
		isLoading.value = true;
		try {
			const response = await $fetch("/api/user-schemes", {
				method: "GET",
			});

			if (response.success == false) {
				throw new Error(
					response.message || "Failed to fetch user schemes",
				);
			}

			schemes.value = response.data;
		} catch (error) {
			const message =
				error.data?.message || "Failed to fetch user schemes";
			console.error("fetch user schemes failed", message, error.data);
		} finally {
			isLoading.value = false;
		}
	};

	return {
		schemes: readonly(schemes),
		isLoading: readonly(isLoading),
		fetchSchemes,
	};
};
