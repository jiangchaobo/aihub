<?php

namespace App\Services\AI;

use App\Models\AiModel;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class AIManager
{
    public function driver(AiModel $model): AIProviderInterface
    {
        $channel = $model->channel;

        if (!$channel || !$channel->status) {
            throw new InvalidArgumentException('AI channel is unavailable.');
        }

        if ($channel->provider !== 'openai_compatible') {
            throw new InvalidArgumentException('Unsupported provider.');
        }

        return new OpenAICompatibleProvider(
            Crypt::decryptString($channel->api_key_encrypted),
            $channel->base_url,
            $model->model_code,
            $channel->timeout ?: config('ai.default_timeout', 60),
        );
    }
}
