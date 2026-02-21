<?php

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogRepository extends BaseRepository
{
    /**
     * AuditLogRepository constructor.
     */
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Get audit logs with filters and pagination
     */
    public function getWithFilters(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('user')
            ->orderBy('created_at', 'desc');

        if (! empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->byAction($filters['action']);
        }

        if (! empty($filters['model_type'])) {
            $query->byModelType($filters['model_type']);
        }

        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'] ?? null, $filters['end_date'] ?? null);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                    ->orWhere('user_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get total count
     */
    public function getTotalCount(): int
    {
        return $this->model->count();
    }

    /**
     * Get count since a specific date
     */
    public function getCountSince(\DateTimeInterface $date): int
    {
        return $this->model->where('created_at', '>=', $date)->count();
    }

    /**
     * Get counts grouped by action
     */
    public function getCountsByAction(): array
    {
        return $this->model->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get recent users activity counts
     */
    public function getRecentUsersActivity(int $limit = 5): array
    {
        return $this->model->selectRaw('user_name, count(*) as count')
            ->whereNotNull('user_name')
            ->groupBy('user_name')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'user_name')
            ->toArray();
    }

    /**
     * Get distinct model types
     */
    public function getDistinctModelTypes(): array
    {
        return $this->model->selectRaw('DISTINCT model_type')
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => AuditLog::getModelTypeLabel($type),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent activity with user relation
     */
    public function getRecentActivity(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stats for audit dashboard
     */
    public function getStats(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total' => $this->getTotalCount(),
            'today' => $this->getCountSince($today),
            'this_week' => $this->getCountSince($thisWeek),
            'this_month' => $this->getCountSince($thisMonth),
            'by_action' => $this->getCountsByAction(),
            'recent_users' => $this->getRecentUsersActivity(),
        ];
    }
}
