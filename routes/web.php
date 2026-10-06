<?php

use Illuminate\Support\Facades\Route;
use App\Models\Demande;
use Illuminate\Http\Request;

Route::get('/usagers/{npi}/demandes', function (Request $request, string $npi) {
    abort_unless(preg_match('/^\d{10}$/', $npi), 404);

    $demandes = Demande::where('npi', $npi)
        ->orderByDesc('created_at')->orderByDesc('id')
        ->paginate(20);

    return view('demandes', compact('npi', 'demandes'));
});
