<template>
	<div class="tab-content">
		<div class="section-header">
			<h2 class="section-title">Programme Detail</h2>
		</div>
		<Form
			ref="formRef"
			v-slot="{ errors }"
			:validation-schema="programmeSchema"
			:initial-values="formInitialValues"
			style="display: block"
			@submit="saveProgramme"
		>
			<div class="form-grid">
				<div class="form-group">
					<label for="programmeName">Programme Name</label>
					<Field v-slot="{ field }" name="name">
						<input
							id="programmeName"
              v-no-leading-spaces
							v-bind="field"
							type="text"
							class="form-control"
							:class="{ error: errors.name }"
							placeholder="Enter programme name"
						/>
					</Field>
					<ErrorMessage name="name" class="field-error" />
				</div>

				<div class="form-group">
					<label for="gameType">Game Type</label>
					<Field v-slot="{ field }" name="typeId">
						<select
							id="gameType"
							v-bind="field"
							class="form-control"
							:class="{ error: errors.typeId }"
						>
							<option value="">Select game type</option>
							<option
								v-for="gameType in props.gameTypes"
								:key="gameType.id"
								:value="gameType.id"
							>
								{{ gameType.name }}
							</option>
						</select>
					</Field>
					<ErrorMessage name="typeId" class="field-error" />
				</div>

				<div class="form-group">
					<label for="synopsis">Programme Synopsis</label>
					<Field v-slot="{ field }" name="synopsis">
						<textarea
							id="synopsis"
							v-bind="field"
							class="form-control"
							:class="{ error: errors.synopsis }"
							style="min-height: auto"
							placeholder="Enter programme description"
						/>
					</Field>
					<ErrorMessage name="synopsis" class="field-error" />
				</div>

				<div class="form-group">
					<label for="intensity">Intensity Level</label>
					<Field v-slot="{ field }" name="intensityLevel">
						<select
							id="intensity"
							v-bind="field"
							class="form-control"
							:class="{ error: errors.intensityLevel }"
						>
							<option value="">Select intensity</option>
							<option value="low">Low</option>
							<option value="medium">Medium</option>
							<option value="high">High</option>
							<option value="extreme">Extreme</option>
						</select>
					</Field>
					<ErrorMessage name="intensityLevel" class="field-error" />
				</div>

				<div class="form-group">
					<label for="sessionType">Session Type</label>
					<Field v-slot="{ field }" name="sessionType">
						<select
							id="sessionType"
							v-bind="field"
							class="form-control"
							:class="{ error: errors.sessionType }"
						>
							<option value="">Select session type</option>
							<option value="single">Single</option>
							<option value="group">Group</option>
						</select>
					</Field>
					<ErrorMessage name="sessionType" class="field-error" />
				</div>

				<div class="form-group">
					<label for="coverImage">Cover Image URL</label>
					<Field v-slot="{ field }" name="coverImage">
						<input
							id="coverImage"
              v-no-leading-spaces
							v-bind="field"
							type="url"
							class="form-control"
							:class="{ error: errors.coverImage }"
							placeholder="Enter image URL (optional)"
						/>
					</Field>
					<ErrorMessage name="coverImage" class="field-error" />

					<!-- Image upload UI -->
					<div style="padding-left: 10px;">
						<span
              v-if="!isLoading"
							style="color: var(--secondary); cursor: pointer; text-decoration: underline; font-size: 0.95em;"
							@click="triggerFileInput"
						>
							Or upload an image
						</span>
            <span v-else style="font-size: 0.95em; color: gray;">
              Uploading...
            </span>
						<input
							ref="fileInputRef"
							type="file"
							accept="image/*"
							style="display: none;"
							@change="onFileChange"
						/>
					</div>
				</div>

				<div class="form-group">
					<label for="maxParticipants">Max Participants</label>
					<Field v-slot="{ field }" name="maxParticipants">
						<input
							id="maxParticipants"
              v-integer
							v-bind="field"
							type="number"
							class="form-control"
							:class="{ error: errors.maxParticipants }"
							min="1"
							max="50"
							placeholder="Enter max participants"
						/>
					</Field>
					<ErrorMessage name="maxParticipants" class="field-error" />
				</div>

				<div class="form-group">
					<label for="duration">Duration (minutes)</label>
					<Field v-slot="{ field }" name="durationMinutes">
						<input
							id="duration"
              v-integer
							v-bind="field"
							type="number"
							class="form-control"
							:class="{ error: errors.durationMinutes }"
							min="5"
              step="5"
							placeholder="Enter duration in minutes"
						/>
					</Field>
					<ErrorMessage name="durationMinutes" class="field-error" />
				</div>

				<div
					class="form-group"
					style="display: flex; align-items: center"
				>
					<label class="checkbox-label">
						<Field v-slot="{ field, value }" name="isActive">
							<input
								name="isActive"
								type="checkbox"
								:checked="value"
								@change="field.onChange($event.target.checked)"
								@blur="field.onBlur"
							/>
						</Field>
						Active Programme
					</label>
					<ErrorMessage name="isActive" class="field-error" />
				</div>
			</div>

      <template v-if="!isEditMode">
        <div class="section-header" style="margin-top: 20px">
          <h2 class="section-title">Programme Schedule Creation</h2>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="roomId">Room</label>
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
          <div class="form-group">
            <label for="trainerId">Trainer</label>
            <Field
              v-if="['admin', 'superadmin'].includes(userRole)"
              v-slot="{ field }"
              name="trainerId"
            >
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
                  {{ trainer.name }} ({{ trainer.username }})
                </option>
              </select>
            </Field>
            <Field
              v-else
              v-slot="{ field }"
              name="trainerId"
            >
              <input
                id="trainerId"
                v-bind="field"
                type="text"
                class="form-control"
                :class="{ error: errors.trainerId }"
                :value="authStore.user?.name + ' (' + authStore.user?.username + ')'"
                readonly
                style="background-color: #f8f9fa; cursor: not-allowed;"
              />
              <input
                type="hidden"
                name="trainerId"
                :value="authStore.user?.id"
              />
            </Field>
            <ErrorMessage
              name="trainerId"
              class="field-error"
            />
          </div>

          <div class="form-group">
            <label for="day">Available Date</label>
            <Field
              class="form-control form-control-solid"
              type="date"
              name="day"
			        :min="today"
            />
            <ErrorMessage name="day" class="field-error" />
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="startTime">Start Time</label>
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
              <label for="endTime">End Time</label>
              <Field v-slot="{ field }" name="endTime">
                <input
                  id="endTime"
                  v-bind="field"
                  type="time"
                  class="form-control"
                  :class="{ error: errors.endTime }"
                  readonly
                  style="background-color: #f8f9fa; cursor: not-allowed;"
                />
              </Field>
              <ErrorMessage name="endTime" class="field-error" />
              <small class="form-text text-muted">
                Auto-calculated from start time + duration
              </small>
            </div>

        </div>
      </template>
			<div>
				<button
					class="btn btn-primary"
					type="submit"
					style="width: 100%; justify-content: center"
					:disabled="isSubmitting"
				>
					<fa-icon
						:icon="
							isSubmitting
								? 'fas fa-spinner fa-spin'
								: 'fas fa-save'
						"
					/>
					{{
						isSubmitting
							? "Processing..."
							: isEditMode
								? "Update Programme"
								: "Save Programme"
					}}
				</button>
				<button
					v-if="isEditMode"
					class="btn btn-outline"
					type="button"
					style="
						width: 100%;
						justify-content: center;
						margin-bottom: 10px;
					"
					:disabled="isSubmitting"
					@click="emit('cancel')"
				>
					<fa-icon icon="fas fa-times" />
					{{ isSubmittedSuccessfully ? "Close" : "Cancel" }}
				</button>
			</div>
		</Form>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { programmeSchema } from "~/utils/validations/programmeSchema";

