/**
 * Composable for preventing leading spaces in text inputs
 * Provides utility functions and reactive helpers
 */
export const usePreventLeadingSpaces = () => {
  const { $preventLeadingSpaces, $preventLeadingSpacesOnPaste, $trimLeadingSpaces } = useNuxtApp();

  /**
   * Creates input event handlers for preventing leading spaces
   * @returns {Object} Event handlers for input and paste events
   */
  const createInputHandlers = () => ({
    onInput: $preventLeadingSpaces,
    onPaste: $preventLeadingSpacesOnPaste,
    onKeydown: (event) => {
      // Prevent space as first character
      if (event.target.value === '' && event.key === ' ') {
        event.preventDefault();
      }
    }
  });

  /**
   * Reactive function to sanitize v-model values
   * @param {Ref} modelRef - Reactive reference to the model value
   */
  const sanitizeModel = (modelRef) => {
    watch(modelRef, (newValue) => {
      if (typeof newValue === 'string' && newValue.startsWith(' ')) {
        modelRef.value = newValue.trimStart();
      }
    }, { immediate: true });
  };

  /**
   * Helper function to add no-leading-spaces behavior to any input
   * @param {HTMLElement} inputElement - The input element to enhance
   */
  const enhanceInput = (inputElement) => {
    if (!inputElement) return;

    inputElement.addEventListener('input', $preventLeadingSpaces);
    inputElement.addEventListener('paste', $preventLeadingSpacesOnPaste);
    inputElement.addEventListener('keydown', (event) => {
      if (event.target.value === '' && event.key === ' ') {
        event.preventDefault();
      }
    });
  };

  /**
   * Cleanup function to remove event listeners
   * @param {HTMLElement} inputElement - The input element to clean up
   */
  const cleanupInput = (inputElement) => {
    if (!inputElement) return;

    inputElement.removeEventListener('input', $preventLeadingSpaces);
    inputElement.removeEventListener('paste', $preventLeadingSpacesOnPaste);
  };

  return {
    // Direct access to plugin functions
    preventLeadingSpaces: $preventLeadingSpaces,
    preventLeadingSpacesOnPaste: $preventLeadingSpacesOnPaste,
    trimLeadingSpaces: $trimLeadingSpaces,

    // Utility functions
    createInputHandlers,
    sanitizeModel,
    enhanceInput,
    cleanupInput
  };
};
