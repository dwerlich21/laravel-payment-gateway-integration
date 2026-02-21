<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    /**
     * UserController constructor.
     *
     * @param  UserRequest  $request
     */
    public function __construct(UserService $service)
    {
        parent::__construct($service, new UserRequest);
    }

    public function index(Request $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $columns = ['id', 'name', 'email', 'phone', 'active', 'avatar'];
            $data = $this->service->get($columns, $request);

            return $this->successResponse($data);

        }, 'Erro ao buscar registros');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($id) {

            $id = base64_decode($id);
            $result = $this->service->show($id);

            return $this->successResponse($result);

        }, 'Erro ao buscar registro');
    }

    public function getUser()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Carrega as permissões com seus names
        $permissions = $user->permissions()->pluck('name')->toArray();

        return response()->json([
            'user' => $user,
            'permissions' => $permissions,
            'success' => true,
            'message' => 'Usuário autenticado',
        ]);
    }

    public function perfil(int $id): BinaryFileResponse
    {
        $path = storage_path('app/private/users/'.$id.'/perfil.png');
        if (! file_exists($path)) {
            $path = storage_path('defaults/user.jpg');
        }

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
