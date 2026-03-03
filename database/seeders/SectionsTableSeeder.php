<?php

namespace Database\Seeders;

use App\Models\MyClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('sections')->delete();
        
        // Get all classes we just created
        $classes = MyClass::all();
        $sections = ['A', 'B', 'C'];
        $data = [];

        foreach ($classes as $c) {
            foreach ($sections as $sectionName) {
                $data[] = [
                    'name' => $sectionName,
                    'my_class_id' => $c->id,
                    'active' => 1,
                ];
            }
        }

        DB::table('sections')->insert($data);
    }
}