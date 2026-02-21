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
        $user = $this->model->with(['address', 'permissions'])->find($id);

        if (! $user) {
            throw new NotFoundException('Usuário não encontrado');
        }

        return [
            'basicInfo' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'type' => $user->type,
                'position' => $user->position,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'active' => $user->active,
            ],
            'address' => $user->address ? [
                'zipCode' => $user->address->zip_code,
                'uf' => $user->address->uf,
                'city' => $user->address->city,
                'neighborhood' => $user->address->neighborhood,
                'address' => $user->address->address,
                'number' => $user->address->number,
                'complement' => $user->address->complement,
            ] : [],
            'permissions' => $user->permissions->pluck('id')->toArray(),
        ];
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get total count of users
     */
    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    /**
     * Get count of active users
     */
    public function getActiveCount(): int
    {
        return $this->model->where('active', true)->count();
    }

    /**
     * Get count of inactive users
     */
    public function getInactiveCount(): int
    {
        return $this->model->where('active', false)->count();
    }

    /**
     * Get users grouped by position
     */
    public function getCountsByPosition(): array
    {
        return $this->model->select('position', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->groupBy('position')
            ->get()
            ->map(function ($item) {
                return [
                    'position' => $item->position,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get users with goal counts
     */
    public function getWithGoalCounts(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->select('id', 'name', 'email', 'position', 'active', 'created_at')
            ->withCount(['goalsAsResponsible' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->withCount('goalsAsResponsible as total_goals')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get top performers (users with best completion rates)
     */
    public function getTopPerformers(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->select('users.id', 'users.name', 'users.position')
            ->withCount(['goalsAsResponsible as total_goals'])
            ->withCount(['goalsAsResponsible as completed_goals' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->having('total_goals', '>', 0)
            ->orderByRaw('(completed_goals / total_goals) DESC')
            ->limit($limit)
            ->get();
    }
}
