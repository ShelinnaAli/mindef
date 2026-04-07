<template>
	<div>
		<h3 class="section-title">My Upcoming Bookings</h3>
		<template v-if="bookings.length > 0">
			<card-booking :items="bookings" />
		</template>
		<template v-else>
			<p style="text-align: center; color: var(--gray)">
				You have no upcoming bookings.
			</p>
		</template>
	</div>
</template>

<script setup>
import CardBooking from "@/components/partials/bookings/Card.vue";

defineProps({
	bookings: {
		type: Array,
		required: true,
	},
});
</script>
