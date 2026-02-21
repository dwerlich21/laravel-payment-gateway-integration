<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Método público para aplicar transformações
     *
     * @param array $data - Dados para transformar
     * @return array - Dados transformados
     */
    public function applyTransformations(array $data): array
    {
        // Armazena os dados no request
        $this->merge($data);

        // Chama o método de preparação
        $this->prepareForValidation();

        // Retorna os dados atualizados do request
        return $this->all();
    }

    /**
     * Método que deve ser sobrescrito nas classes filhas
     * Este método deve usar $this->merge() para aplicar as transformações
     */
    protected function prepareForValidation(): void
    {
        // Por padrão, não faz transformação
        $data = $this->all();

        // Classes filhas devem sobrescrever e usar $this->merge()
        $this->merge($data);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $exception = new ValidationException($validator->errors()->toArray());

        throw new HttpResponseException($exception->render());
    }
}
