<template>
	<div>
		<div v-if="!userRole" class="loading-dashboard">
			<div class="loading-spinner">Loading dashboard...</div>
		</div>
		<DashboardsSummariesUserPage v-else-if="userRole === 'user'" />
		<DashboardsSummariesAdminPage v-else-if="['admin', 'superadmin'].includes(userRole)" />
		<DashboardsSummariesTrainerPage v-else-if="userRole === 'trainer'" />
	</div>
</template>

<script setup>
useHead({
	title: "Dashboard",
});

definePageMeta({
	layout: "dashboard",
	middleware: "auth",
});

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);
</script>

<style scoped>
.loading-dashboard {
	display: flex;
	justify-content: center;
	align-items: center;
	height: 200px;
}

.loading-spinner {
	font-size: 18px;
	color: var(--gray);
}
</style>
