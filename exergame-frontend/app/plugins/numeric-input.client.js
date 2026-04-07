export default defineNuxtPlugin((nuxtApp) => {
  // Global function to allow only numeric input
  const allowOnlyNumeric = (event) => {
    const input = event.target;
    const value = input.value;

    // Remove any non-numeric characters except decimal point
    const numericValue = value.replace(/[^0-9.]/g, '');

    // Ensure only one decimal point
    const parts = numericValue.split('.');
    let finalValue = parts[0];
    if (parts.length > 1) {
      finalValue += '.' + parts.slice(1).join('');
    }

    // Update input value if it changed
    if (input.value !== finalValue) {
      input.value = finalValue;

      // Trigger input event to update v-model
      const inputEvent = new Event('input', { bubbles: true });
      input.dispatchEvent(inputEvent);
    }
  };

  // Global function to allow only integers (no decimal point)
  const allowOnlyInteger = (event) => {
    const input = event.target;
    const value = input.value;

    // Remove any non-numeric characters
    const integerValue = value.replace(/[^0-9]/g, '');

    // Update input value if it changed
    if (input.value !== integerValue) {
      input.value = integerValue;

      // Trigger input event to update v-model
      const inputEvent = new Event('input', { bubbles: true });
      input.dispatchEvent(inputEvent);
    }
  };

  // Global function to handle keydown events for numeric input
  const handleNumericKeydown = (event, allowDecimal = true) => {
    const key = event.key;
    const input = event.target;
    const value = input.value;

    // Allow control keys (backspace, delete, arrow keys, tab, etc.)
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

  // Global function to handle paste events for numeric input
  const handleNumericPaste = (event, allowDecimal = true) => {
    const input = event.target;
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');

    // Check if pasted text contains only valid numeric characters
    const validPattern = allowDecimal ? /^[0-9.]*$/ : /^[0-9]*$/;

    if (!validPattern.test(pastedText)) {
      event.preventDefault();

      // Extract only valid characters
      const validChars = allowDecimal ?
        pastedText.replace(/[^0-9.]/g, '') :
        pastedText.replace(/[^0-9]/g, '');

      // Handle decimal point if allowing decimals
      let cleanedText = validChars;
      if (allowDecimal && cleanedText.includes('.')) {
        const parts = cleanedText.split('.');
        cleanedText = parts[0] + '.' + parts.slice(1).join('');
      }

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

  // Provide global functions
  nuxtApp.provide('allowOnlyNumeric', allowOnlyNumeric);
  nuxtApp.provide('allowOnlyInteger', allowOnlyInteger);
  nuxtApp.provide('handleNumericKeydown', handleNumericKeydown);
  nuxtApp.provide('handleNumericPaste', handleNumericPaste);

  // Create directive for numeric input (allows decimals)
  nuxtApp.vueApp.directive('numeric', {
    mounted(el, binding) {
      const allowDecimal = binding.value !== false; // Default to true unless explicitly set to false

      // Handle input events
      el.addEventListener('input', allowOnlyNumeric);

      // Handle keydown events
      el.addEventListener('keydown', (event) => handleNumericKeydown(event, allowDecimal));

      // Handle paste events
      el.addEventListener('paste', (event) => handleNumericPaste(event, allowDecimal));
    },

    unmounted(el) {
      // Clean up event listeners
      el.removeEventListener('input', allowOnlyNumeric);
      el.removeEventListener('keydown', handleNumericKeydown);
      el.removeEventListener('paste', handleNumericPaste);
    }
  });

  // Create directive for integer input only (no decimals)
  nuxtApp.vueApp.directive('integer', {
    mounted(el) {
      // Handle input events
      el.addEventListener('input', allowOnlyInteger);

      // Handle keydown events
      el.addEventListener('keydown', (event) => handleNumericKeydown(event, false));

      // Handle paste events
      el.addEventListener('paste', (event) => handleNumericPaste(event, false));
    },

    unmounted(el) {
      // Clean up event listeners
      el.removeEventListener('input', allowOnlyInteger);
      el.removeEventListener('keydown', handleNumericKeydown);
      el.removeEventListener('paste', handleNumericPaste);
    }
  });

  // Global utility functions for manual validation
  const isNumeric = (value) => {
    return !isNaN(parseFloat(value)) && isFinite(value);
  };

  const sanitizeNumeric = (value, allowDecimal = true) => {
    if (typeof value !== 'string') {
      value = String(value);
    }

    if (allowDecimal) {
      const cleaned = value.replace(/[^0-9.]/g, '');
      const parts = cleaned.split('.');
      return parts[0] + (parts.length > 1 ? '.' + parts.slice(1).join('') : '');
    } else {
      return value.replace(/[^0-9]/g, '');
    }
  };

  const formatNumber = (value, decimals = 2) => {
    const num = parseFloat(value);
    return isNaN(num) ? '0' : num.toFixed(decimals);
  };

  nuxtApp.provide('isNumeric', isNumeric);
  nuxtApp.provide('sanitizeNumeric', sanitizeNumeric);
  nuxtApp.provide('formatNumber', formatNumber);
});
