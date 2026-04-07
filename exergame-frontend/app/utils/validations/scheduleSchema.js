import * as yup from "yup";

export const scheduleSchema = (userRole, isEditMode) =>
	yup.object({
		programmeId: yup.string().required("Programme is required"),

		trainerId: yup.string().when([], {
			is: () => ['admin', 'superadmin'].includes(userRole),
			then: (schema) => schema.required("Trainer is required"),
			otherwise: (schema) => schema.notRequired(),
		}),

		roomId: yup.string().required("Room is required"),

		startTime: yup
			.string()
			.when([], {
				is: () => !isEditMode,
				then: (schema) => schema.required("Start time is required"),
				otherwise: (schema) => schema.notRequired(),
			})
			.matches(
				/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/,
				"Please enter a valid time format (HH:MM)",
			),

		endTime: yup
			.string()
			.when([], {
				is: () => !isEditMode,
				then: (schema) => schema.required("End time is required"),
				otherwise: (schema) => schema.notRequired(),
			})
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
	});
