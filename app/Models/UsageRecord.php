<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageRecord extends Model
{
    protected $fillable = [
        'user_id', 'model_id', 'conversation_id', 'request_id',
        'input_tokens', 'output_tokens', 'total_cost',
        'billed_amount', 'status', 'error_code',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_cost' => 'decimal:8',
            'billed_amount' => 'decimal:8',
        ];
    }
}
