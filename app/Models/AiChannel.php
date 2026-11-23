<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChannel extends Model
{
    protected $fillable = [
        'name', 'provider', 'base_url', 'api_key_encrypted',
        'timeout', 'priority', 'status',
    ];

    protected $hidden = ['api_key_encrypted'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class, 'channel_id');
    }
}
