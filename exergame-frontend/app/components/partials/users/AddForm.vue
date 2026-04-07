<template>
	<div>
		<h3>Add Trainer User</h3>
		<hr class="form-section-divider" />

		<!-- Personal Information Section -->
		<h4 class="form-section-title">Personal Information</h4>
		<hr class="form-section-divider" />

		<!-- Form -->
		<Form
			v-slot="{ errors }"
			:validation-schema="addUserSchema"
			:initial-values="formInitialValues"
			style="display: block"
			@submit="handleSubmit"
		>
			<div class="form-row-grid">
				<div class="form-group">
					<label for="name">Full Name *</label>
					<Field v-slot="{ field }" name="name">
						<input
							id="name"
              v-no-leading-spaces
              v-alphabet-only
							v-bind="field"
							type="text"
							class="form-control"
							:class="{ error: errors.name }"
							placeholder="Enter full name"
						/>
					</Field>
					<ErrorMessage name="name" class="field-error" />
				</div>

				<div class="form-group" style="position: relative">
					<label for="username">Login ID *</label>
					<Field v-slot="{ field }" name="username">
						<div class="username-input-container">
							<input
								id="username"
                v-no-leading-spaces
								v-bind="field"
								type="text"
								class="form-control"
								:class="{
									error:
										errors.username ||
										(validationResult &&
											!validationResult.isValid),
									success:
										validationResult &&
										validationResult.isValid,
									validating: isValidatingUsername,
								}"
								placeholder="Enter login ID"
								@blur="handleUsernameBlur"
								@input="handleUsernameInput"
							/>
							<div
								v-if="isValidatingUsername"
								class="validation-spinner"
							>
								<fa-icon icon="fas fa-spinner fa-spin" />
							</div>
							<div
								v-else-if="
									field.value &&
									(recommendations.length > 0 ||
										(validationResult &&
											!validationResult.isValid))
								"
								class="clear-icon"
								@click="clearUsernameField"
							>
								<fa-icon icon="fas fa-times" />
							</div>
						</div>
						<div
							v-if="!isValidationUsernameValid"
							class="username-validation-container"
						>
							<!-- Validation Message -->
							<div
								v-if="validationResult"
								class="username-validation-message"
								:class="{
									success: validationResult.isValid,
									error: !validationResult.isValid,
								}"
							>
								{{ validationResult.message }}
							</div>
							<!-- Recommendations Dropdown -->
							<div
								v-if="recommendations.length > 0"
								class="username-recommendations"
							>
								<div class="recommendations-header">
									Suggestions:
								</div>
								<div class="recommendations-list">
									<button
										v-for="recommendation in recommendations"
										:key="recommendation"
										type="button"
										class="recommendation-item"
										@click="
											selectRecommendation(recommendation)
										"
									>
										{{ recommendation }}
									</button>
								</div>
							</div>
						</div>
					</Field>
					<ErrorMessage name="username" class="field-error" />
				</div>

				<div class="form-group">
					<label for="phone">Contact Details *</label>
					<Field v-slot="{ field }" name="phone">
						<div class="phone-input-group">
							<span class="phone-prefix">+65</span>
							<input
								id="phone"
                v-no-leading-spaces
                v-integer
                minlength="7"
                maxlength="15"
								v-bind="field"
								type="tel"
								class="phone-input form-control"
								:class="{ error: errors.phone }"
								placeholder="e.g., 91234567"
							/>
						</div>
					</Field>
					<ErrorMessage name="phone" class="field-error" />
				</div>
			</div>

			<div class="form-row-grid">
				<div class="form-group">
					<label for="password">Password *</label>
					<Field v-slot="{ field }" name="password">
						<input
							id="password"
              v-no-leading-spaces
							v-bind="field"
							type="password"
							:class="{ error: errors.password }"
						/>
					</Field>
				</div>

				<div class="form-group">
					<label for="birthYear">Birth Year (YYYY) *</label>
					<Field v-slot="{ field }" name="birthYear">
						<input
							id="birthYear"
              v-integer
							v-bind="field"
							type="number"
							class="form-control"
							:class="{ error: errors.birthYear }"
							min="1901"
							:max="new Date().getFullYear()"
							placeholder="Enter birth year"
						/>
					</Field>
					<ErrorMessage name="birthYear" class="field-error" />
				</div>

        <div class="form-group">
          <label for="role">Role *</label>
          <Field v-slot="{ field }" name="role">
            <select
              id="role"
              v-bind="field"
              class="form-control"
              :class="{ error: errors.role }"
              @change="handleRoleChange"
            >
              <option value="">Select Role</option>
              <option value="user">User</option>
              <option value="trainer">Trainer</option>
              <option value="admin">Admin</option>
            </select>
          </Field>
          <ErrorMessage name="role" class="field-error" />
        </div>
			</div>

			<div class="form-row-grid">
				<!-- Gender Selection -->
				<div class="form-group">
					<label>Gender *</label>
					<div class="gender-options">
						<Field
							id="genderMale"
							name="gender"
							type="radio"
							value="male"
						/>
						<label for="genderMale">Male</label>

						<Field
							id="genderFemale"
							name="gender"
							type="radio"
							value="female"
						/>
						<label for="genderFemale">Female</label>

						<Field
							id="genderOther"
							name="gender"
							type="radio"
							value="other"
						/>
						<label for="genderOther">Non-Binary</label>
					</div>
					<ErrorMessage name="gender" class="field-error" />
				</div>

				<div class="form-group">
					<label v-if="selectedRole === 'user'" for="schemeId">Scheme</label>
					<Field
            v-if="selectedRole === 'user'"
            v-slot="{ field }"
            name="schemeId"
          >
						<select
							id="schemeId"
							v-bind="field"
							class="form-control"
							:class="{ error: errors.schemeId }"
						>
							<option value="">Select Scheme</option>
							<option
								v-for="scheme in schemes"
								:key="scheme.id"
								:value="scheme.id"
							>
								{{ scheme.name }}
							</option>
						</select>
					</Field>
          <Field
            v-else
            name="schemeId"
            type="hidden"
            :value="1"
          />
					<ErrorMessage name="schemeId" class="field-error" />
				</div>

        <div class="form-group"></div>
			</div>

			<!-- Emergency Contact Information Section -->
			<h4 class="form-section-title" style="margin-top: 30px">
				Emergency Contact Information
			</h4>
			<hr class="form-section-divider" />

			<div class="form-row-grid">
				<div class="form-group-flex">
					<label for="emergencyContactName"
						>Emergency Contact Name</label
					>
					<Field v-slot="{ field }" name="emergencyContactName">
						<input
							id="emergencyContactName"
              v-no-leading-spaces
              v-alphabet-only
							v-bind="field"
							type="text"
							class="form-control"
							:class="{ error: errors.emergencyContactName }"
							placeholder="Enter emergency contact name"
						/>
					</Field>
					<ErrorMessage
						name="emergencyContactName"
						class="field-error"
					/>
				</div>

				<div class="form-group-flex">
					<label for="emergencyContactNumber"
						>Emergency Contact Number</label
					>
					<Field v-slot="{ field }" name="emergencyContactNumber">
						<div class="phone-input-group">
							<span class="phone-prefix">+65</span>
							<input
								id="emergencyContactNumber"
                v-no-leading-spaces
                v-integer
								v-bind="field"
                minlength="7"
                maxlength="15"
								type="tel"
								class="phone-input form-control"
								:class="{
									error: errors.emergencyContactNumber,
								}"
								placeholder="e.g., 91234567"
							/>
						</div>
					</Field>
					<ErrorMessage
						name="emergencyContactNumber"
						class="field-error"
					/>
				</div>

				<!-- <div class="form-group-flex">
					<label for="emergencyRelationship">Relationship</label>
					<Field v-slot="{ field }" name="emergencyRelationship">
						<input
							id="emergencyRelationship"
							v-bind="field"
							type="text"
							class="form-control"
							:class="{ error: errors.emergencyRelationship }"
							placeholder="e.g., Spouse, Parent, Sibling"
						/>
					</Field>
					<ErrorMessage
						name="emergencyRelationship"
						class="field-error"
					/>
				</div> -->
			</div>

			<div style="margin-top: 20px">
				<button
					class="btn btn-primary"
					type="submit"
					style="width: 100%; justify-content: center"
					:disabled="isLoading"
				>
					<fa-icon
						:icon="
							isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-save'
						"
					/>
					{{ isLoading ? "Processing..." : "Submit" }}
				</button>
			</div>
		</Form>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { addUserSchema } from "~/utils/validations/userSchema";

