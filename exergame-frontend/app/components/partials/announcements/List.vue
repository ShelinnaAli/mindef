<template>
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">Manage Existing Announcements</h2>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Content</th>
            <th>Type</th>
            <th>Posted On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="5">
              <PartialsDataLoading />
            </td>
          </tr>
          <tr v-else-if="error">
            <td colspan="5">
              <PartialsDataError :error="error" @retry="getAnnouncements" />
            </td>
          </tr>
          <tr v-else-if="announcements.length === 0">
            <td colspan="5">
              <PartialsDataNotFound />
            </td>
          </tr>
          <tr
            v-for="(item, index) in announcements"
            v-else
            :key="index"
          >
            <td>{{ item.title }}</td>
            <td>{{ item.content }}</td>
            <td>{{ capitalize(item.type?.name) }}</td>
            <td>{{ formatDateTime(item.updatedAt) }}</td>
            <td>
              <button
                class="action-btn"
                @click="$emit('edit-announcement', item)"
              >
                <fa-icon icon="fas fa-edit" />
              </button>
              <button
                class="action-btn"
                @click="handleDeleteAnnouncement(item)"
              >
                <fa-icon icon="fas fa-trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
const { $notify } = useNuxtApp();
const {
  announcements,
  isLoading,
  error,
  fetchAnnouncements,
  deleteAnnouncement
} = useAnnouncement();

defineEmits(["edit-announcement", "delete-announcement"]);

const handleDeleteAnnouncement = async (item) => {
	if (!item) return;

  $notify
    .confirm(
      `Are you sure you want to deactivate this announcement?`,
      `Confirm Deactivate`,
      "Deactivate",
      "Cancel",
      { preventCloseOnOk: true },
    )
    .then(async ({ noty, isConfirmed }) => {
      try {
        if (isConfirmed) {
          await deleteAnnouncement(item.id);
          getAnnouncements();

          noty.close();
          $notify.success(`Announcement deactivated successfully`);
        }
      } catch (error) {
        $notify.error(
          error?.message ||
          `Failed to deactivate announcement "${item.title}"`,
        );
      }
    });
};

const getAnnouncements = async() => {
  await fetchAnnouncements({
    status: 'active'
  })
};

defineExpose({
  getAnnouncements
});

onMounted(getAnnouncements);
</script>
