<?php

namespace App\Services\Freelancer;

use App\Models\Freelancer\FreelancerAccount;
use App\Models\Freelancer\FreelancerAuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FreelancerAuthService
{
    public function __construct(protected FreelancerSettingsService $settingsService) {}

    public function isConfigured(?FreelancerAccount $account = null): bool
    {
        $settings = $account?->loadMissing('settings')->settings;

        return (bool) (($settings?->client_id ?: config('freelancer.client_id'))
            && ($settings?->plainClientSecret() ?: config('freelancer.client_secret'))
            && ($settings?->oauth_redirect_uri ?: config('freelancer.redirect_uri')));
    }

    public function authorizationUrl(string $state, ?FreelancerAccount $account = null): string
    {
        if (!$this->isConfigured($account)) {
            throw new RuntimeException('Freelancer OAuth is not configured. Set Freelancer API credentials first.');
        }

        $settings = $account?->loadMissing('settings')->settings;
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $settings?->client_id ?: config('freelancer.client_id'),
            'redirect_uri' => $settings?->oauth_redirect_uri ?: config('freelancer.redirect_uri'),
            'scope' => config('freelancer.oauth.scope', 'basic'),
            'prompt' => config('freelancer.oauth.prompt', 'select_account consent'),
            'advanced_scopes' => config('freelancer.oauth.advanced_scopes', '1 2 3 4 5'),
            'state' => $state,
        ]);

        $authorizeUrl = config('freelancer.oauth.authorize_url');
        if ($settings?->environment === 'production') {
            $authorizeUrl = 'https://accounts.freelancer.com/oauth/authorise';
        } elseif ($settings?->environment === 'sandbox') {
            $authorizeUrl = 'https://accounts.freelancer-sandbox.com/oauth/authorise';
        }

        return rtrim((string) $authorizeUrl, '?').'?'.$query;
    }

    public function makeState(): string
    {
        return Str::random(40);
    }

    public function exchangeCode(FreelancerAccount $account, string $code): array
    {
        return $this->tokenRequest($account, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $account->settings?->client_id ?: config('freelancer.client_id'),
            'client_secret' => $account->settings?->plainClientSecret() ?: config('freelancer.client_secret'),
            'redirect_uri' => $account->settings?->oauth_redirect_uri ?: config('freelancer.redirect_uri'),
        ]);
    }

    public function refresh(FreelancerAccount $account): array
    {
        $refresh = $account->plainRefreshToken();
        if (!$refresh) {
            throw new RuntimeException('No refresh token available. Reconnect the Freelancer account.');
        }

        $tokens = $this->tokenRequest($account, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh,
            'client_id' => $account->settings?->client_id ?: config('freelancer.client_id'),
            'client_secret' => $account->settings?->plainClientSecret() ?: config('freelancer.client_secret'),
        ]);

        $this->storeTokens($account, $tokens);

        return $tokens;
    }

    public function storeTokens(FreelancerAccount $account, array $tokens): FreelancerAccount
    {
        $expiresIn = isset($tokens['expires_in']) ? (int) $tokens['expires_in'] : null;

        $account->fill([
            'access_token' => $tokens['access_token'] ?? null,
            'refresh_token' => $tokens['refresh_token'] ?? $account->plainRefreshToken(),
            'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : null,
            'is_connected' => !empty($tokens['access_token']),
            'connected_at' => !empty($tokens['access_token']) ? ($account->connected_at ?: now()) : $account->connected_at,
        ])->save();

        return $account->fresh(['settings']);
    }

    public function disconnect(FreelancerAccount $account, ?int $userId = null): void
    {
        $old = [
            'is_connected' => $account->is_connected,
            'automation_enabled' => $account->automation_enabled,
        ];

        $account->fill([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'is_connected' => false,
            'automation_enabled' => false,
        ])->save();

        FreelancerAuditLog::create([
            'user_id' => $userId,
            'freelancer_account_id' => $account->id,
            'action' => 'account.disconnected',
            'entity_type' => FreelancerAccount::class,
            'entity_id' => $account->id,
            'old_value' => $old,
            'new_value' => ['is_connected' => false],
        ]);
    }

    public function ensureFreshToken(FreelancerAccount $account): FreelancerAccount
    {
        if (!$account->plainAccessToken()) {
            throw new RuntimeException('Freelancer account is not connected.');
        }

        if ($account->token_expires_at && $account->token_expires_at->copy()->subMinutes(2)->isPast()) {
            $this->refresh($account->loadMissing('settings'));
            $account->refresh();
        }

        return $account;
    }

    protected function tokenRequest(FreelancerAccount $account, array $payload): array
    {
        if (!$this->isConfigured($account)) {
            throw new RuntimeException('Freelancer OAuth is not configured.');
        }

        $tokenUrl = match ($account->settings?->environment) {
            'production' => 'https://accounts.freelancer.com/oauth/token',
            'sandbox' => 'https://accounts.freelancer-sandbox.com/oauth/token',
            default => (string) config('freelancer.oauth.token_url'),
        };

        $response = Http::asForm()
            ->timeout((int) ($account->settings?->api_timeout ?: 30))
            ->acceptJson()
            ->post($tokenUrl, $payload);

        $json = $response->json() ?? [];
        if (!$response->successful() || empty($json['access_token'])) {
            $message = $json['error_description'] ?? $json['error'] ?? ('Token exchange failed (HTTP '.$response->status().')');
            throw new RuntimeException($message);
        }

        return [
            'access_token' => $json['access_token'],
            'refresh_token' => $json['refresh_token'] ?? null,
            'expires_in' => $json['expires_in'] ?? null,
            'token_type' => $json['token_type'] ?? null,
        ];
    }
}
