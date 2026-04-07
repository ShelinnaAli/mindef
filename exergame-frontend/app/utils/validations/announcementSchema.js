import * as yup from "yup";

export const announcementSchema = yup.object({
	title: yup.string().required("Title is required"),
	content: yup.string().required("Content is required"),
  typeId: yup.string().required("Type is required"),
});
