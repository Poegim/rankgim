<?php

namespace App\Services\Soop;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin HTTP wrapper around SOOP Open API.
 *
 * Stateless — does not cache. Caching is done in SoopLiveStatusService
 * so the client stays single-purpose and easy to test.
 */
class SoopApiClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $userAgent,
        protected int $timeoutSeconds,
    ) {}

    /**
     * Fetch live broadcasts for a given SOOP category number.
     *
     * Endpoint: GET /broad/list
     * Auth: client_id only (no OAuth needed for read-only listing).
     *
     * Pages 60 broadcasts at a time. We follow pagination until we either
     * exhaust the list or hit a sane safety cap, so a runaway response
     * cannot loop forever.
     *
     * @return array<int, array<string, mixed>> Raw broadcast rows from SOOP.
     */
    public function fetchLiveBroadcastsByCategory(string $categoryNo, int $maxPages = 5): array
    {
        $broadcasts = [];
        $page       = 1;

        do {
            $response = $this->request('GET', '/broad/list', [
                'client_id'    => $this->clientId,
                'select_key'   => 'cate',
                'select_value' => $categoryNo,
                'order_type'   => 'view_cnt',
                'page_no'      => $page,
            ]);

            $rows = $response['broad'] ?? [];

            // Defensive: if SOOP returns the language map shape (happens on
            // malformed select_value), bail out instead of polluting results.
            if (! array_is_list($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $broadcasts[] = $row;
            }

            $totalCount = (int) ($response['total_cnt'] ?? 0);
            $pageBlock  = (int) ($response['page_block'] ?? 60);
            $hasMore    = count($broadcasts) < $totalCount && count($rows) === $pageBlock;

            $page++;
        } while ($hasMore && $page <= $maxPages);

        return $broadcasts;
    }

    /**
     * Perform a request with retry on transient failures.
     *
     * Retries 3 times with 200ms exponential backoff for connection errors
     * and 5xx responses. 4xx errors throw immediately — they indicate a bug
     * in our request, not a transient SOOP problem.
     *
     * @return array<string, mixed>
     */
    protected function request(string $method, string $path, array $query = []): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->withHeaders([
                    'Accept'     => 'application/json',
                    'User-Agent' => $this->userAgent,
                ])
                ->timeout($this->timeoutSeconds)
                ->retry(3, 200, throw: false)
                ->{strtolower($method)}($path, $query);

            if ($response->status() === 429) {
                // Honor Retry-After if present; surface as a clear error
                // so the caller can decide what to do (we mostly let the
                // scheduler skip this tick and try again in 5 minutes).
                $retryAfter = $response->header('Retry-After');
                throw new RuntimeException(
                    "SOOP rate-limited (429). Retry-After: {$retryAfter}"
                );
            }

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $e) {
            Log::warning('SOOP API request failed', [
                'path'    => $path,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}