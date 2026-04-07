<template>
	<div>
		<div class="programme-detail-grid">
			<PartialsBookingsSynopsisPanel :programme="selectedProgramme" />

			<PartialsBookingsSchedulePanel
				:selected-session="selectedSchedule"
				@update-selected-session="updateSelectedSession"
				@reset-waiver-accepted="waiverAccepted = false"
			/>
		</div>

		<div class="liability-waiver-section">
			<label class="consent-label">
				<input v-model="waiverAccepted" type="checkbox" />
				I acknowledge that participation in physical activities carries
				inherent risks, and I assume full responsibility for any
				injuries, or health issues that may result including fatality.
			</label>
			<button
				style="width: 100%"
				:disabled="!canConfirm"
				@click="confirmSelection"
			>
				{{
					isLoading ? "Processing..." : "Confirm Programme Selection"
				}}
			</button>
		</div>
	</div>
</template>

<script setup>
const props = defineProps({
	isFromRegister: {
		type: Boolean,
		default: false,
	},
});

const { $notify } = useNuxtApp();
const { isLoading, createBooking } = useBookings();

const selectedSchedule = ref(null);
const waiverAccepted = ref(false);

const updateSelectedSession = (schedule) => {
	selectedSchedule.value = schedule;
};

const confirmSelection = async () => {
	if (!canConfirm.value) return;

	try {
		const bookingData = {
			scheduleId: selectedSchedule.value.id,
			isLiabilityWaiverAccepted: waiverAccepted.value,
		};

		await createBooking(bookingData);

		if (props.isFromRegister) {
			$notify
				.alert(
					"You have successfully selected your first programme. You can view your bookings in the Bookings section",
					"Programme Selected Successfully!",
					"Go To Dashboard",
				)
				.then(() => navigateTo("/dashboard/programmes#my-bookings"));
		} else {
			const schedule = selectedSchedule.value;
			$notify
				.alert(
					`You have successfully booked "${schedule.programme.name}" on ${schedule.date} at ${schedule.time}.`,
					"Booking Confirmed!",
				)
				.then(() => navigateTo("/dashboard/programmes#my-bookings"));
		}
	} catch (error) {
		if (props.isFromRegister) {
			$notify
				.alert(
					error?.message ||
						"An error occurred while confirming your programme selection.",
					"Booking Failed!",
					"Go To Dashboard",
				)
				.then(() => navigateTo("/dashboard/programmes#my-bookings"));
		} else {
			$notify.error(
				error?.message ||
					"An error occurred while confirming your programme selection.",
			);
		}
	} finally {
		// Reset form
		selectedSchedule.value = null;
		waiverAccepted.value = false;
	}
};

const selectedProgramme = computed(() => {
	if (!selectedSchedule.value?.programme) return null;

	return selectedSchedule.value.programme;
	/* return {
    workout: `${programme.durationMinutes || 30}mins session`,
    intensity: programme.intensity || programme.intensityLevel || 'Unknown',
    coverImage: programme.coverImage || 'https://placehold.co/300x150/e0e0e0/555555?text=Programme',
    synopsis: programme.synopsis || 'No description available.'
  }; */
});

const canConfirm = computed(() => {
	return selectedSchedule.value && waiverAccepted.value && !isLoading.value;
});
</script>

<style lang="scss" scoped>
.liability-waiver-section {
	margin-top: 30px;
	padding-top: 20px;
	border-top: 1px solid var(--border);
	text-align: center;

	.consent-label {
		font-size: 0.85rem;
		line-height: 1.3;
		display: flex;
		align-items: flex-start;
		margin-top: 20px;
		margin-bottom: 20px;
		text-align: left;
		color: var(--secondary);

		input {
			margin-right: 5px;
		}
	}

	button {
		max-width: 300px;
		margin: 0 auto;
	}
}

@media (max-width: 992px) {
	.modal-content {
		max-width: 768px;
	}
}

@media (max-width: 768px) {
	.modal-content {
		padding: 20px;
		margin: 2vh auto;
		max-width: 95%;
	}
	.liability-waiver-section {
		.consent-label {
			font-size: 0.75rem;
		}
		button {
			max-width: 250px;
		}
	}
}

@media (max-width: 480px) {
	.modal-content {
		padding: 15px;
	}
}
</style>
