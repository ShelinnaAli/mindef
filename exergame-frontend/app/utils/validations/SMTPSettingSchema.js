import * as yup from "yup";

export const smtpSettingSchema = yup.object({
	smtpHost: yup.string().required("SMTP Host is required"),
	smtpPort: yup
		.number()
		.typeError("Port must be a number")
		.required("SMTP Port is required")
		.min(1, "Port must be greater than 0"),
	smtpUsername: yup.string().required("SMTP Username is required"),
	smtpPassword: yup.string().required("SMTP Password is required"),
	smtpEmailSender: yup
		.string()
		.email("Sender Email must be a valid email")
		.required("Sender Email is required"),
});