const selectedRole = ref("");
const { $notify } = useNuxtApp();
const { schemes, fetchSchemes } = useUserSchemes();
const { isLoading, addUser } = useUser();
const {
	isValidating: isValidatingUsername,
	validationResult,
	recommendations,
	validateUsername,
	clearValidation,
} = useUsernameValidation();

const emit = defineEmits(["add-user"]);

// Initial values for add user form
const formInitialValues = computed(() => ({
	name: "",
	phone: "",
	username: "",
	password: "",
	birthYear: "",
	role: "",
	schemeId: 1, // 1 = DXO
	gender: "",

	emergencyContactName: "",
	emergencyContactNumber: "",
	emergencyRelationship: "",
}));

const usernameDebounceTimer = ref(null);

const handleRoleChange = (event) => {
	const role = event.target.value;
	selectedRole.value = role;

	// Set default scheme value for non-user roles
	if (role !== "user") {
		const schemeSelect = document.getElementById("schemeId");
		if (schemeSelect) {
			schemeSelect.value = "1";
			schemeSelect.dispatchEvent(new Event("change", { bubbles: true }));
		}
	}
};

const handleUsernameBlur = async (event) => {
	const username = event.target.value;
	if (username && username.trim() !== "") {
		// Add a slight delay to show the validating state animation
		await new Promise((resolve) => setTimeout(resolve, 100));
		await validateUsername(username);
	}
};

