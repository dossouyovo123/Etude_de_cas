<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemandeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/demandes', [DemandeController::class, 'store']);

Route::get('/demandes/statistiques', [DemandeController::class, 'statistiques']);

Route::get('/demandes/{demande}', [DemandeController::class, 'show']);

Route::patch('/demandes/{demande}/statut', [DemandeController::class, 'changerStatut']);

Route::get('/usagers/{npi}/demandes', [DemandeController::class, 'indexParUsager']);