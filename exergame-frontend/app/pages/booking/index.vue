<template>
	<div class="modal">
		<div class="modal-content">
			<h3>Power Up with Exergame</h3>
			<hr class="form-section-divider" />

			<PartialsBookingsForm :is-from-register="true" />
		</div>
	</div>
</template>

<script setup>
definePageMeta({
	middleware: "auth",
});
</script>

<style lang="scss" scoped>
.modal-content {
	max-width: 1000px;
}
.modal-content h3 {
	font-size: 28px;
	text-align: left;
	margin-bottom: 10px;
}
</style>
