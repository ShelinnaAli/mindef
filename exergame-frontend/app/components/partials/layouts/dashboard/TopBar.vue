<template>
  <div class="topbar">
    <button
      class="menu-toggle"
      aria-label="Toggle menu"
			@click.stop="$emit('toggle-sidebar')"
		>
      <fa-icon icon="fas fa-bars" />
    </button>
    <div class="page-title">{{ pageTitle }}</div>
    <div class="actions">
      <button
        class="btn btn-outline"
				@click="navigateTo('/dashboard/notifications')"
			>
        <fa-icon icon="fa fa-bell" />
        Notifications
        <span :class="{ 'badge-notif': notificationsCount > 0 }">{{
          notificationsCount
          }}</span>
      </button>
      <button
        v-if="showNewProgrammButton"
        class="btn btn-primary"
				@click="navigateTo('/dashboard/programmes#create-new')"
			>
        <fa-icon icon="fas fa-plus" />
        <span>New Programme</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from "vue-router";

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);
const route = useRoute();
const { notifications, setIntervalNotif, fetchNotifications } = useNotifications();

defineEmits(["toggle-sidebar"]);

const getNotifications = async () => {
  if (route.path === "/dashboard/notifications") {
    return;
  }
  await fetchNotifications();
};

const notificationsCount = computed(() => {
  return notifications.value.filter((notification) => !notification.isRead).length;
});

const showNewProgrammButton = computed(() => {
  return userRole.value !== 'user' && route.path !== '/dashboard/announcements'
})

const pageTitle = computed(() => {
  if (route.path === "/" || route.path === "/dashboard") {
    return `Welcome ${authStore.user?.name || "User"}!`;
  } else if (route.path === "/dashboard/programmes") {
    return "Programmes";
  } else if (route.path === "/dashboard/notifications") {
    return ['admin', 'superadmin'].includes(userRole.value)
      ? "Notifications"
      : "Announcements & News";
  } else if (route.path === "/dashboard/settings") {
    return "Profile & Settings";
  }
  return "Dashboard";
});

onMounted(() => {
  getNotifications();
  setIntervalNotif();
});
</script>

<style scoped>
.topbar {
  background: var(--primary);
  padding: 10px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}

.menu-toggle {
	display: none; /* Hidden by default */
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--secondary);
  padding: 8px;
  margin-right: 10px;
}

.page-title {
  font-family: "Montserrat", sans-serif;
  font-weight: 700;
  font-size: 24px;
  color: var(--secondary);
  letter-spacing: 0.5px;
}

.actions {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

@media (max-width: 992px) {
  .menu-toggle {
    display: block;
  }

  .topbar {
    padding: 15px 20px;
    justify-content: space-between;
  }

  .page-title {
    font-size: 20px;
  }

  .actions {
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
  }

  .btn {
    width: auto;
    font-size: 14px;
    padding: 8px 15px;
  }
}

@media (max-width: 768px) {
  .topbar {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 15px;
  }

  .actions {
    width: 100%;
    justify-content: space-between;
    flex-direction: row;
  }
}

@media (max-width: 576px) {
  .page-title {
    font-size: 18px;
  }

  .btn {
    padding: 8px 12px;
    font-size: 13px;
  }

  .actions {
    flex-direction: column;
  }
}
</style>
