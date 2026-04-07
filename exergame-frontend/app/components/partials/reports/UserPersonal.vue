<template>
  <div class="tab-content active table-container">
    <table>
      <thead>
        <tr>
          <th>Participation Date</th>
          <th>Participation Time</th>
          <th>Programme Name</th>
          <th>Intensity</th>
          <th>Status</th>
          <th>Participant Age</th>
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
        <tr v-else-if="!userParticipationReport.length">
          <td colspan="10">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="item in userParticipationReport"
          :key="item.id || item.name"
        >
          <td>{{ formatDate(item.day) }}</td>
          <td>{{ formatTimeString(item.startTime) }}</td>
          <td>{{ item.programmeName }}</td>
          <td>{{ capitalize(item.intensityLevel) }}</td>
          <td>{{ capitalize(item.status) }}</td>
          <td>{{ item.age }}</td>
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
  userParticipationReport,
  isLoading,
  error,
  fetchUserParticipationReport
} = useUserReport()

const _options = ref({})

async function generateReport(options) {
  if (options) {
    // backup options
    _options.value = options
  } else {
    options = _options.value
  }
  await fetchUserParticipationReport(options)
}
</script>
