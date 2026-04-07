// Utility functions for converting between camelCase and snake_case

/**
 * Convert camelCase string to snake_case
 * @param {string} str - The camelCase string
 * @returns {string} - The snake_case string
 */
export const camelToSnake = (str) => {
	return str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
};

/**
 * Convert snake_case string to camelCase
 * @param {string} str - The snake_case string
 * @returns {string} - The camelCase string
 */
export const snakeToCamel = (str) => {
	return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
};

/**
 * Convert object keys from camelCase to snake_case
 * @param {Object} obj - The object with camelCase keys
 * @returns {Object} - The object with snake_case keys
 */
export const convertKeysToSnakeCase = (obj) => {
	if (obj === null || obj === undefined || typeof obj !== "object") {
		return obj;
	}

	if (Array.isArray(obj)) {
		return obj.map(convertKeysToSnakeCase);
	}

	const converted = {};
	for (const [key, value] of Object.entries(obj)) {
		const snakeKey = camelToSnake(key);
		converted[snakeKey] =
			typeof value === "object" ? convertKeysToSnakeCase(value) : value;
	}
	return converted;
};

/**
 * Convert object keys from snake_case to camelCase
 * @param {Object} obj - The object with snake_case keys
 * @returns {Object} - The object with camelCase keys
 */
export const convertKeysToCamelCase = (obj) => {
	if (obj === null || obj === undefined || typeof obj !== "object") {
		return obj;
	}

	if (Array.isArray(obj)) {
		return obj.map(convertKeysToCamelCase);
	}

	const converted = {};
	for (const [key, value] of Object.entries(obj)) {
		const camelKey = snakeToCamel(key);
		converted[camelKey] =
			typeof value === "object" ? convertKeysToCamelCase(value) : value;
	}
	return converted;
};
