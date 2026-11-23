<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $plain = 'ah_' . Str::random(48);

        $key = ApiKey::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'key_prefix' => substr($plain, 0, 12),
            'key_hash' => hash('sha256', $plain),
        ]);

        // 明文只返回这一次；数据库只存 hash。
        return response()->json([
            'data' => [
                'id' => $key->id,
                'name' => $key->name,
                'key' => $plain,
            ],
            'warning' => 'Copy this key now. It will not be shown again.',
        ], 201);
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => ApiKey::where('user_id', $request->user()->id)
                ->select('id', 'name', 'key_prefix', 'last_used_at', 'expires_at', 'revoked_at', 'created_at')
                ->latest()
                ->get(),
        ]);
    }

    public function revoke(Request $request, int $id)
    {
        $key = ApiKey::where('user_id', $request->user()->id)->findOrFail($id);
        $key->update(['revoked_at' => now()]);

        return response()->json(['message' => 'API key revoked.']);
    }
}
