import * as yup from "yup";

export const programmeSchema = yup.object({
	name: yup
		.string()
		.required("Programme name is required")
		.min(3, "Programme name must be at least 3 characters")
		.max(255, "Programme name must not exceed 255 characters"),

	typeId: yup.string().required("Game type is required"),

	synopsis: yup
		.string()
		.nullable()
		.max(1000, "Synopsis must not exceed 1000 characters"),

	coverImage: yup.string().nullable().url("Please enter a valid URL"),

	intensityLevel: yup
		.string()
		.required("Intensity level is required")
		.oneOf(
			["low", "medium", "high", "extreme"],
			"Please select a valid intensity level",
		),

	sessionType: yup
		.string()
		.required("Session type is required")
		.oneOf(["single", "group"], "Please select a valid session type"),

	maxParticipants: yup
		.number()
		.required("Maximum participants is required")
		.min(1, "Maximum participants must be at least 1")
		.integer("Maximum participants must be a whole number"),

	durationMinutes: yup
		.number()
		.required("Duration is required")
		.min(5, "Duration must be at least 5 minutes")
		.integer("Duration must be a whole number"),

	isActive: yup.boolean(),

  // programme schedule creation
  trainerId: yup.string().notRequired(),

  roomId: yup.string(),

  day: yup.string().notRequired(),

  startTime: yup.string().notRequired()
    .test(
      "valid-time-format",
      "Please enter a valid time format (HH:MM)",
      function (value) {
        if (!value) return true;
        return /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value);
      }
    ),

  endTime: yup.string().notRequired()
    .test(
      "valid-time-format",
      "Please enter a valid time format (HH:MM)",
      function (value) {
        if (!value) return true;
        return /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value);
      }
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
      }
    )
    .test(
      "matches-duration",
      "End time should match start time + duration",
      function (value) {
        const { startTime, durationMinutes } = this.parent;
        if (!startTime || !value || !durationMinutes) return true;

        // Calculate expected end time
        const [hours, mins] = startTime.split(':').map(Number);
        const totalMinutes = hours * 60 + mins + durationMinutes;
        const expectedHours = Math.floor(totalMinutes / 60) % 24;
        const expectedMins = totalMinutes % 60;
        const expectedEndTime = `${String(expectedHours).padStart(2, '0')}:${String(expectedMins).padStart(2, '0')}`;

        return value === expectedEndTime;
      }
    ),
});
