<?php

namespace App\Http\Requests;

class ProductRequest extends BaseRequest
{
    public function rules($id = null): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['required', 'numeric', 'min:0.01'],
            'active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute é obrigatório',
            'string'   => ':attribute deve ser um texto',
            'max'      => ':attribute não pode ter mais que :max caracteres',
            'numeric'  => ':attribute deve ser um número',
            'min'      => ':attribute deve ser no mínimo :min',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'Nome',
            'description' => 'Descrição',
            'price'       => 'Preço',
            'active'      => 'Status',
        ];
    }
}
