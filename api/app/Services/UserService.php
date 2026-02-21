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

        return $user;
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

        return $user;
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

        // Hash da senha se fornecida
        if (!empty($password)) {
            $userData['password'] = Hash::make($password);
        } elseif (!$isUpdate) {
            // Senha padrão para novos usuários se não fornecida
            $userData['password'] = Hash::make('123456');
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
