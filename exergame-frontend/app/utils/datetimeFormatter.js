const formatTimeToHHMM = (timeString) => {
	if (!timeString) return "";
	return timeString.substring(0, 5); // Take only first 5 characters (HH:MM)
};

const formatDate = (dateString) => {
	if (!dateString) return "";
	const date = new Date(dateString);
	return date.toLocaleDateString("en-US", {
		weekday: "short",
		year: "numeric",
		month: "long",
		day: "numeric",
	});
};

const formatTimeString = (timeString) => {
	if (!timeString) return "";
	const [hours, minutes] = timeString.split(":");
	const hour = parseInt(hours);
	const ampm = hour >= 12 ? "PM" : "AM";
	const displayHour = hour % 12 || 12;
	return `${displayHour}:${minutes} ${ampm}`;
};

const formatTime = (startTime, endTime) => {
	return `${formatTimeString(startTime)} - ${formatTimeString(endTime)}`;
};

const formatDateTime = (dateTimeString) => {
	if (!dateTimeString) return null;

	const date = new Date(dateTimeString);
	return date.toLocaleDateString("en-US", {
		weekday: "short",
		year: "numeric",
		month: "short",
		day: "numeric",
		hour: "numeric",
		minute: "2-digit",
		hour12: true,
	});
};

const formatHumanDateTime = (dateString) => {
	// Fix for ISO string with 6-digit milliseconds
	let fixedDateString = "";
	if (typeof dateString === "string") {
		dateString = dateString.split("T");
		fixedDateString = dateString[0] + " " + dateString[1].slice(0, 8);
	}

	const date = new Date(fixedDateString);
	const now = new Date();
	const diffInSeconds = Math.floor((now - date) / 1000);
	if (diffInSeconds < 60) return "just now";
	if (diffInSeconds < 3600)
		return Math.floor(diffInSeconds / 60) + " minutes ago";
	if (diffInSeconds < 86400)
		return Math.floor(diffInSeconds / 3600) + " hours ago";
	if (diffInSeconds < 604800)
		return Math.floor(diffInSeconds / 86400) + " days ago"; // less than 7 days
	if (diffInSeconds < 2592000)
		return Math.floor(diffInSeconds / 604800) + " weeks ago"; // less than 30 days
	if (diffInSeconds < 31536000)
		return Math.floor(diffInSeconds / 2592000) + " months ago"; // less than 1 year
	return Math.floor(diffInSeconds / 31536000) + " years ago";
};

export {
	formatTimeToHHMM,
	formatDate,
	formatTime,
	formatDateTime,
	formatTimeString,
	formatHumanDateTime,
};
