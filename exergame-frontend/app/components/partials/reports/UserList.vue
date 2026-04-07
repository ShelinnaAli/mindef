<template>
  <div class="tab-content active table-container">
    <table>
      <thead>
        <tr>
          <th>Registration Date</th>
          <th>Scheme</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Age</th>
          <th>Emergency Contact</th>
          <!-- <th>Relationship / Kin</th> -->
          <th>Relation Phone Number</th>
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
        <tr v-else-if="!userListReport.length">
          <td colspan="10">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="item in userListReport"
          :key="item.name"
        >
          <td>{{ formatDateTime(item.registrationDate) }}</td>
          <td>{{ item.schemeName }}</td>
          <td>{{ item.name }}</td>
          <td>{{ item.phone }}</td>
          <td>{{ item.age }}</td>
          <td>{{ item.emergencyContactName || 'N/A' }}</td>
          <!-- <td>{{ item.emergencyContactRelationship }}</td> -->
          <td>{{ item.emergencyContactPhone || 'N/A' }}</td>
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
  userListReport,
  isLoading,
  error,
  fetchUserListReport
} = useUserReport()

const _options = ref({})

async function generateReport(options) {
  if (options) {
    // backup options
    _options.value = options
  } else {
    options = _options.value
  }
  await fetchUserListReport(options)
}
</script>
