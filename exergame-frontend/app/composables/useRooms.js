export const useRooms = () => {
	const isLoadingRooms = ref(false);
	const rooms = ref([]);
	const room = ref(null);
	const error = ref(null);

	// Fetch all rooms
	const fetchRooms = async () => {
		isLoadingRooms.value = true;
		error.value = null;
		rooms.value = [];

		try {
			const response = await $fetch("/api/rooms", {
				method: "GET",
			});

			if (response.success === false) {
				error.value = response.message || "Failed to fetch rooms";
			}

			rooms.value = response.data.map((room) => ({
				id: room.id,
				name: room.name,
			}));
		} catch (err) {
			error.value =
				err?.data?.message || "An error occurred while fetching rooms";
			console.error("fetch rooms failed", error.value, err?.data);
		} finally {
			isLoadingRooms.value = false;
		}
	};

	return {
		// State
		isLoadingRooms: readonly(isLoadingRooms),
		rooms: readonly(rooms),
		room: readonly(room),
		error: readonly(error),

		// Methods
		fetchRooms,
	};
};
