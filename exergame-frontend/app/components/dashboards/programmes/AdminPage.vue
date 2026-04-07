<template>
	<div class="section" style="margin-bottom: 0; padding: 10px 25px">
		<div class="section-header" style="margin-bottom: 15px">
			<h2 class="section-title">Programme Management</h2>
			<PartialsTab
				:tab-items="tabs"
				:default-tab="'all-programmes'"
				@tab-change="handleTabChange"
			/>
		</div>

		<component
			:is="activeComponent.component"
			v-bind="activeComponent.props"
			v-on="activeComponent.events"
		/>

		<div v-if="showEditModal" class="modal" @click.self="closeEditModal">
			<div class="modal-content" style="max-width: 800px;">
				<span class="close-modal" @click="closeEditModal">&times;</span>
				<h3 class="modal-title">Edit Programme</h3>
				<hr class="form-section-divider" />

				<PartialsProgrammesForm
					:programme="selectedProgramme"
					:game-types="gameTypes"
          :available-trainers="userTrainers"
          :available-rooms="rooms"
					@programme-saved="handleProgrammeUpdated"
					@cancel="closeEditModal"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import PartialsProgrammesListTable from "@/components/partials/programmes/ListTable.vue";
import PartialsProgrammesForm from "@/components/partials/programmes/Form.vue";
import PartialsProgrammesSchedulesListCalendar from "@/components/partials/programmes/schedules/ListCalendar.vue";

const { gameTypes, fetchGameTypes } = useGameTypes();
const { programmes, error, isLoading, fetchProgrammes } = useProgrammes();
const { users, fetchUsers } = useUser();
const { rooms, fetchRooms } = useRooms();

const tabs = [
	{
		id: "all-programmes",
		label: "Active Programmes",
		component: PartialsProgrammesListTable,
	},
	{
		id: "create-new",
		label: "Create New",
		component: PartialsProgrammesForm,
	},
	{
		id: "scheduled",
		label: "Scheduled",
		component: PartialsProgrammesSchedulesListCalendar,
	},
];

const activeTab = ref("all-programmes");
const showEditModal = ref(false);
const selectedProgramme = ref(null);

const handleTabChange = (tabId) => {
	activeTab.value = tabId;
};

const handleEditProgramme = (programme) => {
	selectedProgramme.value = programme;
	showEditModal.value = true;
};

const closeEditModal = () => {
	showEditModal.value = false;
	selectedProgramme.value = null;
};

const handleProgrammeUpdated = async (isFromEditModal) => {
	// Refresh the list if we're on the active programmes tab
	handleRefreshProgrammes();
	if (isFromEditModal) {
		closeEditModal();
	}

	if (activeTab.value !== "all-programmes") {
		await getProgrammes();
		navigateTo("/dashboard/programmes#all-programmes");
	}
};

const handleRefreshProgrammes = async () => {
	// This will trigger a refresh of the programme list
	if (activeTab.value === "all-programmes") {
		await getProgrammes();
	}
};

const getProgrammes = () => {
	return new Promise((resolve) => {
		fetchProgrammes({ expand: "gameType", sorts: 'name:asc' }).then(() => resolve());
	});
};

const userTrainers = computed(() => {
	const list = Array.isArray(users.value) ? users.value : [];
	return list.filter((user) => user.role === "trainer");
});

const activeComponent = computed(() => {
	const tab = tabs.find((t) => t.id === activeTab.value);
	if (!tab) return { component: null, props: {}, events: {} };

	const config = {
		component: tab.component,
		props: {},
		events: {},
	};

	switch (activeTab.value) {
		case "all-programmes":
			config.props = {
				gameTypes: gameTypes.value,
				programmes: programmes.value,
        error: error.value,
        isLoading: isLoading.value,
			};
			config.events = {
				"edit-programme": handleEditProgramme,
				"refresh-programmes": handleRefreshProgrammes,
        "retry": getProgrammes
			};
			break;
		case "create-new":
			config.props = {
				gameTypes: gameTypes.value,
				availableTrainers: userTrainers.value,
				availableRooms: rooms.value,
			};
			config.events = {
				"programme-saved": handleProgrammeUpdated,
			};
			break;
		case "scheduled":
			config.props = {
				availableProgrammes: programmes.value.filter((p) => p.isActive),
			};
			config.events = {};
			break;
	}

	return config;
});

onMounted(() => {
	fetchGameTypes();
	getProgrammes();
	fetchUsers({ role: "trainer", sorts: "name:asc,username:asc" });
	fetchRooms();
});
</script>
