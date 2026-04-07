<template>
	<div class="table-container">
		<table id="upcoming-programmes-table">
			<thead>
				<tr>
					<th>Programme Name</th>
					<th>Date</th>
					<th>Time</th>
					<th>Duration</th>
					<th>Attendees</th>
					<th>Max Capacity</th>
				</tr>
			</thead>
			<tbody>
        <tr v-if="isLoading">
          <td colspan="6">
            <PartialsDataLoading />
          </td>
        </tr>
        <tr v-else-if="error">
          <td colspan="6">
            <PartialsDataError :error="error" @retry="getSchedules" />
          </td>
        </tr>
        <tr v-else-if="schedules.length === 0">
          <td colspan="6">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="schedule in schedules"
          v-else
          :key="schedule.id || schedule.name"
        >
          <td>{{ schedule.programme?.name }}</td>
          <td>{{ schedule.date }}</td>
          <td>{{ schedule.time }}</td>
          <td>{{ schedule.programme?.durationMinutes }} mins</td>
          <td>{{ schedule.totalBookings }}</td>
          <td>{{ schedule.programme?.maxParticipants }}</td>
        </tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
const { schedules, isLoading, error, fetchSchedules } = useProgrammeSchedules();

const getSchedules = () => {
	fetchSchedules({
		expand: "programme",
		count: "bookings",
		status: "active",
		latest: true,
		sorts: "day:asc,startTime:asc",
	});
}
onMounted(() => {
	getSchedules();
});
</script>
