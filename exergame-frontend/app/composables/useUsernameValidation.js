export const useUsernameValidation = () => {
	const isValidating = ref(false);
	const validationResult = ref(null);
	const recommendations = ref([]);

	const validateUsername = async (username) => {
		if (!username || username.trim() === "") {
			validationResult.value = null;
			recommendations.value = [];
			return null;
		}

		isValidating.value = true;

		try {
			const response = await $fetch("/api/auth/validate-username", {
				method: "POST",
				body: { username: username.trim() },
			});

			if (response.success) {
				validationResult.value = response.data;
				recommendations.value = response.data.recommendations || [];
				return response.data;
			} else {
				throw new Error(response.message || "Validation failed");
			}
		} catch (error) {
			console.error("Username validation error:", error);
			validationResult.value = {
				isValid: false,
				isValidFormat: false,
				isAvailable: false,
				message: "Unable to validate username. Please try again.",
				recommendations: [],
			};
			recommendations.value = [];
			return validationResult.value;
		} finally {
			isValidating.value = false;
		}
	};

	const clearValidation = () => {
		validationResult.value = null;
		recommendations.value = [];
	};

	return {
		isValidating: readonly(isValidating),
		validationResult: readonly(validationResult),
		recommendations: readonly(recommendations),
		validateUsername,
		clearValidation,
	};
};
