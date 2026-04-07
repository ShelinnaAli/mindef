import { encrypt, decrypt } from "~/utils/crypto";
import { useAuthStore } from "~/stores/useAuthStore";

export function useUser() {
	const authStore = useAuthStore();
	const isLoading = ref(false);
	const user = ref(null);

	// Users list state
	const users = ref([]);
	const usersLoading = ref(false);
	const usersError = ref(null);
	const totalUsers = ref(0);

	// Initialize with auth store user data if available
	onMounted(() => {
		if (authStore.user) {
			user.value = authStore.user;
		}
	});

	// Fetch user data
	const fetchUser = async (id = null, options = {}) => {
		isLoading.value = true;
		try {
			const endpoint = id ? `/api/user/${id}` : "/api/user";
			const fetchOptions = {
				method: "GET",
			};

			// Add query parameters if provided
			if (options.expand) {
				fetchOptions.query = {
					expand: options.expand,
				};
			}

			const response = await $fetch(endpoint, fetchOptions);

			if (response.success === false) {
				throw new Error(response.message || "Failed to fetch user");
			}

			const userData = decrypt(response.data);
			if (userData) {
				user.value = {
					...userData,
					scheme_: userData.scheme,
					scheme: userData.scheme?.name || "",
				};
			}

			return userData;
		} catch (error) {
			const message = error.data?.message || "Failed to fetch user";
			console.error("fetch user failed", message, error.data);

			// Keep using auth store data if API fails
			if (id === null) {
				if (authStore.user && !user.value) {
					user.value = authStore.user;
				}
			}
		} finally {
			isLoading.value = false;
		}
	};

	// Update user data
	const updateUser = async (userData, id = null) => {
		isLoading.value = true;
		try {
			const endpoint = id ? `/api/user/${id}` : "/api/user";
			const response = await $fetch(endpoint, {
				method: "PUT",
				body: { data: encrypt(userData) },
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to update user");
			}

			const updatedUser = decrypt(response.data);
			user.value = { ...user.value, ...updatedUser };

			// Update auth store with new user data if this is the current user
			if (id === null && authStore.user) {
				authStore.setSession(user.value, authStore.accessToken);
			}

			return response;
		} catch (error) {
			const message = error.data?.message || "Failed to update user";
			console.error("user update error:", message, error.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Add user method
	const addUser = async (userData) => {
		isLoading.value = true;
		try {
			const response = await $fetch("/api/user", {
				method: "POST",
				body: userData,
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to add user");
			}

			const newUser = decrypt(response.data);
			const currentUsers = Array.isArray(users.value) ? users.value : [];
			// Optionally update users list if needed
			users.value = [newUser, ...currentUsers];
			return response;
		} catch (error) {
			const message = error.data?.message || "Failed to add user";
			console.error("add user error:", message, error.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Fetch users list
	const fetchUsers = async (options = {}) => {
		usersLoading.value = true;
		usersError.value = null;
		users.value = [];

		try {
			const fetchOptions = {
				method: "GET",
				query: options,
			};

			const response = await $fetch(`/api/users`, fetchOptions);

			if (response.success == false) {
				throw new Error(response.message || "Failed to fetch users");
			}

			// Transform users data to match frontend format
			const decryptedData = decrypt(response.data);
			const data = Array.isArray(decryptedData)
				? decryptedData
				: Array.isArray(decryptedData?.data)
					? decryptedData.data
					: [];

			const transformedUsers = data.map((user) => ({
				...user,
				age: user.birthYear
					? new Date().getFullYear() - user.birthYear
					: null,
				lastLogin: user.lastLoginAt
					? formatLastLogin(user.lastLoginAt)
					: "Never",
				status: user.isActive ? "Active" : "Inactive",
			}));

			users.value = transformedUsers;
		} catch (err) {
			usersError.value =
				err?.data?.message || "An error occurred while fetching users";
			console.error("Error fetching users:", usersError.value, err);
		} finally {
			usersLoading.value = false;
		}
	};

	// Fetch total registered users
	const fetchTotalUsers = async () => {
		isLoading.value = true;
		totalUsers.value = 0;
		try {
			const response = await $fetch("/api/users/total", {
				method: "GET",
			});
			if (response.success === false) {
				throw new Error(
					response.message || "Failed to fetch total users",
				);
			}

			totalUsers.value = response.data.total || 0;
		} catch (error) {
			const message =
				error.data?.message ||
				error.message ||
				"An error occurred while fetching total users";
			console.error("Total users fetch error:", message, error.data);
		} finally {
			isLoading.value = false;
		}
	};

	// Delete user
	const deleteUser = async (userId) => {
		try {
			const response = await $fetch(`/api/user/${userId}`, {
				method: "DELETE",
			});

			if (response.success === false) {
				throw new Error(response.message || "Failed to delete user");
			}
			// Remove user from local state
			const currentUsers = Array.isArray(users.value) ? users.value : [];
				users.value = currentUsers.filter((user) => user.id !== userId);
			return response;
		} catch (error) {
			const message =
				error.data?.message || error.message || "Failed to delete user";
			console.error("Delete user error:", message, error.data);
			throw error?.data;
		}
	};

	// Helper function to format last login date
	const formatLastLogin = (dateString) => {
		const date = new Date(dateString);
		const now = new Date();
		const diffInSeconds = Math.floor((now - date) / 1000);

		if (diffInSeconds < 60) {
			return "Just now";
		} else if (diffInSeconds < 3600) {
			const minutes = Math.floor(diffInSeconds / 60);
			return `${minutes} minute${minutes > 1 ? "s" : ""} ago`;
		} else if (diffInSeconds < 86400) {
			const hours = Math.floor(diffInSeconds / 3600);
			return `${hours} hour${hours > 1 ? "s" : ""} ago`;
		} else if (diffInSeconds < 2592000) {
			const days = Math.floor(diffInSeconds / 86400);
			return `${days} day${days > 1 ? "s" : ""} ago`;
		} else {
			return date.toLocaleDateString();
		}
	};

	// Change password function
	const changePassword = async (passwordData) => {
		isLoading.value = true;

		try {
			const encryptedData = encrypt(passwordData);
			const response = await $fetch("/api/auth/change-password", {
				method: "POST",
				body: { data: encryptedData },
			});

			if (response.success === false) {
				throw new Error(
					response.message || "Failed to change password",
				);
			}

			return response;
		} catch (error) {
			const message = error.data?.message || "Failed to change password";
			console.error("Password change error:", message, error.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	// Register function
	const register = async (userData) => {
		isLoading.value = true;

		try {
			const encryptedData = encrypt(userData);
			const response = await $fetch("/api/auth/register", {
				method: "POST",
				body: { data: encryptedData },
			});

			if (response.success === false) {
				throw new Error(response.message || "Registration failed");
			}

			const session = decrypt(response.data);
			authStore.setSession(session.user, session.accessToken);

			return response;
		} catch (error) {
			const message = error.data?.message || "Registration failed";
			console.error("Registration error:", message, error.data);
			throw error?.data;
		} finally {
			isLoading.value = false;
		}
	};

	return {
		user: readonly(user),
		isLoading: readonly(isLoading),
		fetchUser,
		updateUser,
		addUser,
		deleteUser,
		changePassword,
		register,
		// Users list functionality
		users: readonly(users),
		usersLoading: readonly(usersLoading),
		usersError: readonly(usersError),
		fetchUsers,

		totalUsers: readonly(totalUsers),
		fetchTotalUsers,
	};
}
