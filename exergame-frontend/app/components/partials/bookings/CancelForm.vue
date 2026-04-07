<template>
	<div class="modal">
		<div class="modal-content" style="max-width: 500px">
			<span class="close-modal" @click="$emit('close')">&times;</span>
			<div class="modal-programme-info">
				<h3>Cancel Booking</h3>

				<div v-if="selectedSession" class="cancel-info">
					<div class="session-details">
						<h4>{{ selectedSession.schedule?.programme?.name }}</h4>
						<div class="info-item">
							<fa-icon icon="fas fa-calendar-alt" />
							<span
								>Date:
								{{
									formatDate(selectedSession.schedule?.day)
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
								{{ selectedSession.schedule?.room?.name }}</span
							>
						</div>
					</div>
				</div>

				<Form
					v-slot="{ errors }"
					:validation-schema="cancelSchema"
					style="display: block"
					@submit="handleSubmit"
				>
					<div class="form-group">
						<label for="cancellationReason"
							>Reason for Cancellation: *</label
						>
						<Field v-slot="{ field }" name="cancellationReason">
							<textarea
								id="cancellationReason"
								v-bind="field"
								rows="4"
								placeholder="Please provide a detailed reason for cancelling this session..."
								maxlength="500"
								class="form-control"
								:class="{ error: errors.cancellationReason }"
							/>
						</Field>
						<ErrorMessage
							name="cancellationReason"
							class="field-error"
						/>
					</div>

					<div style="margin-top: 20px">
						<button
							type="submit"
							class="btn btn-danger"
							style="width: 100%; justify-content: center"
							:disabled="isLoading"
						>
							<fa-icon
								:icon="
									isLoading
										? 'fas fa-spinner fa-spin'
										: 'fas fa-save'
								"
							/>
							{{ isLoading ? "Processing..." : "Cancel Booking" }}
						</button>
						<button
							type="button"
							class="btn btn-outline"
							style="width: 100%; justify-content: center"
							@click="$emit('close')"
						>
							<fa-icon icon="fas fa-times" />
							Keep Booking
						</button>
					</div>
				</Form>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { cancelSchema } from "~/utils/validations/cancelSchema";

const { $notify } = useNuxtApp();
const { currentBooking, isLoading, updateBooking } = useBookings();

const props = defineProps({
	selectedSession: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["close", "update-booking-status"]);

const handleSubmit = async (values) => {
	try {
		const sessionId = props.selectedSession.id;
		const reason = values.cancellationReason.trim();

		if (!sessionId) {
			throw new Error("Session ID not found");
		}

		const updateData = {
			cancellationReason: reason,
			status: "cancelled",
		};

		await updateBooking(sessionId, updateData);

		$notify.success("Session cancelled successfully");

		emit("update-booking-status", currentBooking.value);
		emit("close");
	} catch (error) {
		$notify.error(error.message || "Failed to cancel session");
	}
};
</script>

<style lang="scss" scoped>
.cancel-info {
	background: #f8f9fa;
	padding: 15px;
	border-radius: 6px;
	margin-bottom: 20px;
	border-left: 4px solid #e74c3c;

	p {
		margin: 5px 0;
		color: #333;

		&:first-child {
			font-weight: bold;
			color: #e74c3c;
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

.char-count {
	color: #666;
	font-size: 12px;
	float: right;
	margin-top: 5px;
}
</style>
