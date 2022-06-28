<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            'name' => 'Emiel',
            'email' => 'emiel.roelofsen@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('emiel'),
            'remember_token' => Str::random(10),
        ]);
    }
}
