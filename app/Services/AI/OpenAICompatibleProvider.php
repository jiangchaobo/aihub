<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAICompatibleProvider implements AIProviderInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly int $timeout = 60,
    ) {}

    public function chat(array $messages, array $options = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/chat/completions';

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->post($url, [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

        if (!$response->successful()) {
            // 不把供应商原始响应或密钥返回给客户端。
            throw new RuntimeException(
                'AI provider request failed (HTTP ' . $response->status() . ').'
            );
        }

        $json = $response->json();
        $content = data_get($json, 'choices.0.message.content');

        if (!is_string($content)) {
            throw new RuntimeException('AI provider returned an invalid response.');
        }

        return [
            'content' => $content,
            'input_tokens' => (int) data_get($json, 'usage.prompt_tokens', 0),
            'output_tokens' => (int) data_get($json, 'usage.completion_tokens', 0),
            'provider_request_id' => $response->header('x-request-id'),
        ];
    }
}
