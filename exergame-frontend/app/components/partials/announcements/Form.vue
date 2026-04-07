<template>
		<div class="section">
			<div class="section-header">
				<h2 class="section-title">Post A New Announcements Or News</h2>
			</div>
      <Form
        ref="formRef"
        v-slot="{ errors }"
        :validation-schema="announcementSchema"
        :initial-values="initialValues"
        style="display: block"
        @submit="handleAnnouncementSubmit"
      >
        <div class="form-group">
          <label for="announcementTitle">Title</label>
          <Field v-slot="{ field }" name="title">
            <input
              id="announcementTitle"
              v-no-leading-spaces
              v-bind="field"
              type="text"
              class="form-control"
              :class="{ error: errors.title }"
              placeholder="Enter announcement title"
            />
          </Field>
          <ErrorMessage name="title" class="field-error" />
        </div>
        <div class="form-group">
          <label for="announcementType">Type</label>
          <Field v-slot="{ field }" name="typeId">
            <select
              id="announcementType"
              v-bind="field"
              class="form-control"
              :class="{ error: errors.typeId }"
            >
              <option value="">Select Announcement Type</option>
              <option v-for="atype in announcementTypes" :key="atype.id" :value="atype.id">
                {{ atype.name }}
              </option>
            </select>
          </Field>
          <ErrorMessage name="typeId" class="field-error" />
        </div>
        <div class="form-group">
          <label for="announcementContent">Content</label>
          <Field v-slot="{ field }" name="content">
            <textarea
              id="announcementContent"
              v-bind="field"
              class="form-control"
              rows="4"
              :class="{ error: errors.content }"
              placeholder="Enter announcement content"
            />
          </Field>
          <ErrorMessage name="content" class="field-error" />
        </div>
        <div>
          <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
            <fa-icon :icon="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'" />
            {{
              isSubmitting
              ? 'Processing...'
              : isEditMode ? 'Update Announcement' : 'Post Announcement' }}
          </button>
        </div>
      </Form>
    </div>
</template>

<script setup>
import { Form, Field, ErrorMessage } from "vee-validate";
import { announcementSchema } from "~/utils/validations/announcementSchema";
const props = defineProps({
  edit: {
    type: Object,
    default: () => ({})
  }
})
const emit = defineEmits(["success-submit"]);
const formRef = ref();
const { $notify } = useNuxtApp();

const {
  announcementTypes,
  fetchAnnouncementTypes,
  createAnnouncement,
  updateAnnouncement,
} = useAnnouncement();


const initialValues = ref({
	id: "",
	title: "",
	content: "",
	typeId: ""
});

const isSubmitting = ref(false);
const isEditMode = computed(() => !!props.edit);

watch(
  () => props.edit,
  (edit) => {
    const newValues = edit && Object.keys(edit).length > 0
      ? {
          id: edit.id || "",
          title: edit.title || "",
          content: edit.content || "",
          typeId: edit.typeId || ""
        }
      : {
          id: "",
          title: "",
          content: "",
          typeId: ""
        };
    initialValues.value = newValues;
    formRef.value?.resetForm({ values: newValues });

    if (typeof window !== "undefined" && typeof document !== "undefined") {
      document.getElementById("announcementTitle")?.focus();
    }
  },
  { immediate: true, deep: true }
);

const handleAnnouncementSubmit = async (values, { resetForm }) => {
	if (isSubmitting.value) return;

  isSubmitting.value = true;
	try {
		const { message } = values.id
      ? await updateAnnouncement(values.id, values)
      : await createAnnouncement(values);

    $notify.success(message || "Announcement stored successfully");
    resetForm();
    emit("success-submit");
	} catch (error) {
    $notify.error(error?.message || "Failed to store announcement");
	} finally {
    isSubmitting.value = false;
  }
};

onMounted(async () => {
  await fetchAnnouncementTypes();
});
</script>
