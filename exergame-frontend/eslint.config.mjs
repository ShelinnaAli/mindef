// @ts-check
import withNuxt from "./.nuxt/eslint.config.mjs";

export default withNuxt({
    rules: {
        "vue/html-self-closing": [
            "warn",
            {
                html: {
                    void: "always", // allow self-closing for void elements
                    normal: "never",
                    component: "always"
                },
                svg: "always",
                math: "always"
            }
        ],
        "vue/max-attributes-per-line": [
            "warn",
            {
                "singleline": {
                    "max": 3
                },
                "multiline": {
                    "max": 1
                }
            }
        ],
        "vue/no-unused-vars": "warn"
    }
});
// Your custom configs here
