<template>
	<div class="section">
		<div class="section-header">
			<h2 class="section-title">Reports & Analytics</h2>
			<tab
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

		<component :is="activeComponent" :filters="filters" />
	</div>
</template>

<script setup>
import Tab from "@/components/partials/Tab.vue";
import ProgrammeFrequency from "../../partials/reports/ProgrammeFrequency.vue";
import TakeUpRate from "../../partials/reports/TakeUpRate.vue";
import CancellationFrequency from "../../partials/reports/CancellationFrequency.vue";
import UserList from "../../partials/reports/UserList.vue";
import UserParticipationHistory from "../../partials/reports/UserParticipationHistory.vue";
import UserAgeDistribution from "../../partials/reports/UserAgeDistribution.vue";

const activeTab = ref("programme-frequency");
const filters = ref({
	fromDate: "",
	toDate: "",
	fromTime: "",
	toTime: "",
});

const tabs = [
	{
		id: "programme-frequency",
		label: "Programme Run Frequency",
		component: ProgrammeFrequency,
	},
	{
		id: "take-up-rate",
		label: "Take Up Rate of Programmes",
		component: TakeUpRate,
	},
	{
		id: "cancellation-frequency",
		label: "Programme Cancellation Frequency",
		component: CancellationFrequency,
	},
	{ id: "user-list", label: "User List", component: UserList },
	{
		id: "user-participation-history",
		label: "User Participation History",
		component: UserParticipationHistory,
	},
	{
		id: "user-age-distribution",
		label: "User Age Distribution",
		component: UserAgeDistribution,
	},
];

const activeComponent = computed(() => {
	const tab = tabs.find((t) => t.id === activeTab.value);
	return tab ? tab.component : null;
});

const handleTabChange = (tabId) => {
	activeTab.value = tabId;
};
const generateReport = () => {
	// This would typically call an API with the filters
	console.log("Generating report with filters:", filters.value);
};
</script>
