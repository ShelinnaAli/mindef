<template>
	<div class="modal">
		<div class="modal-content">
			<span class="close-modal" @click="$emit('close')">&times;</span>
			<div v-if="schedule" class="modal-programme-info">
				<h3 style="text-align: left">{{ schedule.programme?.name }}</h3>
				<h4>Synopsis</h4>
				<p>{{ schedule.programme?.synopsis }}</p>
				<div class="info-item">
					<fa-icon icon="fas fa-calendar-alt" />
					<span>Date: {{ schedule.date }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-clock" />
					<span>Time: {{ schedule.time }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-user" />
					<span>Trainer: {{ schedule.trainer?.name }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-running" />
					<span
						>Type:
						{{ capitalize(schedule.programme?.sessionType) }}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-bolt" />
					<span
						>Intensity:
						{{
							capitalize(schedule.programme?.intensityLevel)
						}}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-info-circle" />
					<span>Status: {{ capitalize(schedule.status) }}</span>
				</div>
				<div class="programme-image-container">
					<img
						class="programme-image"
						:src="schedule.programme?.coverImage"
						:alt="schedule.programme?.name"
            @error="handleCoverImageError(schedule.programme?.name, $event)"
					/>
				</div>
				<div class="modal-actions">
					<template v-if="showButtonActions">
						<button class="btn btn-primary" @click="editSession">
							<fa-icon icon="fas fa-edit" /> Edit Session
						</button>
						<button
							id="reschedule-session-btn"
							class="btn btn-outline"
							:disabled="isSessionInPast"
							:title="
								isSessionInPast
									? 'Cannot reschedule past sessions'
									: 'Reschedule this session'
							"
							@click="rescheduleSession"
						>
							<fa-icon icon="fas fa-clock" /> Reschedule Session
						</button>
						<button
							v-if="canCancel"
							id="cancel-session-btn"
							class="btn btn-outline btn-cancel"
							:disabled="isSessionInPast"
							:title="
								isSessionInPast
									? 'Cannot cancel past sessions'
									: 'Cancel this session'
							"
							@click="cancelSession"
						>
							<fa-icon icon="fas fa-times-circle" /> Cancel
							Session
						</button>
					</template>
				</div>
			</div>
			<div v-else>
				<p class="text-center">No session data available.</p>
			</div>
		</div>
	</div>
</template>

<script setup>
const props = defineProps({
	schedule: {
		type: Object,
		default: null,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["close", "edit", "reschedule", "cancel", "rebook"]);
const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role);

const handleCoverImageError = (title, event) => {
  event.onerror = null;
  event.target.src = generateCoverImage(title);
};

const editSession = () => {
	const session = props.schedule;
	emit("edit", session.day, props.schedule);
};

const rescheduleSession = () => {
	// Prevent rescheduling of past sessions
	if (isSessionInPast.value) {
		return;
	}

	emit("reschedule", props.schedule);
};

const cancelSession = () => {
	emit("cancel", props.schedule);
};

const canCancel = computed(() => {
	return !isSessionInPast.value && props.schedule.isCancelled === false;
});

const canModify = () => {
  return ['admin', 'superadmin'].includes(userRole.value) || props.schedule.trainerId === authStore.user?.id;
}

const showButtonActions = computed(() => {
	if (props.isReadonly || !canModify()) {
		return false;
	} else {
		return (
			!isSessionExpired(props.schedule) ||
			!["cancelled"].includes(props.schedule.status)
		);
	}
});

const isSessionInPast = computed(() => {
	return isSessionExpired(props.schedule);
});
</script>

<style lang="scss" scoped>
.modal {
	.modal-content {
		margin: auto;

		.modal-programme-info {
			.info-item {
				display: flex;
				align-items: flex-start;
				gap: 10px;
				font-size: 0.95rem;
				color: var(--dark);
				margin-top: 10px;

				.svg-inline--fa {
					color: var(--secondary);
					width: 20px;
					flex-shrink: 0;
					margin-top: 3px;
				}
			}
		}
	}

	.modal-actions {
		flex-direction: column;
		gap: 10px;

		button {
			margin: 0;

			&:disabled {
				opacity: 0.5;
				cursor: not-allowed;

				&:hover {
					opacity: 0.5;
				}
			}

			&.btn-cancel {
				background: rgba(231, 76, 60, 0.15) !important;
				color: #e74c3c !important;

				&:disabled {
					opacity: 0.5;
					cursor: not-allowed;
					background: rgba(231, 76, 60, 0.05) !important;
					color: #a94442 !important;

					&:hover {
						background: rgba(231, 76, 60, 0.05) !important;
						color: #a94442 !important;
					}
				}
			}
		}
	}
}
</style>
