<template>
	<div>
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">Manage Bookings</h2>
			</div>
			<PartialsBookingsList />
		</div>
	</div>
</template>

<script setup>
useHead({
	title: "Bookings",
});

definePageMeta({
	layout: "dashboard",
	middleware: "auth",
});
</script>
