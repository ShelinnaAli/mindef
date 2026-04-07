export const isSessionExpired = (schedule) => {
	if (!schedule?.day) return true;

	const sessionDate = new Date(schedule.day);
	const today = new Date();

	// Reset time to compare only dates
	sessionDate.setHours(0, 0, 0, 0);
	today.setHours(0, 0, 0, 0);

	// If schedule has a start time, compare with current time on the same day
	if (schedule.startTime) {
		const sessionDateTime = new Date(
			schedule.day + "T" + schedule.startTime,
		);
		const now = new Date();

		return sessionDateTime < now;
	}

	// If no time available, just compare dates
	return sessionDate < today;
};

export const isSessionCancelled = (schedule) => {
	return schedule?.isCancelled === true;
};

export const isSessionFull = (sessionType, totalBookings, maxParticipants) => {
	if (sessionType === "group") {
		return totalBookings >= maxParticipants;
	}

	return false;
};

export const capitalize = (text) => {
	if (!text) return "";
	return text.charAt(0).toUpperCase() + text.slice(1);
};

export const lowercase = (text) => text.toLowerCase();

export const generateCoverImage = (title) => {
	if (!title) return "";

	const colors = [
		"#ff9f1c",
		"#1cbbff",
		"#4caf50",
		"#f44336",
		"#9c27b0",
		"#ff9800",
	];

	const getRandomColor = () => {
		return colors[Math.floor(Math.random() * colors.length)].replace("#", "");
	};

	return `https://placehold.co/600x400/${getRandomColor()}/ffffff?text=${encodeURIComponent(
		title
	)}`;
};
