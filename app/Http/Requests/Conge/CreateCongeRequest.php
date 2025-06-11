<?php

namespace App\Http\Requests\Conge;

use Illuminate\Foundation\Http\FormRequest;

class CreateCongeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type_conge_id' => ['required', 'exists:type_conges,id'],
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'motif' => ['nullable', 'string', 'max:500'],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'type_conge_id.required' => 'Le type de congé est requis',
            'type_conge_id.exists' => 'Le type de congé sélectionné n\'existe pas',
            'date_debut.required' => 'La date de début est requise',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_debut.after_or_equal' => 'La date de début doit être aujourd\'hui ou une date future',
            'date_fin.required' => 'La date de fin est requise',
            'date_fin.date' => 'La date de fin doit être une date valide',
            'date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début',
            'motif.max' => 'Le motif ne doit pas dépasser 500 caractères',
            'justificatif.file' => 'Le justificatif doit être un fichier',
            'justificatif.mimes' => 'Le justificatif doit être un fichier PDF, JPG, JPEG ou PNG',
            'justificatif.max' => 'Le justificatif ne doit pas dépasser 5Mo'
        ];
    }
}
