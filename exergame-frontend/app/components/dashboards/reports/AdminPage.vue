<template>
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">Reports & Analytics</h2>
      <PartialsTab
        :tab-items="tabs"
        :default-tab="'programme-frequency'"
        @tab-change="handleTabChange"
      />
    </div>

    <div class="report-filters">
      <div class="form-group">
        <label for="filterFromDate">From Date:</label>
        <input
          id="filterFromDate"
          v-model="filters.fromDate"
          type="date"
					class="form-control"
				/>
      </div>
      <div class="form-group">
        <label for="filterToDate">To Date:</label>
        <input
          id="filterToDate"
          v-model="filters.toDate"
          type="date"
          :max="getDefaultDateRange().toDate"
          class="form-control"
        />
      </div>
      <div class="form-group">
        <label for="filterFromTime">From Time (Optional):</label>
        <input
          id="filterFromTime"
          v-model="filters.fromTime"
          type="time"
          class="form-control"
        />
      </div>
      <div class="form-group">
        <label for="filterToTime">To Time (Optional):</label>
        <input
          id="filterToTime"
          v-model="filters.toTime"
          type="time"
          class="form-control"
        />
      </div>
			<button class="btn btn-primary" @click="generateReport">
        <fa-icon icon="fas fa-chart-line" />
        <span>Generate Report</span>
      </button>
    </div>

    <component
      :is="activeComponent"
      ref="reportComponentRef"
      :filters="filters"
    />
  </div>
</template>

<script setup>
import PartialsTab from "~/components/partials/Tab.vue";
import PartialsReportsTakeUpRate from "~/components/partials/reports/TakeUpRate.vue";
import PartialsReportsCancellationFrequency from "~/components/partials/reports/CancellationFrequency.vue";
import PartialsReportsUserList from "~/components/partials/reports/UserList.vue";
import PartialsReportsUserParticipationHistory from "~/components/partials/reports/UserParticipationHistory.vue";
import PartialsReportsUserPersonal from "~/components/partials/reports/UserPersonal.vue";

const activeTab = ref("programme-frequency");
const getDefaultDateRange = () => {
  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth();
  const firstDay = new Date(year, month, 1);

  const pad = (n) => n.toString().padStart(2, '0');
  const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

  return {
    fromDate: formatDate(firstDay),
    toDate: formatDate(now),
    fromTime: "00:00",
    toTime: "23:59",
  };
};

const filters = ref(getDefaultDateRange());

const reportComponentRef = ref(null);

const tabs = [
  {
    id: "take-up-rate",
    label: "Consolidated Programme",
    component: PartialsReportsTakeUpRate,
  },
  {
    id: "cancellation-frequency",
    label: "Programme Cancellation Frequency",
    component: PartialsReportsCancellationFrequency,
  },
  {
    id: "user-list",
    label: "User List",
    component: PartialsReportsUserList,
  },
  {
    id: "user-participation-history",
    label: "User Participation History",
    component: PartialsReportsUserParticipationHistory,
  },
  {
    id: "user-personal",
    label: "User Personal",
    component: PartialsReportsUserPersonal,
  },
];

const activeComponent = computed(() => {
  const tab = tabs.find((t) => t.id === activeTab.value);
  return tab ? tab.component : null;
});

const handleTabChange = async (tabId) => {
  activeTab.value = tabId;
  await nextTick();
  generateReport();
};

const generateReport = async () => {
  const options = {
    startDate: filters.value.fromDate + ' ' + filters.value.fromTime,
    endDate: filters.value.toDate + ' ' + filters.value.toTime,
  };
  await nextTick();
  if (reportComponentRef.value && typeof reportComponentRef.value.generateReport === 'function') {
    reportComponentRef.value.generateReport(options);
  } else {
    console.error('generateReport is not a function on', reportComponentRef.value);
  }
};

onMounted(() => generateReport())
</script>
