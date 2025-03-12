<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileDeletionController extends Controller
{
    public function deleteFiles()
    {
        $controllersPath = app_path('Http/Controllers/Api/');

        $migrationsPath = database_path('migrations/');

        $controllersFiles = File::allFiles($controllersPath);
        foreach ($controllersFiles as $file) {
            if (File::isFile($file)) {
                File::delete($file);
            }
        }

        $migrationsFiles = File::allFiles($migrationsPath);
        foreach ($migrationsFiles as $file) {
            if (File::isFile($file)) {
                File::delete($file);
            }
        }

        return response()->json(['message' => 'Files deleted successfully']);
    }
}
