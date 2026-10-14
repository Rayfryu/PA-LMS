<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'teacher_id',
        'title',
        'description',
        'due_at',
        'status',
        'allowed_file_types',
        'max_file_size_mb',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function studentSubmission(int $studentId): HasOne
    {
        return $this->hasOne(AssignmentSubmission::class)->where('student_id', $studentId);
    }

    public function isPastDue(): bool
    {
        return $this->due_at && Carbon::now('Asia/Jakarta')->greaterThan($this->due_at);
    }

    public function isAcceptingSubmissions(): bool
    {
        return $this->status === 'published' && !$this->isPastDue();
    }
}