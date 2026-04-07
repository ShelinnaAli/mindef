<template>
	<div>
		<div class="section">
			<h2 class="section-title">My Profile</h2>

			<div v-if="isLoadingProfile" class="loading-state">
				<fa-icon icon="fas fa-spinner" spin /> Loading profile data...
			</div>

			<template v-else>
				<div class="profile-section-header">Personal Information</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-id-card" />
					<span
						><strong>Username:</strong>
						{{ user.username || "Not available" }}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-user" />
					<span
						><strong>Full Name:</strong>
						{{ user?.name || "Not available" }}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-phone" />
					<span
						><strong>Contact No:</strong>
						{{ user?.phone || "Not available" }}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-venus-mars" />
					<span
						><strong>Gender:</strong>
						{{ formatGender(user?.gender) }}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-calendar-alt" />
					<span
						><strong>Birth Year:</strong>
						{{ user?.birthYear || "Not available" }}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-tag" />
					<span
						><strong>Scheme:</strong>
						{{ user?.scheme || "Not available" }}</span
					>
				</div>

				<div class="profile-section-header" style="margin-top: 30px">
					Emergency Contact
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-user-shield" />
					<span
						><strong>Name:</strong>
						{{
							user?.emergencyContact?.name || "Not available"
						}}</span
					>
				</div>
				<div class="profile-info-item">
					<fa-icon icon="fas fa-phone-alt" />
					<span
						><strong>Phone:</strong>
						{{
							user?.emergencyContact?.phone || "Not available"
						}}</span
					>
				</div>
				<!-- <div class="profile-info-item">
					<fa-icon icon="fas fa-handshake" />
					<span
						><strong>Relationship:</strong>
						{{
							user?.emergencyContact?.relationship ||
							"Not available"
						}}</span
					>
				</div> -->
				<div style="text-align: right; margin-top: 20px">
					<button
						class="btn btn-primary"
						@click="showEditModal = true"
					>
						<fa-icon icon="fas fa-edit" />
						Edit Profile
					</button>
				</div>
			</template>
		</div>

		<div class="section" style="margin-top: 30px">
			<h2 class="section-title">Account Settings</h2>
			<div class="profile-section-header">Change Password</div>

			<Form
				v-slot="{ isSubmitting }"
				:validation-schema="passwordSchema"
				style="display: block"
				@submit="onPasswordSubmit"
			>
				<div class="form-grid">
					<div class="form-group">
						<label for="currentPassword">Current Password</label>
						<Field
							id="currentPassword"
							v-slot="{ field, meta }"
							name="currentPassword"
							type="password"
							class="form-control"
						>
							<input
                v-no-leading-spaces
								v-bind="field"
								type="password"
								class="form-control"
								:class="{ error: meta.touched && !meta.valid }"
							/>
						</Field>
						<ErrorMessage
							name="currentPassword"
							class="field-error"
						/>
					</div>

					<div class="form-group">
						<label for="newPassword">New Password</label>
						<Field
							id="newPassword"
							v-slot="{ field, meta }"
							name="newPassword"
							type="password"
							class="form-control"
						>
							<input
                v-no-leading-spaces
								v-bind="field"
								type="password"
								class="form-control"
								:class="{ error: meta.touched && !meta.valid }"
							/>
						</Field>
						<ErrorMessage name="newPassword" class="field-error" />
					</div>

					<div class="form-group">
						<label for="confirmNewPassword"
							>Confirm New Password</label
						>
						<Field
							id="confirmNewPassword"
							v-slot="{ field, meta }"
							name="confirmPassword"
							type="password"
							class="form-control"
						>
							<input
                v-no-leading-spaces
								v-bind="field"
								type="password"
								class="form-control"
								:class="{ error: meta.touched && !meta.valid }"
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
						:disabled="isSubmitting || isLoading"
					>
						<fa-icon
							v-if="isSubmitting || isLoading"
							icon="fas fa-spinner"
							spin
						/>
						<fa-icon v-else icon="fas fa-save" />
						{{
							isSubmitting || isLoading
								? "Processing..."
								: "Update Password"
						}}
					</button>
				</div>
			</Form>
		</div>

    <PartialsSettingsProfileForm
      v-if="showEditModal"
      :user-data="user"
      @close="showEditModal = false"
      @updated="populateUserData"
    />
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { passwordSchema } from "~/utils/validations/userSchema";

const { $notify } = useNuxtApp();
const { isLoading, fetchUser, changePassword } = useUser();
const isLoadingProfile = ref(false);
const showEditModal = ref(false);

const user = ref({
	name: "",
	username: "",
	phone: "",
	gender: "",
	birthYear: "",
	scheme: "",
	emergencyContacts: [
		{
			name: "",
			phone: "",
			relationship: "",
		},
	],
});

const formatGender = (gender) => {
  gender = gender == '' ? 'Not available' : gender;
  gender = gender === 'other' ? 'non-binary' : gender;
  return capitalize(gender);
};

const populateUserData = async () => {
	isLoadingProfile.value = true;
	const fetchedUser = await fetchUser(null, {
		expand: "emergencyContact,scheme",
	});
	if (fetchedUser) {
		user.value = {
			...fetchedUser,
			scheme: fetchedUser.scheme?.name || "",
		};
	}
	isLoadingProfile.value = false;
};

const onPasswordSubmit = async (values, { resetForm }) => {
	if (isLoading.value) return;
	try {
		const { message } = await changePassword(values);
		resetForm();
		$notify.success(message || "Password updated successfully");
	} catch (error) {
		$notify.error(error?.message || "Failed to change password");
	}
};

onMounted(() => populateUserData());
</script>

<style lang="scss" scoped>
.profile-section-header {
	font-family: "Montserrat", sans-serif;
	font-weight: 600;
	font-size: 1.2rem;
	color: var(--secondary);
	margin-bottom: 20px;
	padding-bottom: 10px;
	border-bottom: 1px solid var(--border);
}

.profile-info-item {
	display: flex;
	align-items: center;
	gap: 15px;
	margin-bottom: 15px;
	font-size: 1rem;
	color: var(--dark);

	i {
		color: var(--secondary);
		font-size: 1.2rem;
		width: 25px;
		text-align: center;
	}

	strong {
		color: var(--secondary);
	}
}

.loading-state {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 10px;
	padding: 2rem;
	color: var(--secondary);
	font-size: 1.1rem;
}
</style>
