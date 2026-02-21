<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Repositories\AuditLogRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    protected AuditLogRepository $repository;

    public function __construct(AuditLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Log a generic audit entry
     */
    public function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $user = Auth::user();

        return $this->repository->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Log authentication event
     */
    public function logAuth(string $action, ?string $description = null, ?string $email = null): AuditLog
    {
        $user = Auth::user();

        return $this->repository->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $email,
            'action' => $action,
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => $email ? ['email' => $email] : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Log export event
     */
    public function logExport(string $type, string $format, ?array $filters = null): AuditLog
    {
        $user = Auth::user();
        $action = $format === 'pdf' ? AuditLog::ACTION_EXPORTED_PDF : AuditLog::ACTION_EXPORTED_EXCEL;

        return $this->repository->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => [
                'type' => $type,
                'format' => $format,
                'filters' => $filters,
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => "Exportou relatório de {$type} em formato ".strtoupper($format),
        ]);
    }

    /**
     * Log status change
     */
    public function logStatusChange(Model $model, string $oldStatus, string $newStatus): AuditLog
    {
        $user = Auth::user();
        $modelName = class_basename($model);

        return $this->repository->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => "{$modelName} teve status alterado de '{$oldStatus}' para '{$newStatus}'",
        ]);
    }

    /**
     * Log password reset
     */
    public function logPasswordReset(?int $userId = null, ?string $email = null): AuditLog
    {
        $user = Auth::user();

        return $this->repository->create([
            'user_id' => $userId ?? $user?->id,
            'user_name' => $email ?? $user?->name,
            'action' => AuditLog::ACTION_PASSWORD_RESET,
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => "Senha foi resetada para o usuário '{$email}'",
        ]);
    }

    /**
     * Get audit logs with filters and pagination
     */
    public function getLogs(array $filters = [], int $perPage = 20)
    {
        return $this->repository->getWithFilters($filters, $perPage);
    }

    /**
     * Get stats for audit dashboard
     */
    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    /**
     * Get available model types from existing logs
     */
    public function getModelTypes(): array
    {
        return $this->repository->getDistinctModelTypes();
    }
}
