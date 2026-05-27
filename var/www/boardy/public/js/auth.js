import { generateChallenge, generateState, generateVerifier } from './pkce.js';

const CLIENT_ID = 'a1dffde3-6309-4cd8-b89e-9be5292c6d2a';
const REDIRECT_URI = `${window.location.origin}/oauth/callback`;

export async function startLogin() {
    const verifier = generateVerifier();
    const challenge = await generateChallenge(verifier);
    const state = generateState();

    sessionStorage.setItem('pkce_verifier', verifier);
    sessionStorage.setItem('oauth_state', state);

    const params = new URLSearchParams({
        client_id: CLIENT_ID,
        response_type: 'code',
        redirect_uri: REDIRECT_URI,
        code_challenge: challenge,
        code_challenge_method: 'S256',
        state,
        scope: '*',
        prompt: 'consent',
    });

    window.location.href = `/oauth/authorize?${params.toString()}`;
}

export async function handleCallback() {
    const params = new URLSearchParams(window.location.search);
    const code = params.get('code');
    const state = params.get('state');

    if (!code) {
        return null;
    }

    const savedState = sessionStorage.getItem('oauth_state');
    if (!savedState || state !== savedState) {
        throw new Error('Invalid OAuth state');
    }

    const verifier = sessionStorage.getItem('pkce_verifier');
    if (!verifier) {
        throw new Error('Missing PKCE verifier');
    }

    const body = new URLSearchParams({
        grant_type: 'authorization_code',
        client_id: CLIENT_ID,
        redirect_uri: REDIRECT_URI,
        code,
        code_verifier: verifier,
    });

    const response = await fetch('/oauth/token', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body,
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || data.error_description || 'Token exchange failed');
    }

    sessionStorage.removeItem('pkce_verifier');
    sessionStorage.removeItem('oauth_state');
    sessionStorage.setItem('access_token', data.access_token);

    return data;
}

document.querySelectorAll('[data-oauth-login]').forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        startLogin();
    });
});

if (window.location.pathname === '/oauth/callback') {
    handleCallback()
        .then((data) => {
            if (data) {
                console.log('OAuth token exchange completed', data);
            }
        })
        .catch((error) => {
            console.error('OAuth callback failed', error);
        });
}

window.boardyAuth = {
    handleCallback,
    startLogin,
};
