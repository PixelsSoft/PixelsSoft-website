<?php

namespace App\Services\Freelancer\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProviderInterface
{
    public function generateProposal(array $payload, array $config = []): array
    {
        $apiKey = (string) ($config['api_key'] ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $model = (string) ($config['model'] ?? 'gemini-1.5-flash');
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        $url = $baseUrl.'/'.$model.':generateContent?key='.urlencode($apiKey);

        $response = Http::timeout((int) ($config['timeout'] ?? 20))
            ->acceptJson()
            ->post($url, [
                'contents' => [[
                    'parts' => [[
                        'text' => ($payload['system_prompt'] ?? '')."\n\n".($payload['prompt'] ?? json_encode($payload)),
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => (float) ($config['temperature'] ?? 0.3),
                    'maxOutputTokens' => (int) ($config['max_tokens'] ?? 600),
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini request failed (HTTP '.$response->status().').');
        }

        $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text');
        $decoded = json_decode((string) $text, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Gemini response was not valid JSON.');
        }

        return [
            'proposal' => $decoded['proposal'] ?? '',
            'suggested_bid' => $decoded['suggested_bid'] ?? null,
            'suggested_delivery_days' => $decoded['suggested_delivery_days'] ?? null,
            'selected_portfolio_urls' => $decoded['selected_portfolio_urls'] ?? [],
            'reasoning' => $decoded['reasoning'] ?? null,
            'confidence_score' => $decoded['confidence_score'] ?? null,
        ];
    }

    public function suggestBid(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function suggestDelivery(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function analyzeProject(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
    public function selectPortfolio(array $payload, array $config = []): array { return $this->generateProposal($payload, $config); }
}
