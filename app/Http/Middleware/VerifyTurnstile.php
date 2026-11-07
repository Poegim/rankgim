<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    /**
     * Verify the Cloudflare Turnstile token before the request reaches Fortify.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->input('cf-turnstile-response', '');

        if ($token === '' || ! $this->isValid($token, $request->ip())) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => __('Captcha verification failed. Please try again.'),
            ]);
        }

        return $next($request);
    }

    /**
     * Ask Cloudflare's siteverify endpoint whether the token is genuine.
     * Fails closed: any network or API error counts as an invalid token.
     */
    private function isValid(string $token, ?string $ip): bool
    {
        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret'   => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            return $response->successful() && $response->json('success') === true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}