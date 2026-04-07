<template>
	<div class="modal">
		<div class="modal-content" style="max-width: 750px">
			<span class="close-modal" @click="closeModal">&times;</span>
			<div class="modal-programme-info">
				<h3>
					{{ isEditMode ? "Edit Session" : "Add New Session" }} -
					{{ selectedDate }}
				</h3>

				<Form
					ref="formRef"
					v-slot="{ errors }"
					:validation-schema="scheduleSchema(userRole.value)"
					:initial-values="formInitialValues"
					style="display: block"
					@submit="submitForm"
				>
					<div class="form-row-grid">
						<div class="form-group">
							<label for="programmeId">Programme *</label>
							<Field v-slot="{ field }" name="programmeId">
								<select
									id="programmeId"
									v-bind="field"
									class="form-control"
									:class="{ error: errors.programmeId }"
								>
									<option value="">Select Programme</option>
									<option
										v-for="programme in availableProgrammes"
										:key="programme.id"
										:value="programme.id"
									>
										{{
											`${programme.name} — ${programme.gameType?.name} — ${capitalize(programme.intensityLevel)} — ${programme.durationMinutes}mins`
										}}
									</option>
								</select>
							</Field>
							<ErrorMessage
								name="programmeId"
								class="field-error"
							/>
						</div>
					</div>
					<div v-if="['admin', 'superadmin'].includes(userRole)" class="form-row-grid">
						<div class="form-group">
							<label for="trainerId">Trainer *</label>
							<Field v-slot="{ field }" name="trainerId">
								<select
									id="trainerId"
									v-bind="field"
									class="form-control"
									:class="{ error: errors.trainerId }"
								>
									<option value="">Select Trainer</option>
									<option
										v-for="trainer in availableTrainers"
										:key="trainer.id"
										:value="trainer.id"
									>
										{{ trainer.name }}
									</option>
								</select>
							</Field>
							<ErrorMessage
								name="trainerId"
								class="field-error"
							/>
						</div>
					</div>
					<div class="form-row-grid">
						<div class="form-group">
							<label for="roomId">Room *</label>
							<Field v-slot="{ field }" name="roomId">
								<select
									id="roomId"
									v-bind="field"
									class="form-control"
									:class="{ error: errors.roomId }"
								>
									<option value="">Select Room</option>
									<option
										v-for="room in availableRooms"
										:key="room.id"
										:value="room.id"
									>
										{{ room.name }}
									</option>
								</select>
							</Field>
							<ErrorMessage name="roomId" class="field-error" />
						</div>
					</div>
					<div v-if="!isEditMode" class="form-row-grid">
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
									:readonly="!isEditMode"
									:style="!isEditMode ? 'background-color: #f8f9fa; cursor: not-allowed;' : ''"
								/>
							</Field>
							<ErrorMessage name="endTime" class="field-error" />
							<small v-if="!isEditMode" class="form-text text-muted">
								Auto-calculated from start time + programme duration
							</small>
						</div>
					</div>

					<div v-if="isEditMode" class="form-group">
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
							<span class="checkmark"></span>
							Active Status
						</label>
					</div>
					<div style="margin-top: 20px">
						<button
							type="submit"
							class="btn btn-primary"
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
							{{
								isLoading
									? "Processing..."
									: isEditMode
										? "Update Session"
										: "Create Session"
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
import { scheduleSchema } from "~/utils/validations/scheduleSchema";

const props = defineProps({
	selectedDate: {
		type: String,
		default: "",
	},
	availableProgrammes: {
		type: Array,
		default: () => [],
	},
	availableTrainers: {
		type: Array,
		default: () => [],
	},
	availableRooms: {
		type: Array,
		default: () => [],
	},
	schedule: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(["close", "submit-success"]);
const { $notify } = useNuxtApp();
const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);

const { createSchedule, updateSchedule, isLoading } = useProgrammeSchedules();

const formRef = ref(null);

// Utility function to add minutes to a time string
const addMinutesToTime = (timeString, minutes) => {
	if (!timeString || !minutes) return '';

	const [hours, mins] = timeString.split(':').map(Number);
	const totalMinutes = hours * 60 + mins + minutes;

	const newHours = Math.floor(totalMinutes / 60) % 24; // Handle overflow past 24 hours
	const newMins = totalMinutes % 60;

	return `${String(newHours).padStart(2, '0')}:${String(newMins).padStart(2, '0')}`;
};

const submitForm = async (values) => {
	const sessionData = {
		programmeId: values.programmeId,
		trainerId:
			['admin', 'superadmin'].includes(userRole.value) ? values.trainerId : authStore.user.id,
		roomId: values.roomId,
		day: props.selectedDate,
		startTime: values.startTime,
		endTime: values.endTime,
	};

	if (isEditMode.value) {
		sessionData.id = props.schedule.id;
		sessionData.isCancelled = values.isActive === false;
	}

	try {
		const { message } = isEditMode.value
			? await updateSchedule(sessionData.id, sessionData)
			: await createSchedule(sessionData);

		$notify.success(message || "Session saved successfully");
		emit("close");
		emit("submit-success");
	} catch (error) {
		$notify.error(error.message || "Failed to save session");
	}
};

const closeModal = () => {
	emit("close");
};

const isEditMode = computed(() => !!props.schedule);

// Get selected programme duration
const selectedProgrammeDuration = computed(() => {
	const formValues = formRef.value?.values;
	if (!formValues?.programmeId) return null;

	const selectedProgramme = props.availableProgrammes.find(p => p.id == formValues.programmeId);
	return selectedProgramme?.durationMinutes || null;
});

// Initial values for vee-validate
const formInitialValues = computed(() => {
	if (props.schedule) {
		// Convert the session data to form format for editing
		const startTime = formatTimeToHHMM(props.schedule.startTime);
		const endTime = formatTimeToHHMM(props.schedule.endTime);

		return {
			programmeId: props.schedule.programmeId || "",
			trainerId: props.schedule.trainerId || "",
			roomId: props.schedule.roomId || "",
			startTime: startTime,
			endTime: endTime,
			isActive: props.schedule.isCancelled === false,
		};
	}

	const now = new Date();
	const currentHour = now.getHours();
	const currentMinute = now.getMinutes();

	// Format current time as HH:MM
	const startTime = `${String(currentHour).padStart(2, "0")}:${String(currentMinute).padStart(2, "0")}`;

	// Calculate end time (2 hours later)
	const endHour = (currentHour + 2) % 24;
	const endTime = `${String(endHour).padStart(2, "0")}:${String(currentMinute).padStart(2, "0")}`;

	return {
		programmeId: "",
		trainerId: "",
		roomId: "",
		startTime: startTime,
		endTime: endTime,
		isActive: true,
	};
});

// Watch for changes in startTime and selected programme to auto-calculate endTime
watch([
	() => formRef.value?.values?.startTime,
	() => selectedProgrammeDuration.value
], ([startTime, durationMinutes]) => {
	if (formRef.value && startTime && durationMinutes && !isEditMode.value) {
		const calculatedEndTime = addMinutesToTime(startTime, durationMinutes);
		formRef.value.setFieldValue('endTime', calculatedEndTime);
	}
}, { immediate: false });
</script>
