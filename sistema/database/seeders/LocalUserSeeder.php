<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('LocalUserSeeder solo puede ejecutarse en el entorno local.');

            return;
        }

        $roles = [
            'Administrador' => 'Acceso administrativo al sistema.',
            'Químico farmacéutico' => 'Gestión farmacéutica del sistema.',
            'Cajero' => 'Gestión de ventas y atención al cliente.',
        ];

        $roleModels = [];

        foreach ($roles as $name => $description) {
            $roleModels[$name] = Role::firstOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }

        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@sanjuan.com',
                'dni' => '10000001',
                'role' => 'Administrador',
            ],
            [
                'name' => 'Rosa Quispe',
                'email' => 'rquispe@sanjuan.com',
                'dni' => '10000002',
                'role' => 'Químico farmacéutico',
            ],
            [
                'name' => 'Cajero',
                'email' => 'cajero@sanjuan.com',
                'dni' => '10000003',
                'role' => 'Cajero',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'dni' => $user['dni'],
                    'role_id' => $roleModels[$user['role']]->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
        }

        $this->command?->info('Roles y usuarios locales creados o conservados correctamente.');
    }
}
