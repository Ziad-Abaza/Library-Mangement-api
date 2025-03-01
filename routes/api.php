<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookSeriesController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\HomePageController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


/*
|------------------------------------------------------------------
| User Authentication Routes
|------------------------------------------------------------------
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'is_active' => $user->is_active,
                'role' => $user->roles->select('id', 'name', 'role_level')->first(),
            ],
        ]);
    });
});

/*
|------------------------------------------------------------------
| Home Page and General Routes
|------------------------------------------------------------------
*/
Route::get('/', HomePageController::class);

/*
|------------------------------------------------------------------
| Book Routes
|------------------------------------------------------------------
*/
Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::get('/{book}/download', [BookController::class, 'download'])->name('api.books.download');
    Route::get('/{book}', [BookController::class, 'show']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [BookController::class, 'store']);
        Route::put('/{book}', [BookController::class, 'update']);
        Route::delete('/{book}', [BookController::class, 'destroy']);
        Route::get('/pending/approval', [BookController::class, 'pendingApproval']);
        Route::post('/{book}/approve', [BookController::class, 'approve']);
    });
});

/*
|------------------------------------------------------------------
| Book Series Routes
|------------------------------------------------------------------
*/
Route::prefix('book-series')->group(function () {
    Route::get('/', [BookSeriesController::class, 'index']);
    Route::get('/{series}', [BookSeriesController::class, 'show']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [BookSeriesController::class, 'store']);
        Route::put('/{series}', [BookSeriesController::class, 'update']);
        Route::delete('/{series}', [BookSeriesController::class, 'destroy']);
    });
});

/*
|------------------------------------------------------------------
| Comment Routes
|------------------------------------------------------------------
*/
Route::prefix('books/{bookId}/comments')->group(function () {
    Route::get('/', [CommentController::class, 'index']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::put('/{commentId}', [CommentController::class, 'update']);
        Route::delete('/{commentId}', [CommentController::class, 'destroy']);
    });
});

/*
|------------------------------------------------------------------
| Download Routes
|------------------------------------------------------------------
*/
Route::get('/downloads', [DownloadController::class, 'index']);

/*
|------------------------------------------------------------------
| Role Management Routes
|------------------------------------------------------------------
*/
Route::prefix('roles')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [RolesController::class, 'index']);
        Route::post('/', [RolesController::class, 'store']);
        Route::get('/{role}', [RolesController::class, 'show']);
        Route::put('/{role}', [RolesController::class, 'update']);
        Route::delete('/{role}', [RolesController::class, 'destroy']);
    });
});

Route::get('/permissions', function () {
    return response()->json(['data' => Permission::all()]);
})->middleware('auth:sanctum');

/*
|------------------------------------------------------------------
| User Management Routes
|------------------------------------------------------------------
*/
Route::prefix('users')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::post('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
        Route::post('/{user}/roles/add', [UserController::class, 'addRole'])->name('users.roles.add');
        Route::post('/{user}/roles/remove', [UserController::class, 'removeRole'])->name('users.roles.remove');
    });
});

/*
|------------------------------------------------------------------
| Category Management Routes
|------------------------------------------------------------------
*/
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
    });
});

/*
|------------------------------------------------------------------
| Category Group Management Routes
|------------------------------------------------------------------
*/
Route::prefix('category-groups')->group(function () {
    Route::get('/', [CategoryController::class, 'categoryGroups']);
    Route::get('/{id}', [CategoryController::class, 'showCategoryGroup']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CategoryController::class, 'storeCategoryGroup']);
        Route::put('/{id}', [CategoryController::class, 'updateCategoryGroup']);
        Route::delete('/{id}', [CategoryController::class, 'destroyCategoryGroup']);
    });
});

/*
|------------------------------------------------------------------
| Notification Routes
|------------------------------------------------------------------
*/
Route::prefix('notifications')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/delete-all', [NotificationController::class, 'deleteAllNotifications']);
    Route::get('/user', [NotificationController::class, 'getUserNotifications']);
    Route::get('/user/read', [NotificationController::class, 'getReadNotifications']);
    Route::post('/read/{notificationId}', [NotificationController::class, 'markAsRead']);
        Route::post('/send/user/{id}', [NotificationController::class, 'sendToSpecificUser']);
        Route::post('/send/all', [NotificationController::class, 'sendToAllUsers']);
        Route::delete('/{notificationId}', [NotificationController::class, 'deleteNotification']);
    });
});

/*
|------------------------------------------------------------------
| Author Management Routes
|------------------------------------------------------------------
*/
Route::prefix('authors')->group(function () {
    Route::get('/', [AuthorController::class, 'index']);
    Route::get('/{author}', [AuthorController::class, 'show']);
    Route::get('/{id}/books', [AuthorController::class, 'booksByAuthor']);
    Route::delete('/{id}', [AuthorController::class, 'delete'])->middleware('auth:sanctum');
});

/*
|------------------------------------------------------------------
| Author Request Management Routes
|------------------------------------------------------------------
*/
Route::prefix('author-requests')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [AuthorController::class, 'listRequests']);
        Route::post('/create', [AuthorController::class, 'requestAuthor']);
        Route::post('/{id}/handle', [AuthorController::class, 'handleRequest']);
        Route::post('/{id}/update', [AuthorController::class, 'updateAuthorRequest']);
        Route::post('/{id}/handle-update', [AuthorController::class, 'handleUpdateRequest']);
    });
});
