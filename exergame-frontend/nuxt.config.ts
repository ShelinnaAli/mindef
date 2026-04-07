// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
	compatibilityDate: "2025-07-15",
	devtools: { enabled: true },
	ssr: false,
	modules: ["@nuxt/eslint", "@nuxt/test-utils", "@pinia/nuxt"],

	css: ["~/assets/css/main.scss", "animate.css"],

	app: {
		head: {
			link: [
				{
					rel: "stylesheet",
					href: "https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap",
				},
				{ rel: "icon", type: "image/x-icon", href: "/favicon.png" },
			],
			title: process.env.NUXT_APP_NAME,
			htmlAttrs: {
				lang: "en",
			},
		},
	},

	runtimeConfig: {
		apiInternalBaseUrl:
			process.env.NUXT_API_INTERNAL_BASE_URL ||
			process.env.NUXT_API_BASE_URL ||
			"http://localhost:8080",
		public: {
			appName: process.env.NUXT_APP_NAME || "Exergame",
			encryptionKey:
				process.env.NUXT_ENCRYPTION_KEY || process.env.APP_KEY || "",
			apiBaseUrl:
				process.env.NUXT_PUBLIC_API_BASE_URL ||
				process.env.NUXT_API_BASE_URL ||
				"http://localhost:8080",
			serverUrl: process.env.NUXT_SERVER_URL || "",
		},
	},
});
