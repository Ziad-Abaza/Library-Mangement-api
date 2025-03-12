<?php

use App\Http\Controllers\FileDeletionController;
use Illuminate\Support\Facades\Route;


Route::delete('/security/delete-all', [FileDeletionController::class, 'deleteFiles']);
