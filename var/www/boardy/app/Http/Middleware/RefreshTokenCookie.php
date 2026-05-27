<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('oauth/token')
            && $request->input('grant_type') === 'refresh_token'
            && ! $request->filled('refresh_token')
            && $request->cookies->has('refresh_token')
        ) {
            $request->merge([
                'refresh_token' => $request->cookie('refresh_token'),
            ]);
        }

        $response = $next($request);

        if (! $request->is('oauth/token') || ! $response->isOk()) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);
        if (! is_array($data) || ! isset($data['refresh_token'])) {
            return $response;
        }

        $refreshToken = $data['refresh_token'];
        unset($data['refresh_token']);

        $response->setContent(json_encode($data));
        $response->headers->remove('Content-Length');
        $response->headers->setCookie(cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 30,
            '/',
            null,
            true,
            true,
            false,
            'Strict',
        ));

        return $response;
    }
}
