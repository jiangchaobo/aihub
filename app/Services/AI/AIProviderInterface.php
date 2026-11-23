<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * @param array<int, array<string, mixed>> $messages
     * @return array{content:string,input_tokens:int,output_tokens:int,provider_request_id:?string}
     */
    public function chat(array $messages, array $options = []): array;
}
