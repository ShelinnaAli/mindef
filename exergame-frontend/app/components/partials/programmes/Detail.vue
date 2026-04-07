<template>
	<div class="modal">
		<div class="modal-content">
			<span class="close-modal" @click="$emit('close')">&times;</span>
			<div v-if="programme" class="modal-programme-info">
				<h3 style="text-align: left">{{ programme?.name }}</h3>
				<h4>Synopsis</h4>
				<p>{{ programme?.synopsis }}</p>
				<div class="info-item">
					<fa-icon icon="fas fa-calendar-alt" />
					<span
						>Date: {{ formatDate(programme?.schedule?.day) }}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-clock" />
					<span
						>Time:
						{{
							formatTime(
								programme?.schedule?.startTime,
								programme?.schedule?.endTime,
							)
						}}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-user" />
					<span
						>Trainer: {{ programme?.schedule?.trainer?.name }}</span
					>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-running" />
					<span>Type: {{ capitalize(programme?.sessionType) }}</span>
				</div>
				<div class="info-item">
					<fa-icon icon="fas fa-bolt" />
					<span
						>Intensity:
						{{ capitalize(programme?.intensityLevel) }}</span
					>
				</div>
				<div class="programme-image-container">
					<img
						class="programme-image"
						:src="programme?.coverImage"
						:alt="programme?.name"
            @error="handleCoverImageError(schedule.programme?.name, $event)"
					/>
				</div>
			</div>
			<div v-else>
				<p class="text-center">No session data available.</p>
			</div>
		</div>
	</div>
</template>

<script setup>
defineProps({
	programme: {
		type: Object,
		default: null,
	},
	isReadonly: {
		type: Boolean,
		default: false,
	},
});
defineEmits(["close"]);
const handleCoverImageError = (title, event) => {
  event.onerror = null;
  event.target.src = generateCoverImage(title);
};
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
