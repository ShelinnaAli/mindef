
import * as yup from "yup";

export const webSettingSchema = yup.object({
	appName: yup.string().required("Gym name is required"),
	advertisementMsg: yup.string().max(500, "Advertisement message must be at most 500 characters"),
  loginBackgroundMediaUrl: yup.string().url("Please enter a valid URL"),
});
