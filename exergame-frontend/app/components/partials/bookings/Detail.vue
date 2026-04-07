<template>
	<div class="modal">
		<div class="modal-content">
			<span class="close-modal" @click="$emit('close')">&times;</span>
			<div v-if="booking" class="modal-programme-info">
				<h3 style="text-align: left">{{ selectedProgramme.name }}</h3>
				<h4>Synopsis</h4>
				<p>{{ selectedProgramme.synopsis }}</p>
				<div class="info-item">
					<fa-icon icon="fas fa-calendar-alt" />
					<span>Date: {{ formatDate(booking?.schedule?.day) }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-clock" />
					<span
						>Time:
						{{
							formatTime(
								booking?.schedule?.startTime,
								booking?.schedule?.endTime,
							)
						}}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-user" />
					<span>Trainer: {{ booking?.schedule?.trainer?.name }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-running" />
					<span
						>Type:
						{{ capitalize(selectedProgramme.sessionType) }}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-bolt" />
					<span
						>Intensity:
						{{ capitalize(selectedProgramme.intensityLevel) }}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-info-circle" />
					<span>Status: {{ capitalize(booking?.status) }}</span>
				</div>
				<div class="programme-image-container">
					<img
						class="programme-image"
						:src="selectedProgramme.coverImage"
						:alt="selectedProgramme.name"
            @error="handleCoverImageError(selectedProgramme.name, $event)"
					/>
				</div>
				<div class="modal-actions">
					<template v-if="showButtonActions">
						<template v-if="userRole !== 'user'">
							<button
								class="btn btn-primary"
								@click="editSession"
							>
								<fa-icon icon="fas fa-edit" /> Edit Session
							</button>
							<button
								id="reschedule-session-btn"
								class="btn btn-outline"
								:disabled="isSessionInPast"
								:title="
									isSessionInPast
										? 'Cannot reschedule past sessions'
										: 'Reschedule this session'
								"
								@click="rescheduleSession"
							>
								<fa-icon icon="fas fa-clock" /> Reschedule
								Session
							</button>
							<button
								v-if="canCancel"
								id="cancel-session-btn"
								class="btn btn-outline btn-cancel"
								:disabled="isSessionInPast"
								:title="
									isSessionInPast
										? 'Cannot cancel past sessions'
										: 'Cancel this session'
								"
								@click="cancelSession"
							>
								<fa-icon icon="fas fa-times-circle" /> Cancel
								Session
							</button>
						</template>
						<template v-else>
							<button
								v-if="canCancel"
								id="cancel-session-btn"
								class="btn btn-outline btn-cancel"
								:disabled="isSessionInPast"
								:title="
									isSessionInPast
										? 'Cannot cancel past booking'
										: 'Cancel this booking'
								"
								@click="cancelSession"
							>
								<fa-icon icon="fas fa-times-circle" /> Cancel
								Booking
							</button>
							<button
								v-if="canRebook"
								id="rebook-session-btn"
								class="btn btn-outline btn-rebook"
								@click="rebookSession"
							>
								<fa-icon icon="fas fa-redo" /> Re-book Session
							</button>
						</template>
					</template>
				</div>
			</div>
			<div v-else>
				<p class="text-center">No session data available.</p>
			</div>
		</div>
	</div>
</template>

<script setup>
const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);

const props = defineProps({
	booking: {
		type: Object,
		default: null,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["close", "edit", "reschedule", "cancel", "rebook"]);

const handleCoverImageError = (title, event) => {
  event.onerror = null;
  event.target.src = generateCoverImage(title);
};

const editSession = () => {
	const session = props.booking;
	emit("edit", session.day, session);
};

const rescheduleSession = () => {
	// Prevent rescheduling of past sessions
	if (isSessionInPast.value) {
		return;
	}

	emit("reschedule", props.booking);
};

const cancelSession = () => {
	emit("cancel", props.booking);
};

const rebookSession = () => {
	// Prevent rebooking of past sessions
	if (isSessionInPast.value) {
		return;
	}

	emit("rebook", props.booking);
};

const canCancel = computed(() => {
	return !isSessionInPast.value && props.booking.status !== "cancelled";
	// && props.booking.schedule?.isCancelled === false;
});

const canRebook = computed(() => {
	return !isSessionInPast.value && props.booking.status === "cancelled";
});

const showButtonActions = computed(() => {
	if (props.isReadonly) {
		return false;
	} else {
		return (
			!isSessionExpired(props.booking.schedule) ||
			!["completed"].includes(props.booking.status)
		);
	}
});

const selectedProgramme = computed(() => {
	const session = props.booking.schedule;
	if (!session) return null;

	return {
		name: session.programme?.name || "Unknown Programme",
		synopsis: session.programme?.synopsis || "No Synopsis Available",
		sessionType: session.programme?.sessionType || "Unknown",
		intensityLevel: session.programme?.intensityLevel || "Unknown",
		coverImage:
			session.programme?.coverImage ||
			"https://placehold.co/600x400/007bff/ffffff?text=Programme",
	};
});

const isSessionInPast = computed(() => {
	const session = props.booking.schedule;
	return isSessionExpired(session);
});
</script>

<style lang="scss" scoped>
.modal {
	.modal-content {
		margin: auto;

		.modal-programme-info {
			.info-item {
				display: flex;
				align-items: flex-start;
				gap: 10px;
				font-size: 0.95rem;
				color: var(--dark);
				margin-top: 10px;

				.svg-inline--fa {
					color: var(--secondary);
					width: 20px;
					flex-shrink: 0;
					margin-top: 3px;
				}
			}
		}
	}

	.modal-actions {
		flex-direction: column;
		gap: 10px;

		button {
			margin: 0;

			&:disabled {
				opacity: 0.5;
				cursor: not-allowed;

				&:hover {
					opacity: 0.5;
				}
			}

			&.btn-cancel {
				background: rgba(231, 76, 60, 0.15) !important;
				color: #e74c3c !important;

				&:disabled {
					opacity: 0.5;
					cursor: not-allowed;
					background: rgba(231, 76, 60, 0.05) !important;
					color: #a94442 !important;

					&:hover {
						background: rgba(231, 76, 60, 0.05) !important;
						color: #a94442 !important;
					}
				}
			}
		}
	}
}
</style>
