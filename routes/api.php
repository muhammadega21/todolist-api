<?php

use App\Http\Controllers\Api\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('todo', TodoController::class);
