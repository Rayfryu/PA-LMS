<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'user_id',
        'status',
        'enrolled_at',
        'left_at',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'enrolled_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}