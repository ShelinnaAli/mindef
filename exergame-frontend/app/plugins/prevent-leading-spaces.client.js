export default defineNuxtPlugin((nuxtApp) => {
  // Global function to prevent leading spaces
  const preventLeadingSpaces = (event) => {
    const input = event.target;
    const value = input.value;

    // Check if the first character is a space
    if (value.length > 0 && value[0] === ' ') {
      // Remove leading spaces
      input.value = value.trimStart();

      // Trigger input event to update v-model
      const inputEvent = new Event('input', { bubbles: true });
      input.dispatchEvent(inputEvent);
    }
  };

  // Global function to handle paste events and prevent leading spaces
  const preventLeadingSpacesOnPaste = (event) => {
    const input = event.target;

    // Get pasted text
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');

    // If pasted text starts with space, prevent default and insert trimmed text
    if (pastedText.startsWith(' ')) {
      event.preventDefault();

      const trimmedText = pastedText.trimStart();
      const currentValue = input.value;
      const selectionStart = input.selectionStart;
      const selectionEnd = input.selectionEnd;

      // Insert trimmed text at cursor position
      const newValue = currentValue.substring(0, selectionStart) +
                      trimmedText +
                      currentValue.substring(selectionEnd);

      input.value = newValue;

      // Set cursor position after inserted text
      const newCursorPosition = selectionStart + trimmedText.length;
      input.setSelectionRange(newCursorPosition, newCursorPosition);

      // Trigger input event to update v-model
      const inputEvent = new Event('input', { bubbles: true });
      input.dispatchEvent(inputEvent);
    }
  };

  // Provide global functions
  nuxtApp.provide('preventLeadingSpaces', preventLeadingSpaces);
  nuxtApp.provide('preventLeadingSpacesOnPaste', preventLeadingSpacesOnPaste);

  // Create a directive for easy use
  nuxtApp.vueApp.directive('no-leading-spaces', {
    mounted(el) {
      // Handle input events
      el.addEventListener('input', preventLeadingSpaces);

      // Handle paste events
      el.addEventListener('paste', preventLeadingSpacesOnPaste);

      // Handle keydown for immediate feedback
      el.addEventListener('keydown', (event) => {
        // If the input is empty and space is pressed, prevent it
        if (event.target.value === '' && event.key === ' ') {
          event.preventDefault();
        }
      });
    },

    unmounted(el) {
      // Clean up event listeners
      el.removeEventListener('input', preventLeadingSpaces);
      el.removeEventListener('paste', preventLeadingSpacesOnPaste);
      el.removeEventListener('keydown', (event) => {
        if (event.target.value === '' && event.key === ' ') {
          event.preventDefault();
        }
      });
    }
  });

  // Global utility function for manual use
  const trimLeadingSpaces = (value) => {
    return typeof value === 'string' ? value.trimStart() : value;
  };

  nuxtApp.provide('trimLeadingSpaces', trimLeadingSpaces);
});
