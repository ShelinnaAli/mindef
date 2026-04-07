<template>
	<!-- Reschedule Session Modal -->
	<div class="modal">
		<div class="modal-content" style="max-width: 500px">
			<span class="close-modal" @click="closeModal">&times;</span>
			<div class="modal-programme-info">
				<h3><fa-icon icon="fas fa-clock" /> Reschedule Session</h3>
				<div v-if="schedule" class="session-info">
					<h4>{{ schedule.programme?.name }}</h4>
					<p><strong>Current Date:</strong> {{ schedule.day }}</p>
					<p><strong>Current Time:</strong> {{ schedule.time }}</p>
					<p>
						<strong>Trainer:</strong> {{ schedule.trainer?.name }}
					</p>
				</div>

				<Form
					v-slot="{ errors }"
					:key="`reschedule-${schedule?.id || 'new'}`"
					:validation-schema="rescheduleSchema"
					:initial-values="formInitialValues"
					style="display: block"
					@submit="submitForm"
				>
					<div class="form-group">
						<label class="required fw-bold fs-6 mb-2" for="newDate"
							>New Date</label
						>
						<Field
							class="form-control form-control-solid"
							type="date"
							name="newDate"
						/>
						<ErrorMessage name="newDate" class="text-danger" />
					</div>
					<div class="form-row-grid">
						<div class="form-group">
							<label for="startTime">Start Time *</label>
							<Field v-slot="{ field }" name="startTime">
								<input
									id="startTime"
									v-bind="field"
									type="time"
									class="form-control"
									:class="{ error: errors.startTime }"
								/>
							</Field>
							<ErrorMessage
								name="startTime"
								class="field-error"
							/>
						</div>

						<div class="form-group">
							<label for="endTime">End Time *</label>
							<Field v-slot="{ field }" name="endTime">
								<input
									id="endTime"
									v-bind="field"
									type="time"
									class="form-control"
									:class="{ error: errors.endTime }"
								/>
							</Field>
							<ErrorMessage name="endTime" class="field-error" />
						</div>
					</div>

					<div class="form-group">
						<label for="reason">Reason for Rescheduling</label>
						<Field v-slot="{ field }" name="reason">
							<textarea
								id="reason"
								v-bind="field"
								rows="3"
								class="form-control"
								placeholder="Optional reason for rescheduling"
							/>
						</Field>
					</div>

					<div class="form-group">
						<label class="checkbox-label">
							<Field
								v-slot="{ field }"
								name="isActive"
								type="checkbox"
								:value="true"
								:unchecked-value="false"
							>
								<input
									v-bind="field"
									type="checkbox"
									class="checkbox"
								/>
							</Field>
							<span class="checkmark" />
							Active Status
						</label>
					</div>

					<div style="margin-top: 20px">
						<button
							type="submit"
							class="btn btn-primary"
							style="width: 100%; justify-content: center"
							:disabled="isSubmitting"
						>
							<fa-icon
								:icon="
									isSubmitting
										? 'fas fa-spinner fa-spin'
										: 'fas fa-clock'
								"
							/>
							{{
								isSubmitting
									? "Processing..."
									: "Reschedule Session"
							}}
						</button>
						<button
							type="button"
							class="btn btn-outline"
							style="width: 100%; justify-content: center"
							@click="closeModal"
						>
							<fa-icon icon="fas fa-times" />
							Cancel
						</button>
					</div>
				</Form>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { rescheduleSchema } from "~/utils/validations/rescheduleSchema";

const props = defineProps({
	schedule: {
		type: Object,
		default: null,
	},
	availableTrainers: {
		type: Array,
		default: () => [],
	},
});

const emit = defineEmits(["close", "submit-success"]);
const { updateSchedule } = useProgrammeSchedules();
const { $notify } = useNuxtApp();

const isSubmitting = ref(false);

const submitForm = async (values) => {
	isSubmitting.value = true;

	try {
		const updateData = {
			day: values.newDate,
			startTime: values.startTime,
			endTime: values.endTime,
			cancellationReason: `Rescheduled: ${values.reason.trim()}`,
			isCancelled: values.isActive === false,
		};

		const { message } = await updateSchedule(props.schedule.id, updateData);

		emit("close");
		emit("submit-success");
		$notify.success(message || "Session saved successfully");
	} catch (error) {
		$notify.error(error.message || "Failed to save session");
	} finally {
		isSubmitting.value = false;
	}
};

const closeModal = () => emit("close");

const formInitialValues = computed(() => {
	if (!props.schedule) {
		return {
			newDate: "",
			startTime: "",
			endTime: "",
			reason: "",
			isActive: true,
		};
	}

	const initialValues = {
		newDate: props.schedule.day || "",
		startTime: formatTimeToHHMM(props.schedule.startTime),
		endTime: formatTimeToHHMM(props.schedule.endTime),
		reason: "",
		isActive: props.schedule.isCancelled === false,
	};

	return initialValues;
});
</script>

<style lang="scss" scoped>
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
}
</style>
