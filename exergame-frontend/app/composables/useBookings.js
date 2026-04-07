export const useBookings = () => {
	const bookingCounters = ref({
		total: 0,
		upcoming: 0,
		completed: 0,
		cancelled: 0,
	});

	const bookings = ref([]);
	const userBookings = ref([]);
	const myBookings = ref([]);
	const currentBooking = ref(null);
	const isLoading = ref(false);
	const error = ref(null);

	// Fetch booking counters
	const fetchBookingCounters = async () => {
		isLoading.value = true;
		error.value = null;
		try {
			const response = await $fetch("/api/bookings/counter", {
				method: "GET",
			});
			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch booking counters",
				);
			}
			bookingCounters.value = response.data || {
				total: 0,
				upcoming: 0,
				completed: 0,
				cancelled: 0,
			};
			return response;
		} catch (err) {
			console.error("Error fetching booking counters:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while fetching booking counters";
			bookingCounters.value = {
				total: 0,
				upcoming: 0,
				completed: 0,
				cancelled: 0,
			};
		} finally {
			isLoading.value = false;
		}
	};

	// Create a new booking
	const createBooking = async (bookingData) => {
		isLoading.value = true;
		error.value = null;

		try {
			const response = await $fetch("/api/booking", {
				method: "POST",
				body: bookingData,
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to create booking");
			}

			currentBooking.value = decrypt(response.data);
			return response;
		} catch (err) {
			console.error("Error creating booking:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while creating the booking";
			throw err?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch all bookings (admin/trainer only)
	const fetchBookings = async (options = {}) => {
		isLoading.value = true;
		error.value = null;
		bookings.value = [];

		try {
			const fetchOptions = {
				method: "GET",
				query: options,
			};

			const response = await $fetch("/api/bookings", fetchOptions);

			if (response.success === false) {
				throw new Error(response.message || "Failed to fetch bookings");
			}

			bookings.value = decrypt(response.data) || [];
			return response;
		} catch (err) {
			console.error("Error fetching bookings:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while fetching bookings";
			bookings.value = [];
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch user's bookings
	const fetchMyBookings = async (options = {}) => {
		isLoading.value = true;
		error.value = null;
		bookings.value = [];

		try {
			const fetchOptions = {
				method: "GET",
				query: options,
			};

			const response = await $fetch(
				"/api/bookings/my-bookings",
				fetchOptions,
			);

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch user bookings",
				);
			}

	    myBookings.value = decrypt(response.data) || [];
			return response;
		} catch (err) {
			console.error("Error fetching user bookings:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while fetching your bookings";
			myBookings.value = [];
		} finally {
			isLoading.value = false;
		}
	};

	// mode = 'programme' | 'schedule'
	const fetchBookingUsers = async (mode, id, options = {}) => {
		isLoading.value = true;
		error.value = null;
		userBookings.value = [];

		try {
			const response = await $fetch(`/api/bookings/users/${mode}/${id}`, {
				method: "GET",
				query: options,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch booking users",
				);
			}

			userBookings.value = decrypt(response.data) || [];
		} catch (err) {
			console.error("Error fetching booking users:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while fetching booking users";
		} finally {
			isLoading.value = false;
		}
	};

	// Update a booking
	const updateBooking = async (id, updateData) => {
		isLoading.value = true;
		error.value = null;

		try {
			const response = await $fetch(`/api/booking/${id}`, {
				method: "PUT",
				body: updateData,
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to update booking");
			}

			currentBooking.value = decrypt(response.data);
			return response;
		} catch (err) {
			console.error("Error updating booking:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while updating the booking";
			throw err?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Delete a booking (admin/trainer only)
	const deleteBooking = async (id) => {
		isLoading.value = true;
		error.value = null;

		try {
			const response = await $fetch(`/api/booking/${id}`, {
				method: "DELETE",
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to delete booking");
			}

			return response;
		} catch (err) {
			console.error("Error deleting booking:", err, err?.data);
			error.value =
				err?.data?.message ||
				"An error occurred while deleting the booking";
			throw err?.data;
		} finally {
			isLoading.value = false;
		}
	};
	// Computed properties
	const confirmedBookings = computed(() =>
		myBookings.value.filter((booking) => booking.status === "confirmed"),
	);

	const cancelledBookings = computed(() =>
		myBookings.value.filter((booking) => booking.status === "cancelled"),
	);

	const completedBookings = computed(() =>
		myBookings.value.filter((booking) => booking.status === "completed"),
	);

	const upcomingBookings = computed(() => {
		const now = new Date();
		return confirmedBookings.value.filter((booking) => {
			const bookingDateTime = new Date(
				booking.schedule.date + " " + booking.schedule.time,
			);
			return bookingDateTime > now;
		});
	});

	const pastBookings = computed(() => {
		const now = new Date();
		return myBookings.value.filter((booking) => {
			const bookingDateTime = new Date(
				booking.schedule.date + " " + booking.schedule.time,
			);
			return bookingDateTime < now;
		});
	});

	return {
		// State
		bookings,
		userBookings,
		myBookings,
		currentBooking,
		isLoading,
		error,

		bookingCounters,

		// Methods
		createBooking,
		fetchBookings,
		fetchMyBookings,
		updateBooking,
		deleteBooking,
		fetchBookingCounters,
		fetchBookingUsers,

		// Computed
		confirmedBookings,
		cancelledBookings,
		completedBookings,
		upcomingBookings,
		pastBookings,
	};
};
