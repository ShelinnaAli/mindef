<template>
	<div class="app-container">
		<div v-if="ADVERTISEMENT_MSG" class="ads-bar">
			{{ ADVERTISEMENT_MSG }}
		</div>
		<video v-if="LOGIN_BACKGROUND_MEDIA_URL" class="video-bg" autoplay muted loop>
			<source :src="LOGIN_BACKGROUND_MEDIA_URL" />
			Your browser does not support HTML5 video.
		</video>

		<slot />
	</div>
</template>

<script setup>
import { useAppSettingsStore } from '~/stores/useAppSettingsStore';

const appSettingsStore = useAppSettingsStore();
const { ADVERTISEMENT_MSG, LOGIN_BACKGROUND_MEDIA_URL } = storeToRefs(appSettingsStore);

</script>

<style scoped>
.ads-bar {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	background-color: rgba(0, 0, 0, 0.7);
	color: var(--light);
	text-align: center;
	padding: 0.75rem;
	z-index: 10;
	font-weight: bold;
	animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
	from {
		transform: translateY(-100%);
		opacity: 0;
	}
	to {
		transform: translateY(0);
		opacity: 1;
	}
}

.video-bg {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	z-index: -1;
}
</style>
