<template>
  <div class="modal">
    <div class="modal-content">
      <span class="close-modal" @click="$emit('close')">&times;</span>
      <h3>User Registration</h3>
      <hr class="form-section-divider" />

      <!-- Personal Information Section -->
      <h4 class="form-section-title">Personal Information</h4>
      <hr class="form-section-divider" />

      <form
        method="post"
        style="display: block"
        @submit.prevent="handleRegister"
      >
        <div class="form-row-grid">
          <div class="form-group-flex">
            <label for="regName">Full Name *</label>
            <input
              id="regName"
              v-model="formData.name"
              v-no-leading-spaces
              v-alphabet-only
              required
              type="text"
            />
          </div>
          <div class="form-group-flex" style="position: relative">
            <label for="regLoginId">Login ID *</label>
            <div class="username-input-container">
              <input
                id="regLoginId"
                v-model="formData.username"
                v-no-leading-spaces
                required
                type="text"
                :class="{
                  error:
                    validationResult && !validationResult.isValid,
                  success:
                    validationResult && validationResult.isValid,
                  validating: isValidatingUsername,
                }"
                @blur="handleUsernameBlur"
                @input="handleUsernameInput"
              />
              <div v-if="isValidatingUsername" class="validation-spinner">
                <fa-icon icon="fas fa-spinner fa-spin" />
              </div>
              <div
                v-else-if="(recommendations.length > 0 ||
                  (validationResult &&
                    !validationResult.isValid))
                "
                class="clear-icon"
                @click="clearUsernameField"
              >
                <fa-icon icon="fas fa-times" />
              </div>
            </div>
            <div v-if="!isValidationUsernameValid" class="username-validation-container">
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
              <div v-if="recommendations.length > 0" class="username-recommendations">
                <div class="recommendations-header">
                  Suggestions:
                </div>
                <div class="recommendations-list">
                  <button
                    v-for="recommendation in recommendations"
                    :key="recommendation"
                    type="button"
                    class="recommendation-item"
                    @click="selectRecommendation(recommendation)"
                  >
                    {{ recommendation }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group-flex">
            <label for="regPassword">Password *</label>
            <div class="password-input-container">
              <input
                id="regPassword"
                v-model="formData.password"
                v-no-leading-spaces
                required
                :type="showPassword ? 'text' : 'password'"
              />
              <div class="password-toggle-icon" @click="showPassword = !showPassword">
                <fa-icon :icon="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" />
              </div>
            </div>
          </div>
          <div class="form-group-flex">
            <label for="regPhone">Contact Details *</label>
            <div class="phone-input-group">
              <span class="phone-prefix">+65</span>
              <input
                id="regPhone"
                v-model="formData.phone"
                v-no-leading-spaces
                v-integer
                minlength="7"
                maxlength="15"
                required
                type="tel"
                class="phone-input"
                placeholder="e.g., 91234567"
              />
            </div>
          </div>
          <div class="form-group-flex">
            <label for="regYearOfBirth">Birth Year (YYYY) *</label>
            <input
              id="regYearOfBirth"
              v-model="formData.birthYear"
              v-integer
              required
              type="number"
              maxlength="4"
              min="1901"
              :max="new Date().getFullYear()"
            />
          </div>
          <div class="form-group-flex">
            <label for="regScheme">Scheme *</label>
            <select
              id="regScheme"
              v-model="formData.schemeId"
              name="schemeId"
              required
            >
              <option value="">Select a scheme</option>
              <option
                v-for="scheme in schemes"
                :key="scheme.id"
                :value="scheme.id"
              >
                {{ scheme.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Gender Selection -->
        <div class="form-group-flex" style="margin-top: 20px">
          <label>Gender *</label>
          <div class="gender-options">
            <label for="genderMale">
              <input
                id="genderMale"
                v-model="formData.gender"
                name="gender"
                type="radio"
                value="male"
              />
              Male
            </label>

            <label for="genderFemale">
              <input
                id="genderFemale"
                v-model="formData.gender"
                name="gender"
                type="radio"
                value="female"
              />
              Female
            </label>

            <label for="genderNonBinary">
              <input
                id="genderNonBinary"
                v-model="formData.gender"
                name="gender"
                type="radio"
                value="other"
              />
              Non-Binary
            </label>
          </div>
        </div>

        <!-- Emergency Contact Information Section -->
        <h4 class="form-section-title" style="margin-top: 30px">
          Emergency Contact Information
        </h4>
        <hr class="form-section-divider" />
        <div class="form-row-grid">
          <div class="form-group-flex">
            <label for="emergencyContactName">Emergency Contact Name</label>
            <input
              id="emergencyContactName"
              v-model="formData.emergencyContactName"
              v-no-leading-spaces
              v-alphabet-only
              type="text"
            />
          </div>
          <div class="form-group-flex">
            <label for="emergencyContactNumber">Emergency Contact Number</label>
            <div class="phone-input-group">
              <span class="phone-prefix">+65</span>
              <input
                id="emergencyContactNumber"
                v-model="formData.emergencyContactNumber"
                v-no-leading-spaces
                v-integer
                minlength="7"
                maxlength="15"
                type="tel"
                class="phone-input"
                placeholder="e.g., 91234567"
              />
            </div>
          </div>
          <!-- <div class="form-group-flex">
            <label for="emergencyRelationship">Relationship</label>
            <input
              id="emergencyRelationship"
              v-model="formData.emergencyRelationship"
              type="text"
            />
          </div> -->
        </div>

        <!-- Consent Checkbox -->
        <div class="form-group-flex">
          <label class="consent-label" style="margin-top: 0">
            <input
              id="emergencyIsAggreedConsent"
              type="checkbox"
              required
              @change="formData.emergencyIsAggreedConsent = $event.target.checked"
            />
            I consent that the information I have provided
            can be used for operational analysis,
            notifications & emergency purposes.
          </label>
        </div>

        <button
          type="submit"
          :disabled="isLoading || !formData.emergencyIsAggreedConsent"
          style="width: 100%"
        >
          {{ isLoading ? "Processing..." : "Submit" }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
defineEmits(["close"]);

const { $notify } = useNuxtApp();
const { register, isLoading } = useUser();

const {
  isValidating: isValidatingUsername,
  validationResult,
  recommendations,
  validateUsername,
  clearValidation,
} = useUsernameValidation();

const { schemes, fetchSchemes } = useUserSchemes();

const formData = reactive({
  name: "",
  username: "",
  password: "",
  phone: "",
  birthYear: "",
  schemeId: "",
  gender: "male",
  emergencyContactName: "",
  emergencyContactNumber: "",
  emergencyRelationship: "",
  emergencyIsAggreedConsent: false
});

const showPassword = ref(false);
const usernameDebounceTimer = ref(null);

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

  // Clear validation if input is empty or less than 4 chars
  if (!username || username.trim().length < 4) {
    clearValidation();
    return;
  }

  // Set new timer for debounced validation
  usernameDebounceTimer.value = setTimeout(async () => {
    await validateUsername(username);
  }, 500); // 500ms delay
};

const selectRecommendation = (recommendation) => {
  const usernameInput = document.getElementById("regLoginId");
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
  const usernameInput = document.getElementById("regLoginId");
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

const handleRegister = async () => {
  try {
    await register(formData);
    $notify
				.alert(
					"You have successfully registered. Click button below to start managing your bookings.",
					"Registration Success!",
					"Go To Dashboard",
          { closeWith: [] }
				)
				.then(() => navigateTo("/dashboard"));
  } catch (error) {
    $notify.error(error?.message || "Registration failed");
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
.modal-content {
  max-width: 750px;
}

.modal-content h3 {
  font-size: 28px;
  text-align: left;
  margin-bottom: 10px;
}

.gender-options {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;

  label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: normal;
    color: var(--dark);
  }
}

.consent-label {
  font-size: 0.85rem;
  line-height: 1.3;
  display: flex !important;
  align-items: flex-start;
  margin-top: 20px;
  margin-bottom: 20px;
}

button {
  margin: 0;
}

// Username validation styles
.username-input-container {
  position: relative;
  width: 100%;

  input {
    width: 100%;
    padding-right: 35px;
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

// Password input styles
.password-input-container {
  position: relative;
  width: 100%;

  input {
    width: 100%;
    padding-right: 35px;
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

.password-toggle-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--secondary);
  cursor: pointer;
  padding: 2px;
  border-radius: 50%;
  transition: all 0.2s ease;

  &:hover {
    color: var(--dark);
    background-color: rgba(0, 0, 0, 0.05);
  }

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