// Handle username input with debouncing
const handleUsernameInput = (event) => {
	const username = event.target.value;

	// Clear previous timer
	if (usernameDebounceTimer.value) {
		clearTimeout(usernameDebounceTimer.value);
	}

	// Clear validation if input is empty
	if (!username || username.trim() === "") {
		clearValidation();
		return;
	}

	// Set new timer for debounced validation
	usernameDebounceTimer.value = setTimeout(async () => {
		await validateUsername(username);
	}, 500); // 500ms delay
};

const selectRecommendation = (recommendation) => {
	const usernameInput = document.getElementById("username");
	if (usernameInput) {
		// Add a subtle animation to show selection
		usernameInput.style.transform = "scale(1.02)";
		usernameInput.style.transition = "transform 0.2s ease";

		// Reset scale after animation
		setTimeout(() => {
			usernameInput.style.transform = "scale(1)";
		}, 200);

		usernameInput.value = recommendation;

		// Trigger input event to update vee-validate
		usernameInput.dispatchEvent(new Event("input", { bubbles: true }));

		// Clear recommendations with a slight delay to show selection
		setTimeout(() => {
			clearValidation();
		}, 150);

		// Validate the selected recommendation
		setTimeout(() => {
			validateUsername(recommendation);
		}, 300);
	}
};

const clearUsernameField = () => {
	const usernameInput = document.getElementById("username");
	if (usernameInput) {
		// Clear debounce timer first
		if (usernameDebounceTimer.value) {
			clearTimeout(usernameDebounceTimer.value);
			usernameDebounceTimer.value = null;
		}

		clearValidation();

		usernameInput.value = "";
		usernameInput.dispatchEvent(new Event("input", { bubbles: true }));
		usernameInput.focus();
	}
};

const handleSubmit = async (values, { resetForm }) => {
  if (isLoading.value) return;

  try {
    // Ensure schemeId is set to 1 for non-user roles
    const data = {
      ...values,
      isActive: true,
      schemeId: values.role !== 'user' ? 1 : values.schemeId
    };
    const { success } = await addUser(data);
    if (success) {
      emit("add-user");
      $notify.success("User added successfully!");
      resetForm();
    }
  } catch (error) {
    $notify.error(error?.message || "Failed to add user");
  }
};

const isValidationUsernameValid = computed(() => {
	return validationResult.value && validationResult.value.isValid;
});

onMounted(() => fetchSchemes());

onUnmounted(() => {
	// Cleanup debounce timer on unmount
	if (usernameDebounceTimer.value) {
		clearTimeout(usernameDebounceTimer.value);
	}
});
</script>

<style lang="scss" scoped>
.gender-options {
	display: flex;
	gap: 20px;
	flex-wrap: wrap;

	label {
		display: flex;
		align-items: center;
		gap: 5px;
		font-weight: normal !important;
		color: var(--dark);
	}
}

.checkbox-label {
	display: flex;
	align-items: center;
	cursor: pointer;
	font-weight: normal !important;

	.checkbox {
		margin-right: 0.5rem;
	}
}

// Username validation styles
.username-input-container {
	position: relative;
	width: 100%;

	input {
		width: 100%;
		padding-right: 35px; // Make space for spinner
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		transform: translateX(0);

		&.validating {
			border-color: #007bff;
			box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
			animation: validating-pulse 2s ease-in-out infinite;
		}

		&.success {
			border-color: #28a745;
			background-color: #f8fff9;
			animation: success-bounce 0.6s ease-out;
		}

		/* &.error {
      animation: error-shake 0.5s ease-in-out;
    } */
	}
}

.validation-spinner {
	position: absolute;
	right: 10px;
	top: 50%;
	transform: translateY(-50%);
	color: var(--success);
	animation: spinner-fade-in 0.3s ease-in;

	.svg-inline--fa {
		animation: spin 1s linear infinite;
	}
}

