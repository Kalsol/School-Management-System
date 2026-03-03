<?php

namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MyClassesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('my_classes')->delete();

        $p = ClassType::where('name', 'Primary')->first()->id;
        $s = ClassType::where('name', 'Secondary')->first()->id;

        $data = [
            ['name' => 'Grade 9', 'class_type_id' => $s],
            ['name' => 'Grade 10','class_type_id' => $s],
            ['name' => 'Grade 11', 'class_type_id' => $s],
            ['name' => 'Grade 12', 'class_type_id' => $s],
        ];

        DB::table('my_classes')->insert($data);
    }
}