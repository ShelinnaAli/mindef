<template>
	<div class="modal">
		<div class="modal-content" style="max-width: 500px">
			<span class="close-modal" @click="closeModal">&times;</span>
			<div class="modal-programme-info">
				<h3><fa-icon icon="fas fa-undo" /> Re-book Session</h3>

				<div v-if="validationError" class="error-message">
					<fa-icon icon="fas fa-exclamation-triangle" />
					{{ validationError }}
				</div>
				<div v-else>
					<h4>Are you sure you want to re-book this session?</h4>
					<div v-if="selectedSession" class="session-info">
						<div class="session-details">
							<h4>
								{{ selectedSession.schedule?.programme?.name }}
							</h4>
							<div class="info-item">
								<fa-icon icon="fas fa-calendar-alt" />
								<span
									>Date:
									{{
										formatDate(
											selectedSession.schedule?.day,
										)
									}}</span
								>
							</div>
							<div class="info-item">
								<fa-icon icon="fas fa-clock" />
								<span
									>Time:
									{{
										formatTime(
											selectedSession.schedule?.startTime,
											selectedSession.schedule?.endTime,
										)
									}}</span
								>
							</div>
							<div class="info-item">
								<fa-icon icon="fas fa-user" />
								<span
									>Trainer:
									{{
										selectedSession.schedule?.trainer?.name
									}}</span
								>
							</div>
							<div class="info-item">
								<fa-icon icon="fas fa-map-marker-alt" />
								<span
									>Location:
									{{
										selectedSession.schedule?.room?.name
									}}</span
								>
							</div>
						</div>
					</div>
					<div
						v-if="
							selectedSession.status === 'cancelled' &&
							selectedSession.cancellationReason
						"
						class="session-info"
					>
						<div class="session-details">
							<div class="info-item">
								<fa-icon icon="fas fa-clock" />
								<span
									>Time:
									{{
										formatDateTime(
											selectedSession.cancellationAt,
										)
									}}</span
								>
							</div>
							<div class="info-item cancellation-reason">
								<fa-icon icon="fas fa-info-circle" />
								<span
									>Previous cancellation:
									<em>{{
										selectedSession.cancellationReason
									}}</em></span
								>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="modal-actions">
				<button
					style="width: 100%; justify-content: center"
					class="btn btn-primary"
					:disabled="isLoading || hasValidationError"
					@click="confirmRebook"
				>
					<fa-icon icon="fas fa-undo" />
					{{ isLoading ? "Processing..." : "Re-book Session" }}
				</button>
				<button
					style="width: 100%; justify-content: center"
					class="btn btn-outline"
					:disabled="isLoading"
					@click="closeModal"
				>
					<fa-icon icon="fas fa-times" />
					Cancel
				</button>
			</div>
		</div>
	</div>
</template>

<script setup>
const { $notify } = useNuxtApp();
const { currentBooking, isLoading, updateBooking } = useBookings();

const props = defineProps({
	selectedSession: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["close", "update-booking-status"]);

const validationError = ref("");

// Validate if the session can be rebooked
const hasValidationError = computed(() => {
	return !!validationError.value;
});

// Validate rebook conditions
const validateRebook = () => {
	validationError.value = "";

	if (!props.selectedSession) {
		validationError.value = "Session data not available";
		return false;
	}

	// Check if session status is cancelled
	if (props.selectedSession.status !== "cancelled") {
		validationError.value = "Only cancelled sessions can be rebooked";
		return false;
	}

	// Check if session is not in the past
	const sessionDate = new Date(
		props.selectedSession.rawBooking?.schedule?.day ||
			props.selectedSession.day ||
			props.selectedSession.date,
	);
	const today = new Date();

	// Reset time to compare only dates
	sessionDate.setHours(0, 0, 0, 0);
	today.setHours(0, 0, 0, 0);

	// If session has start time, check with current time on same day
	if (
		props.selectedSession.rawBooking?.schedule?.startTime ||
		props.selectedSession.startTime
	) {
		const startTime =
			props.selectedSession.rawBooking?.schedule?.startTime ||
			props.selectedSession.startTime;
		const sessionDay =
			props.selectedSession.rawBooking?.schedule?.day ||
			props.selectedSession.day ||
			props.selectedSession.date;
		const sessionDateTime = new Date(sessionDay + "T" + startTime);
		const now = new Date();

		if (sessionDateTime < now) {
			validationError.value = "Cannot rebook past sessions";
			return false;
		}
	} else if (sessionDate < today) {
		validationError.value = "Cannot rebook past sessions";
		return false;
	}

	return true;
};

const closeModal = () => {
	validationError.value = "";
	emit("close");
};

const confirmRebook = async () => {
	if (!validateRebook()) {
		return;
	}

	try {
		// Get the booking ID from the booking data
		const bookingId =
			props.selectedSession.rawBooking?.id || props.selectedSession.id;

		if (!bookingId) {
			throw new Error("Booking ID not found");
		}

		// Update the booking to remove cancellation (rebook)
		const updateData = {
			status: "pending",
			cancellationReason: null,
			cancellationAt: null,
		};

		await updateBooking(bookingId, updateData);

		$notify.success("Session rebooked successfully!");

		emit("update-booking-status", currentBooking.value);
		closeModal();
	} catch (error) {
		console.error("Rebook error:", error);
		$notify.error(
			error.message || "Failed to rebook session. Please try again.",
		);
	}
};

// Watch for session data changes to validate
watch(
	() => props.selectedSession,
	() => {
		if (props.selectedSession) {
			validateRebook();
		}
	},
	{ immediate: true },
);
</script>

<style lang="scss" scoped>
.modal {
	.modal-content {
		margin: auto;

		.modal-programme-info {
			.session-info {
				background: var(--light);
				border: 1px solid var(--border);
				border-radius: 8px;
				padding: 15px;
				margin-bottom: 20px;

				h4 {
					margin: 0 0 10px 0;
					color: var(--secondary);
					font-weight: 600;
				}

				p {
					margin: 5px 0;
					font-size: 0.95rem;

					strong {
						color: var(--dark);
					}
				}

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
