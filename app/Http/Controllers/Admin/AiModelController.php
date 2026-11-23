<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\Request;

class AiModelController extends Controller
{
    // 请在路由外层添加管理员鉴权与权限策略。
    public function index()
    {
        return AiModel::with('channel:id,name,provider')->latest()->paginate(30);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel_id' => ['required', 'exists:ai_channels,id'],
            'model_code' => ['required', 'string', 'max:100'],
            'display_name' => ['required', 'string', 'max:100'],
            'model_type' => ['required', 'in:chat,embedding,image,audio'],
            'input_price' => ['nullable', 'numeric', 'min:0'],
            'output_price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['required', 'in:per_1m_tokens,per_request'],
            'context_limit' => ['nullable', 'integer', 'min:1'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        return response()->json(['data' => AiModel::create($data)], 201);
    }

    public function update(Request $request, AiModel $model)
    {
        $data = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:100'],
            'input_price' => ['sometimes', 'numeric', 'min:0'],
            'output_price' => ['sometimes', 'numeric', 'min:0'],
            'price_unit' => ['sometimes', 'in:per_1m_tokens,per_request'],
            'context_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        $model->update($data);

        return response()->json(['data' => $model]);
    }

    public function destroy(AiModel $model)
    {
        $model->delete();

        return response()->json(['message' => 'Model deleted.']);
    }
}
