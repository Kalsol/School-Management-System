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

        // Calling this to generate user for each user type
        $this->createAdminUsers();
        $this->createTeacherUsers();
        $this->createParentUsers();
    }
    
    protected function createAdminUsers()
    {
        $password = Hash::make('password');

        $d = [
            [
                'name' => 'Kaleab Solomon',
                'email' => 'superadmin@arsi.edu.et',
                'username' => 'kaleab',
                'password' => $password,
                'user_type' => 'super_admin',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@arsi.edu.et',
                'password' => $password,
                'user_type' => 'admin',
                'username' => 'admin',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ],
        ];
        DB::table('users')->insert($d);
    }

    public function createTeacherUsers()
    {
        $password = Hash::make('teacher');
        // Extracted directly from your image
        $teachers = [
            ['name' => 'Mr. Chala'],
            ['name' => 'Mr. Gurmecha B.'],
            ['name' => 'Mr. Tadele W.'],
            ['name' => 'Mr. Kumela R.'],
            ['name' => 'Mr. Muhammed H.'],
            ['name' => 'Mr. Mohamed A.'],
            ['name' => 'Mr. Tekalegn A.'],
            ['name' => 'Mr. Aschalew D.'],
            ['name' => 'Mr. Getu Al.'],
            ['name' => 'Mr. Tadesse Z.'],
            ['name' => 'Mrs. Weyneshet G.'],
            ['name' => 'Mr. Abdulmelik'],
            ['name' => 'Mr. Fitsum'],
            ['name' => 'Mr. Asnake S.'],
            ['name' => 'Mr. Amare Girma'],
            ['name' => 'Mr. Jeylan H.'],
            ['name' => 'Mr. Kabede'],
            ['name' => 'Mr. Maserat'],
        ];

        $data = [];

        foreach ($teachers as $teacher) {
            // Remove Mr./Mrs. and trailing initials/dots
            $cleanName = preg_replace('/^(Mr\.|Mrs\.|Ms\.|Dr\.)\s+/i', '', $teacher['name']);
            $cleanName = rtrim($cleanName, '. '); // Removes trailing dots like in "Tadele W."

            // Generate Username: e.g., "chala123"
            $username = Str::slug($cleanName, rand(100, 999));
        
            $data[] = [
                'name'           => $teacher['name'],
                'email'          => $username . '@arsi.edu.et',
                'user_type'      => 'teacher',
                'username'       => $username,
                'password'       => $password,
                'code'           => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        DB::table('users')->insert($data);
    }
    
    protected function createParentUsers()
    {
        $data = [];
        $password = Hash::make('parent');
    
        $parentNames = [
            'Abebe Kebede', 'Mulugeta Tesfaye', 'Zewdu Tekle', 'Belaynesh Amare',
            'Tewodros Kassahun', 'Genet Assefa', 'Yohannes Haile', 'Saba Gebremariam',
            'Desta Alemu', 'Kifle Woldemariam', 'Hiwot Tadesse', 'Mesfin Hagos',
            'Almaz Ayana', 'Birhanu Jula', 'Rahel Getachew', 'Solomon Bogale',
            'Martha Wegayehu', 'Samuel Bekele', 'Eskinder Nega', 'Tigist Assefa'
        ];
    
        foreach ($parentNames as $originalName) {

            // Create a clean username (e.g., "parent_chala")
            $username = 'parent_' . Str::slug($originalName, '_');
    
            $data[] = [
                'name'           => $originalName, // Storing name without title
                'email'          => $username . '@arsi.edu.et',
                'user_type'      => 'parent',
                'username'       => $username,
                'password'       => $password,
                'code'           => strtoupper(Str::random(10)),
                'remember_token' => null,
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
