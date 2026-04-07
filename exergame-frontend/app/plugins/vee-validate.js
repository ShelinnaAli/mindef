import { defineNuxtPlugin } from "#app";
import { configure, defineRule } from "vee-validate";
import * as rules from "@vee-validate/rules";

export default defineNuxtPlugin(() => {
	// Register all rules
	Object.keys(rules).forEach((rule) => {
		if (typeof rules[rule] === "function") {
			defineRule(rule, rules[rule]);
		}
	});

	configure({
		validateOnInput: true,
		validateOnChange: true,
	});
});
