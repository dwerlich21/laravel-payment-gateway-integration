<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Jobs\RecoverPasswordEmail;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class UserService extends BaseService
{
    /**
     * UserService constructor.
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new user with related data
     */
    public function create(array $data): mixed
    {
        // Preparar dados do usuário
        $userData = $this->prepareUserData($data);

        // Criar usuário
        $user = $this->repository->create($userData);

        // Upload da imagem se existir (pode estar em basicInfo.img ou avatar)
        $imageFile = $data['basicInfo']['img'] ?? $data['avatar'] ?? null;
        if ($imageFile instanceof UploadedFile) {
            $this->uploadAvatar($imageFile, $user->id);

            $time = time();
            $userData['avatar'] = "users/perfil/{$user->id}?v={$time}";

            $user->update(['avatar' => $userData['avatar']]);
        }

        $this->saveAddress($user, $data);
        $this->savePermissions($user, $data);

        return $user->load(['address', 'permissions']);
    }

    /**
     * Update an existing user with related data
     *
     * @throws NotFoundException
     */
    public function update(array $data, int $id): mixed
    {
        $user = $this->repository->find($id);

        // Preparar dados do usuário
        $userData = $this->prepareUserData($data, true);

        $imageFile = $data['basicInfo']['img'] ?? null;
        if ($imageFile instanceof UploadedFile) {
            $this->uploadAvatar($imageFile, $id);
            $time = time();
            $userData['avatar'] = "users/perfil/{$id}?v={$time}";
        }

        // Atualizar usuário
        $user->update($userData);

        $this->saveAddress($user, $data);
        $this->savePermissions($user, $data);

        return $user->load(['address', 'permissions']);
    }

    private function savePermissions($user, $data): void
    {
        $permissions = $data['permissions'] ?? [];

        if (!empty($permissions)) {
            // Converte para array de inteiros se vier como strings
            $permissionIds = array_map('intval', $permissions);
            $user->permissions()->sync($permissionIds);
        }
    }

    private function saveAddress($user, $data): void
    {
        // Criar ou atualizar endereço se existir
        $addressData = $data['address'] ?? null;
        if ($addressData && $this->hasNonEmptyValues($addressData)) {
            // Converter keys do formato camelCase para snake_case se necessário
            $addressFormatted = [
                'zip_code'     => $addressData['zipCode'] ?? $addressData['zip_code'] ?? null,
                'uf'           => $addressData['uf'] ?? null,
                'city'         => $addressData['city'] ?? null,
                'neighborhood' => $addressData['neighborhood'] ?? null,
                'address'      => $addressData['address'] ?? null,
                'number'       => $addressData['number'] ?? null,
                'complement'   => $addressData['complement'] ?? null,
                'tenant_id'    => auth()->user()->tenant_id ?? null,
            ];

            // Usar updateOrCreate para evitar duplicatas
            $user->address()->updateOrCreate(
                ['user_id' => $user->id],
                $addressFormatted
            );
        }
    }

    public function show($id): mixed
    {
        return $this->repository->show($id);
    }

    /**
     * Prepare user data for creation or update
     */
    private function prepareUserData(array $data, bool $isUpdate = false): array
    {
        // Se os dados vierem em basicInfo, extrai eles
        if (isset($data['basicInfo'])) {
            $basicInfo = $data['basicInfo'];
            $userData = [
                'name'     => $basicInfo['name'] ?? null,
                'email'    => $basicInfo['email'] ?? null,
                'cpf'      => $basicInfo['cpf'] ?? null,
                'type'     => $basicInfo['type'] ?? null,
                'position' => $basicInfo['position'] ?? null,
                'phone'    => $basicInfo['phone'] ?? null,
                'active'   => $basicInfo['active'] ?? $data['active'] ?? true,
            ];

            // Password pode estar em basicInfo ou na raiz
            $password = $basicInfo['password'] ?? $data['password'] ?? null;
        } else {
            // Dados flat (sem basicInfo)
            $userData = [
                'name'     => $data['name'] ?? null,
                'email'    => $data['email'] ?? null,
                'cpf'      => $data['cpf'] ?? null,
                'type'     => $data['type'] ?? null,
                'position' => $data['position'] ?? null,
                'phone'    => $data['phone'] ?? null,
                'active'   => $data['active'] ?? true,
            ];

            $password = $data['password'] ?? null;
        }

        // Hash da senha se fornecida
        if (!empty($password)) {
            $userData['password'] = Hash::make($password);
        } elseif (!$isUpdate) {
            // Senha padrão para novos usuários se não fornecida
            $userData['password'] = Hash::make('123456');
        }

        // Adicionar tenant_id do usuário autenticado
        if (!$isUpdate && auth()->user()) {
            $userData['tenant_id'] = auth()->user()->tenant_id;
        }

        // Remover valores nulos para update
        if ($isUpdate) {
            $userData = array_filter($userData, function ($value) {
                return $value !== null;
            });
        }

        return $userData;
    }

    /**
     * Upload avatar image to storage
     */
    private function uploadAvatar(UploadedFile $file, $id): string
    {
        $path = "users/{$id}/";
        $filename = 'perfil.png';

        return $file->storeAs($path, $filename, 'local');
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function recoverPassword($data): void
    {
        $errors = [];
        if (empty($data['email'])) {
            $errors['email'] = ['E-mail é obrigatório para recuperação de senha'];
        }

        if (empty($data['token'])) {
            $errors['token'] = ['Token é obrigatório para recuperação de senha'];
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $user = $this->repository->findByEmail($data['email']);

        if (!$user) {
            throw new NotFoundException('Usuário não encontrado com o e-mail fornecido');
        }

        if (!Password::broker()->tokenExists($user, $data['token'])) {
            throw new NotFoundException('Token de recuperação de senha inválido ou expirado');
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->delete();
    }

    /**
     * @throws NotFoundException
     */
    public function forgotPassword($data)
    {
        $user = $this->repository->findByEmail($data['email']);

        if (!$user) {
            throw new NotFoundException('Usuário não encontrado com o e-mail fornecido');
        }

        RecoverPasswordEmail::dispatch($user);
    }
}
