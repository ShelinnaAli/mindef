<template>
	<div>
		<!-- User-centric Stats -->
		<PartialsSummariesStatCounter />

		<!-- My Programmes -->
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">My Upcoming Programmes</h2>
				<button
					class="btn btn-outline"
					@click="navigateTo('/dashboard/programmes')"
				>
					View All Programmes
				</button>
			</div>
			<PartialsProgrammesSchedulesListTable />
		</div>

		<!-- Upcoming Session Details -->
		<!-- <div class="section" style="margin-top: 30px">
			<div class="section-header">
				<h2 class="section-title">
					Upcoming Session Details (Next 7 Days)
				</h2>
			</div>
      <PartialsDataLoading v-if="isLoading" />
      <PartialsDataError
        v-else-if="error"
        :error="error"
        @retry="getSchedules"
      />
      <PartialsDataNotFound v-else-if="schedules.length === 0" />
      <PartialsProgrammesSchedulesCard
        v-else
        :schedules="schedules"
        :is-readonly="true"
        @refresh="getSchedules"
      />
		</div> -->
	</div>
</template>

<script setup>
/* const { schedules, isLoading, error, fetchSchedules } = useProgrammeSchedules();

const getSchedules = () => {
	let options = {
		expand: "programme,trainer,room",
		count: "bookings",
		latest: "1w",
	};
	fetchSchedules(options);
};
onMounted(() => {
	getSchedules();
}); */
</script>
