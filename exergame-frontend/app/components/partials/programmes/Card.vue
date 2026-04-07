<template>
	<div class="card-grid">
		<div v-for="programme in programmes" :key="programme.id" class="card">
			<div class="card-header">
				<h3 class="card-title">{{ programme.name }}</h3>
				<div class="card-subtitle">
					<div class="status-badge" :class="statusClass(programme)">
						{{ statusLabel(programme) }}
					</div>
					<button class="action-btn" @click="viewUserList(programme)">
						({{ programme.totalBookings }} attendees)
					</button>
				</div>
			</div>
			<div class="card-body">
				<template v-if="programme.schedule">
					<p>
						<fa-icon icon="fas fa-calendar-alt" />
						{{ formatDate(programme.schedule.day) }}
					</p>
					<p>
						<fa-icon icon="fas fa-clock" />
						{{
							formatTime(
								programme.schedule.startTime,
								programme.schedule.endTime,
							)
						}}
					</p>
					<p v-if="programme.sessionType == 'group'">
						<fa-icon icon="fas fa-users" /> Group Session ({{
							programme.schedule.totalBookings || 0
						}}
						/ {{ programme.maxParticipants }} slots available)
					</p>
					<p>
						<fa-icon icon="fas fa-user" /> Trainer:
						{{ programme.schedule.trainer?.name }}
					</p>
					<p>
						<fa-icon icon="fas fa-map-marker-alt" />
						{{ programme.schedule.room?.name }}
					</p>
					<p>
						<fa-icon icon="fas fa-bolt" />
						{{ programme.intensityLevel }} Intensity
					</p>
				</template>
				<template v-else>No sessions available</template>
			</div>
			<div class="card-footer">
				<button class="action-btn" @click="viewDetails(programme)">
					View Details
				</button>
			</div>
		</div>
	</div>

	<!-- Schedule Detail Popup -->
	<PartialsProgrammesDetail
		v-if="showDetailModal"
		:programme="selectedItem"
		:is-readonly="isReadonly"
		@close="closeDetailModal"
	/>

	<PartialsBookingsUserList
		v-if="showUserListModal"
		:programme="selectedItem"
		@close="closeUserListModal"
	/>
</template>

<script setup>
const selectedItem = ref(null);
const showDetailModal = ref(false);
const showUserListModal = ref(false);

defineProps({
	programmes: {
		type: Array,
		required: true,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});

const viewUserList = (schedule) => {
	if (schedule.totalBookings === 0) return;
	selectedItem.value = schedule;
	showUserListModal.value = true;
};

// Close user list modal
const closeUserListModal = () => {
	showUserListModal.value = false;
	selectedItem.value = null;
};

const statusLabel = (programmeItem) => {
	const p = programmeItem;
	const s = programmeItem.schedule;

	if (s) {
		if (
			isSessionFull(
				p?.sessionType,
				s?.totalBookings,
				p?.maxParticipants,
			) &&
			s.isCancelled === false
		) {
			return "Full";
		} else if (s.isCancelled) {
			return "Cancelled";
		} else if (isSessionExpired(s)) {
			return "Session is Over";
		}

		return "Active";
	} else {
		return "Not Available";
	}
};

const statusClass = (programmeItem) => {
	const p = programmeItem;
	const s = programmeItem.schedule;

	if (s) {
		if (
			isSessionFull(
				p?.sessionType,
				s?.totalBookings,
				p?.maxParticipants,
			) &&
			!isSessionExpired(s)
		) {
			return `status-${s.isCancelled ? "cancelled" : "active"}`;
		} else if (
			isSessionFull(p, s) ||
			s.isCancelled ||
			isSessionExpired(s)
		) {
			return "status-inactive";
		}
		return "status-active";
	} else {
		return "status-inactive";
	}
};

const viewDetails = (programmeItem) => {
	selectedItem.value = programmeItem;
	showDetailModal.value = true;
};

// Close detail modal
const closeDetailModal = () => {
	showDetailModal.value = false;
	selectedItem.value = null;
};
</script>

<style lang="scss" scoped>
.card-body {
	height: 215px;
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
  margin: 0;

	&.btn-cancel {
		background: rgba(231, 76, 60, 0.15);
		color: #e74c3c;

		&:hover {
			box-shadow: 0 5px 15px rgba(231, 76, 60, 0.2);
		}
	}
}
</style>
