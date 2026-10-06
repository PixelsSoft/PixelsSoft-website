<?php

namespace App\Services\Freelancer\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCompatibleProvider implements AiProviderInterface
{
    public function __construct(protected string $defaultBaseUrl = 'https://api.openai.com/v1') {}

    public function generateProposal(array $payload, array $config = []): array
    {
        $response = $this->request($payload, $config);
        return $this->normalize($response);
    }

    public function suggestBid(array $payload, array $config = []): array
    {
        return $this->generateProposal($payload, $config);
    }

    public function suggestDelivery(array $payload, array $config = []): array
    {
        return $this->generateProposal($payload, $config);
    }

    public function analyzeProject(array $payload, array $config = []): array
    {
        return $this->generateProposal($payload, $config);
    }

    public function selectPortfolio(array $payload, array $config = []): array
    {
        return $this->generateProposal($payload, $config);
    }

    protected function request(array $payload, array $config): array
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? $this->defaultBaseUrl), '/');
        $apiKey = (string) ($config['api_key'] ?? '');
        $model = (string) ($config['model'] ?? 'gpt-4o-mini');
        if ($apiKey === '') {
            throw new RuntimeException('AI API key is not configured.');
        }

        $timeout = (int) ($config['timeout'] ?? 20);
        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => (float) ($config['temperature'] ?? 0.3),
                'max_tokens' => (int) ($config['max_tokens'] ?? 600),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $payload['system_prompt'] ?? 'You are a careful bidding assistant.'],
                    ['role' => 'user', 'content' => $payload['prompt'] ?? json_encode($payload)],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('AI request failed (HTTP '.$response->status().').');
        }

        $content = Arr::get($response->json(), 'choices.0.message.content');
        if (!$content) {
            throw new RuntimeException('AI response did not contain content.');
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('AI response was not valid JSON.');
        }

        return $decoded;
    }

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
