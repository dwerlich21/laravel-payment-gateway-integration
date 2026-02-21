<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use App\Utils\Utils;

class UserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param mixed $idOrMethod ID do usuário ou método HTTP
     * @return array<string, array<int, string>>
     */
    public function rules($userId = null): array
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'unique:users,email,' . $userId],
            'cpf'    => ['required', 'string', 'max:14', new Cpf, 'unique:users,cpf,' . $userId],
            'phone'  => ['required', 'string', 'max:20'],
            'img'    => ['nullable', 'file', 'image', 'max:5120'],
            'active' => ['nullable', 'boolean'],
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
            'required' => ':attribute é obrigatório',
            'string'   => ':attribute deve ser um texto',
            'max'      => ':attribute não pode ter mais que :max caracteres',
            'email'    => ':attribute deve ser um e-mail válido',
            'unique'   => ':attribute já está em uso',
            'size'     => ':attribute deve ter :size caracteres',
            'in'       => ':attribute deve ser um dos valores permitidos',
            'min'      => ':attribute deve ter no mínimo :min caracteres',
            'boolean'  => ':attribute deve ser verdadeiro ou falso',
            'array'    => ':attribute deve ser uma lista',
            'exists'   => ':attribute selecionado é inválido',
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
            'name'     => 'Nome',
            'email'    => 'E-mail',
            'cpf'      => 'CPF',
            'phone'    => 'Telefone',
            'password' => 'Senha',
            'img'      => 'Imagem',
            'imgUrl'   => 'URL da imagem',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (isset($data['cpf'])) {
            $data['cpf'] = Utils::onlyNumbers($data['cpf']);
        }

        if (isset($data['phone'])) {
            $data['phone'] = Utils::onlyNumbers($data['phone']);
        }

        $this->merge($data);
    }
}
