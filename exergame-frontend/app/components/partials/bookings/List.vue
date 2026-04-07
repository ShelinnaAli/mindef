<template>
	<div>
    <PartialsDataLoading v-if="isLoading" />
    <PartialsDataError v-else-if="error" :error="error" @retry="getBookings" />
    <PartialsDataNotFound v-else-if="userBookings.length === 0"/>

    <PartialsBookingsCard
      v-else
      :user-bookings="userBookings"
      :is-readonly="forDashboard && ['admin', 'superadmin'].includes(userRole)"
      @update-booking-status="updateBookingStatus"
    />
	</div>
</template>

<script setup>
const {
	myBookings,
	bookings,
	isLoading,
	error,
	fetchMyBookings,
	fetchBookings,
} = useBookings();

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);

const props = defineProps({
	forDashboard: {
		type: Boolean,
		default: false,
	},
});

const updateBookingStatus = (bookingData) => {
	if (authStore.user?.role === "user") {
		const index = myBookings.value.findIndex(
			(booking) => booking.id === bookingData.id,
		);
		if (index > -1) {
			myBookings.value[index].status = bookingData.status;
			myBookings.value[index].schedule = bookingData.schedule;
			myBookings.value[index].cancellationReason =
				bookingData.cancellationReason;
			myBookings.value[index].cancellationAt = bookingData.cancellationAt;
		}
	} else {
		const index = bookings.value.findIndex(
			(booking) => booking.id === bookingData.id,
		);
		if (index > -1) {
			bookings.value[index].status = bookingData.status;
			bookings.value[index].schedule = bookingData.schedule;
			bookings.value[index].cancellationReason =
				bookingData.cancellationReason;
			bookings.value[index].cancellationAt = bookingData.cancellationAt;
		}
	}
};

const getBookings = () => {
	let options = {
		expand: "schedule.programme,schedule.trainer,schedule.room,user",
		count: "schedule.bookings",
	};

	if (userRole.value === "user") {
		if (props.forDashboard) {
			options = { ...options, latest: true };
		}
		fetchMyBookings(options);
	} else {
		fetchBookings(options);
	}
};

const userBookings = computed(() => {
	if (userRole.value === "user") {
		return myBookings.value;
	} else {
		return bookings.value;
	}
});

onMounted(() => getBookings());
</script>
