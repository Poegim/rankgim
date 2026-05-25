<?php

namespace App\Services\Twitch;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin HTTP wrapper around the Twitch Helix API.
 *
 * Responsibilities:
 *   - Mint and cache the app access token (client_credentials grant).
 *   - Issue authenticated requests with the required Client-Id + Bearer headers.
 *   - Paginate /helix/streams cursor-based responses.
 *   - On 401, invalidate the cached token and retry once (handles token revocation).
 *
 * No business logic / no caching of stream data — that lives in
 * TwitchLiveStatusService. This class is pure transport.
 */
class TwitchApiClient
{
    private const TOKEN_CACHE_KEY = 'twitch:app_access_token';

    // Refresh slightly before the real expiry to avoid edge-of-expiry races.
    private const TOKEN_EXPIRY_SAFETY_MARGIN_SECONDS = 60;

    /**
     * Fetch live streams for a given Twitch game/category ID.
     *
     * Paginates Helix's cursor-based response until either:
     *   - the cursor is empty (we've seen everything), or
     *   - we hit $maxPages (defensive cap against runaway loops).
     *
     * @return array<int, array<string, mixed>>  Raw Helix stream payloads.
     */
    public function fetchLiveStreamsByGame(string $gameId, int $maxPages = 5): array
    {
        $streams = [];
        $cursor  = null;
        $page    = 0;

        do {
            $page++;
            $query = [
                'game_id' => $gameId,
                'first'   => 100, // Helix max
            ];

            if ($cursor !== null) {
                $query['after'] = $cursor;
            }

            $response = $this->authedGet('/streams', $query);
            $payload  = $response->json();

            $streams = array_merge($streams, $payload['data'] ?? []);
            $cursor  = $payload['pagination']['cursor'] ?? null;
        } while ($cursor !== null && $page < $maxPages);

        return $streams;
    }

    /**
     * Authenticated GET with one automatic retry on 401 (token expired or revoked).
     */
    private function authedGet(string $path, array $query = []): Response
    {
        $url = rtrim(config('services.twitch.base_url'), '/') . $path;

        try {
            $response = $this->buildClient($this->getAppAccessToken())->get($url, $query);

            // 401 → token is stale despite our cache; force a refresh and retry once.
            if ($response->status() === 401) {
                Cache::forget(self::TOKEN_CACHE_KEY);
                $response = $this->buildClient($this->getAppAccessToken())->get($url, $query);
            }

            $response->throw();

            return $response;
        } catch (ConnectionException | RequestException $e) {
            Log::warning('Twitch API request failed', [
                'path'  => $path,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Returns a cached app access token, minting a new one if absent or expired.
     *
     * Uses the client_credentials OAuth grant. Tokens are cached for
     * (expires_in - safety margin) seconds so we never present a token that's
     * about to expire mid-request.
     */
    public function getAppAccessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $clientId     = config('services.twitch.client_id');
        $clientSecret = config('services.twitch.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw new RuntimeException(
                'Twitch credentials are not configured. Set TWITCH_CLIENT_ID and TWITCH_CLIENT_SECRET in .env.'
            );
        }

        $url = rtrim(config('services.twitch.oauth_url'), '/') . '/token';

        $response = Http::asForm()
            ->timeout(config('services.twitch.timeout'))
            ->withUserAgent(config('services.twitch.user_agent'))
            ->post($url, [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'grant_type'    => 'client_credentials',
            ]);

        $response->throw();

        $payload     = $response->json();
        $accessToken = $payload['access_token']      ?? null;
        $expiresIn   = (int) ($payload['expires_in'] ?? 0);

        if (! is_string($accessToken) || $accessToken === '' || $expiresIn <= 0) {
            throw new RuntimeException('Twitch OAuth response missing access_token or expires_in.');
        }

        $ttl = max(60, $expiresIn - self::TOKEN_EXPIRY_SAFETY_MARGIN_SECONDS);
        Cache::put(self::TOKEN_CACHE_KEY, $accessToken, $ttl);

        return $accessToken;
    }

    /**
     * Builds an Http client preconfigured with the Helix auth headers,
     * timeout, and user agent. Re-built per request so we can swap tokens
     * on the 401-retry path without mutating shared state.
     */
    private function buildClient(string $accessToken): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Client-Id'     => config('services.twitch.client_id'),
            'Authorization' => 'Bearer ' . $accessToken,
        ])
            ->timeout(config('services.twitch.timeout'))
            ->withUserAgent(config('services.twitch.user_agent'))
            ->acceptJson();
    }
}