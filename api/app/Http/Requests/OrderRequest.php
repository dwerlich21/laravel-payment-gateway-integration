<?php

namespace App\Http\Requests;

class OrderRequest extends BaseRequest
{
    public function rules($id = null): array
    {
        $gateways = implode(',', array_keys(config('payment.gateways', [])));

        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'gateway'    => ['nullable', 'string', "in:{$gateways}"],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute é obrigatório',
            'integer'  => ':attribute deve ser um número inteiro',
            'min'      => ':attribute deve ser no mínimo :min',
            'exists'   => ':attribute selecionado é inválido',
            'in'       => ':attribute deve ser um dos gateways disponíveis',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'Produto',
            'quantity'   => 'Quantidade',
            'gateway'    => 'Gateway de pagamento',
        ];
    }
}
