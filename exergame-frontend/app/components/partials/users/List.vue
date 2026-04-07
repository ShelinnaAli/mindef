<template>
  <table>
    <thead>
      <tr>
        <th>Scheme</th>
        <th>Name</th>
        <th>Age</th>
        <th>Phone</th>
        <th>Last Login</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-if="isLoading">
        <td colspan="7">
          <PartialsDataLoading />
        </td>
      </tr>
      <tr v-else-if="error">
        <td colspan="7">
          <PartialsDataError :error="error" @retry="$emit('retry')" />
        </td>
      </tr>
      <tr v-else-if="users.length === 0">
        <td colspan="7">
          <PartialsDataNotFound />
        </td>
      </tr>
      <tr v-for="user in users" v-else :key="user.id">
        <td>{{ user.scheme?.name }}</td>
        <td>{{ user.name }}</td>
        <td>{{ user.age }}</td>
        <td>{{ user.phone }}</td>
        <td>{{ user.lastLogin }}</td>
        <td>
          <span class="status-badge" :class="getStatusClass(user.status)">
            {{ user.status }}
          </span>
        </td>
        <td>
          <button
            class="action-btn"
            :disabled="user.role === 'admin' && userRole === 'admin' && user.id !== userId"
            @click="$emit('edit-user', user.id)"
          >
            <fa-icon icon="fas fa-edit" />
          </button>
          <button
            class="action-btn"
            :disabled="user.role === 'admin' && userRole === 'admin'"
            @click="confirmDelete(user)"
          >
            <fa-icon icon="fas fa-trash" />
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
const { $notify } = useNuxtApp();
const { deleteUser } = useUser();

defineProps({
  users: {
    type: Array,
    required: true,
  },
  error: {
    type: [String, Object],
    default: null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["edit-user", "delete-user", "retry"]);

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);
const userId = computed(() => authStore.user?.id);

const confirmDelete = async (user) => {
  $notify
    .confirm(
      `Are you sure you want to delete user "${user.name}"? This action cannot be undone.`,
      "",
      "Delete",
      "Cancel",
      { preventCloseOnOk: true },
    )
    .then(async ({ noty, isConfirmed }) => {
      try {
        if (isConfirmed) {
          await deleteUser(user.id);
          emit("delete-user");
        }
        noty.close();
      } catch (error) {
        $notify.error(error?.message || "Failed to delete user");
      }
    });
};

const getStatusClass = (status) => {
  return status.toLowerCase() === "active"
    ? "status-active"
    : "status-pending";
};
</script>
