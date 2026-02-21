<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service, new ProductRequest);
    }

    /**
     * Lista produtos ativos para a vitrine.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $request->merge(['active' => 1]);
            $columns = ['id', 'name', 'description', 'price'];
            $data = $this->service->get($columns, $request);

            return $this->successResponse($data);

        }, 'Erro ao buscar produtos');
    }
}
