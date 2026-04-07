export default defineNuxtPlugin((nuxtApp) => {
	nuxtApp.$router.options.scrollBehavior = (to) => {
		if (to.hash) {
			return {
				el: to.hash,
				behavior: "smooth",
				top: 80, // Optional offset from top
			};
		}
		return { top: 0 };
	};
});
