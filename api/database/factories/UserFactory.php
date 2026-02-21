<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['admin', 'manager', 'user'];
        $positions = [
            'Desenvolvedor',
            'Analista de Sistemas',
            'Gerente de Projetos',
            'Designer',
            'Product Owner',
            'Scrum Master',
            'QA',
            'DevOps',
            'Coordenador',
            'Diretor',
        ];

        return [
            'name' => fake('pt_BR')->name(),
            'email' => fake()->unique()->safeEmail(),
            'cpf' => $this->generateCpf(),
            'type' => fake()->randomElement($types),
            'position' => fake()->randomElement($positions),
            'phone' => fake('pt_BR')->cellphoneNumber(false),
            'avatar' => null,
            'active' => fake()->boolean(85),
            'password' => static::$password ??= Hash::make('password'),
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the user is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manager',
        ]);
    }

    /**
     * Generate a valid CPF number.
     */
    private function generateCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = rand(0, 9);
        }

        // Calculate first digit
        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) $d1 = 0;

        // Calculate second digit
        $d2 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 += $d1 * 2;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) $d2 = 0;

        return sprintf(
            '%d%d%d.%d%d%d.%d%d%d-%d%d',
            $n[0], $n[1], $n[2],
            $n[3], $n[4], $n[5],
            $n[6], $n[7], $n[8],
            $d1, $d2
        );
    }
}
