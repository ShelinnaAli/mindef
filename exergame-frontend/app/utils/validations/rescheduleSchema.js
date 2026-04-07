import * as yup from "yup";

export const rescheduleSchema = yup.object({
	newDate: yup
		.date()
		.required("New date is required")
		.min(new Date(), "New date must be today or in the future")
		.typeError("Please enter a valid date"),

	startTime: yup
		.string()
		.required("Start time is required")
		.matches(
			/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/,
			"Please enter a valid time format (HH:MM)",
		),

	endTime: yup
		.string()
		.required("End time is required")
		.matches(
			/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/,
			"Please enter a valid time format (HH:MM)",
		)
		.test(
			"is-after-start",
			"End time must be after start time",
			function (value) {
				const { startTime } = this.parent;
				if (!startTime || !value) return true;

				// Convert time strings to minutes for comparison
				const startMinutes = startTime
					.split(":")
					.reduce((acc, time) => 60 * acc + +time);
				const endMinutes = value
					.split(":")
					.reduce((acc, time) => 60 * acc + +time);

				return endMinutes > startMinutes;
			},
		),

	reason: yup.string().max(500, "Reason cannot exceed 500 characters"),
});