const fileInputRef = ref(null);
const formRef = ref(null);

const props = defineProps({
	programme: {
		type: Object,
		default: null,
	},
	gameTypes: {
		type: Array,
		default: () => [],
	},
  availableRooms: {
		type: Array,
		default: () => [],
	},
  availableTrainers: {
		type: Array,
		default: () => [],
	},
});

const { $notify } = useNuxtApp();
const authStore = useAuthStore();
const emit = defineEmits(["programme-saved", "cancel"]);

const { isLoading, createProgramme, updateProgramme, uploadFile } = useProgrammes();

// Utility function to add minutes to a time string
const addMinutesToTime = (timeString, minutes) => {
	if (!timeString || !minutes) return '';

	const [hours, mins] = timeString.split(':').map(Number);
	const totalMinutes = hours * 60 + mins + minutes;

	const newHours = Math.floor(totalMinutes / 60) % 24; // Handle overflow past 24 hours
	const newMins = totalMinutes % 60;

	return `${String(newHours).padStart(2, '0')}:${String(newMins).padStart(2, '0')}`;
};

const userRole = computed(() => authStore.user?.role);
const isEditMode = computed(() => props.programme && props.programme.id);
const isSubmitting = ref(false);
const isSubmittedSuccessfully = ref(false);

// Save programme - now called from form submission
const saveProgramme = async (values, { resetForm }) => {
	if (isSubmitting.value) return;
	isSubmitting.value = true;
	try {
		const { message } = await (isEditMode.value
			? updateProgramme(props.programme.id, values)
			: createProgramme(values));
		isSubmittedSuccessfully.value = true;
		$notify.success(message || "Programme updated successfully");
		resetForm();
		emit("programme-saved", isEditMode.value);
	} catch (error) {
		$notify.error(error?.message || "Failed to save programme");
	} finally {
		isSubmitting.value = false;
	}
};

