<template>
	<div>
		<DashboardsNotificationsNotificationPage />
	</div>
</template>

<script setup>
useHead({
	title: "Notifications",
});

definePageMeta({
	layout: "dashboard",
	middleware: "auth",
});
</script>
