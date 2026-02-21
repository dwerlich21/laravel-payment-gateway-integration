<?php

namespace App\Http\Requests;

class RecoverPasswordRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'password'  => ['required', 'min:8'],
            'password2' => ['required', 'min:8', 'confirmed:password'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required'  => ':attribute é obrigatório',
            'min'       => ':attribute deve ter pelo menos :min caracteres',
            'confirmed' => 'As senhas não são iguais',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password'  => 'Senha',
            'password2' => 'Confirmar Senha',
        ];
    }
}
