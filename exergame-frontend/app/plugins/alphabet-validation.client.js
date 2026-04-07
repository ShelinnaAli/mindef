export default defineNuxtPlugin((nuxtApp) => {
  // Regular expression for alphabetic characters and spaces
  const alphabetSpaceRegex = /^[a-zA-ZÀ-ÿ\s]*$/;

  /**
   * Filters out non-alphabetic characters and non-spaces from input
   * @param {string} value - The value to filter
   * @returns {string} Filtered value containing only alphabetic characters and spaces
   */
  const filterAlphabetSpace = (value) => {
    if (typeof value !== 'string') return '';
    return value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
  };

  /**
   * Global function to restrict input to alphabetic characters and spaces
   * Also prevents leading spaces
   */
  const restrictToAlphabetSpace = (event) => {
    const input = event.target;
    const currentValue = input.value;
    const filteredValue = filterAlphabetSpace(currentValue);

    // Prevent leading spaces
    const finalValue = filteredValue.startsWith(' ') ? filteredValue.trimStart() : filteredValue;

    if (currentValue !== finalValue) {
      input.value = finalValue;
      // Trigger input event to update v-model
      const inputEvent = new Event('input', { bubbles: true });
      input.dispatchEvent(inputEvent);
    }
  };

  /**
   * Global function to handle keydown events for alphabet validation
   * Prevents invalid characters from being typed
   */
  const restrictAlphabetKeydown = (event) => {
    const key = event.key;
    const target = event.target;
    const currentValue = target.value;

    // Allow control keys (backspace, delete, arrow keys, etc.)
    if (key.length > 1) {
      // Prevent space as first character
      if (key === ' ' && currentValue === '') {
        event.preventDefault();
        return;
      }
      return;
    }

    // Check if the key is alphabetic or space
    if (!alphabetSpaceRegex.test(key)) {
      event.preventDefault();
      return;
    }

    // Prevent space as first character
    if (key === ' ' && currentValue === '') {
      event.preventDefault();
    }
  };

  /**
   * Global function to handle paste events for alphabet validation
   * Filters pasted content to only allow alphabetic characters and spaces
   */
  const restrictAlphabetPaste = (event) => {
    event.preventDefault();

    const paste = (event.clipboardData || window.clipboardData).getData('text');
    const filteredPaste = filterAlphabetSpace(paste);
    const target = event.target;

    // Get current cursor position
    const start = target.selectionStart;
    const end = target.selectionEnd;
    const currentValue = target.value;

    // Replace selected text with filtered paste
    const newValue = currentValue.substring(0, start) + filteredPaste + currentValue.substring(end);

    // Prevent leading spaces
    const finalValue = newValue.startsWith(' ') ? newValue.trimStart() : newValue;

    target.value = finalValue;

    // Set cursor position after pasted content
    const newCursorPos = start + filteredPaste.length;
    target.setSelectionRange(newCursorPos, newCursorPos);

    // Trigger input event to update v-model
    const inputEvent = new Event('input', { bubbles: true });
    target.dispatchEvent(inputEvent);
  };

  /**
   * Validates if the input contains only alphabetic characters and spaces
   * @param {string} value - The value to validate
   * @returns {boolean} True if valid, false otherwise
   */
  const isValidAlphabetSpace = (value) => {
    if (typeof value !== 'string') return false;
    return alphabetSpaceRegex.test(value);
  };

  /**
   * Creates a directive for applying alphabet validation to input elements
   * Usage: v-alphabet-only
   */
  const alphabetOnlyDirective = {
    mounted(el) {
      // Find the input element (could be the element itself or a child)
      const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
      if (!input) return;

      input.addEventListener('input', restrictToAlphabetSpace);
      input.addEventListener('keydown', restrictAlphabetKeydown);
      input.addEventListener('paste', restrictAlphabetPaste);
    },
    unmounted(el) {
      const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
      if (!input) return;

      input.removeEventListener('input', restrictToAlphabetSpace);
      input.removeEventListener('keydown', restrictAlphabetKeydown);
      input.removeEventListener('paste', restrictAlphabetPaste);
    }
  };

  // Register the directive globally
  nuxtApp.vueApp.directive('alphabet-only', alphabetOnlyDirective);

  // Provide global functions
  nuxtApp.provide('restrictToAlphabetSpace', restrictToAlphabetSpace);
  nuxtApp.provide('restrictAlphabetKeydown', restrictAlphabetKeydown);
  nuxtApp.provide('restrictAlphabetPaste', restrictAlphabetPaste);
  nuxtApp.provide('isValidAlphabetSpace', isValidAlphabetSpace);
  nuxtApp.provide('filterAlphabetSpace', filterAlphabetSpace);
  nuxtApp.provide('alphabetSpaceRegex', alphabetSpaceRegex);
});
