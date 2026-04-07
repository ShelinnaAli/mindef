<template>
	<div class="programme-schedule-panel">
		<div class="calendar-navigation">
			<button @click="prevMonth">
				<fa-icon icon="fas fa-chevron-left" />
			</button>
			<span class="current-month">{{ currentMonthYear }}</span>
			<button @click="nextMonth">
				<fa-icon icon="fas fa-chevron-right" />
			</button>
		</div>

		<PartialsProgrammesSchedulesCalendar
			:current-displayed-date="currentDate"
			:selected-session="selectedSession"
			:available-programmes="programmes"
			@session-selected="selectSession"
		/>
	</div>
</template>

<script setup>
defineProps({
	selectedSession: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["reset-waiver-accepted", "update-selected-session"]);
const { programmes, fetchProgrammes } = useProgrammes();
const currentDate = ref(new Date());

const updateCurrentDate = (newDate) => {
	currentDate.value = newDate;
	selectSession(null);
	emit("reset-waiver-accepted");
};

const selectSession = (session) => {
	emit("update-selected-session", session);
};

const prevMonth = () => {
	const newDate = new Date(currentDate.value);
	newDate.setMonth(newDate.getMonth() - 1);
	updateCurrentDate(newDate);
};

const nextMonth = () => {
	const newDate = new Date(currentDate.value);
	newDate.setMonth(newDate.getMonth() + 1);
	updateCurrentDate(newDate);
};

const currentMonthYear = computed(() => {
	return currentDate.value.toLocaleString("en-US", {
		month: "long",
		year: "numeric",
	});
});

onMounted(() => fetchProgrammes({sorts: 'name:asc'}));
</script>

<style lang="scss" scoped>
.calendar-navigation {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15px;

	button {
		background: none;
		border: 1px solid var(--border);
		color: var(--secondary);
		padding: 8px 12px;
		border-radius: 8px;
		cursor: pointer;
		transition: all 0.2s;

		&:hover {
			background: var(--secondary);
			color: var(--light);
		}
	}

	span {
		font-weight: 600;
		font-size: 1.1rem;
		color: var(--secondary);
	}
}
</style>