const triggerFileInput = () => {
	if (fileInputRef.value) fileInputRef.value.click();
};

const onFileChange = async (event) => {
	const file = event.target.files[0];
	if (!file) return;

	try {
		const url = await uploadFile(file);
		// Try multiple ways to update the field
		await nextTick();

		if (formRef.value && formRef.value.setFieldValue) {
			formRef.value.setFieldValue('coverImage', url);
		}

		const coverImageInput = document.getElementById('coverImage');
		if (coverImageInput) {
			coverImageInput.value = url;
			coverImageInput.dispatchEvent(new Event('input', { bubbles: true }));
		}

		$notify.success('Image uploaded successfully');
	} catch (error) {
		$notify.error(error?.message || 'Image upload failed');
	}
};

// Computed initial values for vee-validate
const formInitialValues = computed(() => {
	if (props.programme) {
		return props.programme;
	} else {
		return {
			name: "",
			typeId: "",
			synopsis: "",
			coverImage: "",
			intensityLevel: "medium",
			sessionType: "group",
			maxParticipants: 10,
			durationMinutes: 30,
			isActive: true,
	  scheduleDay: "",
	  scheduleStartTime: "",
	  scheduleEndTime: "",
		};
	}
});

// Computed property for today's date in YYYY-MM-DD format
const today = computed(() => {
  const d = new Date();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${month}-${day}`;
});

// Watch for changes in startTime and durationMinutes to auto-calculate endTime
watch([
  () => formRef.value?.values?.startTime,
  () => formRef.value?.values?.durationMinutes
], ([startTime, durationMinutes]) => {
  if (formRef.value && startTime && durationMinutes) {
    const calculatedEndTime = addMinutesToTime(startTime, durationMinutes);
    formRef.value.setFieldValue('endTime', calculatedEndTime);
  }
}, { immediate: false });
</script>
