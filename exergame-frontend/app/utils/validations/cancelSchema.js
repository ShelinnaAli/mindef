import * as yup from "yup";

export const cancelSchema = yup.object({
	cancellationReason: yup
		.string()
		.required("Cancellation reason is required")
		.min(10, "Please provide a detailed reason (at least 10 characters)")
		.max(500, "Reason cannot exceed 500 characters")
		.trim(),
});
