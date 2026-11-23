<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    protected $fillable = [
        'channel_id', 'model_code', 'display_name', 'model_type',
        'input_price', 'output_price', 'price_unit',
        'context_limit', 'enabled',
    ];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(AiChannel::class, 'channel_id');
    }
}
