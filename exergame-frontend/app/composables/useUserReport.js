import { ref, readonly } from 'vue'

export function useUserReport() {
  const userListReport = ref([])
  const userParticipationReport = ref([])
  const userParticipationHistory = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchUserListReport(options = {}) {
    isLoading.value = true
    error.value = null
    userListReport.value = []
    try {
      const response = await $fetch('/api/report/user-list-report', {
        method: 'GET',
        query: options
      })
      userListReport.value = decrypt(response.data) || []
    } catch (err) {
      const message = err?.data?.message || 'Failed to fetch user list report'
      error.value = message
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUserParticipationReport(options = {}) {
    isLoading.value = true
    error.value = null
    userParticipationReport.value = []
    try {
      const response = await $fetch('/api/report/user-participation-report', {
        method: 'GET',
        query: options
      })
      userParticipationReport.value = decrypt(response.data) || []
    } catch (err) {
      const message = err?.data?.message || 'Failed to fetch user participation report'
      error.value = message
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUserParticipationHistory(options = {}) {
    isLoading.value = true
    error.value = null
    userParticipationHistory.value = []
    try {
      const response = await $fetch('/api/report/user-participation-history', {
        method: 'GET',
        query: options
      })
      userParticipationHistory.value = decrypt(response.data) || []
    } catch (err) {
      const message = err?.data?.message || 'Failed to fetch user participation history'
      error.value = message
    } finally {
      isLoading.value = false
    }
  }

  return {
    userListReport: readonly(userListReport),
    userParticipationReport: readonly(userParticipationReport),
    userParticipationHistory: readonly(userParticipationHistory),
    isLoading: readonly(isLoading),
    error: readonly(error),
    fetchUserListReport,
    fetchUserParticipationReport,
    fetchUserParticipationHistory
  }
}
