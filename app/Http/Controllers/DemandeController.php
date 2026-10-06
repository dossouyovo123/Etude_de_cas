<?php

namespace App\Http\Controllers;

use App\Enums\StatutDemande;
use App\Http\Requests\ChangerStatutRequest;
use App\Http\Requests\StoreDemandeRequest;
use App\Http\Resources\DemandeResource;
use App\Models\Demande;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DemandeController extends Controller
{
    public function store(StoreDemandeRequest $request): JsonResponse
    {
        $demande = Demande::create([
            ...$request->validated(),
            'statut' => StatutDemande::Deposee,
        ]);

        return (new DemandeResource($demande))->response()->setStatusCode(201);
    }

    public function show(Demande $demande): DemandeResource
    {
        return new DemandeResource($demande);
    }

    public function indexParUsager(Request $request, string $npi)
    {
        Validator::make(
            ['npi' => $npi, 'statut' => $request->query('statut')],
            [
                'npi' => ['required', 'regex:/^\d{10}$/'],
                'statut' => ['nullable', Rule::enum(StatutDemande::class)],
            ],
            [
                'npi.regex' => 'Le NPI doit comporter exactement 10 chiffres.',
                'statut.enum' => 'Statut invalide. Valeurs possibles : deposee, en_cours, validee, rejetee.',
            ]
        )->validate();

        $query = Demande::where('npi', $npi)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('statut')) {
            $query->where('statut', $request->query('statut'));
        }

        return DemandeResource::collection($query->paginate(20));
    }

    public function changerStatut(ChangerStatutRequest $request, Demande $demande): JsonResponse|DemandeResource
    {
        $cible = StatutDemande::from($request->validated('statut'));

        if ($demande->statut->estFinal()) {
            return response()->json([
                'message' => "Cette demande est déjà {$demande->statut->value} : son statut ne peut plus être modifié.",
            ], 409);
        }

        if (! $demande->statut->peutPasserA($cible)) {
            $autorises = collect($demande->statut->transitionsPossibles())
                ->map(fn ($s) => $s->value)->implode(', ');

            return response()->json([
                'message' => "Transition interdite : de '{$demande->statut->value}' vers '{$cible->value}'. Statuts autorisés : {$autorises}.",
            ], 409);
        }

        $demande->statut = $cible;
        $demande->motif_rejet = $cible === StatutDemande::Rejetee
            ? $request->validated('motif')
            : null;
        $demande->save();

        return new DemandeResource($demande);
    }

    public function statistiques(): JsonResponse
    {
        $comptes = Demande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $resultat = [];
        foreach (StatutDemande::cases() as $statut) {
            $resultat[$statut->value] = (int) ($comptes[$statut->value] ?? 0);
        }

        return response()->json(['data' => $resultat, 'total' => array_sum($resultat)]);
    }
}