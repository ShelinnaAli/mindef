import * as yup from "yup";

export const profileSchema = yup.object({
	name: yup
		.string()
		.required("Full name is required"),
	phone: yup
		.string()
		.required("Phone number is required")
		.matches(/^\d{7,15}$/, "Please enter a valid 7-15 digit phone number"),
	currentPassword: yup.string().when(["newPassword", "confirmPassword"], {
		is: (newPassword, confirmPassword) => newPassword || confirmPassword,
		then: (schema) =>
			schema.required(
				"Current password is required when changing password",
			),
		otherwise: (schema) => schema.nullable(),
	}),
	newPassword: yup
		.string()
		.nullable()
		.when([], {
			is: (val) => val && val.length > 0,
			then: (schema) =>
				schema
					.min(8, "New password must be at least 8 characters")
					.matches(
						/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
						"Password must contain at least one uppercase letter, one lowercase letter, and one number",
					),
			otherwise: (schema) => schema,
		}),
	confirmPassword: yup
		.string()
		.nullable()
		.test("passwords-match", "Passwords must match", function (value) {
			return this.parent.newPassword
				? value === this.parent.newPassword
				: true;
		}),
});

export const passwordSchema = yup.object({
	currentPassword: yup.string().required("Current password is required"),
	newPassword: yup
		.string()
		.required("New password is required")
		.min(8, "New password must be at least 8 characters")
		.matches(
			/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
			"Password must contain at least one uppercase letter, one lowercase letter, and one number",
		),
	confirmPassword: yup
		.string()
		.required("Please confirm your new password")
		.test("passwords-match", "Passwords must match", function (value) {
			return value === this.parent.newPassword;
		}),
});

export const updateUserSchema = yup.object({
	name: yup
		.string()
		.required("Name is required"),
	phone: yup
		.string()
		.required("Phone number is required")
		.matches(/^\d{7,15}$/, "Phone number must be 7-15 digits"),
	role: yup
		.string()
		.required("Role is required")
		.oneOf(["user", "trainer", "admin"], "Invalid role selected"),
	birthYear: yup
		.number()
		.nullable()
		.min(1901, "Birth year must be after 1900")
		.max(
			new Date().getFullYear(),
			`Birth year must be before ${new Date().getFullYear() + 1}`,
		),
	gender: yup
		.string()
		.required("Gender is required")
		.oneOf(["male", "female", "other"]),
	// schemeId: yup.string().required("Scheme is required"),
	isActive: yup.boolean(),
	// Emergency contact fields
	/* emergencyContactName: yup.string().required('Emergency contact name is required'),
	emergencyContactNumber: yup.string()
			.required('Emergency contact number is required')
			.matches(/^\\d{8,14}$/, 'Please enter a valid 8-14 digit phone number')
			.not([yup.ref('phone')], 'Emergency contact number must be different from the phone number'),
	emergencyRelationship: yup.string().required('Relationship is required'),
	emergencyIsAggreedConsent: yup.boolean().oneOf([true], 'You must give consent to proceed') */
});

// Add user schema for creation
export const addUserSchema = yup.object({
	name: yup
		.string()
		.required("Name is required"),
	phone: yup
		.string()
		.required("Phone number is required")
		.matches(/^\d{7,15}$/, "Phone number must be 7-15 digits"),
	username: yup
		.string()
		.required("Login ID is required")
		.min(4, "Login ID must be at least 4 characters"),
	birthYear: yup
		.number()
		.nullable()
		.min(1901, "Birth year must be after 1900")
		.max(
			new Date().getFullYear(),
			`Birth year must be before ${new Date().getFullYear() + 1}`,
		),
	gender: yup
		.string()
		.required("Gender is required")
		.oneOf(["male", "female", "other"]),
	role: yup
		.string()
		.required("Role is required")
		.oneOf(["user", "trainer", "admin"], "Invalid role selected"),
	// schemeId: yup.string().required("Scheme is required"),
	// Emergency contact fields
	/* emergencyContactName: yup.string().required('Emergency contact name is required'),
	emergencyContactNumber: yup.string()
			.required('Emergency contact number is required')
			.matches(/^[0-9]{8,14}$/, 'Please enter a valid 8-14 digit phone number')
			.not([yup.ref('phone')], 'Emergency contact number must be different from the phone number'),
	emergencyRelationship: yup.string().required('Relationship is required'), */
});
