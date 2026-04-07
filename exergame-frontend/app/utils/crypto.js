import CryptoJS from "crypto-js";

export const encrypt = (data) => {
	const config = useRuntimeConfig();
	const key = config.public.encryptionKey;

	// Use CryptoJS with default OpenSSL-compatible format (matches Laravel backend)
	return CryptoJS.AES.encrypt(JSON.stringify(data), key).toString();
};

export const decrypt = (encryptedData) => {
	if (encryptedData == null) {
		return null;
	}

	if (typeof encryptedData !== "string") {
		return encryptedData;
	}

	const config = useRuntimeConfig();
	const key = config.public.encryptionKey;

	try {
		// Decrypt using CryptoJS (compatible with Laravel's OpenSSL encryption)
		const bytes = CryptoJS.AES.decrypt(encryptedData, key);
		const decryptedString = bytes.toString(CryptoJS.enc.Utf8);

		return decryptedString ? JSON.parse(decryptedString) : null;
	} catch {
		return encryptedData;
	}
};
