<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerApiLog;
use App\Models\Freelancer\FreelancerSetting;
use App\Support\Freelancer\FreelancerApiQuery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FreelancerApiService
{
    public function __construct(
        protected ?FreelancerAccount $account = null,
        protected ?FreelancerSetting $settings = null
    ) {}

    public function forAccount(FreelancerAccount $account): self
    {
        $this->account = $account->loadMissing('settings');
        $this->settings = $this->account->settings;

        return $this;
    }

    public function get(string $path, array $query = [], array $meta = [], array $repeatedQuery = []): array
    {
        return $this->request('GET', $path, [
            'query' => $query,
            'repeated_query' => $repeatedQuery,
        ], $meta);
    }

    public function post(string $path, array $payload = [], array $meta = []): array
    {
        return $this->request('POST', $path, ['json' => $payload], $meta);
    }

    public function put(string $path, array $payload = [], array $meta = []): array
    {
        return $this->request('PUT', $path, ['json' => $payload], $meta);
    }

    public function request(string $method, string $path, array $options = [], array $meta = []): array
    {
        if (!$this->account) {
            throw new RuntimeException('Freelancer account is required for API calls.');
        }

        $token = $this->account->plainAccessToken();
        if (!$token) {
            throw new RuntimeException('Freelancer access token is missing. Reconnect the account.');
        }

        $baseUrl = rtrim((string) ($this->settings?->api_base_url ?: config('freelancer.api.base_url')), '/');
        $url = $baseUrl.'/'.ltrim($path, '/');
        $started = microtime(true);
        $status = null;
        $success = false;
        $error = null;
        $body = [];
        $rateLimit = null;

        try {
            $pending = Http::withHeaders([
                'Freelancer-OAuth-V1' => $token,
                'Accept' => 'application/json',
                'User-Agent' => 'PixelsSoft-CRM/1.0',
            ])
                ->timeout((int) ($this->settings?->api_timeout ?: config('freelancer.api.timeout', 30)))
                ->retry(
                    (int) ($this->settings?->api_retries ?: config('freelancer.api.retries', 2)),
                    (int) config('freelancer.api.retry_sleep_ms', 500),
                    function ($exception) {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        return $exception->response?->status() === 429;
                    },
                    throw: false
                );

            $getUrl = $url;
            if (strtoupper($method) === 'GET') {
                $queryString = FreelancerApiQuery::build(
                    $options['query'] ?? [],
                    $options['repeated_query'] ?? []
                );
                if ($queryString !== '') {
                    $getUrl .= '?'.$queryString;
                }
            }

            $response = match (strtoupper($method)) {
                'GET' => $pending->get($getUrl),
                'POST' => $pending->asJson()->post($url, $options['json'] ?? []),
                'PUT' => $pending->asJson()->put($url, $options['json'] ?? []),
                'DELETE' => $pending->delete($url, $options['query'] ?? []),
                default => throw new RuntimeException('Unsupported HTTP method: '.$method),
            };

            $status = $response->status();
            $body = $response->json() ?? [];
            $success = $response->successful() && (($body['status'] ?? null) !== 'error');
            $rateLimit = $this->extractRateLimit($response);

            if (($body['status'] ?? null) === 'error') {
                $success = false;
                $error = $body['message'] ?? ($body['error']['message'] ?? 'Freelancer API error');
            }

            if (!$response->successful()) {
                $success = false;
                $error = $error ?: ('HTTP '.$status.': '.substr($response->body(), 0, 300));
            }

            if ($status === 429) {
                $retryAfter = $response->header('Retry-After');
                $error = 'Rate limited'.($retryAfter ? ' (Retry-After: '.$retryAfter.')' : '');
            }
        } catch (ConnectionException $e) {
            $error = 'Connection failed: '.$e->getMessage();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $elapsed = (int) round((microtime(true) - $started) * 1000);
        $this->log($method, $path, $status, $elapsed, $success, $error, $meta);
        $this->updateSettingsState($elapsed, $success, $error, $rateLimit);

        if (!$success) {
            throw new RuntimeException($error ?: 'Freelancer API request failed.');
        }

        return $body;
    }

    protected function updateSettingsState(int $elapsed, bool $success, ?string $error, ?array $rateLimit): void
    {
        if (!$this->account) {
            return;
        }

        $this->account->forceFill([
            'last_api_success_at' => $success ? now() : $this->account->last_api_success_at,
            'last_api_error_at' => $success ? $this->account->last_api_error_at : now(),
            'last_api_error' => $success ? null : ($error ? substr($error, 0, 500) : 'Unknown error'),
        ])->save();

        if ($this->settings) {
            $this->settings->forceFill([
                'api_status' => $success ? 'connected' : 'failed',
                'api_last_request_at' => now(),
                'api_last_success_at' => $success ? now() : $this->settings->api_last_success_at,
                'api_last_failure_at' => $success ? $this->settings->api_last_failure_at : now(),
                'api_last_response_time_ms' => $elapsed,
                'api_last_error' => $success ? null : ($error ? substr($error, 0, 500) : 'Unknown error'),
                'api_rate_limit' => $rateLimit,
            ])->save();
        }
    }

    protected function extractRateLimit(Response $response): ?array
    {
        $headers = [
            'limit' => $response->header('X-RateLimit-Limit'),
            'remaining' => $response->header('X-RateLimit-Remaining'),
            'reset' => $response->header('X-RateLimit-Reset'),
            'retry_after' => $response->header('Retry-After'),
        ];

        return array_filter($headers, fn ($value) => $value !== null && $value !== '');
    }

    protected function log(string $method, string $endpoint, ?int $status, int $ms, bool $success, ?string $error, array $meta = []): void
    {
        FreelancerApiLog::create([
            'freelancer_account_id' => $this->account?->id,
            'endpoint' => $endpoint,
            'method' => strtoupper($method),
            'status_code' => $status,
            'response_time_ms' => $ms,
            'success' => $success,
            'error_message' => $error ? substr($error, 0, 500) : null,
            'related_project_id' => $meta['project_id'] ?? null,
            'related_bid_id' => $meta['bid_id'] ?? null,
        ]);
    }
}
