<template>
	<div class="modal">
		<div class="modal-content" style="max-width: 500px">
			<span class="close-modal" @click="closeModal">&times;</span>
			<div class="modal-programme-info">
				<h3>Cancel Session</h3>

				<div v-if="schedule" class="cancel-info">
					<div class="session-details">
						<h4>{{ schedule.programme?.name }}</h4>
						<div class="info-item">
							<fa-icon icon="fas fa-calendar-alt" />
							<span>Date: {{ schedule.date }}</span>
						</div>
						<div class="info-item">
							<fa-icon icon="fas fa-clock" />
							<span>Time: {{ schedule.time }}</span>
						</div>
						<div class="info-item">
							<fa-icon icon="fas fa-user" />
							<span>Trainer: {{ schedule.trainer?.name }}</span>
						</div>
						<div class="info-item">
							<fa-icon icon="fas fa-map-marker-alt" />
							<span>Location: {{ schedule.room?.name }}</span>
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
							{{ isLoading ? "Processing..." : "Cancel Session" }}
						</button>
						<button
							type="button"
							class="btn btn-outline"
							style="width: 100%; justify-content: center"
							@click="closeModal"
						>
							<fa-icon icon="fas fa-times" />
							Close
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
const { updateSchedule } = useProgrammeSchedules();

const isLoading = ref(false);

const props = defineProps({
	schedule: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["close", "submit-success"]);

const handleSubmit = async (values) => {
	try {
		isLoading.value = true;
		const sessionId = props.schedule.id;
		const reason = values.cancellationReason.trim();

		if (!sessionId) {
			throw new Error("Session ID not found");
		}

		const updateData = {
			cancellationReason: reason,
			isCancelled: true,
		};

		await updateSchedule(sessionId, updateData);

		$notify.success("Session cancelled successfully");

		emit("submit-success");
		closeModal();
	} catch (error) {
		$notify.error(error.message || "Failed to cancel session");
	} finally {
		isLoading.value = false;
	}
};

const closeModal = () => emit("close");
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