.clear-icon {
	position: absolute;
	right: 10px;
	top: 50%;
	transform: translateY(-50%);
	color: var(--error);
	cursor: pointer;
	padding: 2px;
	border-radius: 50%;
	transition: all 0.2s ease;
	animation: clear-icon-fade-in 0.3s ease-in;

	.svg-inline--fa {
		font-size: 0.875rem;
	}
}

.username-validation-container {
	position: absolute;
	width: auto;
	top: 75px;
	z-index: 1;
	background: var(--primary);
	box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
	border: 1px solid var(--border);
}

.username-validation-message {
	font-size: 0.875rem;
	margin-top: 5px;
	padding: 5px 8px;
	border-radius: 4px;
	animation: message-slide-down 0.3s ease-out;
	transform: translateY(0);
	opacity: 1;

	&.success {
		color: var(--success);
		background-color: var(--light);
		border: 1px solid var(--success);
	}

	&.error {
		color: var(--error);
		background-color: var(--light);
	}
}

.username-recommendations {
	margin-top: 8px;
	background: var(--light);
	border: 1px solid var(--border);
	padding: 10px;
	animation: recommendations-slide-down 0.4s ease-out;
	transform: translateY(0);
	opacity: 1;
}

.recommendations-header {
	font-size: 0.875rem;
	font-weight: 600;
	color: var(--secondary);
	margin-bottom: 8px;
}

.recommendations-list {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;

	button {
		margin: 0;
	}
}

.recommendation-item {
	background: var(--primary);
	border: 1px solid var(--secondary);
	color: var(--secondary);
	width: 100%;
	padding: 5px;
	border-radius: 4px;
	font-size: 0.875rem;
	cursor: pointer;
	transition: all 0.2s ease;
	animation: recommendation-fade-in 0.4s ease-out;
	animation-fill-mode: both;

	&:nth-child(1) {
		animation-delay: 0.1s;
	}
	&:nth-child(2) {
		animation-delay: 0.15s;
	}
	&:nth-child(3) {
		animation-delay: 0.2s;
	}
	&:nth-child(4) {
		animation-delay: 0.25s;
	}
	&:nth-child(5) {
		animation-delay: 0.3s;
	}

	&:hover {
		background: var(--secondary);
		color: var(--primary);
		transform: translateY(-2px);
	}

	&:active {
		transform: translateY(0) scale(0.98);
		transition: all 0.1s ease;
	}

	&:focus {
		outline: none;
		box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
	}
}

// Keyframe Animations
@keyframes validating-pulse {
	0%,
	100% {
		box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
	}
	50% {
		box-shadow: 0 0 0 6px rgba(0, 123, 255, 0.2);
	}
}

@keyframes success-bounce {
	0% {
		transform: scale(1);
		box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
	}
	50% {
		transform: scale(1.02);
		box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
	}
	100% {
		transform: scale(1);
		box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
	}
}

/* @keyframes error-shake {
  0%, 100% {
    transform: translateX(0);
  }
  10%, 30%, 50%, 70%, 90% {
    transform: translateX(-3px);
  }
  20%, 40%, 60%, 80% {
    transform: translateX(3px);
  }
} */

@keyframes spinner-fade-in {
	0% {
		opacity: 0;
		transform: translateY(-50%) scale(0.8);
	}
	100% {
		opacity: 1;
		transform: translateY(-50%) scale(1);
	}
}

@keyframes clear-icon-fade-in {
	0% {
		opacity: 0;
		transform: translateY(-50%) scale(0.8);
	}
	100% {
		opacity: 1;
		transform: translateY(-50%) scale(1);
	}
}

@keyframes spin {
	0% {
		transform: rotate(0deg);
	}
	100% {
		transform: rotate(360deg);
	}
}

@keyframes message-slide-down {
	0% {
		opacity: 0;
		transform: translateY(-10px);
		max-height: 0;
		padding-top: 0;
		padding-bottom: 0;
	}
	100% {
		opacity: 1;
		transform: translateY(0);
		max-height: 50px;
		padding-top: 5px;
		padding-bottom: 5px;
	}
}

@keyframes recommendations-slide-down {
	0% {
		opacity: 0;
		transform: translateY(-15px);
		max-height: 0;
		padding-top: 0;
		padding-bottom: 0;
	}
	100% {
		opacity: 1;
		transform: translateY(0);
		max-height: 200px;
		padding-top: 10px;
		padding-bottom: 10px;
	}
}

@keyframes recommendation-fade-in {
	0% {
		opacity: 0;
		transform: translateY(10px) scale(0.9);
	}
	100% {
		opacity: 1;
		transform: translateY(0) scale(1);
	}
}
</style>
