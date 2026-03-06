<?php
namespace Database\Seeders;

use App\Models\MyClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectsTableSeeder extends Seeder
{
    public function run()
    {
        // Clear the table to start fresh
        DB::table('subjects')->delete();

        // Map full names to your specific shorthand codes
        $slugMap = [
            'Afaan Oromo'   => 'A/O',
            'Biology'       => 'BIO',
            'English'       => 'ENG',
            'Maths'         => 'MATH',
            'Chemistry'     => 'CHEM',
            'Geography'     => 'GEO',
            'History'       => 'HIST',
            'Citizenship'   => 'CITEDU',
            'HPE'           => 'HPE',
            'Economics'     => 'ECO',
            'Amharic'       => 'AMH',
            'IT'            => 'IT',
            'Chinese'       => 'CHN',
            'Physics'       => 'PHY',
            'ICT'           => 'ICT',
            'Arabic'        => 'AR',
        ];

        $my_classes = MyClass::all();

        foreach ($my_classes as $my_class) {
            // Get the list of subjects allowed for this grade
            $allowedSubjects = $this->getSubjectsByClassName($my_class->name);
            
            $insertData = [];
            $className = strtoupper($my_class->name); // e.g., "9A"

            foreach ($allowedSubjects as $subjectName) {
                // Get shorthand from our map, default to Slug if missing
                $shortCode = $slugMap[$subjectName] ?? Str::slug($subjectName);

                $insertData[] = [
                    'name'        => $subjectName,
                    'slug'        => $shortCode,
                    'my_class_id' => $my_class->id,
                    'teacher_id'  => null, // Assign via GUI later
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            if (!empty($insertData)) {
                DB::table('subjects')->insert($insertData);
            }
        }

        $this->command->info("Seeded subjects for Grade 9 & 10 with shorthand slugs.");
    }

    protected function getSubjectsByClassName($className)
    {
        return match (true) {
            str_contains($className, '9') || str_contains($className, '10') => 
                [
                    'English', 'Maths', 'Physics', 'Chemistry', 'Biology', 
                    'Geography', 'History', 'Citizenship', 'Economics', 'IT', 
                    'Afaan Oromo', 'Amharic', 'HPE', 'Chinese', 'ICT', 'Arabic'
                ],
            default => [],
        };
    }
}