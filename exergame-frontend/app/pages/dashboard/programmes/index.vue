<template>
	<div>
		<DashboardsProgrammesUserPage v-if="userRole === 'user'" />
		<DashboardsProgrammesAdminPage v-else />
	</div>
</template>

<script setup>
useHead({
	title: "Programmes",
});

definePageMeta({
	layout: "dashboard",
	middleware: "auth",
});

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);
</script>
