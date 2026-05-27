function base64UrlEncode(bytes) {
    const binary = String.fromCharCode(...bytes);

    return btoa(binary)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

export function generateVerifier() {
    const bytes = new Uint8Array(32);
    crypto.getRandomValues(bytes);

    return base64UrlEncode(bytes);
}

export async function generateChallenge(verifier) {
    const data = new TextEncoder().encode(verifier);
    const hash = await crypto.subtle.digest('SHA-256', data);

    return base64UrlEncode(new Uint8Array(hash));
}

export function generateState() {
    return generateVerifier();
}

window.pkce = {
    generateVerifier,
    generateChallenge,
    generateState,
};
