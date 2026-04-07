<template>
  <div class="modal">
		<div class="modal-content" @click.stop>
			<span class="close-modal" @click="$emit('close')">&times;</span>
      <h3>Edit Profile</h3>
      <hr class="form-section-divider" />

      <!-- Personal Information Section -->
      <h4 class="form-section-title">Personal Information</h4>
      <hr class="form-section-divider" />

      <!-- Form -->
      <Form
        v-slot="{ errors }"
        :validation-schema="updateUserSchema"
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

          <div class="form-group">
            <label for="phone">Contact Details *</label>
						<Field v-slot="{ field }" name="phone">
              <div class="phone-input-group">
                <span class="phone-prefix">+65</span>
                <input
                  id="phone"
                  v-integer
                  v-bind="field"
                  minlength="7"
                  maxlength="15"
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
            <label for="username">Login ID *</label>
						<Field v-slot="{ field }" name="username">
              <input
                id="username"
                v-bind="field"
                type="text"
                class="form-control readonly"
								readonly
							/>
            </Field>
						<small class="form-text"
							>Login ID cannot be changed</small
						>
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
        </div>

        <div class="form-row-grid">

          <div class="form-group">
            <label for="schemeId">Scheme</label>
						<Field v-slot="{ field }" name="schemeId">
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
						<ErrorMessage name="schemeId" class="field-error" />
          </div>

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
        </div>

        <!-- <div class="form-row-grid">

					<div class="form-group" style="padding-top: 20px">
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
        </div> -->

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
								isLoading
                ? 'fas fa-spinner fa-spin'
                : 'fas fa-save'
							"
						/>
            {{ isLoading ? "Processing..." : "Submit" }}
          </button>
          <button
            class="btn btn-outline"
            type="button"
            style="
							width: 100%;
							justify-content: center;
							margin-bottom: 10px;
						"
            :disabled="isLoading"
						@click="emit('close')"
					>
            <fa-icon icon="fas fa-times" />
            Cancel
          </button>
        </div>
      </Form>
    </div>
  </div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { updateUserSchema } from "~/utils/validations/userSchema";

const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
});
const { $notify } = useNuxtApp();
const { schemes, fetchSchemes } = useUserSchemes();
const { isLoading, updateUser } = useUser();

const emit = defineEmits(["close", "updated"]);

// Computed initial values for the form
const formInitialValues = computed(() => {
  if (!props.userData) return {};

  const values = {
    ...props.userData,
    scheme: props.userData.scheme_id || 1, // 1 = DXO
  };

  // Emergency contact data (get first emergency contact if exists)
  const emergencyContact = props.userData.emergencyContact;
  values.emergencyContactName = emergencyContact?.name || "";
  values.emergencyContactNumber = emergencyContact?.phone || "";
  values.emergencyRelationship = emergencyContact?.relationship || "";

  return values;
});

const handleSubmit = async (values) => {
  if (isLoading.value) return;

  try {
    const updateData = {
      ...values,
      id: props.userData.id,
    };

    const { success } = await updateUser(updateData, props.userData.id);

    if (success) {
      emit("updated");
      emit("close");
      $notify.success("User updated successfully!");
    }
  } catch (error) {
    $notify.error(error?.message || "Failed to update user");
  }
};

onMounted(() => fetchSchemes());
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
</style>
