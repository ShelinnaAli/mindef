export const useProgrammes = () => {
	const isLoading = ref(false);
	const programmes = ref([]);
	const programme = ref(null);
	const trainerTotalProgrammes = ref(0);
	const error = ref(null);
	const programmeCounter = ref(null);

	// Fetch all programmes
	const fetchProgrammes = async (options = {}) => {
		isLoading.value = true;
		error.value = null;
    programmes.value = [];

		try {
			const response = await $fetch("/api/programmes", {
				method: "GET",
				query: options,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch programmes",
				);
			}

			programmes.value = response.data;
		} catch (err) {
			error.value = err?.data?.message || "Failed to fetch programmes";
			console.warn("Failed to fetch programmes:", error.value);
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch popular programmes
	const fetchPopularProgrammes = async (options = {}) => {
		isLoading.value = true;
		error.value = null;
		programmes.value = [];

		try {
			const response = await $fetch("/api/programmes/popular", {
				method: "GET",
				query: options,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to popular programmes",
				);
			}

			programmes.value = decrypt(response.data) || [];
		} catch (err) {
			error.value =
				err?.data?.message || "Failed to fetch popular programmes";
			console.warn(
				"failed to fetch popular programmes",
				error.value,
				err,
			);
		} finally {
			isLoading.value = false;
		}
	};

	// Get a specific programme by ID
	const fetchProgramme = async (id, options = {}) => {
		try {
			isLoading.value = true;
			const fetchOptions = {
				method: "GET",
			};

			// Add query parameters if provided
			if (options.expand) {
				fetchOptions.query = {
					expand: options.expand,
				};
			}
			const response = await $fetch(`/api/programme/${id}`, fetchOptions);

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch programme",
				);
			}

			const programmeData = response.data;
			if (programmeData) {
				programme.value = {
					...programmeData,
					type: programme.gameType?.name || "Unknown",
					intensity:
						programme.intensityLevel.charAt(0).toUpperCase() +
						programme.intensityLevel.slice(1),
				};
			}

			return programmeData;
		} catch (error) {
			const message = error.data?.message || "Failed to fetch programme";
			console.error("Programme fetch error:", message, error.data);
			return null;
		} finally {
			isLoading.value = false;
		}
	};

	const fetchTrainerTotalProgrammes = async () => {
		isLoading.value = true;
		try {
			const response = await $fetch(
				"/api/programmes/trainer-programmes",
				{
					method: "GET",
				},
			);
			if (response.success === false)
				throw new Error(
					response.message ||
						"Failed to fetch trainer total programmes",
				);
			trainerTotalProgrammes.value = response.data.total || 0;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching trainer total programmes";
			trainerTotalProgrammes.value = 0;
		} finally {
			isLoading.value = false;
		}
	};


	const fetchProgrammeCounters = async () => {
		isLoading.value = true;

		try {
			programmeCounter.value = {
				active: 0,
				inactive: 0,
			};
			const response = await $fetch("/api/programmes/counters", {
				method: "GET",
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch programme counters",
				);
			}
			programmeCounter.value = {
				active: response.data.active || 0,
				inactive: response.data.inactive || 0,
			};
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching programme counters";
			console.error("Programme counters fetch error:", error.value, err);
		} finally {
			isLoading.value = false;
		}
	};

	// Create a new programme
	const createProgramme = async (programmeData) => {
		try {
			isLoading.value = true;
			const response = await $fetch("/api/programme", {
				method: "POST",
				body: programmeData,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to create programme",
				);
			}

			programmes.value.push(response.data);
			return response;
		} catch (error) {
			const message = error.data?.message || "Failed to create programme";
			console.error("Programme create error:", message, error?.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Update a programme
	const updateProgramme = async (id, programmeData) => {
		try {
			isLoading.value = true;
			const response = await $fetch(`/api/programme/${id}`, {
				method: "PUT",
				body: programmeData,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to update programme",
				);
			}

			// Update the programme in the local array
			const index = programmes.value.findIndex((p) => p.id === id);
			if (index !== -1) {
				programmes.value[index] = response.data;
			}
			return response;
		} catch (error) {
			const message = error.data?.message || "Failed to update programme";
			console.error("Programme update error:", message, error?.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Delete/deactivate a programme
	const deleteProgramme = async (id) => {
		try {
			const response = await $fetch(`/api/programme/${id}`, {
				method: "DELETE",
			});

			if (response.success) {
				// Update the programme's is_active status in local state instead of removing it
				const index = programmes.value.findIndex((p) => p.id === id);
				if (index !== -1) {
					programmes.value[index].isActive = false;
				}
				return true;
			} else {
				throw new Error(
					response.message || "Failed to deactivate programme",
				);
			}
		} catch (error) {
			const message =
				error.data?.message || "Failed to deactivate programme";
			console.error("Delete programme error:", message, error.data);
			return false;
		}
	};

	// Activate or deactivate a programme
	const updateStatusProgramme = async (id, isActive = true) => {
		try {
			const response = await $fetch(`/api/programme/${id}`, {
				method: "PUT",
				body: {
					isActive,
				},
			});

			if (response.success === false) {
				throw new Error(
					response.message ||
						`Failed to ${isActive ? "activate" : "deactivate"} programme`,
				);
			}

			// Update the programme in the local array
			const index = programmes.value.findIndex((p) => p.id === id);
			if (index !== -1) {
				programmes.value[index].isActive = response.data.isActive;
			}
			return response;
		} catch (error) {
			const actionText = isActive ? "activate" : "deactivate";
			const message =
				error.data?.message || `Failed to ${actionText} programme`;
			console.error(
				`${actionText.charAt(0).toUpperCase() + actionText.slice(1)} programme error:`,
				message,
				error.data,
			);
			throw error?.data;
		}
	};

  const uploadFile = async (file) => {
    isLoading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      formData.append('file', file);
      // Use $fetch from Nuxt 3
      const response = await $fetch('/api/programmes/upload-cover', {
        method: 'POST',
        body: formData,
      });
      return response.data?.url;
    } catch (err) {
      error.value = err?.data?.message || 'Upload failed';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

	return {
		programme: programme,
		programmes: programmes,
		isLoading: readonly(isLoading),
		error: readonly(error),
		trainerTotalProgrammes: readonly(trainerTotalProgrammes),
    programmeCounter: readonly(programmeCounter),
		fetchProgrammes,
		fetchProgramme,
		fetchPopularProgrammes,
		fetchTrainerTotalProgrammes,
    fetchProgrammeCounters,
		createProgramme,
		updateProgramme,
		deleteProgramme,
		updateStatusProgramme,
    uploadFile,
	};
};
