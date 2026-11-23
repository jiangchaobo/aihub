<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Conversation::where('user_id', $request->user()->id)
            ->with('model:id,display_name')
            ->withCount('messages')
            ->latest('updated_at')
            ->paginate(20);

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'model_id' => ['required', 'integer', 'exists:ai_models,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'system_prompt' => ['nullable', 'string', 'max:10000'],
        ]);

        $conversation = Conversation::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'active',
        ]);

        return response()->json(['data' => $conversation], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $conversation = Conversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->findOrFail($id);

        return response()->json(['data' => $conversation]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $conversation = Conversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted.']);
    }
}
