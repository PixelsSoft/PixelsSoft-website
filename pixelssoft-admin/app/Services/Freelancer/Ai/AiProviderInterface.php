<?php

namespace App\Services\Freelancer\Ai;

interface AiProviderInterface
{
    public function generateProposal(array $payload, array $config = []): array;

    public function suggestBid(array $payload, array $config = []): array;

    public function suggestDelivery(array $payload, array $config = []): array;

    public function analyzeProject(array $payload, array $config = []): array;

    public function selectPortfolio(array $payload, array $config = []): array;
}
