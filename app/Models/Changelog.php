<?php

namespace App\Models;

use App\Enums\ActiveEnums;
use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    protected $fillable = [
        'tweak_id',
        'version',
        'changelog',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActiveEnums::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tweak()
    {
        return $this->belongsTo(Tweak::class);
    }
}
