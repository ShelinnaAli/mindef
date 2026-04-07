<template>
  <div class="tab-content table-container">
    <table>
      <thead>
        <tr>
          <th>Programme Name</th>
          <th>Game Type</th>
          <th>Intensity</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Actions</th>
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
            <PartialsDataError :error="error" @retry="$emit('retry')" />
          </td>
        </tr>
        <tr v-else-if="programmes.length === 0">
          <td colspan="6">
            <PartialsDataNotFound />
          </td>
        </tr>
        <tr
          v-for="programme in programmes"
          v-else
          :key="programme.id"
        >
          <td>{{ programme.name }}</td>
          <td>{{ programme.gameType?.name }}</td>
          <td>{{ capitalize(programme.intensityLevel) }}</td>
          <td>{{ formatDateTime(programme.createdAt) }}</td>
          <td>
            <span
              class="status-badge"
              :class="programme.isActive
                ? 'status-active'
                : 'status-inactive'
              "
            >
              {{ programme.isActive ? "Active" : "Inactive" }}
            </span>
          </td>
          <td>
            <template v-if="canModify(programme)">
              <button class="action-btn" @click="editProgramme(programme)">
                <fa-icon icon="fas fa-edit" /> Edit
              </button>
              <button
                class="action-btn btn-status"
                :style="programme.isActive
                  ? 'background:rgba(231, 76, 60, 0.15); color:#e74c3c;'
                  : 'background:rgba(46, 204, 113, 0.15); color:#2ecc71;'
                "
                @click="confirmStatusChange(programme)"
              >
                <fa-icon
                  :icon="programme.isActive
                    ? 'fas fa-times-circle'
                    : 'fas fa-check-circle'
                  "
                />
                {{
                  programme.isActive
                    ? "Deactivate"
                    : "Activate"
                }}
              </button>
            </template>
            <template v-else>
              <em style="color:var(--gray);">You don't have permission to modify this programme.</em>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  programmes: {
    type: Array,
    default: () => [],
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
const emit = defineEmits(["edit-programme", "refresh-programmes", "retry"]);

const { updateStatusProgramme } = useProgrammes();

const { $notify } = useNuxtApp();

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);

const editProgramme = (programme) => {
  emit("edit-programme", programme);
};

const confirmStatusChange = async (programme) => {
  const isActivating = !programme.isActive;
  const action = isActivating ? "activate" : "deactivate";
  const actionCapitalized = isActivating ? "Deactivation" : "Activation";

  $notify
    .confirm(
      `Are you sure you want to ${action} the programme "${programme.name}"?${!isActivating ? " This action can be reversed later." : ""}`,
      `Confirm ${actionCapitalized}`,
      action,
      "Cancel",
      { preventCloseOnOk: true },
    )
    .then(async ({ noty, isConfirmed }) => {
      try {
        if (isConfirmed) {
          const { success } = await updateStatusProgramme(
            programme.id,
            isActivating,
          );
          if (success) {
            programme.isActive = isActivating;
          }
          noty.close();
          $notify.success(`Programme ${action}d successfully`);
        }
      } catch (error) {
        $notify.error(
          error?.message ||
          `Failed to ${action} programme "${programme.name}"`,
        );
      }
    });
};

const canModify = (item) => {
  return ['admin', 'superadmin'].includes(userRole.value) || item.createdBy === authStore.user?.id;
}
</script>

<style lang="scss" scoped>
button {
  margin: 0;

  &:disabled {
    opacity: 0.5;
  }
}

.action-btn {
  &.btn-status {
    min-width: 120px;
    text-align: left;
  }
}

.status-inactive {
  background-color: rgba(231, 76, 60, 0.15);
  color: #e74c3c;
}

.status-active {
  background-color: rgba(46, 204, 113, 0.15);
  color: #2ecc71;
}
</style>
