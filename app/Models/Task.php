<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'priority_id',
        'image',
        'deleted',
        'reminder_time',
        'reminder_before',
        'reminder_unit',
        'reminder_sent'
    ];

    protected $casts = [
        'reminder_time' => 'datetime',
        'reminder_sent' => 'boolean',
        'deleted' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }
}
