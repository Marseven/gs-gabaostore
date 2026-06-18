<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// API versionnée — préfixe /api/v1, throttle global pour la disponibilité.
Route::prefix('v1')->middleware('throttle:api')->group(function () {

// Authentification
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Articles
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

    // Catégories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Mouvements
    Route::get('/mouvements', [MouvementController::class, 'index']);
    Route::post('/mouvements/entree', [MouvementController::class, 'storeEntree']);
    Route::post('/mouvements/sortie', [MouvementController::class, 'storeSortie']);
    Route::put('/mouvements/{mouvement}', [MouvementController::class, 'update']);
    Route::delete('/mouvements/{mouvement}', [MouvementController::class, 'destroy']);

    // Stock & dashboard
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/alertes', [StockController::class, 'alertes']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Export
    Route::get('/export/mouvements', [ExportController::class, 'mouvements']);
    Route::get('/export/stock', [ExportController::class, 'stock']);

    // Utilisateurs (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

}); // fin prefix v1
