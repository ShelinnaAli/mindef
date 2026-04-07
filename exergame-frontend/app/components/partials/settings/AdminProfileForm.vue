<template>
	<div class="section">
		<div class="section-header">
			<h2 class="section-title">My Account</h2>
		</div>
		<!-- Profile Form -->
		<Form
      ref="formRef"
			v-slot="{ errors }"
			:validation-schema="profileSchema"
			:initial-values="initialValues"
			style="display: block"
			@submit="onSubmit"
		>
			<div class="form-group">
				<label for="adminName">Full Name</label>
				<Field
					id="adminName"
					name="name"
					type="text"
					class="form-control"
					:class="{ error: errors.name }"
					placeholder="Enter your full name"
				/>
				<ErrorMessage name="name" class="field-error" />
			</div>
			<div class="form-group">
				<label for="adminPhone">Phone Number</label>
				<Field
					id="adminPhone"
					name="phone"
					type="tel"
					class="form-control"
					:class="{ error: errors.phone }"
					placeholder="Enter your phone number"
				/>
				<ErrorMessage name="phone" class="field-error" />
			</div>
			<!-- Password Change Section -->
			<div
				class="password-section"
				style="
					margin-top: 30px;
					padding-top: 20px;
					border-top: 1px solid #eee;
				"
			>
				<h3
					style="
						margin-bottom: 20px;
						font-size: 1.2rem;
						color: #333;
					"
				>
					Change Password
				</h3>
				<div class="form-group">
					<label for="currentPassword">Current Password</label>
					<Field v-slot="{ field }" name="currentPassword">
						<input
							id="currentPassword"
              v-no-leading-spaces
							v-bind="field"
							type="password"
							class="form-control"
							:class="{ error: errors.currentPassword }"
							placeholder="Enter current password"
						/>
					</Field>
					<ErrorMessage
						name="currentPassword"
						class="field-error"
					/>
				</div>
				<div class="form-group">
					<label for="newPassword">New Password</label>
					<Field v-slot="{ field }" name="newPassword">
						<input
							id="newPassword"
              v-no-leading-spaces
							v-bind="field"
							type="password"
							class="form-control"
							:class="{ error: errors.newPassword }"
							placeholder="Enter new password"
						/>
					</Field>
					<ErrorMessage name="newPassword" class="field-error" />
				</div>
				<div class="form-group">
					<label for="confirmNewPassword"
						>Confirm New Password</label
					>
					<Field v-slot="{ field }" name="confirmPassword">
						<input
							id="confirmNewPassword"
              v-no-leading-spaces
							v-bind="field"
							type="password"
							class="form-control"
							:class="{ error: errors.confirmPassword }"
							placeholder="Confirm new password"
						/>
					</Field>
					<ErrorMessage
						name="confirmPassword"
						class="field-error"
					/>
				</div>
			</div>
			<div style="text-align: right; margin-top: 20px">
				<button
					type="submit"
					class="btn btn-primary"
					:disabled="isLoading"
				>
					<fa-icon icon="fas fa-save" />
					{{ isLoading ? "Processing..." : "Update My Account" }}
				</button>
			</div>
		</Form>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { profileSchema } from "~/utils/validations/userSchema";
const { $notify } = useNuxtApp();
const { user, isLoading, fetchUser, updateUser, changePassword } = useUser();

const formRef = ref();
const initialValues = {
	name: user.value?.name || "",
	phone: user.value?.phone || "",
	currentPassword: "",
	newPassword: "",
	confirmPassword: "",
};


watch(
  () => user.value,
  (userData) => {
    initialValues.value = {
      name: userData?.name || "",
      phone: userData?.phone || "",
      currentPassword: "",
      newPassword: "",
      confirmPassword: "",
    };
    formRef.value?.resetForm({ values: initialValues.value });
  },
  { immediate: true, deep: true }
);

// Form submission handler
const onSubmit = async (values) => {
	const isPasswordChange = values.currentPassword && values.newPassword;
	let profileUpdated = false;
	let passwordChanged = false;
	let hasErrors = false;
  let error = "";

	try {
		await updateUser({
			name: values.name,
			phone: values.phone,
		});
		profileUpdated = true;
	} catch (err) {
		console.error("Failed to update profile:", err);
      hasErrors = true;
      error += "<br/>" + err?.message || "Failed to update profile";
	}
	// Change password if provided
	if (isPasswordChange) {
		try {
			await changePassword({
				currentPassword: values.currentPassword,
				newPassword: values.newPassword,
				confirmPassword: values.confirmPassword,
			});
			passwordChanged = true;
		} catch (err) {
			console.error("Failed to change password:", err);
			hasErrors = true;
      error += "<br/>" + err?.message || "Failed to change password";
		}
	}
	// Show appropriate success message for completed operations
	if (!hasErrors) {
		const message = isPasswordChange
			? "Profile and password updated successfully!"
			: "Profile updated successfully!";
		$notify.success(message);
	} else if (profileUpdated || passwordChanged) {
		// Partial success
		let partialMessage = "<strong>Update completed</strong>";
		if (profileUpdated) partialMessage += "<br/>Profile updated. ";
		if (passwordChanged) partialMessage += "<br/>Password changed. ";
		if (profileUpdated && !passwordChanged)
			partialMessage += "<br/>Password change failed.";
		if (!profileUpdated && passwordChanged)
			partialMessage += "<br/>Profile update failed.";
		$notify.warning(partialMessage + "<br/>" + error);
	}
};

onMounted(() => {
	fetchUser();
});
</script>
