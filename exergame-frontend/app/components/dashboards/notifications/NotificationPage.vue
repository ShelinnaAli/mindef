<template>
	<div>
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">Notifications</h2>
			</div>

			<div class="notification-list">
				<PartialsDataLoading v-if="isLoading" />
        <PartialsDataError
          v-else-if="error"
          :error="error"
          @retry="getNotifications"
        />
        <PartialsDataNotFound v-else-if="notifications.length === 0" />
				<div
					v-for="notification in notifications"
					v-else
					:key="notification.id"
					class="notification"
					:class="notification.isRead ? '' : 'unread'"
					@click="!notification.isRead && markAsRead(notification.id)"
				>
					<div class="notification-icon">
						<fa-icon
							:icon="`fas ${notification?.type?.icon || 'fa-info-circle'}`"
						/>
					</div>
					<div class="notification-content">
						<h3>{{ notification.title }}</h3>
						<p v-html="sanitize(notification.message)" />
						<div class="notification-time">
							{{ notification.createdAt }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import DOMPurify from "dompurify";

const { notifications, isLoading, error, fetchNotifications, markAsRead } =
	useNotifications();

function sanitize(html) {
	return DOMPurify.sanitize(html);
}

const getNotifications = async () => {
	await fetchNotifications();
};

const intervalNotif = setInterval(() => {
	getNotifications();
}, 60000);

onMounted(() => {
	getNotifications();
	intervalNotif;
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

	&.unread {
		border-left-width: 3px;
		border-left-style: solid;
		border-left-color: var(--accent);
	}

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
