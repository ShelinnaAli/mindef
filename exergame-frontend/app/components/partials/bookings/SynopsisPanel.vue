<template>
	<div class="programme-info-panel">
		<h3 class="text-left-important">
			{{ programme?.name || "Select a Programme Slot" }}
		</h3>
		<template v-if="!programme">
			<p class="text-muted">
				Click on an available session in the calendar to see its details
				here.
			</p>
		</template>
		<template v-else>
			<h4>Synopsis</h4>
			<p>
				{{
					programme?.synopsis ||
					"Click on an available session in the calendar to see its details here."
				}}
			</p>

			<div class="program-info-item">
				<fa-icon icon="fas fa-dumbbell" />
				<span
					>SB1 Workout: {{ programme.durationMinutes }} mins
					session</span
				>
			</div>

			<div class="program-info-item">
				<fa-icon icon="fas fa-bolt" />
				<span
					>Intensity Level:
					{{ capitalize(programme.intensityLevel) }}</span
				>
			</div>

			<div>
				<img
					:src="programme.coverImage"
					:alt="programme.name"
					class="programme-image"
          @error="handleCoverImageError(programme.name, $event)"
				/>
			</div>
		</template>
	</div>
</template>
<script setup>
defineProps({
	programme: {
		type: Object,
		default: null,
	},
});
const handleCoverImageError = (title, event) => {
  event.onerror = null;
  event.target.src = generateCoverImage(title);
};
</script>
