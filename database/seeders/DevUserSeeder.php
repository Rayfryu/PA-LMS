<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Creates one local test user per role for manual login testing during
 * development ONLY. This seeder refuses to run outside local/testing
 * environments so it can never seed weak/known credentials in production.
 *
 * Passwords are never hardcoded. Provide them via .env:
 *   SEED_ADMIN_PASSWORD=...
 *   SEED_TEACHER_PASSWORD=...
 *   SEED_STUDENT_PASSWORD=...
 *
 * If an env password is not set, a random one is generated and printed
 * ONCE to the console output (not written to any file or migration).
 */
class DevUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! App::environment(['local', 'testing'])) {
            $this->command?->warn('DevUserSeeder skipped: only runs in local/testing environments.');

            return;
        }

        $accounts = [
            Role::ADMIN => ['name' => 'Dev Admin', 'email' => 'admin@salms.test'],
            Role::TEACHER => ['name' => 'Dev Teacher', 'email' => 'teacher@salms.test'],
            Role::STUDENT => ['name' => 'Dev Student', 'email' => 'student@salms.test'],
        ];

        foreach ($accounts as $slug => $account) {
            $role = Role::query()->where('slug', $slug)->first();

            if (! $role) {
                $this->command?->warn("DevUserSeeder: role [{$slug}] not found — run RoleSeeder first.");

                continue;
            }

            $envKey = 'SEED_'.strtoupper($slug).'_PASSWORD';
            $password = env($envKey) ?: Str::password(16);

            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $password,
                    'role_id' => $role->id,
                    'email_verified_at' => now(),
                ],
            );

            if (! env($envKey)) {
                $this->command?->info("Generated password for {$account['email']}: {$password}");
            }
        }
    }
}
