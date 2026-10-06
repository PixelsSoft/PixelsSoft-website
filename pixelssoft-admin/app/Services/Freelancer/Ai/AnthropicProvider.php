<?php

namespace App\Services\Freelancer\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicProvider implements AiProviderInterface
{
    public function generateProposal(array $payload, array $config = []): array
    {
        $apiKey = (string) ($config['api_key'] ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.anthropic.com/v1'), '/');
        $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])
            ->timeout((int) ($config['timeout'] ?? 20))
            ->acceptJson()
            ->post($baseUrl.'/messages', [
                'model' => (string) ($config['model'] ?? 'claude-3-5-sonnet-latest'),
                'max_tokens' => (int) ($config['max_tokens'] ?? 600),
                'temperature' => (float) ($config['temperature'] ?? 0.3),
                'system' => $payload['system_prompt'] ?? '',
                'messages' => [
                    ['role' => 'user', 'content' => [['type' => 'text', 'text' => $payload['prompt'] ?? json_encode($payload)]]],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Anthropic request failed (HTTP '.$response->status().').');
        }

        $text = Arr::get($response->json(), 'content.0.text');
        $decoded = json_decode((string) $text, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Anthropic response was not valid JSON.');
        }

        return $this->normalize($decoded);
    }

    public function suggestBid(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function suggestDelivery(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function analyzeProject(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function selectPortfolio(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }

    protected function normalize(array $response): array
    {
        return [
            'proposal' => $response['proposal'] ?? '',
            'suggested_bid' => $response['suggested_bid'] ?? null,
            'suggested_delivery_days' => $response['suggested_delivery_days'] ?? null,
            'selected_portfolio_urls' => $response['selected_portfolio_urls'] ?? [],
            'reasoning' => $response['reasoning'] ?? null,
            'confidence_score' => $response['confidence_score'] ?? null,
        ];
    }
}
