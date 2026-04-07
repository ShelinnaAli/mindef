import { ref, readonly } from 'vue'

export function useProgrammeReport() {
	const programmeCancellationFrequencies = ref([])
	const programmeTakeUpRates = ref([])
	const programmeRunFrequencies = ref([])
	const isLoading = ref(false)
	const error = ref(null)

	async function fetchProgrammeCancellationFrequencies(options = {}) {
		isLoading.value = true
		error.value = null
    programmeCancellationFrequencies.value = []
		try {
			const response = await $fetch('/api/report/programme-cancellation-frequencies', {
				method: 'GET',
				query: options
			})
			programmeCancellationFrequencies.value = decrypt(response.data) || []
		} catch (err) {
			const message = err?.data?.message || 'Failed to fetch programme cancellation frequencies'
			error.value = message
		} finally {
			isLoading.value = false
		}
	}

	async function fetchProgrammeTakeUpRates(options = {}) {
		isLoading.value = true
		error.value = null
    programmeTakeUpRates.value = []
		try {
			const response = await $fetch('/api/report/programme-take-up-rates', {
				method: 'GET',
				query: options
			})
			programmeTakeUpRates.value = decrypt(response.data) || []
		} catch (err) {
			const message = err?.data?.message || 'Failed to fetch programme take up rates'
			error.value = message
		} finally {
			isLoading.value = false
		}
	}

	async function fetchProgrammeRunFrequencies(options = {}) {
		isLoading.value = true
		error.value = null
    programmeRunFrequencies.value = []
		try {
			const response = await $fetch('/api/report/programme-run-frequencies', {
				method: 'GET',
				query: options
			})
			programmeRunFrequencies.value = decrypt(response.data) || []
		} catch (err) {
			const message = err?.data?.message || 'Failed to fetch programme run frequencies'
			error.value = message
		} finally {
			isLoading.value = false
		}
	}

	return {
		programmeCancellationFrequencies: readonly(programmeCancellationFrequencies),
		programmeTakeUpRates: readonly(programmeTakeUpRates),
		programmeRunFrequencies: readonly(programmeRunFrequencies),
		isLoading: readonly(isLoading),
		error: readonly(error),
		fetchProgrammeCancellationFrequencies,
		fetchProgrammeTakeUpRates,
		fetchProgrammeRunFrequencies
	}
}
