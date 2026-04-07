<template>
	<div class="card-grid">
		<div v-for="booking in userBookings" :key="booking.id" class="card">
			<div class="card-header">
				<h3 class="card-title">
					{{ booking.schedule?.programme?.name }}
				</h3>
				<div class="status-badge" :class="statusClass(booking)">
					{{ statusLabel(booking) }}
				</div>
			</div>
			<div class="card-body">
				<div v-if="userRole !== 'user'" class="user-info">
					<h4>User Info:</h4>
					<p>
						<fa-icon icon="fas fa-user" /> {{ booking.user?.name }}
					</p>
					<p>
						<fa-icon icon="fas fa-phone" />
						{{ booking.user?.phone }}
					</p>
				</div>
				<p>
					<fa-icon icon="fas fa-calendar-alt" />
					{{ formatDate(booking.schedule?.day) }}
				</p>
				<p>
					<fa-icon icon="fas fa-clock" />
					{{
						formatTime(
							booking.schedule?.startTime,
							booking.schedule?.endTime,
						)
					}}
				</p>
				<p v-if="booking.schedule?.programme?.sessionType == 'group'">
					<fa-icon icon="fas fa-users" /> Group Session ({{
						booking.schedule?.totalBookings || 0
					}}
					/
					{{
						booking.schedule?.programme?.maxParticipants || 0
					}}
					slots available)
				</p>
				<p>
					<fa-icon icon="fas fa-user" /> Trainer:
					{{ booking.schedule?.trainer?.name }}
				</p>
				<p>
					<fa-icon icon="fas fa-map-marker-alt" />
					{{ booking.schedule?.room?.name }}
				</p>
				<p>
					<fa-icon icon="fas fa-bolt" />
					{{ booking.schedule?.programme?.intensityLevel }} Intensity
				</p>
				<p
					v-if="
						booking.status === 'cancelled' &&
						booking.cancellationReason
					"
					class="cancellation-reason"
				>
					<fa-icon icon="fas fa-info-circle" /> Reason By User: <br />
					<em class="cancellation-reason-text">{{
						booking.cancellationReason
					}}</em>
				</p>
				<p
					v-if="
						booking.schedule?.isCancelled &&
						booking.schedule?.cancellationReason
					"
					class="cancellation-reason"
				>
					<fa-icon icon="fas fa-info-circle" /> Reason By Trainer:
					<br />
					<em class="cancellation-reason-text">{{
						booking.schedule?.cancellationReason
					}}</em>
				</p>
			</div>
			<div class="card-footer">
				<template v-if="showButtonActions(booking)">
					<template v-if="userRole == 'user'">
						<button
							class="action-btn"
							@click="viewDetails(booking)"
						>
							View Details
						</button>
						<button
							v-if="canRebook(booking)"
							class="action-btn"
							@click="reBooking(booking.id)"
						>
							Re-book
						</button>
						<button
							v-if="canCancel(booking)"
							class="action-btn btn-cancel"
							@click="cancelBooking(booking.id)"
						>
							{{
								booking.status === "pending"
									? "Cancel"
									: "Cancel Booking"
							}}
						</button>
					</template>

					<template v-else>
						<template v-if="isReadonly">
							<button
								class="action-btn"
								@click="viewDetails(booking)"
							>
								View Details
							</button>
						</template>
						<template v-else>
							<button
								v-if="canConfirm(booking)"
								class="action-btn"
								@click="confirmBooking(booking.id)"
							>
								Confirm
							</button>
							<button
								v-if="canRebook(booking)"
								class="action-btn"
								@click="reBooking(booking.id)"
							>
								Re-book
							</button>
							<button
								v-if="canComplete(booking)"
								class="action-btn"
								@click="completeBooking(booking.id)"
							>
								Complete
							</button>
							<button
								v-if="canCancel(booking)"
								class="action-btn btn-cancel"
								@click="cancelBooking(booking.id)"
							>
								{{
									booking.status === "pending"
										? "Cancel"
										: "Cancel Booking"
								}}
							</button>
						</template>
					</template>
				</template>
			</div>
		</div>
	</div>

	<!-- Schedule Detail Popup -->
	<PartialsBookingsDetail
		v-if="showDetailModal"
		:booking="selectedBooking"
		@close="closeDetailModal"
		@cancel="cancelBooking(selectedBooking.id)"
		@rebook="reBooking(selectedBooking.id)"
	/>

	<!-- Rebook Form Popup -->
	<PartialsBookingsRebookForm
		v-if="showRebookModal"
		:selected-session="selectedBooking"
		@close="closeRebookModal"
		@update-booking-status="updateBookingStatus"
	/>

	<!-- Cancel Form Popup -->
	<PartialsBookingsCancelForm
		v-if="showCancelModal"
		:selected-session="selectedBooking"
		@close="closeCancelModal"
		@update-booking-status="updateBookingStatus"
	/>
</template>

<script setup>
const authStore = useAuthStore();
const { $notify } = useNuxtApp();
const { currentBooking, updateBooking } = useBookings();

const selectedBooking = ref(null);
const showDetailModal = ref(false);
const showRebookModal = ref(false);
const showCancelModal = ref(false);

