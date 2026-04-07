<template>
	<div>
		<PartialsSettingsWebForm
      :web-setting="webSettings"
      :is-loading="isWebSettingSubmit"
      @submit="handleAppSettingSubmit"
    />

		<PartialsSettingsSMTPForm
      :smtp-setting="smtpSettings"
      :is-loading="isSmtpSettingSubmit"
      @submit="handleAppSettingSubmit"
    />

		<PartialsSettingsAdminProfileForm />
	</div>
</template>

<script setup>
import { useAppSettingsStore } from '~/stores/useAppSettingsStore';

const { $notify } = useNuxtApp();
const appSettingsStore = useAppSettingsStore();
const { webSettings, smtpSettings } = storeToRefs(appSettingsStore);

const isWebSettingSubmit = ref(false)
const isSmtpSettingSubmit = ref(false)

const handleAppSettingSubmit = async (values, flag) => {
  if (flag === 'WEB') {
    isWebSettingSubmit.value = true;
  } else if (flag === 'SMTP') {
    isSmtpSettingSubmit.value = true;
  }

  try {
    if (values.value != undefined) delete values.value;
    const { message } = await appSettingsStore.updateAppSetting(flag, values);
    $notify.success(message || "Data stored successfully");
	} catch (error) {
    $notify.error(error?.message || "Failed to store the updated data");
  } finally {
    isWebSettingSubmit.value = false;
    isSmtpSettingSubmit.value = false;
  }
}

</script>
