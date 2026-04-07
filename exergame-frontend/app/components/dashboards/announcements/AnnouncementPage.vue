<template>
	<div>
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">Announcements & News</h2>
			</div>

			<div class="notification-list">
				<PartialsDataLoading v-if="loading" />
				<PartialsDataError v-else-if="error" :error="error" @retry="fetchAnnouncements" />
				<PartialsDataNotFound v-else-if="announcements.length === 0" />
				<div
					v-for="announcement in announcements"
					v-else
					:key="announcement.id"
					class="notification"
					:class="announcement?.type?.name || ''"
				>
					<div class="notification-icon">
						<fa-icon
							:icon="`fas ${announcement?.type?.icon || 'fa-info-circle'}`"
						/>
					</div>
					<div class="notification-content">
						<h3>{{ announcement.title }}</h3>
						<p>{{ announcement.content }}</p>
						<div class="notification-time">
							{{ announcement.createdAt }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
const { announcements, loading, error, fetchAnnouncements } = useAnnouncement();

onMounted(() => {
	fetchAnnouncements();
});
</script>

<style lang="scss" scoped>
.notification-list {
	display: flex;
	flex-direction: column;
	gap: 15px;
}

.notification {
	background: var(--card-bg);
	border-radius: 10px;
	padding: 20px;
	box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
	border-left: 4px solid var(--accent);
	display: flex;
	align-items: flex-start;
	border: 1px solid var(--border);

	&.alert {
		border-left-color: var(--warning);

		.notification-icon {
			color: var(--warning);
		}
	}

	&.info {
		border-left-color: var(--secondary);

		.notification-icon {
			color: var(--secondary);
		}
	}
}

.notification-icon {
	font-size: 24px;
	margin-right: 15px;
	color: var(--accent);
}

.notification-content {
	h3 {
		margin-bottom: 5px;
		color: var(--dark);
	}

	p {
		color: var(--gray);
		margin-bottom: 5px;
	}
}

.notification-time {
	color: var(--gray);
	font-size: 13px;
}
</style>
