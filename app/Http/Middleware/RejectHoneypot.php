<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectHoneypot
{
    /**
     * Reject requests where the honeypot field is filled in.
     *
     * Real users never see or fill this field. Bots auto-fill every
     * input they find, so a non-empty value means automated submission.
     * We return a fake 422 to avoid leaking that we detected the bot.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('website')) {
            // Abort with a generic validation error so the bot
            // doesn't know it was caught by the honeypot.
            abort(422, 'Validation failed.');
        }

        // Remove the honeypot field so it doesn't reach validation rules.
        $request->request->remove('website');

        return $next($request);
    }
}