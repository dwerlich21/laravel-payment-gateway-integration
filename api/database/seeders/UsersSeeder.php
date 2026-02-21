<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando usuários...');

        // Usuários especificos para teste (admins)
        $admins = [
            [
                'name' => 'Diego',
                'email' => 'dwerlich21@gmail.com',
                'cpf' => '000.000.000-00',
                'password' => Hash::make('admin123'),
                'active' => true,
                'type' => 'master',
                'position' => 'Desenvolvedor',
                'phone' => '11999999999',
            ],
            [
                'name' => 'Rodrigo',
                'email' => 'rodrigo@lifecode.dev',
                'cpf' => '111.111.111-11',
                'password' => Hash::make('admin123'),
                'active' => true,
                'type' => 'master',
                'position' => 'Desenvolvedor',
                'phone' => '11988888888',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }

        $this->command->info('- 2 usuários master criados');

        // Criar usuários com factory
        // Admins
        User::factory()
            ->count(3)
            ->admin()
            ->active()
            ->create();

        $this->command->info('- 3 usuários admin criados');

        // Managers
        User::factory()
            ->count(5)
            ->manager()
            ->active()
            ->create();

        $this->command->info('- 5 usuários manager criados');

        // Usuários comuns ativos
        User::factory()
            ->count(15)
            ->active()
            ->create();

        $this->command->info('- 15 usuários comuns ativos criados');

        // Alguns usuários inativos
        User::factory()
            ->count(5)
            ->inactive()
            ->create();

        $this->command->info('- 5 usuários inativos criados');

        $this->command->info('Total: ' . User::count() . ' usuários criados!');
    }
}
