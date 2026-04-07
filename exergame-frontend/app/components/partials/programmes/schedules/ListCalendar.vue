<template>
	<div>
		<div class="section-header">
			<h2 class="section-title">Programme Calendar</h2>
		</div>
		<div class="calendar-navigation">
			<button @click="prevMonth">
				<fa-icon icon="fas fa-chevron-left" />
			</button>
			<span>{{ currentMonthYear }}</span>
			<button @click="nextMonth">
				<fa-icon icon="fas fa-chevron-right" />
			</button>
		</div>

		<PartialsProgrammesSchedulesCalendar
			ref="calendarRef"
			:current-displayed-date="currentDisplayedDate"
			@open-session-modal="openSessionDetailModal"
			@open-session-form-modal="openSessionFormModal"
		/>
	</div>

	<!-- Session Detail Modal -->
	<PartialsProgrammesSchedulesDetail
		v-if="showDetailModal"
		:schedule="selectedSchedule"
		@close="closeDetailModal"
		@edit="openSessionFormModal"
		@reschedule="openRescheduleModal"
		@cancel="openCancelModal"
	/>

	<!-- Session Form Modal -->
	<PartialsProgrammesSchedulesForm
		v-if="showSessionFormModal"
		:selected-date="selectedDate"
		:schedule="editSchedule"
		:available-programmes="availableProgrammes"
		:available-trainers="users"
		:available-rooms="rooms"
		@close="closeSessionFormModal"
		@submit-success="getMonthlySchedules"
	/>

	<!-- Reschedule Session Modal -->
	<PartialsProgrammesSchedulesRescheduleForm
		v-if="showRescheduleModal"
		:schedule="editSchedule"
		:available-trainers="users"
		@close="closeRescheduleModal"
		@submit-success="getMonthlySchedules"
	/>

	<!-- Cancel Session Modal -->
	<PartialsProgrammesSchedulesCancelForm
		v-if="showCancelModal"
		:schedule="editSchedule"
		@close="closeCancelModal"
		@submit-success="getMonthlySchedules"
	/>
</template>

<script setup>
defineProps({
	availableProgrammes: {
		type: Array,
		default: () => [],
	},
});
const { users, fetchUsers } = useUser();
const { rooms, fetchRooms } = useRooms();

const currentDisplayedDate = ref(
	new Date(new Date().getFullYear(), new Date().getMonth(), 1),
);
const calendarRef = ref(null);

// Detail Modal Data
const showDetailModal = ref(false);
const selectedSchedule = ref(null);

// schedule Modal Data
const showSessionFormModal = ref(false);
const isEditSession = ref(false);
const selectedDate = ref("");
const editSchedule = ref(null);

// Reschedule Modal Data
const showRescheduleModal = ref(false);

// Cancel Modal Data
const showCancelModal = ref(false);

const getMonthlySchedules = async () => {
	calendarRef.value?.fetchMonthlySchedule();
};

const prevMonth = async () => {
	const newDate = new Date(currentDisplayedDate.value);
	newDate.setMonth(newDate.getMonth() - 1);
	currentDisplayedDate.value = newDate;
};

const nextMonth = async () => {
	const newDate = new Date(currentDisplayedDate.value);
	newDate.setMonth(newDate.getMonth() + 1);
	currentDisplayedDate.value = newDate;
};

const openSessionDetailModal = (schedule) => {
	selectedSchedule.value = schedule;
	showDetailModal.value = true;
};

const closeDetailModal = () => {
	showDetailModal.value = false;
	selectedSchedule.value = null;
};

const openSessionFormModal = (date, schedule = null) => {
	isEditSession.value = schedule !== null;

	if (isEditSession.value) {
		editSchedule.value = schedule;
		selectedDate.value = date;
		closeDetailModal();
	} else {
		const year = currentDisplayedDate.value.getFullYear();
		const month = currentDisplayedDate.value.getMonth() + 1;
		const formattedDate = `${year}-${String(month).padStart(2, "0")}-${String(date).padStart(2, "0")}`;

		selectedDate.value = formattedDate;
	}
	showSessionFormModal.value = true;
};

// reset
const closeSessionFormModal = () => {
	isEditSession.value = false;
	showSessionFormModal.value = false;
	selectedDate.value = "";
	editSchedule.value = null;
};

const openRescheduleModal = (schedule) => {
	closeDetailModal();
	editSchedule.value = schedule;
	showRescheduleModal.value = true;
};

const closeRescheduleModal = () => {
	showRescheduleModal.value = false;
	editSchedule.value = null;
};

const openCancelModal = (schedule) => {
	closeDetailModal();
	editSchedule.value = schedule;
	showCancelModal.value = true;
};

const closeCancelModal = () => {
	showCancelModal.value = false;
	editSchedule.value = null;
};

const currentMonthYear = computed(() => {
	return currentDisplayedDate.value.toLocaleString("default", {
		month: "long",
		year: "numeric",
	});
});

onMounted(async () => {
	// Fetch both schedule data and form data in parallel
	await Promise.all([fetchUsers({ role: "trainer" }), fetchRooms()]);
});
</script>

<style lang="scss" scoped>
// Calendar styles
.section-header {
	margin-bottom: 1rem;

	.section-title {
		font-weight: 600;
		color: var(--dark);
	}
}

.calendar-navigation {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 1rem;
	margin-bottom: 1rem;
	color: var(--secondary);
	font-weight: bold;

	button {
		background: none;
		border: 1px solid var(--border);
		color: var(--secondary);
		padding: 8px 12px;
		border-radius: 8px;
		cursor: pointer;
    margin: 0;
		transition: all 0.2s;
		/* transition: background 0.2s ease; */

		&:hover {
			background: var(--secondary);
			color: var(--light);
		}
	}
}
</style>
