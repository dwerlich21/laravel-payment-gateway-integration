<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Plano Basic',
                'description' => 'Acesso às funcionalidades essenciais da plataforma.',
                'price'       => 9.99,
                'active'      => true,
            ],
            [
                'name'        => 'Plano Pro',
                'description' => 'Todas as funcionalidades + relatórios avançados e suporte prioritário.',
                'price'       => 29.99,
                'active'      => true,
            ],
            [
                'name'        => 'Plano Enterprise',
                'description' => 'Solução completa com API dedicada, SLA garantido e gestor de conta.',
                'price'       => 99.99,
                'active'      => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
