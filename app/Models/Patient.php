<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    // mengambil relasi dari user
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // relasi 1.1 ke tabel queue
    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class);
    }
}
