export default class ValidationManager {
    /**
     * Converts any BD phone format into a solid 11-digit string (01XXXXXXXXX).
     * Returns the clean string if valid, or null if it's completely incorrect.
     */
    cleanPhone(phone) {
        if (!phone) return null;

        // 1. Remove all non-numeric characters (+, -, spaces, etc.)
        let cleaned = phone.replace(/\D/g, "");

        // 2. Handle '880' prefix: convert 88017... to 017...
        if (cleaned.startsWith("880")) {
            cleaned = cleaned.substring(2);
        }

        // 3. Handle missing '0': convert 171... to 017...
        else if (cleaned.length === 10 && cleaned.startsWith("1")) {
            cleaned = "0" + cleaned;
        }

        // 4. Final check: Must be exactly 11 digits and start with 01
        const isValid = /^01[3-9]\d{8}$/.test(cleaned);

        return isValid ? cleaned : null;
    }

    /**
     * Silent email check: returns the email if it's a major provider,
     * otherwise returns null (so you can handle the rejection).
     */
    cleanEmail(email) {
        if (!email) return null;

        const allowedDomains = [
            "gmail.com",
            "yahoo.com",
            "hotmail.com",
            "outlook.com",
        ];
        const emailLower = email.toLowerCase().trim();
        const domain = emailLower.split("@")[1];

        return allowedDomains.includes(domain) ? emailLower : null;
    }

    /**
     * Handles mixed input. If it has '@', it's an email.
     * Otherwise, treats it as a phone number and formats it.
     */
    cleanLoginInput(input) {
        if (!input) return null;
        const val = input.trim();

        // 1. If it contains '@', treat as email (just lowercase it)
        if (val.includes("@")) {
            return val.toLowerCase();
        }

        // 2. Otherwise, treat as phone and use our solid 11-digit formatter
        return this.cleanPhone(val);
    }
}
