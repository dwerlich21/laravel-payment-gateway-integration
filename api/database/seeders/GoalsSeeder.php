<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garantir que existem usuários para associar as metas
        $userCount = User::count();
        if ($userCount < 5) {
            $this->command->info('Criando usuários adicionais para as metas...');
            User::factory()->count(10)->create();
        }

        $this->command->info('Criando metas distribuídas nos últimos 12 meses...');

        // Criar metas distribuídas por mês (últimos 12 meses)
        for ($month = 11; $month >= 0; $month--) {
            // Metas criadas neste mês
            $createdCount = fake()->numberBetween(5, 12);
            Goal::factory()
                ->count($createdCount)
                ->createdMonthsAgo($month)
                ->create();

            // Metas concluídas neste mês (menos no mês atual)
            if ($month > 0) {
                $completedCount = fake()->numberBetween(3, 8);
                Goal::factory()
                    ->count($completedCount)
                    ->completedInMonth($month)
                    ->create();
            }

            $this->command->info("- Mês -{$month}: {$createdCount} criadas" . ($month > 0 ? ", ~{$completedCount} concluídas" : ""));
        }

        // Metas em andamento (atuais)
        Goal::factory()
            ->count(15)
            ->inProgress()
            ->create();

        $this->command->info('- 15 metas em andamento criadas');

        // Metas pendentes com prazo futuro
        Goal::factory()
            ->count(10)
            ->pending()
            ->upcoming()
            ->create();

        $this->command->info('- 10 metas pendentes (futuras) criadas');

        // Metas atrasadas
        Goal::factory()
            ->count(8)
            ->overdue()
            ->create();

        $this->command->info('- 8 metas atrasadas criadas');

        // Metas canceladas distribuídas
        for ($month = 10; $month >= 2; $month -= 2) {
            Goal::factory()
                ->count(2)
                ->cancelled()
                ->createdMonthsAgo($month)
                ->create();
        }

        $this->command->info('- 10 metas canceladas distribuídas');

        $this->command->info('');
        $this->command->info('Total: ' . Goal::count() . ' metas criadas!');
    }
}
