<template>
	<div class="page-content">
		<slot />
		<div class="footer">
			<p>
				FITCOMMAND Gym Management System ©
				{{ new Date().getFullYear() }} | Booking Management
			</p>
		</div>
	</div>
</template>

<script setup>
// This is a container component that will render the page content
</script>

<style scoped>
.page-content {
	padding: 15px;
	background: var(--primary);
	overflow-y: auto;
	flex-grow: 1;
}

.footer {
	text-align: center;
	padding: 15px 20px 0;
	color: var(--gray);
	font-size: 14px;
	border-top: 1px solid var(--border);
}

@media (max-width: 768px) {
	.page-content {
		padding: 20px;
	}
}

@media (max-width: 576px) {
	.page-content {
		padding: 15px;
	}
}
</style>
