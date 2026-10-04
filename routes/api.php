<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\ShelfController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/authors', AuthorController::class);
    Route::apiResource('/publishers', PublisherController::class);
    Route::apiResource('/shelves', ShelfController::class);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/books', BookController::class);
    Route::post('/exemplars/create/{id}', [BookController::class, 'createBookExemplar']);
    Route::put('/exemplars/change/{id}', [BookController::class, 'updateBookExemplarStatus']);
    Route::apiResource('/carts', CartController::class)->only(['index', 'store', 'destroy']);
    Route::post('/carts/checkout', [CartController::class, 'checkout']);
});
