<template>
	<div>
		<AuthLoginForm />
	</div>
</template>

<script setup>
definePageMeta({
	middleware: "guest",
});
useHead({
	title: "Login",
});
</script>
