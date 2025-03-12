<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    protected $model = \App\Models\User::class;

    public function run(): void
    {
        $email = 'admin@gmail.com';
        $find =  User::where('email',$email)->where('role',1)->first();
            if(!$find){
                User::create([
                'first_name' => 'fire',
                'last_name' => 'chill',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 1,
                'email_verified_at' => now(),
            ]);
        }


    }
}
