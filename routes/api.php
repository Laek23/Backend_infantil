<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\LivesController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/billing/webhook', [BillingController::class, 'webhook']);

Route::middleware('auth:sanctum')->group(function () {
	Route::get('/auth/me', [AuthController::class, 'me']);
	Route::post('/auth/logout', [AuthController::class, 'logout']);
	Route::get('/subjects', [SubjectController::class, 'index']);
	Route::get('/subjects/{subject}/activities', [ActivityController::class, 'index']);
	Route::post('/activities/{activity}/answer', [ActivityController::class, 'answer']);
	Route::get('/progress', [ProgressController::class, 'index']);
	Route::put('/progress/{subject}', [ProgressController::class, 'update']);
	Route::get('/ranking', [RankingController::class, 'index']);
	Route::get('/lives', [LivesController::class, 'status']);
	Route::post('/lives/fail', [LivesController::class, 'fail']);
	Route::post('/billing/checkout', [BillingController::class, 'checkout']);
	Route::post('/billing/confirm', [BillingController::class, 'confirm']);
	Route::get('/rewards', [RewardController::class, 'index']);
	Route::put('/rewards/{reward}/equip', [RewardController::class, 'equip']);

	Route::prefix('admin')->group(function () {
		Route::get('/subjects', [SubjectController::class, 'adminIndex']);
		Route::post('/subjects', [SubjectController::class, 'store']);
		Route::patch('/subjects/{subject}', [SubjectController::class, 'update']);
		Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);
		Route::get('/subjects/{subject}/activities', [ActivityController::class, 'adminIndex']);
		Route::post('/subjects/{subject}/activities', [ActivityController::class, 'store']);
		Route::patch('/activities/{activity}', [ActivityController::class, 'update']);
		Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);
	});
});
