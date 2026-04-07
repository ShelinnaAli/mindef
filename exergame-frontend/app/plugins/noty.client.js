import Noty from "noty";
import "noty/lib/noty.css";
import "noty/lib/themes/mint.css";
import "noty/lib/themes/light.css";
import "noty/lib/themes/metroui.css";
import "noty/lib/themes/nest.css";
import "noty/lib/themes/relax.css";
import "noty/lib/themes/semanticui.css";
import "noty/lib/themes/sunset.css";

export default defineNuxtPlugin(() => {
	const popupConfig = {
		type: "alert",
		layout: "topCenter",
		theme: "mint",
		modal: true,
		progressBar: false,
		closeWith: ["button"],
		animation: {
			open: "animate__animated animate__slideInDown animate__faster",
			close: "animate__animated animate__slideOutUp animate__faster",
		},
	};

	const toastConfig = {
		layout: "topRight",
		theme: "mint",
		timeout: 5000,
		progressBar: true,
	};

	const formatText = (title, message = "") => {
		if (title && message) {
			return `<h4 class="noty_title">${title}</h4><div class="noty_content">${message}</div>`;
		} else if (title && message === "") {
			return `<div class="noty_content">${title}</div>`;
		} else if (title === "" && message) {
			return `<div class="noty_content">${message}</div>`;
		}
		return "";
	};

	const notify = {
		alert: (message = "", title = "", OkText = "Okay", options = {}) => {
			return new Promise((resolve) => {
				const n = new Noty({
					...popupConfig,
					...options,
					text: formatText(title, message),
					buttons: [
						Noty.button(OkText, "btn-primary", () => {
							if (options?.preventCloseOnOk === true) {
								// do nothing
							} else {
								n.close();
							}
							resolve({ noty: n, isConfirmed: true });
						}),
					],
				}).show();
			});
		},
		confirm: (
			message = "",
			title = "",
			OkText = "Okay",
			CancelText = "Cancel",
			options = {},
		) => {
			return new Promise((resolve) => {
				const n = new Noty({
					...popupConfig,
					...options,
					text: formatText(title, message),
					buttons: [
						Noty.button(OkText, "btn-primary", () => {
							if (options?.preventCloseOnOk === true) {
								// do nothing
							} else {
								n.close();
							}
							resolve({ noty: n, isConfirmed: true });
						}),
						Noty.button(CancelText, "btn-outline", () => {
							n.close();
							resolve({ noty: n, isConfirmed: false });
						}),
					],
				}).show();
			});
		},
		success: (text, options = {}) => {
			return new Noty({
				...toastConfig,
				...options,
				text: formatText(text),
				type: "success",
			}).show();
		},
		error: (text, options = {}) => {
			return new Noty({
				...toastConfig,
				...options,
				type: "error",
				text: formatText(text),
			}).show();
		},
		warning: (text, options = {}) => {
			return new Noty({
				...toastConfig,
				...options,
				text: formatText(text),
				type: "warning",
			}).show();
		},
		info: (text, options = {}) => {
			return new Noty({
				...toastConfig,
				...options,
				text: formatText(text),
				type: "info",
			}).show();
		},
	};

	return {
		provide: {
			notify,
		},
	};
});
