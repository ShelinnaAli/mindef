import * as yup from "yup";

export const registerSchema = yup.object({
	name: yup.string().required("Full name is required"),
	username: yup
		.string()
		.required("Login ID is required")
		.min(4, "Login ID must be at least 4 characters")
		.matches(
			/^[a-zA-Z0-9]+$/,
			"Login ID must contain only letters and numbers",
		),
	password: yup
		.string()
		.required("Password is required")
		.min(8, "Password must be at least 8 characters"),
	phone: yup.string().required("Phone number is required"),
	// .matches(/^\d{8,14}$/, 'Please enter a valid 8-14 digit phone number'),
	birthYear: yup
		.number()
		.required("Birth year is required")
		.max(
			new Date().getFullYear(),
			`Birth year must be before ${new Date().getFullYear() + 1}`,
		),
	schemeId: yup.number().required("Scheme is required"),
	gender: yup
		.string()
		.required("Gender is required")
		.oneOf(["male", "female", "other"]),
	/* emergencyContactName: yup.string().required('Emergency contact name is required'),
		emergencyContactNumber: yup.string()
				.required('Emergency contact number is required')
				.matches(/^\d{8,14}$/, 'Please enter a valid 8-14 digit phone number')
				.not([yup.ref('phone')], 'Emergency contact number must be different from the phone number'),
		emergencyRelationship: yup.string().required('Relationship is required'), */
	emergencyIsAggreedConsent: yup
		.mixed()
		.transform((value) => {
			// Transform "on" or true to boolean true, everything else to false
			return value === "on" || value === true || value === "true";
		})
		.test(
			"is-true",
			"You must give consent to proceed",
			(value) => value === true,
		),
});

export const loginSchema = yup.object({
	username: yup.string().required("Login ID is required"),
	password: yup.string().required("Password is required"),
});
