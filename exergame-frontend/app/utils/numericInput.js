/**
 * Numeric input utilities for validating and sanitizing numeric input
 */

/**
 * Check if a value is numeric
 * @param {any} value - The value to check
 * @returns {boolean} - True if the value is numeric
 */
export const isNumeric = (value) => {
  return !isNaN(parseFloat(value)) && isFinite(value);
};

/**
 * Sanitize a string to contain only numeric characters
 * @param {string|number} value - The value to sanitize
 * @param {boolean} allowDecimal - Whether to allow decimal points
 * @returns {string} - The sanitized numeric string
 */
export const sanitizeNumeric = (value, allowDecimal = true) => {
  if (typeof value !== 'string') {
    value = String(value);
  }

  if (allowDecimal) {
    // Remove non-numeric characters except decimal point
    const cleaned = value.replace(/[^0-9.]/g, '');
    // Ensure only one decimal point
    const parts = cleaned.split('.');
    return parts[0] + (parts.length > 1 ? '.' + parts.slice(1).join('') : '');
  } else {
    // Remove all non-numeric characters
    return value.replace(/[^0-9]/g, '');
  }
};

/**
 * Format a number with specified decimal places
 * @param {string|number} value - The value to format
 * @param {number} decimals - Number of decimal places (default: 2)
 * @returns {string} - The formatted number string
 */
export const formatNumber = (value, decimals = 2) => {
  const num = parseFloat(value);
  return isNaN(num) ? '0' : num.toFixed(decimals);
};

/**
 * Parse a string to a number, returning 0 if invalid
 * @param {string} value - The string value to parse
 * @returns {number} - The parsed number or 0 if invalid
 */
export const parseNumeric = (value) => {
  const num = parseFloat(value);
  return isNaN(num) ? 0 : num;
};

/**
 * Validate if a value is within a specified range
 * @param {string|number} value - The value to validate
 * @param {number} min - Minimum allowed value
 * @param {number} max - Maximum allowed value
 * @returns {boolean} - True if the value is within range
 */
export const isInRange = (value, min = Number.MIN_SAFE_INTEGER, max = Number.MAX_SAFE_INTEGER) => {
  const num = parseFloat(value);
  return !isNaN(num) && num >= min && num <= max;
};

/**
 * Clamp a value within a specified range
 * @param {string|number} value - The value to clamp
 * @param {number} min - Minimum allowed value
 * @param {number} max - Maximum allowed value
 * @returns {number} - The clamped value
 */
export const clampValue = (value, min = Number.MIN_SAFE_INTEGER, max = Number.MAX_SAFE_INTEGER) => {
  const num = parseFloat(value);
  if (isNaN(num)) return min;
  return Math.min(Math.max(num, min), max);
};

/**
 * Event handler to allow only numeric input
 * @param {Event} event - The input event
 * @param {boolean} allowDecimal - Whether to allow decimal points
 */
export const handleNumericInput = (event, allowDecimal = true) => {
  const input = event.target;
  const value = input.value;
  const sanitized = sanitizeNumeric(value, allowDecimal);

  if (input.value !== sanitized) {
    input.value = sanitized;

    // Trigger input event to update v-model
    const inputEvent = new Event('input', { bubbles: true });
    input.dispatchEvent(inputEvent);
  }
};

/**
 * Event handler for keydown events on numeric inputs
 * @param {KeyboardEvent} event - The keydown event
 * @param {boolean} allowDecimal - Whether to allow decimal points
 */
export const handleNumericKeydown = (event, allowDecimal = true) => {
  const key = event.key;
  const input = event.target;
  const value = input.value;

  // Allow control keys
  if ([
    'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
    'Home', 'End'
  ].includes(key)) {
    return;
  }

  // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
  if (event.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(key.toLowerCase())) {
    return;
  }

  // Allow numbers
  if (/^[0-9]$/.test(key)) {
    return;
  }

  // Allow decimal point if enabled and not already present
  if (allowDecimal && key === '.' && !value.includes('.')) {
    return;
  }

  // Prevent all other keys
  event.preventDefault();
};

/**
 * Event handler for paste events on numeric inputs
 * @param {ClipboardEvent} event - The paste event
 * @param {boolean} allowDecimal - Whether to allow decimal points
 */
export const handleNumericPaste = (event, allowDecimal = true) => {
  const input = event.target;
  const pastedText = (event.clipboardData || window.clipboardData).getData('text');

  // Check if pasted text contains only valid numeric characters
  const validPattern = allowDecimal ? /^[0-9.]*$/ : /^[0-9]*$/;

  if (!validPattern.test(pastedText)) {
    event.preventDefault();

    // Extract and sanitize valid characters
    const cleanedText = sanitizeNumeric(pastedText, allowDecimal);

    const currentValue = input.value;
    const selectionStart = input.selectionStart;
    const selectionEnd = input.selectionEnd;

    // Insert cleaned text at cursor position
    const newValue = currentValue.substring(0, selectionStart) +
                    cleanedText +
                    currentValue.substring(selectionEnd);

    input.value = newValue;

    // Set cursor position after inserted text
    const newCursorPosition = selectionStart + cleanedText.length;
    input.setSelectionRange(newCursorPosition, newCursorPosition);

    // Trigger input event to update v-model
    const inputEvent = new Event('input', { bubbles: true });
    input.dispatchEvent(inputEvent);
  }
};
