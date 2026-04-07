<template>
	<div class="calendar-item-wrapper">
		<template v-if="userRole === 'user'">
			<label
				v-for="schedule in schedules"
				:key="schedule.time"
				:for="schedule.id"
				class="schedule-slot"
				:class="{
					available:
						!isPastDate && lowercase(schedule.status) === 'active',
					unavailable:
						isFullBooked(schedule),
					selected: !isPastDate && isSessionSelected(schedule),
					cancelled:
						!isPastDate &&
						lowercase(schedule.status) === 'cancelled',
					'not-allowed':
						isPastDate ||
            lowercase(schedule.status) === 'cancelled'
				}"
				@click="selectSession(schedule, isPastDate)"
			>
				<input
					:id="schedule.id"
					type="radio"
					:name="'session_selection_' + date"
					:disabled="lowercase(schedule.status) !== 'active' && isFullBooked(schedule)"
				/>
				<span>{{ schedule?.programme?.name }}</span>
				<span v-if="userRole === 'user'" class="difficulty">{{
					capitalize(schedule.programme?.intensityLevel)
				}}</span>
				<span>{{ schedule.time }}</span>
				<span v-if="userRole === 'user'">
          ({{ schedule.programme?.durationMinutes }}mins)
        </span>
				<span v-else>{{ lowercase(schedule.status) }}</span>
        <span v-if="schedule.programme?.sessionType === 'group'" :class="{ full: isFullBooked(schedule) }">
          {{ schedule.totalBookings }} / {{ schedule.programme?.maxParticipants }} {{ isFullBooked(schedule) ? '(Full)' : ''  }}
        </span>
			</label>
		</template>
		<template v-else>
			<div
				v-for="schedule in schedules"
				:key="schedule.id"
				class="schedule-slot"
				:class="{
					cancelled:
						!isPastDate &&
						lowercase(schedule.status) === 'cancelled',
				}"
				@click="openSessionModal(schedule)"
			>
				<span class="slot-title">{{ schedule.programme?.name }}</span>
				<span class="slot-time"
					>{{ schedule.time }} {{ schedule.trainer? '('+schedule.trainer?.name +')' : '' }}</span
				>
			</div>
		</template>
	</div>
</template>

<script setup>
const props = defineProps({
	schedules: {
		type: Array,
		default: () => [],
	},
	date: {
		type: [String, Number],
		required: true,
	},
	isPastDate: {
		type: Boolean,
		default: false,
	},
	selectedSession: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["session-selected", "open-session-modal"]);
const authStore = useAuthStore();

const isSessionSelected = (schedule) => {
	if (!props.selectedSession) return false;
	return (
		props.selectedSession.date === schedule.date &&
		props.selectedSession.time === schedule.time &&
		props.selectedSession.programmeId === schedule.programmeId
	);
};

const selectSession = (schedule, isPastDate) => {
	if (userRole.value === "user") {
		if (
			lowercase(schedule.status) !== "cancelled" &&
			!isPastDate &&
      !isFullBooked(schedule)
		) {
			emit("session-selected", schedule);
		}
	} else {
		emit("session-selected", schedule);
	}
};

const openSessionModal = (schedule) => {
	emit("open-session-modal", schedule);
};

const isFullBooked = (schedule) => {
  return schedule.totalBookings >= schedule.programme.maxParticipants;
};

const userRole = computed(() => authStore.user?.role);
</script>
