<?php

use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Admin\AiChannelController;
use App\Http\Controllers\Admin\AiModelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/models', [ModelController::class, 'index']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::delete('/conversations/{id}', [ConversationController::class, 'destroy']);

    Route::post('/chat/completions', [ChatController::class, 'store']);

    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'revoke']);
});

// 管理接口：上线前必须添加管理员认证、角色权限和审计。
Route::prefix('admin/v1')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('channels', AiChannelController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('models', AiModelController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
