<template>
  <div class="tab-content active table-container">
    <table>
      <thead>
        <tr>
          <th>Programme Date</th>
          <th>Programme Time</th>
          <th>Trainer Name</th>
          <th>Programme Name</th>
          <th>Game Type</th>
          <th>Intensity</th>
          <th>Duration (Minutes)</th>
          <th>Total Booked Slots</th>
          <th>Max Capacity</th>
          <th>Take-Up Rate (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="isLoading">
          <td colspan="10" >
            <PartialsDataLoading />
          </td>
        </tr>
        <tr v-else-if="error">
          <td colspan="10">
            <PartialsDataError :error="error" @retry="generateReport" />
          </td>
        </tr>
        <tr v-else-if="!programmeTakeUpRates.length">
          <td colspan="10">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="item in programmeTakeUpRates"
          v-else
          :key="item.name"
        >
          <td>{{ formatDate(item.day) }}</td>
          <td>{{ formatTimeString(item.startTime) }}</td>
          <td>{{ item.trainerName }}</td>
          <td>{{ item.programmeName }}</td>
          <td>{{ item.gameTypeName }}</td>
          <td>{{ capitalize(item.intensityLevel) }}</td>
          <td>{{ item.durationMinutes }}</td>
          <td>{{ item.totalBookings }}</td>
          <td>{{ item.maxParticipants }}</td>
          <td>{{ item.takeUpRate }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineExpose({
  generateReport
})

const {
  programmeTakeUpRates,
  isLoading,
  error,
  fetchProgrammeTakeUpRates
} = useProgrammeReport()

const _options = ref({})

async function generateReport(options) {
  if (options) {
    // backup options
    _options.value = options
  } else {
    options = _options.value
  }
  await fetchProgrammeTakeUpRates(options)

}
</script>
