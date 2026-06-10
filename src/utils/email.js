import punycode from 'punycode'

/**
 * Convert the domain part of an email address to ASCII (punycode) if needed.
 * Returns the original email if conversion fails or input is malformed.
 *
 * @param {string} email
 * @return {string}
 */
export function convertEmailDomainToASCII(email) {
    if (typeof email !== 'string') {
        return email
    }
    const parts = email.split('@')
    if (parts.length !== 2) {
        return email
    }
    const [local, domain] = parts
    if (/[^\x00-\x7F]/.test(domain)) {
        try {
            const ascii = punycode.toASCII(domain)
            return `${local}@${ascii}`
        } catch (e) {
            return email
        }
    }
    return email
}

/**
 * Convert punycode domain back to Unicode for display purposes.
 * If the domain is not punycoded, returns the original email.
 *
 * @param {string} email
 * @return {string}
 */
export function convertEmailDomainToUnicode(email) {
    if (typeof email !== 'string') {
        return email
    }
    const parts = email.split('@')
    if (parts.length !== 2) {
        return email
    }
    const [local, domain] = parts
    if (/\bxn--/i.test(domain)) {
        try {
            const unicode = punycode.toUnicode(domain)
            return `${local}@${unicode}`
        } catch (e) {
            return email
        }
    }
    return email
}
