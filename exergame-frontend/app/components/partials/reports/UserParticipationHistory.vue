<template>
  <div class="tab-content active table-container">
    <table>
      <thead>
        <tr>
          <th>Scheme</th>
          <th>Name</th>
          <th>Participant Age</th>
          <th>Programme Name</th>
          <th>Game Type</th>
          <th>Intensity</th>
          <th>Participation Date</th>
          <th>Participation Time</th>
          <!-- <th>Attendance Status</th> -->
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
        <tr v-else-if="!userParticipationHistory.length">
          <td colspan="10">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="item in userParticipationHistory"
          v-else
          :key="item.id || item.name"
        >
          <td>{{ item.schemeName }}</td>
          <td>{{ item.userName }}</td>
          <td>{{ item.userAge }}</td>
          <td>{{ item.programmeName }}</td>
          <td>{{ item.gameTypeName }}</td>
          <td>{{ capitalize(item.intensityLevel) }}</td>
          <td>{{ formatDate(item.day) }}</td>
          <td>{{ formatTimeString(item.startTime) }}</td>
          <!-- <td>{{ item.attendanceStatus }}</td> -->
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineExpose({
  generateReport
})

const { userParticipationHistory, isLoading, error, fetchUserParticipationHistory } = useUserReport()

const _options = ref({})

async function generateReport(options) {
  if (options) {
    // backup options
    _options.value = options
  } else {
    options = _options.value
  }
  await fetchUserParticipationHistory(options)
}
</script>
