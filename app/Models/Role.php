<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name'])]
class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'admin';

    public const TEACHER = 'teacher';

    public const STUDENT = 'student';

    /**
     * All role slugs recognized by the system.
     *
     * @return array<int, string>
     */
    public static function slugs(): array
    {
        return [self::ADMIN, self::TEACHER, self::STUDENT];
    }

    /**
     * Named route each role is redirected to after login.
     * Centralized here so the mapping is not duplicated across controllers.
     */
    public static function dashboardRouteFor(string $slug): string
    {
        return match ($slug) {
            self::ADMIN => 'admin.dashboard',
            self::TEACHER => 'teacher.dashboard',
            self::STUDENT => 'student.dashboard',
            default => throw new \InvalidArgumentException("Unknown role slug [{$slug}]."),
        };
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
