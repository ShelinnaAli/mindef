<template>
	<div>
		<!-- User-centric Stats -->
		<PartialsSummariesStatCounter />

		<!-- My Programmes -->
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">My Programmes</h2>
				<button
					class="btn btn-outline"
					@click="navigateTo('/dashboard/programmes#my-bookings')"
				>
					View All My Bookings
				</button>
			</div>

			<PartialsBookingsList :for-dashboard="true" />
		</div>
	</div>
</template>
