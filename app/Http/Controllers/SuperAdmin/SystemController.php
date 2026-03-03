<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Exception;
use Carbon\Carbon;

class SystemController extends Controller
{
 
    public function downloadBackup($fileName)
    {
        $path = config('backup.backup.name') . '/' . $fileName;
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }
        return back()->with('flash_danger', 'File not found.');
    }

    public function deleteBackup($fileName)
    {
        $path = config('backup.backup.name') . '/' . $fileName;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return back()->with('flash_success', 'Backup deleted successfully.');
        }
        return back()->with('flash_danger', 'File not found.');
    }    

    public function runCommand(Request $request)
    {
        $command = $request->command;
        
        // Define allowed commands for security
        $allowedCommands = [
            'cache:clear' => 'Cache Cleared',
            'view:clear'  => 'Views Cleared',
            'config:cache' => 'Config Cached',
            'route:cache'  => 'Routes Cached',
            'backup:run'   => 'Backup Started',
            'backup:clean' => 'Old Backups Cleaned',
            'storage:link' => 'Storage Linked'
        ];

        if (!array_key_exists($command, $allowedCommands)) {
            return back()->with('flash_danger', 'Unauthorized Command');
        }

        try {
            // Run the command
            Artisan::call($command);
            return back()->with('flash_success', $allowedCommands[$command] . ' Successfully!');
        } catch (Exception $e) {
            return back()->with('flash_danger', 'Error: ' . $e->getMessage());
        }
    }
}
