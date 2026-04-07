<template>
	<div>
		<DashboardsAnnouncementsAdminPage v-if="['admin', 'superadmin'].includes(userRole)" />
		<DashboardsAnnouncementsAnnouncementPage v-else />
	</div>
</template>

<script setup>
useHead({
	title: "Announcement & News",
});

definePageMeta({
	layout: "dashboard",
	middleware: "auth",
});

const authStore = useAuthStore();
const userRole = ref(authStore.user?.role);
</script>
