<template>
	<div class="tabs">
		<div
			v-for="tab in tabs"
			:id="tab.id"
			:key="tab.id"
			class="tab"
			:class="{ active: activeTab === tab.id }"
			@click="handleTabClick(tab.id)"
		>
			{{ tab.label }}
		</div>
	</div>
</template>

<script setup>
import { useRoute, useRouter } from "vue-router";

const props = defineProps({
	tabItems: {
		type: Array,
		required: true,
		validator: (value) => {
			return value.every(
				(item) =>
					item.id &&
					typeof item.id === "string" &&
					item.label &&
					typeof item.label === "string",
			);
		},
	},
	defaultTab: {
		type: String,
		default: "",
	},
});

const emit = defineEmits(["tab-change"]);

const route = useRoute();
const router = useRouter();

const tabs = ref(props.tabItems);
const activeTab = ref(
	props.defaultTab || (props.tabItems.length > 0 ? props.tabItems[0].id : ""),
);

const setActiveTabFromHash = () => {
	const validTabs = tabs.value.map((t) => t.id);
	const hashTab = route.hash.substring(1); // Remove #

	if (validTabs.includes(hashTab)) {
		activeTab.value = hashTab;
		emit("tab-change", hashTab);
	} else if (validTabs.length > 0) {
		// Default to first tab if hash is invalid
		activeTab.value = validTabs[0];
		router.replace({ hash: `#${validTabs[0]}` });
		emit("tab-change", validTabs[0]);
	}
};

const handleTabClick = (tabId) => {
	if (activeTab.value !== tabId) {
		activeTab.value = tabId;
		router.push({ hash: `#${tabId}` });
		emit("tab-change", tabId);
	}
};

watch(() => route.hash, setActiveTabFromHash);

// Initialize on client-side mount only to avoid SSR/client mismatch
onMounted(() => {
	setActiveTabFromHash();
});
</script>

<style lang="scss" scoped>
@forward "@/assets/css/tab.scss";
</style>
