<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@taller.com',
            'password' => Hash::make('admin123'),
        ]);

        // Usuario de prueba
        User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@taller.com',
            'password' => Hash::make('demo123'),
        ]);

        echo "✅ Usuarios creados:\n";
        echo "   • admin@taller.com / admin123\n";
        echo "   • demo@taller.com / demo123\n";
    }
}
