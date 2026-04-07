/**
 * Composable for validating text inputs to allow only alphabetic characters and spaces
 * Provides utility functions and reactive helpers for text field validation
 */
export const useAlphabetValidation = () => {
  // const { $preventLeadingSpaces, $preventLeadingSpacesOnPaste, $trimLeadingSpaces } = useNuxtApp();

  /**
   * Regular expression for alphabetic characters and spaces
   * Allows letters (a-z, A-Z), spaces, and common accented characters
   */
  const alphabetSpaceRegex = /^[a-zA-ZÀ-ÿ\s]*$/;

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
   * Filters out non-alphabetic characters and non-spaces from input
   * @param {string} value - The value to filter
   * @returns {string} Filtered value containing only alphabetic characters and spaces
   */
  const filterAlphabetSpace = (value) => {
    if (typeof value !== 'string') return '';
    return value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
  };

  /**
   * Handles input events to restrict input to alphabetic characters and spaces
   * Prevents leading spaces and filters invalid characters
   * @param {Event} event - The input event
   */
  const handleAlphabetInput = (event) => {
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
   * Handles keydown events to prevent invalid characters from being typed
   * @param {KeyboardEvent} event - The keydown event
   */
  const handleAlphabetKeydown = (event) => {
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
   * Handles paste events to filter pasted content
   * @param {ClipboardEvent} event - The paste event
   */
  const handleAlphabetPaste = (event) => {
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
   * Creates a complete set of event handlers for alphabet and space validation
   * @returns {Object} Event handlers for input, keydown, and paste events
   */
  const createAlphabetInputHandlers = () => ({
    onInput: handleAlphabetInput,
    onKeydown: handleAlphabetKeydown,
    onPaste: handleAlphabetPaste
  });

  /**
   * Reactive function to sanitize v-model values for alphabet and space validation
   * @param {Ref} modelRef - Reactive reference to the model value
   */
  const sanitizeAlphabetModel = (modelRef) => {
    watch(modelRef, (newValue) => {
      if (typeof newValue === 'string') {
        const filtered = filterAlphabetSpace(newValue);
        const sanitized = filtered.startsWith(' ') ? filtered.trimStart() : filtered;

        if (newValue !== sanitized) {
          modelRef.value = sanitized;
        }
      }
    }, { immediate: true });
  };

  /**
   * Validates a model value and returns validation state
   * @param {Ref} modelRef - Reactive reference to the model value
   * @returns {Object} Validation state with isValid and errorMessage
   */
  const validateAlphabetModel = (modelRef) => {
    const isValid = computed(() => {
      if (!modelRef.value) return true; // Empty is valid
      return isValidAlphabetSpace(modelRef.value);
    });

    const errorMessage = computed(() => {
      if (isValid.value) return '';
      return 'Only alphabetic characters and spaces are allowed';
    });

    return {
      isValid,
      errorMessage
    };
  };

  /**
   * Directive-like helper for applying alphabet validation to an input element
   * @param {HTMLInputElement} element - The input element
   */
  const applyAlphabetValidation = (element) => {
    if (!element || element.tagName !== 'INPUT') return;

    const handlers = createAlphabetInputHandlers();
    element.addEventListener('input', handlers.onInput);
    element.addEventListener('keydown', handlers.onKeydown);
    element.addEventListener('paste', handlers.onPaste);

    // Return cleanup function
    return () => {
      element.removeEventListener('input', handlers.onInput);
      element.removeEventListener('keydown', handlers.onKeydown);
      element.removeEventListener('paste', handlers.onPaste);
    };
  };

  return {
    // Validation functions
    isValidAlphabetSpace,
    filterAlphabetSpace,

    // Event handlers
    handleAlphabetInput,
    handleAlphabetKeydown,
    handleAlphabetPaste,
    createAlphabetInputHandlers,

    // Reactive helpers
    sanitizeAlphabetModel,
    validateAlphabetModel,

    // Utility
    applyAlphabetValidation,

    // Constants
    alphabetSpaceRegex
  };
};
