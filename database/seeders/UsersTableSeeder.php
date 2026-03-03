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

    protected function createManyUsers(int $count)
    {
        $data = [];
        $password = Hash::make('password');
        
        $firstNames = ['Abebe', 'Almaz', 'Bekele', 'Chala', 'Dawit', 'Eden', 'Fasil', 'Genet', 'Hanna', 'Ismael', 'Jember', 'Kassa', 'Luel', 'Marta', 'Nardos', 'Omar', 'Pawlos', 'Ruth', 'Samuel', 'Tadesse'];
        $lastNames = ['Kebede', 'Tekle', 'Mamo', 'Girma', 'Wolde', 'Tesfaye', 'Bekele', 'Aberra', 'Tessema', 'Desta'];
    
        $types = ['teacher', 'parent'];
    
        foreach ($types as $type) {
            for ($i = 1; $i <= $count; $i++) {
                $fName = $firstNames[array_rand($firstNames)];
                $lName = $lastNames[array_rand($lastNames)];
                
                // Format name: "Abebe Kebede (Teacher 1)"
                $fullName = $fName . ' ' . $lName . ' (' . ucfirst($type) . ' ' . $i . ')';
                
                // Unique Username: "teacher_abebe_1"
                // This guarantees uniqueness even if the random name is picked again
                $username = strtolower($type . '_' . $fName . '_' . $i);
                
                $data[] = [
                    'name' => $fullName,
                    'email' => $username . '@sms.com',
                    'user_type' => $type,
                    'username' => $username,
                    'password' => $password,
                    'code' => strtoupper(Str::random(10)),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                // Insert in chunks to keep it clean
                if (count($data) >= 50) {
                    DB::table('users')->insert($data);
                    $data = [];
                }
            }
        }
    
        if (!empty($data)) {
            DB::table('users')->insert($data);
        }
    }
}