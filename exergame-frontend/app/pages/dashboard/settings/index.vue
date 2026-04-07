<template>
	<div>
		<div v-if="!userRole" class="loading-dashboard">
			<div class="loading-spinner">Loading settings...</div>
		</div>
		<DashboardsSettingsAdminPage v-else-if="['admin', 'superadmin'].includes(userRole)" />
		<DashboardsSettingsProfilePage v-else />
	</div>
</template>

<script setup>
useHead({
	title: "Settings",
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
