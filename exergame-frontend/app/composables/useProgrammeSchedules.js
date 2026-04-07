export const useProgrammeSchedules = () => {
	const scheduleCounter = ref({ active: 0, cancelled: 0 });
	const trainerTotalBookings = ref(0);
	const trainerAvgAttendance = ref(0);
	const userBookings = ref([]);
	const isLoading = ref(false);
	const schedules = ref([]);
	const schedule = ref(null);
	const error = ref(null);
	const monthlySchedule = ref({});

	const fetchTrainerTotalBookings = async () => {
		isLoading.value = true;
		try {
			const response = await $fetch(
				"/api/programme-schedule/trainer-bookings",
				{
					method: "GET",
				},
			);
			if (response.success === false)
				throw new Error(
					response.message || "Failed to fetch trainer bookings",
				);
			trainerTotalBookings.value = response.data.total || 0;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching trainer bookings";
			trainerTotalBookings.value = 0;
		} finally {
			isLoading.value = false;
		}
	};

	const fetchTrainerAvgAttendance = async () => {
		isLoading.value = true;
		try {
			const response = await $fetch(
				"/api/programme-schedule/trainer-avg-attendance",
				{
					method: "GET",
				},
			);
			if (response.success === false)
				throw new Error(
					response.message ||
						"Failed to fetch trainer avg attendance",
				);
			trainerAvgAttendance.value = response.data.average || 0;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching trainer avg attendance";
			trainerAvgAttendance.value = 0;
		} finally {
			isLoading.value = false;
		}
	};

	const fetchScheduleCounters = async (options = {}) => {
		isLoading.value = true;

		try {
			scheduleCounter.value = {
				active: 0,
				cancelled: 0,
			};
			const response = await $fetch("/api/programme-schedule/counters", {
				method: "GET",
				query: options,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch schedule counters",
				);
			}
			scheduleCounter.value = {
				active: response.data.active || 0,
				cancelled: response.data.cancelled || 0,
			};
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching schedule counters";
			console.error("Schedule counters fetch error:", error.value, err);
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch all schedules
	const fetchSchedules = async (options = {}) => {
		isLoading.value = true;
    error.value = null
		schedules.value = [];
		try {
			const response = await $fetch("/api/programme-schedules", {
				method: "GET",
				query: options,
			});
			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch schedules",
				);
			}

			const data = decrypt(response.data) || [];
			// Transform the data to match frontend expectations
			const transformedSchedules = data.map((schedule) => ({
				...schedule,
				status: schedule.isCancelled ? "cancelled" : "active",
				date: formatDate(schedule.day),
				time: formatTimeString(schedule.startTime),
				totalBookings: schedule.bookingsCount || 0,
			}));
			schedules.value = transformedSchedules;
			return transformedSchedules;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching schedules";
			console.error("Error fetching schedules:", error.value, err);
			return [];
		} finally {
			isLoading.value = false;
		}
	};

	const fetchScheduleBookingUsers = async (scheduleId, options = {}) => {
		isLoading.value = true;
		error.value = null;
		userBookings.value = [];

		try {
			const response = await $fetch(
				`/api/programme-schedule/${scheduleId}/user-bookings`,
				{
					method: "GET",
					query: options,
				},
			);

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

	// Fetch monthly schedules
	const fetchMonthlySchedules = async (year, month) => {
		isLoading.value = true;
		error.value = null;

		try {
			const response = await $fetch("/api/programme-schedules/monthly", {
				method: "GET",
				query: {
					year: year,
					month: month,
				},
			});

			if (response.success === false) {
				error.value = response.message || "Failed to fetch schedules";
				return [];
			}

			const data = decrypt(response.data) || [];
			// Transform the data to match frontend expectations
			const transformedSchedules = data.map((schedule) => ({
				...schedule,
				status: schedule.isCancelled ? "cancelled" : "active",
				date: formatDate(schedule.day),
				time: formatTimeString(schedule.startTime),
				totalBookings: schedule.bookingsCount || 0,
			}));

			schedules.value = transformedSchedules;

			// Transform data to match the calendar structure expected by the frontend
			const monthKey = `${year}-${String(month).padStart(2, "0")}`;
			monthlySchedule.value[monthKey] = transformedSchedules;

			return transformedSchedules;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while fetching schedules";
			console.error(
				"Error fetching monthly schedules:",
				error.value,
				err,
			);
			return [];
		} finally {
			isLoading.value = false;
		}
	};

	// Get schedules for a specific day
	const getSchedulesForDay = (monthKey, dayKey) => {
		const schedule =
			monthlySchedule.value[monthKey]?.filter((s) => s.day === dayKey) ||
			[];
		return schedule;
	};

	// Create a new schedule
	const createSchedule = async (scheduleData) => {
		isLoading.value = true;
		error.value = null;
		schedule.value = null;

		try {
			const response = await $fetch("/api/programme-schedule", {
				method: "POST",
				body: scheduleData,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to create schedule",
				);
			}
			const data = decrypt(response.data) || null;
			schedule.value = data;
			return data;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while creating the schedule";
			console.error("Error creating schedule:", error.value, err);
			throw err?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Update an existing schedule
	const updateSchedule = async (id, scheduleData) => {
		isLoading.value = true;
		error.value = null;
		schedule.value = null;

		try {
			const response = await $fetch(`/api/programme-schedule/${id}`, {
				method: "PUT",
				body: scheduleData,
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to update schedule",
				);
			}
			const data = decrypt(response.data) || null;
			schedule.value = data;
			return data;
		} catch (err) {
			error.value =
				err?.data?.message ||
				"An error occurred while updating the schedule";
			console.error("Error updating schedule:", error.value, err);
			throw err?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Initialize schedule data for multiple months (like the original component)
	const initializeScheduleData = async (baseDate = new Date()) => {
		const year = baseDate.getFullYear();
		const month = baseDate.getMonth();

		// Fetch data for previous, current, and next month
		for (let i = -1; i <= 1; i++) {
			const targetDate = new Date(year, month + i, 1);
			await fetchMonthlySchedules(
				targetDate.getFullYear(),
				targetDate.getMonth() + 1,
			);
		}
	};

	return {
		trainerTotalBookings: readonly(trainerTotalBookings),
		trainerAvgAttendance: readonly(trainerAvgAttendance),
		fetchTrainerTotalBookings,
		fetchTrainerAvgAttendance,

		isLoading: readonly(isLoading),
		schedules: readonly(schedules),
		schedule: readonly(schedule),
		error: readonly(error),
		monthlySchedule: readonly(monthlySchedule),
		scheduleCounter: readonly(scheduleCounter),
		userBookings: readonly(userBookings),
		fetchSchedules,
		fetchMonthlySchedules,
		getSchedulesForDay,
		createSchedule,
		updateSchedule,
		initializeScheduleData,
		fetchScheduleCounters,
		fetchScheduleBookingUsers,
	};
};
