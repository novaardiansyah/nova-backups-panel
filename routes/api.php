<?php

use App\Http\Controllers\Api\AgentApiController;
use App\Http\Middleware\AuthenticateAgent;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/agent')
  ->middleware(AuthenticateAgent::class)
  ->group(function (): void {
    Route::post('/heartbeat', [AgentApiController::class, 'heartbeat']);
    Route::post('/tasks/claim', [AgentApiController::class, 'claimTask']);
    Route::post('/tasks/{task:uid}/events', [AgentApiController::class, 'reportEvent']);
    Route::post('/tasks/{task:uid}/complete', [AgentApiController::class, 'completeTask']);
    Route::post('/tasks/{task:uid}/fail', [AgentApiController::class, 'failTask']);
  });
