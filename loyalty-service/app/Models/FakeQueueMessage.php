<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakeQueueMessage extends Model
{
    protected $fillable = ['event', 'payload', 'processed_at'];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
