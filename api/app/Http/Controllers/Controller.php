<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Services\BaseService;
use App\Traits\ExceptionHandlerTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    use ExceptionHandlerTrait;

    /**
     * @var BaseService
     */
    protected $service;

    /**
     * @var FormRequest
     */
    protected $request;

    /**
     * BaseController constructor.
     */
    public function __construct(BaseService $service, FormRequest $request)
    {
        $this->service = $service;
        $this->request = $request;
    }

    /**
     * Lista todos os registros com filtros aplicados
     *
     * Endpoint: GET /api/resource
     *
     * @param  Request  $request  Requisição HTTP com parâmetros de filtro
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $columns = $request->input('select', '*');
            $data = $this->service->get($columns, $request);

            return $this->successResponse($data);

        }, 'Erro ao buscar registros');
    }

    /**
     * Cria um novo registro
     *
     * Endpoint: POST /api/resource
     *
     * @param  Request  $request  Requisição HTTP com dados do novo registro
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {

            $this->validation();
            $el = $this->service->create($request->all());

            return $this->successResponse($el, 'Cadastro realizado!', 201);

        }, 'Não foi possível adicionar o registro');
    }

    /**
     * Atualiza um registro existente
     *
     * Endpoint: PUT/PATCH /api/resource/{id}
     *
     * @param  Request  $request  Requisição HTTP com dados para atualização
     * @param  string  $id  ID do registro a ser atualizado
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request, $id) {

            $id = base64_decode($id);
            $this->validation($id);
            $el = $this->service->update($request->all(), $id);

            return $this->successResponse($el, 'Registro atualizado');

        }, 'Não foi possível atualizar o registro');
    }

    /**
     * Exibe um registro específico
     *
     * Endpoint: GET /api/resource/{id}
     *
     * @param  Request  $request  Requisição HTTP com parâmetros adicionais
     * @param  string  $id  ID do registro a ser exibido
     */
    public function show(Request $request, string $id): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request, $id) {

            $id = base64_decode($id);
            $request->merge(['id' => $id]);
            $columns = $request->input('select', '*');
            $result = $this->service->get($columns, $request);

            if (empty($result['data'])) {
                throw new NotFoundException('Registro não encontrado');
            }

            return $this->successResponse($result['data'][0]);

        }, 'Erro ao buscar registro');
    }

    /**
     * Remove um registro
     *
     * Endpoint: DELETE /api/resource/{id}
     *
     * @param  string  $id  ID do registro a ser removido
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($id) {

            $id = base64_decode($id);
            $this->service->delete($id);

            return $this->successResponse(null, 'Registro excluído', 204);

        }, 'Não foi possível excluir o registro');
    }

    /**
     * Ativar/desativar um registro
     *
     * Endpoint: PUT /api/{model}/change-active/{id}
     *
     * @param  string  $id  ID do registro a alterado o active
     */
    public function changeActive(string $id): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($id) {

            $id = base64_decode($id);
            $this->service->changeActive($id);

            return $this->successResponse(null, 'Registro excluído', 204);

        }, 'Não foi possível excluir o registro');
    }

    /**
     * Ativar/desativar um registro
     *
     * Endpoint: PUT /api/{model}/available-years
     */
    public function availableYears(): JsonResponse
    {
        $years = $this->service->getAvailableYears();

        return response()->json(['years' => $years]);
    }

    /**
     * Remove múltiplos registros
     *
     * Endpoint: POST /api/{model}/bulk-delete
     *
     * @param  Request  $request  Requisição HTTP com array de IDs
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {

            $ids = $request->input('ids', []);

            if (empty($ids)) {
                throw new NotFoundException('Nenhum registro selecionado');
            }

            $count = $this->service->bulkDelete($ids);

            return $this->successResponse(
                ['deleted' => $count],
                "{$count} registro(s) excluído(s) com sucesso"
            );

        }, 'Não foi possível excluir os registros');
    }

    /**
     * Altera o status ativo de múltiplos registros
     *
     * Endpoint: POST /api/{model}/bulk-change-active
     *
     * @param  Request  $request  Requisição HTTP com array de IDs e status
     */
    public function bulkChangeActive(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {

            $ids = $request->input('ids', []);
            $active = $request->boolean('active');

            if (empty($ids)) {
                return $this->errorResponse('Nenhum registro selecionado', 400);
            }

            $count = $this->service->bulkChangeActive($ids, $active);
            $status = $active ? 'ativado(s)' : 'desativado(s)';

            return $this->successResponse(
                ['updated' => $count],
                "{$count} registro(s) {$status} com sucesso"
            );

        }, 'Não foi possível atualizar os registros');
    }

    /**
     * Valida a requisição de acordo com as regras definidas no FormRequest
     * Aplica transformações automaticamente usando o FormRequest específico
     *
     * @param  string|null  $method  Método HTTP para validação condicional (ex: PUT)
     * @return array Dados validados e transformados
     *
     * @throws ValidationException Quando a validação falha
     */
    protected function validation(?string $id = null): array
    {
        $data = request()->all();

        $data = $this->request->applyTransformations($data);

        request()->replace($data);

        $validator = Validator::make(
            request()->all(),
            $this->request->rules($id),
            $this->request->messages(),
            $this->request->attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }

        return $data;
    }
}
