<?php

namespace App\Http\Requests;

class OrderRequest extends BaseRequest
{
    public function rules($id = null): array
    {
        $gateways = implode(',', array_keys(config('payment.gateways', [])));

        $rules = [
            'product_id'       => ['required', 'integer', 'exists:products,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'gateway'          => ['required', 'string', "in:{$gateways}"],
            'payment_method'   => ['required', 'string', 'in:credit_card,boleto,pix'],
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_email'   => ['required', 'email', 'max:255'],
            'customer_cpf_cnpj' => ['required', 'string', 'max:20'],
            'customer_phone'   => ['required', 'string', 'max:20'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute é obrigatório',
            'integer'  => ':attribute deve ser um número inteiro',
            'min'      => ':attribute deve ser no mínimo :min',
            'exists'   => ':attribute selecionado é inválido',
            'in'       => ':attribute deve ser uma das opções disponíveis',
            'email'    => ':attribute deve ser um e-mail válido',
            'max'      => ':attribute deve ter no máximo :max caracteres',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id'        => 'Produto',
            'quantity'          => 'Quantidade',
            'gateway'           => 'Gateway de pagamento',
            'payment_method'    => 'Meio de pagamento',
            'customer_name'     => 'Nome',
            'customer_email'    => 'E-mail',
            'customer_cpf_cnpj' => 'CPF/CNPJ',
            'customer_phone'    => 'Telefone',
        ];
    }
}
