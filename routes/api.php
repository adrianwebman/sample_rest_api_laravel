<?php

use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Resources\PostCollection;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// The route groups handled by auth sanctum
Route::group(['middleware' => 'auth:sanctum'], function () {

    // The comments routes
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::patch('posts/{post}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('posts/{post}/comments/{comment}', [CommentController::class, 'destroy']);

    // The posts routes
    Route::resource('posts', PostController::class)->only([
        'store', 'update', 'destroy',
    ]);

    // The logout route
    Route::post('/logout', [LogoutController::class, 'index']);
});

// The login route
Route::post('/login', [LoginController::class, 'index']);

// The register route
Route::post('/register', [RegisterController::class, 'index']);

// The post comments route
Route::get('posts/{post}/comments', [CommentController::class, 'show']);

// The posts show method
Route::get('/posts/{post}', [PostController::class, 'show']);

// The posts collection route
Route::get('/posts', function () {
    return new PostCollection(Post::paginate());
});
