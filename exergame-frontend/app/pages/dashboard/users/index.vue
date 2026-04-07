<template>
  <div class="user-management-page">
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">User Management</h2>
        <div id="user-management-tabs" class="tabs">
          <PartialsTab :tab-items="tabs" :default-tab="'user'" @tab-change="handleTabChange" />
        </div>
      </div>

      <PartialsUsersAddForm v-if="activeTab === 'form-create'" @add-user="getUsers" />
      <PartialsUsersList
        v-else
        class="tab-content table-container"
        :users="userData"
        :error="usersError"
        :is-loading="usersLoading"
        @edit-user="handleEditUser"
        @delete-user="getUsers"
        @retry="getUsers"
      />

      <!-- Edit User Modal -->
      <PartialsUsersEditForm
        v-if="showEditModal"
        :user-data="selectedUserData"
        @close="closeEditModal"
        @updated="getUsers"
      />
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "User Management",
});

definePageMeta({
  layout: "dashboard",
  middleware: "auth",
});

const { $notify } = useNuxtApp();
const { users, usersLoading, usersError, fetchUsers } = useUser();

const tabs = [
  { id: "user", label: "All Users" },
  { id: "admin", label: "Admins" },
  { id: "trainer", label: "Trainers" },
  { id: "form-create", label: "Create New User" },
];

const activeTab = ref("user");
const showEditModal = ref(false);
const selectedUserData = ref(null);

const handleTabChange = (tabId) => {
  activeTab.value = tabId;
};

const handleEditUser = (userId) => {
  const user = users.value.find((user) => user.id === userId);

  if (user) {
    selectedUserData.value = user;
    showEditModal.value = true;
  } else {
    $notify.error(`Failed to load user data`);
  }
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedUserData.value = null;
};

const getUsers = () => {
  return new Promise((resolve) => {
    fetchUsers({ expand: "scheme,emergencyContact" }).then(() => {
      resolve();
    });
  });
};

const userData = computed(() => {
	const list = Array.isArray(users.value) ? users.value : [];

	if (activeTab.value === "user") {
		return list;
	}

	return list.filter((user) => user.role === activeTab.value);
});

onMounted(() => getUsers());
</script>

<style lang="scss" scoped>
.user-management-page {
  display: flex;
  flex-direction: column;
}
</style>
