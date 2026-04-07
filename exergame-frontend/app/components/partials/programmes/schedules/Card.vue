<template>
	<div class="card-grid">
		<div v-for="schedule in schedules" :key="schedule.id" class="card">
			<div class="card-header">
				<h3 class="card-title">{{ schedule.programme?.name }}</h3>
				<div class="card-subtitle">
					<div
						class="status-badge"
						:class="`status-${schedule.isCancelled ? 'cancelled' : 'active'}`"
					>
						{{ schedule.isCancelled ? "Cancelled" : "Active" }}
					</div>
					<button class="action-btn" @click="viewUserList(schedule)">
						({{ schedule.totalBookings }} attendees)
					</button>
				</div>
			</div>
			<div class="card-body">
				<p>
					<fa-icon icon="fas fa-calendar-alt" /> {{ schedule.date }}
				</p>
				<p><fa-icon icon="fas fa-clock" /> {{ schedule.time }}</p>
				<p v-if="schedule.programme?.sessionType == 'group'">
					<fa-icon icon="fas fa-users" /> Group Session ({{
						schedule.totalBookings
					}}
					/ {{ schedule.programme?.maxParticipants }} slots available)
				</p>
				<p>
					<fa-icon icon="fas fa-user" /> Trainer:
					{{ schedule.trainer?.name }}
				</p>
				<p>
					<fa-icon icon="fas fa-map-marker-alt" />
					{{ schedule.room?.name }}
				</p>
				<p>
					<fa-icon icon="fas fa-bolt" />
					{{
						capitalize(schedule.programme?.intensityLevel)
					}}
					Intensity
				</p>
				<p
					v-if="schedule.isCancelled && schedule.cancellationReason"
					class="cancellation-reason"
				>
					<fa-icon icon="fas fa-info-circle" /> Reason: <br />
					<em class="cancellation-reason-text">{{
						schedule.cancellationReason
					}}</em>
				</p>
			</div>
			<div class="card-footer">
				<button class="action-btn" @click="viewDetails(schedule)">
					View Details
				</button>
			</div>
		</div>
	</div>

	<!-- Schedule Detail Popup -->
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
		:schedule="editSession"
		:available-programmes="programmes"
		:available-trainers="users"
		:available-rooms="rooms"
		@close="closeSessionFormModal"
		@submit-success="$emit('refresh')"
	/>

	<!-- Reschedule Session Modal -->
	<PartialsProgrammesSchedulesRescheduleForm
		v-if="showRescheduleModal"
		:schedule="selectedSchedule"
		:available-trainers="users"
		@close="closeRescheduleModal"
		@submit-success="$emit('refresh')"
	/>

	<!-- Cancel Session Modal -->
	<PartialsProgrammesSchedulesCancelForm
		v-if="showCancelModal"
		:schedule="selectedSchedule"
		@close="closeCancelModal"
		@submit-success="$emit('refresh')"
	/>

	<PartialsBookingsUserList
		v-if="showUserListModal"
		:schedule="selectedSchedule"
		@close="closeUserListModal"
	/>
</template>

<script setup>
const { users, fetchUsers } = useUser();
const { rooms, fetchRooms } = useRooms();
const { programmes, fetchProgrammes } = useProgrammes();

defineProps({
	schedules: {
		type: Array,
		required: true,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});

defineEmits(["refresh"]);

// Detail Modal Data
const showDetailModal = ref(false);
const selectedSchedule = ref(null);

// Session Modal Data
const showSessionFormModal = ref(false);
const isEditSession = ref(false);
const editSession = ref(null);
const selectedDate = ref("");

// Reschedule Modal Data
const showRescheduleModal = ref(false);

// Cancel Modal Data
const showCancelModal = ref(false);

const showUserListModal = ref(false);

const viewDetails = (schedule) => {
	selectedSchedule.value = schedule;
	showDetailModal.value = true;
};

// Close detail modal
const closeDetailModal = () => {
	showDetailModal.value = false;
	selectedSchedule.value = null;
};

const openSessionFormModal = (date, schedule) => {
	closeDetailModal();
	editSession.value = schedule;
	selectedDate.value = date;
	showSessionFormModal.value = true;
};

// reset
const closeSessionFormModal = () => {
	isEditSession.value = false;
	showSessionFormModal.value = false;
	selectedDate.value = "";
	editSession.value = null;
};

const openRescheduleModal = (schedule) => {
	closeDetailModal();
	selectedSchedule.value = schedule;
	showRescheduleModal.value = true;
};

const closeRescheduleModal = () => {
	showRescheduleModal.value = false;
	selectedSchedule.value = null;
};

const openCancelModal = (schedule) => {
	closeDetailModal();
	selectedSchedule.value = schedule;
	showCancelModal.value = true;
};

const closeCancelModal = () => {
	showCancelModal.value = false;
	selectedSchedule.value = null;
};

const viewUserList = (schedule) => {
	if (schedule.totalBookings === 0) return;
	selectedSchedule.value = schedule;
	showUserListModal.value = true;
};

// Close user list modal
const closeUserListModal = () => {
	showUserListModal.value = false;
	selectedSchedule.value = null;
};

onMounted(async () => {
	await Promise.all([
		fetchProgrammes({sorts: 'name:asc'}),
		fetchUsers({ role: "trainer" }),
		fetchRooms(),
	]);
});
</script>

<style lang="scss" scoped>
.card-body {
	height: 250px;
	overflow-y: auto;

	p {
		text-transform: capitalize;
	}

	.cancellation-reason {
		display: block;

		.cancellation-reason-text {
			color: var(--gray);
			line-height: 15px;
			font-size: 14px;
			margin-left: 1.5rem;
		}
	}
}
.status-badge {
	text-transform: capitalize;
}

.action-btn {
	font-weight: normal;

	&.btn-cancel {
		background: rgba(231, 76, 60, 0.15);
		color: #e74c3c;

		&:hover {
			box-shadow: 0 5px 15px rgba(231, 76, 60, 0.2);
		}
	}
}
</style>
