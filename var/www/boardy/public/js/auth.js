import { generateChallenge, generateState, generateVerifier } from './pkce.js';

const CLIENT_ID = 'a1dffde3-6309-4cd8-b89e-9be5292c6d2a';
const REDIRECT_URI = `${window.location.origin}/oauth/callback`;
const API_ORIGIN = ['localhost', '127.0.0.1'].includes(window.location.hostname)
    ? 'http://127.0.0.1:8001'
    : `https://api.${window.location.hostname}`;

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

export async function refreshToken() {
    const response = await fetch('/oauth/token', {
        method: 'POST',
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            grant_type: 'refresh_token',
            client_id: CLIENT_ID,
        }),
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || data.error_description || 'Refresh failed');
    }

    sessionStorage.setItem('access_token', data.access_token);

    return data.access_token;
}

export async function authedFetch(url, options = {}) {
    const token = sessionStorage.getItem('access_token');
    const response = await fetch(url, {
        ...options,
        headers: {
            ...(options.headers || {}),
            Authorization: `Bearer ${token}`,
        },
    });

    if (response.status !== 401) {
        return response;
    }

    const newToken = await refreshToken();

    return fetch(url, {
        ...options,
        headers: {
            ...(options.headers || {}),
            Authorization: `Bearer ${newToken}`,
        },
    });
}

export async function silentRefreshDemo() {
    sessionStorage.setItem('access_token', 'expired.invalid.token');

    const response = await authedFetch(`${API_ORIGIN}/api/posts/1/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            body: 'Silent refresh demo',
            author_name: 'Silent Refresh User',
        }),
    });

    const data = await response.json();
    console.log('Silent refresh demo completed', {
        status: response.status,
        data,
    });

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
    authedFetch,
    handleCallback,
    refreshToken,
    silentRefreshDemo,
    startLogin,
};
