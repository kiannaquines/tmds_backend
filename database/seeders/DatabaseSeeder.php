<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Kian Naquines',
            'email' => 'kjgnaquines@gmail.com',
            'password' => 'password'
        ])->assignRole('Student');

        User::factory()->create([
            'name' => 'Elizabeth R. Gentiva',
            'email' => 'elizabeth@gmail.com',
            'password' => 'password'
        ])->assignRole('Adviser');

        User::factory()->create([
            'name' => 'Catherine Daffon',
            'email' => 'cath@gmail.com',
            'password' => 'password'
        ])->assignRole('Department Research Coordinator');

        User::factory()->create([
            'name' => 'Arjay Agbunag',
            'email' => 'arjay@gmail.com',
            'password' => 'password'
        ])->assignRole('Department Chairperson');

        User::factory()->create([
            'name' => 'Sherly Ortiz',
            'email' => 'ortiz@gmail.com',
            'password' => 'password'
        ])->assignRole('College Research Coordinator');

        User::factory()->create([
            'name' => 'Sherly Dayaday',
            'email' => 'dayaday@gmail.com',
            'password' => 'password'
        ])->assignRole('College Dean');

        User::factory()->create([
            'name' => 'Ryan Gonzaga',
            'email' => 'ryan@gmail.com',
            'password' => 'password'
        ])->assignRole('Panel');
    }
}
