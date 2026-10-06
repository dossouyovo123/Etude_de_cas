<?php

namespace App\Http\Requests;

use App\Enums\TypeActe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'npi' => ['required', 'string', 'regex:/^\d{10}$/'],
            'type_acte' => ['required', 'string', Rule::enum(TypeActe::class)],
            'nombre_copies' => ['required', 'integer', 'between:1,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'npi.required' => 'Le NPI est obligatoire.',
            'npi.regex' => 'Le NPI doit comporter exactement 10 chiffres.',
            'type_acte.required' => 'Le type d\'acte est obligatoire.',
            'type_acte.enum' => 'Le type d\'acte doit être : acte_naissance, casier_judiciaire ou certificat_residence.',
            'nombre_copies.required' => 'Le nombre de copies est obligatoire.',
            'nombre_copies.integer' => 'Le nombre de copies doit être un entier.',
            'nombre_copies.between' => 'Le nombre de copies doit être compris entre 1 et 5.',
        ];
    }
}