<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service, new OrderRequest);
    }

    public function index(Request $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $columns = ['id', 'product_id', 'quantity', 'total_amount', 'status', 'gateway', 'created_at'];
            $request->merge(['with' => 'product']);
            $data = $this->service->get($columns, $request);

            return $this->successResponse($data);

        }, 'Erro ao buscar pedidos');
    }

    public function store(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {

            $this->validation();
            $order = $this->service->create($request->all());

            return $this->successResponse($order, 'Pedido criado! Processando pagamento...', 201);

        }, 'Não foi possível criar o pedido');
    }
}
