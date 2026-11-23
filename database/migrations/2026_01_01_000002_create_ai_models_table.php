<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('ai_channels')->cascadeOnDelete();
            $table->string('model_code', 100);
            $table->string('display_name', 100);
            $table->string('model_type', 30)->default('chat');
            $table->decimal('input_price', 18, 8)->default(0);
            $table->decimal('output_price', 18, 8)->default(0);
            $table->string('price_unit', 30)->default('per_1m_tokens');
            $table->unsignedInteger('context_limit')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['channel_id', 'model_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
