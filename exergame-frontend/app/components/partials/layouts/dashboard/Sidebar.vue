<template>
	<div
		id="sidebar"
		class="sidebar"
		:class="{ active: isOpen }"
		@click="handleInsideClick"
	>
    <div class="brand">
      <div class="brand-icon">
        <fa-icon icon="fas fa-dumbbell" />
      </div>
      <div class="brand-text">{{ APP_NAME }}</div>
    </div>

    <div class="user-info">
      <div class="user-avatar">
        <fa-icon icon="fas fa-user" />
      </div>
      <div class="user-details">
        <h3>{{ user?.name || "User" }}</h3>
        <span class="user-role">{{ user?.role || "Loading..." }}</span>
      </div>
    </div>

    <div class="nav-menu">
			<div
				v-for="item in navItems"
				:key="item.page"
				class="nav-item"
				:class="{ active: activePage === item.page }"
				@click="navigateTo(item.page)"
			>
        <fa-icon :icon="item.icon" class="nav-icon" />
        <span>{{ item.label }}</span>
      </div>
      <div class="nav-item" @click="logout">
        <fa-icon icon="fas fa-sign-out-alt" class="nav-icon" />
        <span>Logout</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAppSettingsStore } from '~/stores/useAppSettingsStore';

const { $notify } = useNuxtApp();
const authStore = useAuthStore();
const { clearIntervalNotif } = useNotifications();
const appSettingsStore = useAppSettingsStore();
const { APP_NAME } = storeToRefs(appSettingsStore);
const activePage = ref("index");
const isOpen = ref(false);

const user = computed(() => authStore.user);
const userRole = computed(() => authStore.user?.role);

// Use computed property to ensure navItems updates when userRole changes
const navItems = computed(() => {
  const baseItems = [
    { page: "/dashboard", icon: "fas fa-home", label: "Dashboard" },
    {
      page: "/dashboard/programmes",
      icon: "fas fa-calendar-alt",
      label: "Programmes",
    },
  ];

  const role = userRole.value;

  if (role === "user") {
    return [
      ...baseItems,
      {
        page: "/dashboard/announcements",
        icon: "fas fa-bell",
        label: "Announcements & News",
      },
      {
        page: "/dashboard/reports",
        icon: "fas fa-chart-bar",
        label: "Reports",
      },
      {
        page: "/dashboard/settings",
        icon: "fas fa-cog",
        label: "Profile & Settings",
      },
    ];
  } else if (['admin', 'superadmin'].includes(role)) {
    return [
      ...baseItems,
      {
        page: "/dashboard/announcements",
        icon: "fas fa-bell",
        label: "Notifications",
      },
      {
        page: "/dashboard/users",
        icon: "fas fa-users",
        label: "User Management",
      },
      {
        page: "/dashboard/bookings",
        icon: "fas fa-clipboard-list",
        label: "Bookings",
      },
      {
        page: "/dashboard/reports",
        icon: "fas fa-chart-bar",
        label: "Reports",
      },
      {
        page: "/dashboard/settings",
        icon: "fas fa-cog",
        label: "Settings",
      },
    ];
  } else if (role === "trainer") {
    return [
      ...baseItems,
      {
        page: "/dashboard/announcements",
        icon: "fas fa-bell",
        label: "Announcements & News",
      },
      {
        page: "/dashboard/bookings",
        icon: "fas fa-clipboard-list",
        label: "Bookings",
      },
      {
        page: "/dashboard/reports",
        icon: "fas fa-chart-bar",
        label: "Reports",
      },
      {
        page: "/dashboard/settings",
        icon: "fas fa-cog",
        label: "Settings",
      },
    ];
  }

  // Return base items if role is not set yet (during SSR or before auth loads)
  return baseItems;
});

const logout = () => {
  $notify
    .confirm("Are you sure you want to logout?", "", "Ok", "Cancel", {
      preventCloseOnOk: true,
    })
    .then(async ({ noty, isConfirmed }) => {
      if (isConfirmed) {
        clearIntervalNotif();
        await authStore.logout();
        noty.close();
        navigateTo("/");
      }
    });
};

const handleInsideClick = (event) => {
  event.stopPropagation();
};

// Expose methods to parent if needed
defineExpose({
  toggle: () => (isOpen.value = !isOpen.value),
  isOpen,
});
defineEmits(["close"]);
</script>

<style scoped>
/* Copy all the sidebar styles from the original CSS */
.sidebar {
  background: var(--secondary);
  color: var(--light);
  padding: 25px 0;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.3s ease-in-out;
  z-index: 1000;
}

.brand {
  display: flex;
  align-items: center;
  padding: 0 20px 3px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-icon {
  background: var(--light);
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: var(--secondary);
  margin-right: 15px;
}

.brand-text {
  font-family: "Montserrat", sans-serif;
  font-weight: 700;
  font-size: 22px;
  color: var(--light);
  letter-spacing: 0.5px;
}

.user-info {
  padding: 25px 20px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--light);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin-right: 15px;
  color: var(--secondary);
  border: 2px solid var(--light);
}

.user-details h3 {
  font-weight: 600;
  margin-bottom: 5px;
  color: var(--light);
}

.user-role {
  background: var(--light);
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  display: inline-block;
  color: var(--secondary);
  font-weight: 600;
}

.nav-menu {
  padding: 20px 0;
}

.nav-item {
  padding: 12px 25px;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s;
  color: rgba(255, 255, 255, 0.7);
}

.nav-item:hover,
.nav-item.active {
  background: rgba(255, 255, 255, 0.1);
  border-left: 4px solid var(--light);
  color: var(--light);
}

.nav-item .nav-icon {
  margin-right: 15px;
  font-size: 20px;
  width: 24px;
  text-align: center;
}

@media (max-width: 992px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 260px;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
  }

  .sidebar.active {
    transform: translateX(0);
  }
}
</style>
