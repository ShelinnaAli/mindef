export const useNotifications = () => {
	const isLoading = ref(false);
	const notifications = ref([]);
	const error = ref(null);
	const intervalNotif = ref(null);

  const setIntervalNotif = () => {
    intervalNotif.value = setInterval(() => {
      fetchNotifications();
    }, 60000);
  };

  const clearIntervalNotif = () => {
    clearInterval(intervalNotif.value);
    intervalNotif.value = null;
  }

	// Fetch all notifications
	const fetchNotifications = async () => {
		isLoading.value = true;
		error.value = null;
    notifications.value = [];

		try {
      // cancel when token is not present
      const token = useCookie("auth-token");
      if (!token.value) {
        clearIntervalNotif()
      }

			const response = await $fetch("/api/notifications", {
				method: "GET",
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch notifications",
				);
			}
			notifications.value = response.data.map((notification) => ({
				...notification,
				createdAt: formatHumanDateTime(notification.createdAt),
			}));
		} catch (err) {
			error.value =
				err?.data?.message || "Failed to fetch notifications";
			console.error("Notifications fetch error:", error.value, err);
      clearIntervalNotif()
		} finally {
			isLoading.value = false;
		}
	};

	const markAsRead = async (notificationId) => {
		try {
			await $fetch(`/api/notifications/${notificationId}/read`, {
				method: "PUT",
			});
			notifications.value = notifications.value.map((notification) =>
				notification.id === notificationId
					? { ...notification, isRead: true }
					: notification,
			);
		} catch (err) {
			console.error("Failed to mark notification as read:", err);
		}
	};

	return {
		notifications: readonly(notifications),
		isLoading: readonly(isLoading),
		error: readonly(error),
		intervalNotif,
		fetchNotifications,
		markAsRead,
    setIntervalNotif,
    clearIntervalNotif,
	};
};
