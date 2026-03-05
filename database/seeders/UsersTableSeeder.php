<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Qs;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        $this->createNewUsers();
        // Calling this to generate 50 teachers and 50 parents
        $this->createManyUsers(25);
    }

    protected function createNewUsers()
    {
        $password = Hash::make('password');

        $d = [
            [
                'name' => 'Kaleab Solomon',
                'email' => 'superadmin@sms.com',
                'username' => 'kaleab',
                'password' => $password,
                'user_type' => 'super_admin',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@sms.com',
                'password' => $password,
                'user_type' => 'admin',
                'username' => 'admin',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
        ];
        DB::table('users')->insert($d);
    }

    protected function createManyUsers()
    {
        $data = [];
        $password = Hash::make('password');
        
        // The specific list of teachers
        $teacherNames = [
            'Mr. Chala', 'Mr. Gurmecha', 'Mr. Tadele', 'Mr. Kumela', 
            'Mr. Muhammed', 'Mr. Tekalegn', 'Mr. Aschalew', 'Mr. Getu', 
            'Mr. Tadesse', 'Mrs. Weyneshet', 'Mr. Abdulmelik', 'Mr. Asnake', 
            'Mr. Fitsum', 'Mr. Amare', 'Mr. Jeylan', 'Mr. Kabede', 'Mr. Maserat'
        ];
    
        foreach ($teacherNames as $originalName) {
            // 1. Remove "Mr. " or "Mrs. " (case insensitive)
            $cleanName = preg_replace('/^(Mr\.|Mrs\.|Ms\.|Dr\.)\s+/i', '', $originalName);
            
            // 2. Create a clean username (e.g., "teacher_chala")
            // Str::slug converts "Weyneshet" to "weyneshet" and handles spaces if they exist
            $username = 'teacher_' . Str::slug($cleanName, '_');
    
            $data[] = [
                'name'           => $cleanName, // Storing name without title
                'email'          => $username . '@sms.com',
                'user_type'      => 'teacher',
                'username'       => $username,
                'password'       => $password,
                'code'           => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
    
            // Optional: Insert in chunks if the list grows very large
            if (count($data) >= 50) {
                DB::table('users')->insert($data);
                $data = [];
            }
        }
    
        // Insert remaining records
        if (!empty($data)) {
            DB::table('users')->insert($data);
        }
    }
}