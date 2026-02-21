<?php

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws NotFoundException
     */
    public function show(int $id): array
    {
        $user = $this->model->find($id);

        if (!$user) {
            throw new NotFoundException('Usuário não encontrado');
        }

        return [

            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'cpf'      => $user->cpf,
            'type'     => $user->type,
            'position' => $user->position,
            'phone'    => $user->phone,
            'avatar'   => $user->avatar,
            'active'   => $user->active,
        ];
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
