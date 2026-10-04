<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'description', 'is_active'])]
class Major extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // classes() relationship is intentionally omitted — the `classes` table
    // does not exist yet (Phase 2B). Add `hasMany(Class::class)` there.
}