<template>
  <div class="app-container">
    <PartialsLayoutsDashboardSidebar ref="sidebarRef" close="closeSidebar" />
    <div class="main-content" @click="closeSidebar">
      <PartialsLayoutsDashboardTopBar @toggle-sidebar="toggleSidebar" />
      <PartialsLayoutsDashboardContent>
        <slot />
      </PartialsLayoutsDashboardContent>
    </div>
  </div>
</template>

<script setup>
const sidebarRef = ref(null);
const isMobile = ref(false);

const toggleSidebar = () => {
  sidebarRef.value?.toggle();
};
const closeSidebar = () => {
  if (isMobile.value && sidebarRef.value?.isOpen) {
    sidebarRef.value.toggle();
  }
};

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 992; // Match mobile screen breakpoint
};

onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>
<style lang="scss">
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background: #000000;
  line-height: 1.6;
  min-height: 100vh;
  padding: 20px !important;
}
</style>
<style lang="scss" scoped>
.app-container {
  max-width: 1400px;
  margin: 0 auto;
  background: var(--primary);
  border-radius: 15px;
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: grid;
  grid-template-columns: 260px 1fr;
  height: 95vh;
  border: 1px solid var(--border);
}

.main-content {
  display: flex;
  flex-direction: column;
  background: var(--primary);
  overflow: hidden;
}

@media (max-width: 992px) {
  .app-container {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  body {
    padding: 10px !important;
  }

  .app-container {
    border-radius: 10px;
    min-height: 98vh;
    max-width: 100%;
  }
}
</style>
