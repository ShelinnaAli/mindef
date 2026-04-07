<template>
	<div class="section">
		<div class="section-header">
			<h2 class="section-title">Book an Exergame Programme</h2>
			<PartialsTab
				:tab-items="tabs"
				:default-tab="'available-programmes'"
				@tab-change="handleTabChange"
			/>
		</div>

		<div v-if="activeTab === 'available-programmes'" class="tab-content">
			<p
				style="
					font-size: 1.1rem;
					color: var(--gray);
					margin: 0 0 20px 0;
				"
			>
				Click on the calendar to select programme slot
			</p>
			<PartialsBookingsForm
				@programme-selected="handleTabChange('my-bookings')"
			/>
		</div>

		<div v-if="activeTab === 'my-bookings'" class="tab-content">
			<h3 class="section-title" style="margin-bottom: 20px">
				My Upcoming Bookings
			</h3>
			<PartialsBookingsList />
		</div>
	</div>
</template>

<script setup>
const tabs = [
	{ id: "available-programmes", label: "Available Programmes" },
	{ id: "my-bookings", label: "My Bookings" },
];

const activeTab = ref("available-programmes");

const handleTabChange = (tabId) => (activeTab.value = tabId);
</script>
