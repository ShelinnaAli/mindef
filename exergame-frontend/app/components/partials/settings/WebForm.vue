<template>
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">System Settings</h2>
    </div>
    <Form
      ref="formRef"
      v-slot="{ errors }"
      :validation-schema="webSettingSchema"
      :initial-values="initialValues"
      style="display: block;"
      @submit="values => $emit('submit', values, 'WEB')"
    >
      <div class="form-group">
        <label for="appName">Gym Name</label>
        <Field
          id="appName"
          name="appName"
          type="text"
          class="form-control"
          :class="{ error: errors.appName }"
          placeholder="Enter gym name"
        />
        <ErrorMessage name="appName" class="field-error" />
      </div>
      <div class="form-group">
        <label for="advertisementMsg">Login Page Advertisement</label>
        <Field
          id="advertisementMsg"
          name="advertisementMsg"
          as="textarea"
          class="form-control"
          rows="3"
          :class="{ error: errors.advertisementMsg }"
          placeholder="Enter advertisement text"
        />
        <ErrorMessage name="advertisementMsg" class="field-error" />
      </div>
      <div class="form-group">
        <label for="loginBackgroundMediaUrl">Login Background Media URL</label>
        <Field
          id="loginBackgroundMediaUrl"
          name="loginBackgroundMediaUrl"
          type="url"
          class="form-control"
          :class="{ error: errors.loginBackgroundMediaUrl }"
          placeholder="Enter login media URL"
        />
        <ErrorMessage name="loginBackgroundMediaUrl" class="field-error" />
      </div>
      <div style="text-align: right; margin-top: 20px">
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="isLoading"
        >
          <fa-icon icon="fas fa-save" />
          {{ isLoading ? "Processing..." : "Save System Settings" }}
        </button>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { webSettingSchema } from "~/utils/validations/webSettingSchema";

defineEmits(["submit"]);

const props = defineProps({
  webSetting: {
    type: Object,
    default: () => ({})
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const formRef = ref();
const initialValues = ref({
  appName: "",
  advertisementMsg: "",
  loginBackgroundMediaUrl: "",
});

watch(
  () => props.webSetting,
  (webSetting) => {
    initialValues.value = {
      appName: webSetting?.find(s => s.key === 'APP_NAME')?.value || "",
      advertisementMsg: webSetting?.find(s => s.key === 'ADVERTISEMENT_MSG')?.value || "",
      loginBackgroundMediaUrl: webSetting?.find(s => s.key === 'LOGIN_BACKGROUND_MEDIA_URL')?.value || "",
    };
    formRef.value?.resetForm({ values: initialValues.value });
  },
  { immediate: true, deep: true }
);
</script>
