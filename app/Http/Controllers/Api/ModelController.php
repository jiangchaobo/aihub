<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\JsonResponse;

class ModelController extends Controller
{
    public function index(): JsonResponse
    {
        $models = AiModel::query()
            ->where('enabled', true)
            ->whereHas('channel', fn ($q) => $q->where('status', true))
            ->select(['id', 'model_code', 'display_name', 'model_type', 'context_limit'])
            ->orderBy('display_name')
            ->get();

        return response()->json(['data' => $models]);
    }
}
