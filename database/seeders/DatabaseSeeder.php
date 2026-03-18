<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LojaSeeder::class,
            UsuarioSeeder::class,
            CategoriaSeeder::class,
            ProdutoSeeder::class,
            PedidoSeeder::class,
            AvaliacaoSeeder::class,
        ]);
    }
}
