export const useGameTypes = () => {
	const isLoading = ref(false);
	const gameTypes = ref([]);
	const error = ref(null);

	// Fetch all game types
	const fetchGameTypes = async () => {
		isLoading.value = true;
		error.value = null;

		try {
			const response = await $fetch("/api/game-types", {
				method: "GET",
			});

			if (response.success) {
				gameTypes.value = response.data || [];
			} else {
				error.value = response.message || "Failed to fetch game types";
			}
		} catch (err) {
			const message = err?.data?.message || "Failed to fetch game types";
			console.error("Game types fetch error:", message, err?.data);
			gameTypes.value = [];
		} finally {
			isLoading.value = false;
		}
	};

	return {
		gameTypes: readonly(gameTypes),
		isLoading: readonly(isLoading),
		error: readonly(error),
		fetchGameTypes,
	};
};
