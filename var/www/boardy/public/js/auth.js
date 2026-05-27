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

document.querySelectorAll('[data-oauth-login]').forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        startLogin();
    });
});

window.boardyAuth = {
    startLogin,
};
