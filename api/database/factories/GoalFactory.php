<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    protected $model = Goal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $status = fake()->randomElement($statuses);

        // Datas podem ser antigas (ate 2 anos atras) ou futuras (ate 6 meses)
        $deadline = fake()->dateTimeBetween('-2 years', '+6 months');
        $deadlineCarbon = \Carbon\Carbon::parse($deadline);

        // Se status for completed, definir data de conclusão
        $completionDate = null;
        $resultPercentage = null;
        $achievementPercentage = null;

        if ($status === 'completed') {
            // Data de conclusão entre criação e deadline (ou um pouco depois)
            $completionDate = fake()->dateTimeBetween(
                $deadlineCarbon->copy()->subMonths(3),
                $deadlineCarbon->copy()->addWeeks(2)
            );
            $resultPercentage = fake()->randomFloat(2, 70, 120);
            $achievementPercentage = fake()->randomFloat(2, 60, 100);
        } elseif ($status === 'in_progress') {
            $achievementPercentage = fake()->randomFloat(2, 10, 80);
        } elseif ($status === 'cancelled') {
            $achievementPercentage = fake()->randomFloat(2, 0, 30);
        }

        $milestones = [
            'Implementar sistema de autenticação',
            'Desenvolver módulo de relatórios',
            'Criar dashboard administrativo',
            'Migrar banco de dados para nova estrutura',
            'Implementar API REST para integração',
            'Desenvolver aplicativo mobile',
            'Otimizar performance do sistema',
            'Implementar sistema de notificações',
            'Criar módulo de gestão de usuários',
            'Desenvolver sistema de backup automático',
            'Implementar testes automatizados',
            'Criar documentação técnica',
            'Desenvolver módulo financeiro',
            'Implementar sistema de logs',
            'Criar integração com serviços externos',
            'Desenvolver painel de métricas',
            'Implementar sistema de cache',
            'Criar módulo de exportação de dados',
            'Desenvolver sistema de permissões',
            'Implementar auditoria de ações',
        ];

        $deliverables = [
            'Código fonte no repositorio com testes',
            'Documentação completa no Confluence',
            'Deploy em ambiente de produção',
            'Apresentação para stakeholders',
            'Relatório de testes e validação',
            'Manual do usuário finalizado',
            'API documentada no Swagger',
            'Métricas de performance coletadas',
            'Aprovação do Product Owner',
            'Code review aprovado pela equipe',
        ];

        return [
            'responsible_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'created_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'milestone_description' => fake()->randomElement($milestones),
            'validation_deliverable' => fake()->randomElement($deliverables),
            'deadline' => $deadline,
            'weight' => fake()->randomFloat(2, 1, 10),
            'completion_date' => $completionDate,
            'result_percentage' => $resultPercentage,
            'proof_link' => $status === 'completed' ? fake()->url() : null,
            'achievement_percentage' => $achievementPercentage,
            'status' => $status,
            'active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the goal is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completion_date' => null,
            'result_percentage' => null,
            'achievement_percentage' => null,
            'proof_link' => null,
        ]);
    }

    /**
     * Indicate that the goal is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completion_date' => null,
            'result_percentage' => null,
            'achievement_percentage' => fake()->randomFloat(2, 10, 80),
            'proof_link' => null,
        ]);
    }

    /**
     * Indicate that the goal is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $deadline = \Carbon\Carbon::parse($attributes['deadline']);
            return [
                'status' => 'completed',
                'completion_date' => fake()->dateTimeBetween(
                    $deadline->copy()->subMonths(1),
                    $deadline->copy()->addWeeks(1)
                ),
                'result_percentage' => fake()->randomFloat(2, 80, 120),
                'achievement_percentage' => fake()->randomFloat(2, 70, 100),
                'proof_link' => fake()->url(),
            ];
        });
    }

    /**
     * Indicate that the goal is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completion_date' => null,
            'result_percentage' => null,
            'achievement_percentage' => fake()->randomFloat(2, 0, 30),
            'proof_link' => null,
        ]);
    }

    /**
     * Indicate that the goal is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => fake()->dateTimeBetween('-6 months', '-1 week'),
            'status' => fake()->randomElement(['pending', 'in_progress']),
            'completion_date' => null,
        ]);
    }

    /**
     * Create goal with old dates (historical data).
     */
    public function historical(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = fake()->dateTimeBetween('-12 months', '-3 months');
            $createdAtCarbon = \Carbon\Carbon::parse($createdAt);
            $deadline = $createdAtCarbon->copy()->addDays(fake()->numberBetween(30, 90));
            $status = fake()->randomElement(['completed', 'cancelled']);

            $completionDate = null;
            $resultPercentage = null;
            $achievementPercentage = null;
            $proofLink = null;

            if ($status === 'completed') {
                $completionDate = fake()->dateTimeBetween(
                    $createdAtCarbon->copy()->addDays(15),
                    $deadline->copy()->addWeeks(2)
                );
                $resultPercentage = fake()->randomFloat(2, 70, 120);
                $achievementPercentage = fake()->randomFloat(2, 60, 100);
                $proofLink = fake()->url();
            } else {
                $achievementPercentage = fake()->randomFloat(2, 0, 40);
            }

            return [
                'created_at' => $createdAt,
                'updated_at' => $completionDate ?? $createdAt,
                'deadline' => $deadline,
                'status' => $status,
                'completion_date' => $completionDate,
                'result_percentage' => $resultPercentage,
                'achievement_percentage' => $achievementPercentage,
                'proof_link' => $proofLink,
            ];
        });
    }

    /**
     * Create goal in a specific month (for timeline distribution).
     */
    public function createdMonthsAgo(int $monthsAgo): static
    {
        return $this->state(function (array $attributes) use ($monthsAgo) {
            $createdAt = \Carbon\Carbon::now()
                ->subMonths($monthsAgo)
                ->startOfMonth()
                ->addDays(fake()->numberBetween(0, 27));

            return [
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        });
    }

    /**
     * Create completed goal with created_at in specific month.
     */
    public function completedInMonth(int $monthsAgo): static
    {
        return $this->state(function (array $attributes) use ($monthsAgo) {
            $createdAt = \Carbon\Carbon::now()
                ->subMonths($monthsAgo)
                ->startOfMonth()
                ->addDays(fake()->numberBetween(0, 15));

            $completionDate = $createdAt->copy()->addDays(fake()->numberBetween(7, 25));
            $deadline = $createdAt->copy()->addDays(fake()->numberBetween(20, 40));

            return [
                'created_at' => $createdAt,
                'updated_at' => $completionDate,
                'deadline' => $deadline,
                'status' => 'completed',
                'completion_date' => $completionDate,
                'result_percentage' => fake()->randomFloat(2, 80, 120),
                'achievement_percentage' => fake()->randomFloat(2, 70, 100),
                'proof_link' => fake()->url(),
            ];
        });
    }

    /**
     * Create goal with future deadline.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'status' => fake()->randomElement(['pending', 'in_progress']),
            'completion_date' => null,
            'result_percentage' => null,
        ]);
    }
}
