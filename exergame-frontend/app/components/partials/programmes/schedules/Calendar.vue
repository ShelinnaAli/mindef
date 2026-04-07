<template>
	<div style="overflow-x: auto">
		<div v-show="isLoading" class="loading-bar">
			<div class="loading-progress" />
		</div>

		<table class="schedule-table">
			<thead>
				<tr>
					<th
						v-for="day in [
							'Mon',
							'Tue',
							'Wed',
							'Thur',
							'Fri',
							'Sat',
							'Sun',
						]"
						:key="day"
					>
						{{ day }}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
					<td
						v-for="(day, dayIndex) in week"
						:key="dayIndex"
						class="schedule-slot-cell"
						:class="{
							'current-date': day.isCurrentDate,
							'past-date': day.isPastDate,
						}"
					>
						<div
							class="day-header"
							:style="{
								display: userRole === 'user' ? 'block' : 'flex',
							}"
						>
							<div
								class="day-number"
								:class="{
									'current-date-number': day.isCurrentDate,
								}"
								:style="{
									margin:
										userRole === 'user' ? '0 auto' : '0',
								}"
							>
								{{ day.date }}
							</div>

							<button
								v-if="userRole !== 'user'"
								v-show="!day.isPastDate && day.date"
								class="add-session-btn"
								title="Add Session"
								@click="openSessionFormModal(day.date)"
							>
								<fa-icon icon="fas fa-plus" />
							</button>
						</div>

						<template v-if="day.isLoading">
							<div class="day-loading">
								<div class="loading-spinner" />
								<!-- <span class="loading-text">Loading...</span> -->
							</div>
						</template>

						<template v-else>
							<!-- schedules for the day -->
							<PartialsProgrammesSchedulesCalendarItem
								:schedules="day?.schedules || []"
								:date="day.date"
								:is-past-date="day.isPastDate"
								:selected-session="selectedSession"
								@session-selected="selectSession"
								@open-session-modal="openSessionModal"
							/>
						</template>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
const props = defineProps({
	currentDisplayedDate: {
		type: Date,
		required: true,
	},
	selectedSession: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits([
	"open-session-modal",
	"open-session-form-modal",
	"session-selected",
]);
const authStore = useAuthStore();
const { monthlySchedule, isLoading, fetchMonthlySchedules } =
	useProgrammeSchedules();

// Fetch monthly schedules whenever current date changes
const fetchMonthlySchedule = async () => {
	const year = props.currentDisplayedDate.getFullYear();
	const month = props.currentDisplayedDate.getMonth() + 1; // API expects 1-based month

	try {
		await fetchMonthlySchedules(year, month);
	} catch (error) {
		console.error("Error fetching monthly schedules:", error);
	}
};

// Helper function to get schedules for a specific day
const getSchedulesForDay = (monthKey, dayKey) => {
	return transformedMonthlySchedule.value[monthKey]?.[dayKey] || [];
};

const selectSession = (schedule, isPastDate) => {
	if (
		["cancelled", "full"].includes(schedule.status.toLowerCase()) ===
			false &&
		!isPastDate
	) {
		emit("session-selected", schedule);
	}
};

const openSessionModal = (schedule) => {
	emit("open-session-modal", schedule);
};

const openSessionFormModal = (date) => {
	emit("open-session-form-modal", date);
};

// Transform raw API data into the format expected by the calendar
const transformedMonthlySchedule = computed(() => {
	const scheduleObj = {};
	const currentYear = props.currentDisplayedDate.getFullYear();
	const currentMonth = props.currentDisplayedDate.getMonth() + 1;
	const monthKey = `${currentYear}-${String(currentMonth).padStart(2, "0")}`;

	const schedules = monthlySchedule.value[monthKey] || [];

	if (schedules && schedules.length > 0) {
		schedules.forEach((schedule) => {
			const date = new Date(schedule.day);
			const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
			const dayKey = `${monthKey}-${String(date.getDate()).padStart(2, "0")}`;

			if (!scheduleObj[monthKey]) {
				scheduleObj[monthKey] = {};
			}

			if (!scheduleObj[monthKey][dayKey]) {
				scheduleObj[monthKey][dayKey] = [];
			}

			scheduleObj[monthKey][dayKey].push(schedule);
		});
	}

	return scheduleObj;
});

const calendarWeeks = computed(() => {
	const year = props.currentDisplayedDate.getFullYear();
	const month = props.currentDisplayedDate.getMonth();
	const firstDayOfMonth = new Date(year, month, 1).getDay();
	const daysInMonth = new Date(year, month + 1, 0).getDate();
	const dayOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

	// Get current date info
	const today = new Date();
	const currentYear = today.getFullYear();
	const currentMonth = today.getMonth();
	const currentDate = today.getDate();

	const weeks = [];
	let currentWeek = [];
	let date = 1;

	const monthKey = `${year}-${String(month + 1).padStart(2, "0")}`;
	const hasMonthData = transformedMonthlySchedule.value[monthKey];

	// Fill empty cells for days before the first of the month
	for (let i = 0; i < dayOffset; i++) {
		currentWeek.push({
			date: "",
			schedules: [],
			isLoading: false,
			isCurrentDate: false,
			isPastDate: false,
		});
	}

	// Fill in the days of the month
	for (let i = dayOffset; i < 42; i++) {
		// 6 weeks max
		if (date > daysInMonth) {
			currentWeek.push({
				date: "",
				schedules: [],
				isLoading: false,
				isCurrentDate: false,
				isPastDate: false,
			});
		} else {
			const dayKey = `${monthKey}-${String(date).padStart(2, "0")}`;
			const schedules = hasMonthData
				? getSchedulesForDay(monthKey, dayKey)
				: [];
			const isDayLoading = !hasMonthData && isLoading.value;

			// Check if this is the current date
			const isCurrentDate =
				year === currentYear &&
				month === currentMonth &&
				date === currentDate;

			// Check if this is a past date
			const isPastDate =
				year < currentYear ||
				(year === currentYear && month < currentMonth) ||
				(year === currentYear &&
					month === currentMonth &&
					date < currentDate);

			currentWeek.push({
				date: date,
				schedules: schedules,
				isLoading: isDayLoading,
				isCurrentDate: isCurrentDate,
				isPastDate: isPastDate,
			});
			date++;
		}

		if (currentWeek.length === 7) {
			weeks.push(currentWeek);
			currentWeek = [];
		}
	}

	return weeks;
});

const userRole = computed(() => authStore.user?.role);

defineExpose({
	fetchMonthlySchedule,
});

// Watch for date changes to refetch schedules
watch(() => props.currentDisplayedDate, fetchMonthlySchedule, { deep: true });

onMounted(() => fetchMonthlySchedule());
</script>
