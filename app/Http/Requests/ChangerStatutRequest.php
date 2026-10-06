<?php

namespace App\Http\Requests;

use App\Enums\StatutDemande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangerStatutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', 'string', Rule::enum(StatutDemande::class)],
            'motif' => ['required_if:statut,rejetee', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'statut.required' => 'Le nouveau statut est obligatoire.',
            'statut.enum' => 'Statut invalide. Valeurs possibles : deposee, en_cours, validee, rejetee.',
            'motif.required_if' => 'Un motif est obligatoire pour rejeter une demande.',
        ];
    }
}