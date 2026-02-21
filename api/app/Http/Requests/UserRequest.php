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
            'basicInfo.name'     => ['required', 'string', 'max:255'],
            'basicInfo.email'    => ['required', 'email', 'unique:users,email,' . $userId],
            'basicInfo.cpf'      => ['required', 'string', 'max:14', new Cpf, 'unique:users,cpf,' . $userId],
            'basicInfo.type'     => ['required', 'string', 'max:50'],
            'basicInfo.position' => ['required', 'string', 'max:100'],
            'basicInfo.phone'    => ['required', 'string', 'max:20'],
            'basicInfo.img'      => ['nullable', 'file', 'image', 'max:5120'],

            'address.zipCode'      => ['required', 'string', 'max:10'],
            'address.uf'           => ['required', 'string', 'size:2'],
            'address.city'         => ['required', 'string', 'max:100'],
            'address.neighborhood' => ['required', 'string', 'max:100'],
            'address.address'      => ['required', 'string', 'max:255'],
            'address.number'       => ['nullable', 'string', 'max:20'],
            'address.complement'   => ['nullable', 'string', 'max:100'],

            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer', 'exists:permissions,id'],

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

            'permissions.required' => 'Selecione pelo menos uma permissão',
            'permissions.min'      => 'Selecione pelo menos uma permissão',
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
            'basicInfo.name'     => 'Nome',
            'basicInfo.email'    => 'E-mail',
            'basicInfo.cpf'      => 'CPF',
            'basicInfo.type'     => 'Nível de acesso',
            'basicInfo.position' => 'Cargo',
            'basicInfo.phone'    => 'Telefone',
            'basicInfo.password' => 'Senha',
            'basicInfo.img'      => 'Imagem',
            'basicInfo.imgUrl'   => 'URL da imagem',

            'address.zipCode'      => 'CEP',
            'address.uf'           => 'Estado',
            'address.city'         => 'Cidade',
            'address.neighborhood' => 'Bairro',
            'address.address'      => 'Logradouro',
            'address.number'       => 'Número',
            'address.complement'   => 'Complemento',

            'permissions'   => 'Permissões',
            'permissions.*' => 'Permissão',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (isset($data['basicInfo']['cpf'])) {
            $data['basicInfo']['cpf'] = Utils::onlyNumbers($data['basicInfo']['cpf']);
        }

        if (isset($data['basicInfo']['phone'])) {
            $data['basicInfo']['phone'] = Utils::onlyNumbers($data['basicInfo']['phone']);
        }

        $this->merge($data);
    }
}
