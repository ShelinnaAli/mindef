export const useAnnouncement = () => {
	const isLoading = ref(false);
	const announcements = ref([]);
	const announcementTypes = ref([]);
	const announcement = ref(null);
	const error = ref(null);

	// Fetch all announcements
	const fetchAnnouncements = async (options = {}) => {
		isLoading.value = true;
		error.value = null;
    announcements.value = [];

		try {
			const response = await $fetch("/api/announcements", {
				method: "GET",
				query: options,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch announcements",
				);
			}

      announcements.value = response.data.map((announcement) => ({
				...announcement,
				createdAt: formatHumanDateTime(announcement.createdAt),
			}));
		} catch (err) {
			error.value = err?.data?.message || "Failed to fetch announcements";
			console.error("Failed to fetch announcements:", error.value);
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch a single announcement
	const fetchAnnouncement = async (id, options = {}) => {
    isLoading.value = true;
		error.value = null;
    announcement.value = null;

		try {
			const response = await $fetch(`/api/announcement/${id}`, {
				method: "GET",
				query: options,
			});

			if (!response.success) {
				throw new Error(
					response.message || "Failed to fetch announcement",
				);
			}
			announcement.value = response.data;
			return response.data;
		} catch (error) {
			error.value = error?.data?.message || "Failed to fetch announcement";
			console.error("Failed to fetch announcement:", error.value);
		} finally {
			isLoading.value = false;
		}
	};

  // fetch announcement types
	const fetchAnnouncementTypes = async (options = {}) => {
    isLoading.value = true;
    error.value = null;
    announcementTypes.value = [];

		try {
			const response = await $fetch("/api/announcement/types", {
				method: "GET",
				query: options,
			});

			if (!response.success) {
				throw new Error(
					response.message || "Failed to fetch announcement types"
				);
			}
      announcementTypes.value = response.data;

		} catch (error) {
			error.value = error?.data?.message || "Failed to fetch announcement types";
			console.error("Failed to fetch announcement types:", error.value);
		} finally {
			isLoading.value = false;
		}
	};

	// Create a new announcement
	const createAnnouncement = async (announcementData) => {
    isLoading.value = true;
    error.value = null;

		try {
			const response = await $fetch("/api/announcement", {
				method: "POST",
				body: announcementData,
			});
			if (!response.success) {
				throw new Error(
					response.message || "Failed to create announcement",
				);
			}

      announcements.value.push(response.data);
      return response;
		} catch (error) {
			error.value = error.data?.message || "Failed to create announcement";
			console.error("announcement create error:", error.value, error?.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Update an announcement
	const updateAnnouncement = async (id, announcementData) => {
    isLoading.value = true;
    error.value = null;

		try {
			const response = await $fetch(`/api/announcement/${id}`, {
				method: "PUT",
				body: announcementData,
			});
			if (!response.success) {
				throw new Error(
					response.message || "Failed to update announcement",
				);
			}

      const index = announcements.value.findIndex((a) => a.id === id);
      if (index !== -1) {
        announcements.value[index] = response.data;
      }
      return response;
		} catch (error) {
			error.value = error.data?.message || "Failed to update announcement";
			console.error("announcement update error:", error.value, error?.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Delete an announcement
	const deleteAnnouncement = async (id) => {
    error.value = null;

		try {
			const response = await $fetch(`/api/announcement/${id}`, {
				method: "DELETE",
			});
			if (!response.success) {
				throw new Error(
					response.message || "Failed to deactivate announcement",
				);
			}

      const index = announcements.value.findIndex((a) => a.id === id);
      if (index !== -1 && announcements.value[index]) {
        announcements.value[index].isActive = false;
      }
      return true;
		} catch (error) {
			error.value = error.data?.message || "Failed to update announcement";
			console.error("announcement update error:", error.value, error?.data);
			throw error?.data;
		}
	};

	return {
		announcement,
		announcements,
		isLoading,
		error,
		fetchAnnouncements,
		fetchAnnouncement,
		createAnnouncement,
		updateAnnouncement,
		deleteAnnouncement,

    announcementTypes,
    fetchAnnouncementTypes
	};
};