const props = defineProps({
	userBookings: {
		type: Array,
		required: true,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["update-booking-status"]);

const userRole = computed(() => authStore.user?.role);

const statusLabel = (bookingItem) => {
	const p = bookingItem.schedule?.programme;
	const s = bookingItem.schedule;
	const status = bookingItem.status;

	if (
		isSessionFull(p?.sessionType, s?.totalBookings, p?.maxParticipants) &&
		status === "pending"
	) {
		return "Full";
	} else if (s.isCancelled) {
		return "Session is Cancelled";
	} else if (isSessionExpired(s)) {
		if (status === "completed") {
			return status;
		}
		return "Session is Over";
	}

	return status;
};

const statusClass = (bookingItem) => {
	const p = bookingItem.schedule?.programme;
	const s = bookingItem.schedule;
	const status = bookingItem.status;

	if (
		isSessionFull(p?.sessionType, s?.totalBookings, p?.maxParticipants) &&
		!isSessionExpired(s)
	) {
		return `status-${status?.toLowerCase()}`;
	} else if (
		isSessionFull(p?.sessionType, s?.totalBookings, p?.maxParticipants) ||
		s.isCancelled ||
		isSessionExpired(s)
	) {
		return "status-inactive";
	}
	return `status-${status?.toLowerCase()}`;
};

const showButtonActions = (bookingItem) => {
	return (
		(!isSessionExpired(bookingItem.schedule) &&
			!bookingItem.schedule?.isCancelled) ||
		["confirmed"].includes(bookingItem.status)
	);
};

const canCancel = (bookingItem) => {
	return (
		!isSessionExpired(bookingItem.schedule) &&
		!["cancelled", "completed"].includes(bookingItem.status) &&
		!props.isReadonly
	);
};

const canRebook = (bookingItem) => {
	return ["cancelled"].includes(bookingItem.status) && !props.isReadonly;
};

const canComplete = (bookingItem) => {
	const scheduleDate = new Date(
		`${bookingItem.schedule.day} ${bookingItem.schedule.startTime}`,
	);
	const now = new Date();

	return (
		["confirmed"].includes(bookingItem.status) &&
		!props.isReadonly &&
		now > scheduleDate &&
		!bookingItem.schedule?.isCancelled
	);
};

const canConfirm = (bookingItem) => {
	const p = bookingItem.schedule?.programme;
	const s = bookingItem.schedule;
	const status = bookingItem.status;

	return (
		["pending"].includes(status) &&
		!isSessionFull(p?.sessionType, s?.totalBookings, p?.maxParticipants) &&
		!props.isReadonly
	);
};

const viewDetails = (bookingItem) => {
	selectedBooking.value = bookingItem;
	showDetailModal.value = true;
};

// Close detail modal
const closeDetailModal = () => {
	showDetailModal.value = false;
	selectedBooking.value = null;
};

const cancelBooking = (bookingId) => {
	// Find the booking item from props
	const bookingItem = props.userBookings.find((b) => b.id === bookingId);

	if (!bookingItem) {
		$notify.error("Booking not found");
		return;
	}
	closeDetailModal();

	// Show cancel modal instead of confirmation dialog
	selectedBooking.value = bookingItem;
	showCancelModal.value = true;
};

// Close cancel modal
const closeCancelModal = () => {
	showCancelModal.value = false;
	selectedBooking.value = null;
};

const reBooking = (bookingId) => {
	// Find the booking item
	const bookingItem = props.userBookings.find((b) => b.id === bookingId);

	if (!bookingItem) {
		$notify.error("Booking not found");
		return;
	}
	closeDetailModal();

	// Validate that it's a cancelled booking
	if (bookingItem.status !== "cancelled") {
		$notify.error("Only cancelled bookings can be rebooked");
		return;
	}

	// Show rebook modal
	selectedBooking.value = bookingItem;
	showRebookModal.value = true;
};

// Close rebook modal
const closeRebookModal = () => {
	showRebookModal.value = false;
	selectedBooking.value = null;
};

const confirmBooking = (bookingId) => {
	// Find the booking item from props
	const bookingItem = props.userBookings.find((b) => b.id === bookingId);

	if (!bookingItem) {
		$notify.error("Booking not found");
		return;
	}

	$notify
		.confirm(
			"Are you sure you want to confirm this booking request?",
			"Confirm User Booking",
		)
		.then(async ({ noty, isConfirmed }) => {
			if (isConfirmed) {
				try {
					const updateData = {
						status: "confirmed",
					};

					await updateBooking(bookingId, updateData);

					$notify.success("Booking confirmed successfully");

					emit("update-booking-status", currentBooking.value);
					noty.close();
				} catch (error) {
					$notify.error(error.message || "Failed to confirm booking");
				}
			}
		});
};

const completeBooking = (bookingId) => {
	// Find the booking item from props
	const bookingItem = props.userBookings.find((b) => b.id === bookingId);

	if (!bookingItem) {
		$notify.error("Booking not found");
		return;
	}

	$notify
		.confirm(
			'Are you sure you want to mark this booking as "Complete"?',
			"Complete User Booking",
		)
		.then(async ({ noty, isConfirmed }) => {
			if (isConfirmed) {
				try {
					const updateData = {
						status: "completed",
					};

					await updateBooking(bookingId, updateData);

					$notify.success("Booking completed successfully");

					emit("update-booking-status", currentBooking.value);
					noty.close();
				} catch (error) {
					$notify.error(error.message || "Failed to confirm booking");
				}
			}
		});
};

// Handle cancel form submission
const updateBookingStatus = (bookingData) => {
	emit("update-booking-status", bookingData);
};
</script>

<style lang="scss" scoped>
.card-body {
	height: 250px;
	overflow-y: auto;

	p {
		text-transform: capitalize;
	}

	.user-info {
		border: 1px solid var(--border);
		padding: 5px;
		background-color: var(--light);
		border-radius: 10px;
		margin-bottom: 10px;
		font-size: 0.9rem;

		p {
			margin-bottom: 5px !important;
		}
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
