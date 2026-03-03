<?php

namespace Database\Seeders;

use App\Models\MyClass;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('subjects')->delete();

        // 1. Get all available teachers
        $teachers = User::where('user_type', 'teacher')->get();
        
        if ($teachers->isEmpty()) {
            $this->command->error("No teachers found! Please run UsersTableSeeder first.");
            return;
        }

        $my_classes = MyClass::all();
        
        // We use a counter to cycle through teachers so they get a fair distribution
        $teacherIndex = 0;
        $teacherCount = $teachers->count();

        foreach ($my_classes as $my_class) {
            $subjects = $this->getSubjectsByClassName($my_class->name);
            
            $insertData = [];
            foreach ($subjects as $subjectName) {
                // 2. Assign the current teacher in the loop
                $assignedTeacher = $teachers[$teacherIndex];

                $insertData[] = [
                    'name'        => $subjectName,
                    'slug'        => Str::slug($my_class->name . '-' . $subjectName),
                    'my_class_id' => $my_class->id,
                    'teacher_id'  => $assignedTeacher->id, // Assigns a different teacher
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                // 3. Move to the next teacher for the next subject
                // If we reach the end of the 50 teachers, start back at 0
                $teacherIndex = ($teacherIndex + 1) % $teacherCount;
            }

            if (!empty($insertData)) {
                DB::table('subjects')->insert($insertData);
            }
        }
        
        $this->command->info("Subjects seeded and distributed among " . $teacherCount . " teachers.");
    }

    protected function getSubjectsByClassName($className)
    {
        return match (true) {
            str_contains($className, '9') || str_contains($className, '10') => 
                ['Amharic', 'Biology', 'Chemistry', 'Civics and Ethical Education', 'English', 'Mathematics', 'Mother Tongue', 'Physical Education', 'Physics', 'Geography', 'History', 'Information Technology'],
                
                str_contains($className, '11') || str_contains($className, '12') => 
                ['Biology', 'Chemistry', 'Physics', 'Technical Drawing', 'English', 'Civics', 'Physical Education', 'Mathematics', 'Information Technology'],
                
                default => [],
        };
    }
}