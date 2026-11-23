<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AiChannelController extends Controller
{
    // 请在路由外层添加管理员鉴权与权限策略。
    public function index()
    {
        return AiChannel::query()->withCount('models')->latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'provider' => ['required', 'in:openai_compatible'],
            'base_url' => ['required', 'url', 'max:500'],
            'api_key' => ['required', 'string', 'max:5000'],
            'timeout' => ['nullable', 'integer', 'between:5,300'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        $channel = AiChannel::create([
            'name' => $data['name'],
            'provider' => $data['provider'],
            'base_url' => $data['base_url'],
            'api_key_encrypted' => Crypt::encryptString($data['api_key']),
            'timeout' => $data['timeout'] ?? 60,
            'priority' => $data['priority'] ?? 0,
            'status' => $data['status'] ?? true,
        ]);

        return response()->json(['data' => $channel], 201);
    }

    public function update(Request $request, AiChannel $channel)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'base_url' => ['sometimes', 'required', 'url', 'max:500'],
            'api_key' => ['sometimes', 'required', 'string', 'max:5000'],
            'timeout' => ['sometimes', 'integer', 'between:5,300'],
            'priority' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['api_key'])) {
            $data['api_key_encrypted'] = Crypt::encryptString($data['api_key']);
            unset($data['api_key']);
        }

        $channel->update($data);

        return response()->json(['data' => $channel]);
    }

    public function destroy(AiChannel $channel)
    {
        $channel->delete();

        return response()->json(['message' => 'Channel deleted.']);
    }
}
