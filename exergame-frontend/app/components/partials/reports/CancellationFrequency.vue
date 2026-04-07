<template>
  <div class="tab-content active table-container">
    <table>
      <thead>
        <tr>
          <th>Programme Name</th>
          <th>Trainer Name</th>
          <th>Game Type</th>
          <th>Intensity</th>
          <th>Duration (Minutes)</th>
          <th>Cancellation Rate (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="isLoading">
          <td colspan="6" >
            <PartialsDataLoading />
          </td>
        </tr>
        <tr v-else-if="error">
          <td colspan="6">
            <PartialsDataError :error="error" @retry="generateReport" />
          </td>
        </tr>
        <tr v-else-if="!programmeCancellationFrequencies.length">
          <td colspan="6">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="item in programmeCancellationFrequencies"
          v-else
          :key="item.name"
        >
          <td>{{ item.programmeName }}</td>
          <td>{{ item.trainerName }}</td>
          <td>{{ item.gameTypeName }}</td>
          <td>{{ capitalize(item.intensityLevel) }}</td>
          <td>{{ item.durationMinutes }}</td>
          <td>{{ item.cancellationRate }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineExpose({
  generateReport
})

const { programmeCancellationFrequencies, isLoading, error, fetchProgrammeCancellationFrequencies } = useProgrammeReport()

const _options = ref({})

async function generateReport(options) {
  if (options) {
    // backup options
    _options.value = options
  } else {
    options = _options.value
  }
  await fetchProgrammeCancellationFrequencies(options)
}
</script>
