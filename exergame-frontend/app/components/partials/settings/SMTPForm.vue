<template>
	<div class="section">
		<div class="section-header">
			<h2 class="section-title">SMTP Email Settings</h2>
		</div>
    <Form
      ref="formRef"
      v-slot="{ errors }"
      :validation-schema="smtpSettingSchema"
      :initial-values="initialValues"
      style="display: block;"
      @submit="values => $emit('submit', values, 'SMTP')"
		>
			<div class="form-grid">
				<div class="form-group">
					<label for="smtpHost">SMTP Host</label>
					<Field
						id="smtpHost"
						name="smtpHost"
						type="text"
						class="form-control"
						:class="{ error: errors.smtpHost }"
						placeholder="e.g., smtp.example.com"
					/>
					<ErrorMessage name="smtpHost" class="field-error" />
				</div>
				<div class="form-group">
					<label for="smtpPort">SMTP Port</label>
					<Field
						id="smtpPort"
						name="smtpPort"
						type="number"
						class="form-control"
						:class="{ error: errors.smtpPort }"
						placeholder="e.g., 587"
					/>
					<ErrorMessage name="smtpPort" class="field-error" />
				</div>
				<div class="form-group">
					<label for="smtpUsername">SMTP Username</label>
					<Field
						id="smtpUsername"
						name="smtpUsername"
						type="text"
						class="form-control"
						:class="{ error: errors.smtpUsername }"
						placeholder="e.g., your_email@example.com"
					/>
					<ErrorMessage name="smtpUsername" class="field-error" />
				</div>
				<div class="form-group">
					<label for="smtpPassword">SMTP Password</label>
					<Field
						id="smtpPassword"
						name="smtpPassword"
						type="text"
						class="form-control"
						:class="{ error: errors.smtpPassword }"
						placeholder="Enter SMTP password"
					/>
					<ErrorMessage name="smtpPassword" class="field-error" />
				</div>
				<div class="form-group">
					<label for="smtpEmailSender">Sender Email Address</label>
					<Field
						id="smtpEmailSender"
						name="smtpEmailSender"
						type="email"
						class="form-control"
						:class="{ error: errors.smtpEmailSender }"
						placeholder="e.g., noreply@example.com"
					/>
					<ErrorMessage name="smtpEmailSender" class="field-error" />
				</div>
			</div>
			<div style="text-align: right; margin-top: 20px">
				<button
					type="submit"
					class="btn btn-primary"
					:disabled="isLoading"
				>
					<fa-icon icon="fas fa-save" />
					{{ isLoading ? "Processing..." : "Save SMTP Settings" }}
				</button>
			</div>
		</Form>
	</div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { smtpSettingSchema } from "~/utils/validations/SMTPSettingSchema";

defineEmits(["submit"]);

const props = defineProps({
	smtpSetting: {
		type: Object,
    default: () => ({})
	},
  isLoading: {
    type: Boolean,
    default: false
  }
});

const formRef = ref();
const initialValues = {
	smtpHost: "",
	smtpPort: "",
	smtpUsername: "",
	smtpPassword: "",
	smtpEmailSender: ""
};

watch(
  () => props.smtpSetting,
  (smtpSetting) => {
    initialValues.value = {
      smtpHost: smtpSetting?.find(s => s.key === 'SMTP_HOST')?.value || "",
      smtpPort: smtpSetting?.find(s => s.key === 'SMTP_PORT')?.value || "",
      smtpUsername: smtpSetting?.find(s => s.key === 'SMTP_USERNAME')?.value || "",
      smtpPassword: smtpSetting?.find(s => s.key === 'SMTP_PASSWORD')?.value || "",
      smtpEmailSender: smtpSetting?.find(s => s.key === 'SMTP_EMAIL_SENDER')?.value || ""
    };
    formRef.value?.resetForm({ values: initialValues.value });
  },
  { immediate: true, deep: true }
);
</script>
