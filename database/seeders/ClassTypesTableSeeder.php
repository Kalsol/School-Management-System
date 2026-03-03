<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('class_types')->delete();

        $data = [
            ['name' => 'Primary', 'code' => 'P'],
            ['name' => 'Secondary', 'code' => 'S'],
        ];

        DB::table('class_types')->insert($data);
    }
}