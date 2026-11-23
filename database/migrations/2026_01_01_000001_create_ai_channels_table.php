<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('provider', 50);
            $table->string('base_url', 500);
            $table->text('api_key_encrypted');
            $table->unsignedInteger('timeout')->default(60);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_channels');
    }
};
