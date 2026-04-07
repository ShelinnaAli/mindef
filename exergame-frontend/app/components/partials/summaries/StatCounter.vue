<template>
	<div class="stats-grid">
		<div v-for="(item, i) in items" :key="i" class="stat-card">
			<div :class="`stat-icon ${item.flag}`">
				<fa-icon :icon="item.icon" />
			</div>
			<div class="stat-info">
				<h3>{{ item.value }}</h3>
				<p>{{ item.label }}</p>
			</div>
		</div>
	</div>
</template>

<script setup>
const authStore = useAuthStore();
const { bookingCounters, fetchBookingCounters } = useBookings();
const {
	scheduleCounter,
	trainerTotalBookings,
	trainerAvgAttendance,
	fetchScheduleCounters,
	fetchTrainerTotalBookings,
	fetchTrainerAvgAttendance,
} = useProgrammeSchedules();
const { programmeCounter, fetchProgrammeCounters } = useProgrammes();
const { trainerTotalProgrammes, fetchTrainerTotalProgrammes } = useProgrammes();
const { totalUsers, fetchTotalUsers } = useUser();
const totalProgrammesRun = ref(0);

const items = computed(() => {
  const userRole = authStore.user?.role;
	if (userRole === "user") {
		return userStats();
	} else if (['admin', 'superadmin'].includes(userRole)) {
		return adminStats();
	} else if (userRole === "trainer") {
		return trainerStats();
	}
	return [];
});

const userStats = () => [
	{
		label: "My Total Bookings",
		value: bookingCounters.value.total,
		icon: "fas fa-calendar-check",
		flag: "bookings",
	},
	/* {
		label: "Upcoming Sessions",
		value: bookingCounters.value.upcoming,
		icon: "fas fa-running",
		flag: "programs",
	},
	{
		label: "Completed Sessions",
		value: bookingCounters.value.completed,
		icon: "fas fa-check-circle",
		flag: "success",
	}, */
	{
		label: "My Cancellations",
		value: bookingCounters.value.cancelled,
		icon: "fas fa-times-circle",
		flag: "cancellations",
	},
];

const adminStats = () => [
	{
		label: "Total Registered Users",
		value: totalUsers.value,
		icon: "fas fa-users",
		flag: "users",
	},
	{
		label: "Total Programmes Run",
		value: totalProgrammesRun.value,
		icon: "fas fa-running",
		flag: "programs",
	},
	{
		label: "Total Session Conducted",
		value: scheduleCounter.value?.active || 0,
		icon: "fas fa-check-circle",
		flag: "sessions",
	},
];

const trainerStats = () => [
	{
		label: "Sessions Conducted",
		value: scheduleCounter.value.active || 0,
		icon: "fas fa-running",
		flag: "sessions",
	},
	{
		label: "Total Attendees",
		value: trainerTotalBookings.value || 0,
		icon: "fas fa-users",
		flag: "attendees",
	},
	{
		label: "Avg. Attendance Per Session",
		value: trainerAvgAttendance.value || 0,
		icon: "fas fa-chart-line",
		flag: "average",
	},
	{
		label: "Total Programmes",
		value: trainerTotalProgrammes.value || 0,
		icon: "fas fa-star",
		flag: "feedback",
	},
];

onMounted(async () => {
  const userRole = authStore.user?.role;
	if (userRole === "user") {
		await fetchBookingCounters();

	} else if (['admin', 'superadmin'].includes(userRole)) {
		await fetchTotalUsers();
		await fetchProgrammeCounters();
		await fetchScheduleCounters({ completed: true });
    totalProgrammesRun.value = (programmeCounter.value?.active || 0) + (programmeCounter.value?.inactive || 0);
	} else if (userRole === "trainer") {
		await fetchScheduleCounters({ completed: true });
		await fetchTrainerTotalBookings();
		await fetchTrainerAvgAttendance();
		await fetchTrainerTotalProgrammes();
	}
});
</script>
