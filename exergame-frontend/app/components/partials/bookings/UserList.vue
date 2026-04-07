<template>
	<div class="modal">
		<div class="modal-content">
			<span class="close-modal" @click="$emit('close')">&times;</span>
			<h2>Attendees for {{ title }}</h2>
			<div class="modal-programme-info">
				<div class="tab-content table-container">
					<table style="min-width: auto">
						<thead>
							<tr>
								<th>Name</th>
								<th>Phone</th>
								<th>Status</th>
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
                  <PartialsDataError :error="error" @retry="getUserBookings" />
                </td>
              </tr>
              <tr v-else-if="userBookings.length === 0">
                <td colspan="6">
                  <PartialsDataNotFound />
                </td>
              </tr>
              <tr v-for="user in userBookings" v-else :key="user.id">
                <td>{{ user.name }}</td>
                <td>{{ user.phone }}</td>
                <td>
                  <div
                    class="status-badge text-center"
                    :class="`status-${user.status}`"
                  >
                    {{ capitalize(user.status) }}
                  </div>
                </td>
              </tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
const props = defineProps({
	schedule: {
		type: Object,
		default: null,
	},
	programme: {
		type: Object,
		default: null,
	},
});

defineEmits(["close"]);
const { userBookings, isLoading, error, fetchBookingUsers } = useBookings();

const getUserBookings = () => {
  if (props.schedule) {
    fetchBookingUsers("schedule", props.schedule.id);
  } else if (props.programme) {
    fetchBookingUsers("programme", props.programme.id);
  }
}
const title = computed(() => {
	return props.schedule
		? props.schedule.programme?.name
		: props.programme?.name;
});

onMounted(() => {
  getUserBookings();
});
</script>
