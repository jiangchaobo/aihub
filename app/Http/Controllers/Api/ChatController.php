<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\Conversation;
use App\Models\UsageRecord;
use App\Services\AI\AIManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ChatController extends Controller
{
    public function store(Request $request, AIManager $ai): JsonResponse
    {
        $data = $request->validate([
            'model_id' => ['required', 'integer'],
            'conversation_id' => ['nullable', 'integer'],
            'message' => ['required', 'string', 'max:' . config('ai.max_message_chars', 20000)],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
        ]);

        $user = $request->user();

        $model = AiModel::with('channel')
            ->where('enabled', true)
            ->whereHas('channel', fn ($q) => $q->where('status', true))
            ->findOrFail($data['model_id']);

        if (!empty($data['conversation_id'])) {
            $conversation = Conversation::where('user_id', $user->id)
                ->findOrFail($data['conversation_id']);
            $conversation->model_id = $model->id;
            $conversation->save();
        } else {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'model_id' => $model->id,
                'title' => mb_substr($data['message'], 0, 80),
                'status' => 'active',
            ]);
        }

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $data['message'],
        ]);

        $history = $conversation->messages()
            ->latest('id')
            ->limit(config('ai.max_history_messages', 20))
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        if ($conversation->system_prompt) {
            array_unshift($history, [
                'role' => 'system',
                'content' => $conversation->system_prompt,
            ]);
        }

        $requestId = (string) Str::uuid();

        try {
            $result = $ai->driver($model)->chat($history, [
                'temperature' => $data['temperature'] ?? 0.7,
            ]);

            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $result['content'],
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'metadata' => ['provider_request_id' => $result['provider_request_id']],
            ]);

            UsageRecord::create([
                'user_id' => $user->id,
                'model_id' => $model->id,
                'conversation_id' => $conversation->id,
                'request_id' => $requestId,
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'total_cost' => '0',
                'billed_amount' => '0',
                'status' => 'succeeded',
            ]);

            return response()->json([
                'conversation_id' => $conversation->id,
                'message' => $result['content'],
                'usage' => [
                    'input_tokens' => $result['input_tokens'],
                    'output_tokens' => $result['output_tokens'],
                ],
                'request_id' => $requestId,
            ]);
        } catch (Throwable $e) {
            UsageRecord::create([
                'user_id' => $user->id,
                'model_id' => $model->id,
                'conversation_id' => $conversation->id,
                'request_id' => $requestId,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_cost' => '0',
                'billed_amount' => '0',
                'status' => 'failed',
                'error_code' => 'provider_error',
            ]);

            report($e);

            return response()->json([
                'message' => 'AI service is temporarily unavailable.',
                'request_id' => $requestId,
            ], 502);
        }
    }
}
