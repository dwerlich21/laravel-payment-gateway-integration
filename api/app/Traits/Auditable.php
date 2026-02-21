<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Log;

trait Auditable
{
    /**
     * Fields to exclude from audit logging
     */
    protected function getAuditExcludedFields(): array
    {
        return ['password', 'remember_token', 'updated_at', 'created_at'];
    }

    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit(AuditLog::ACTION_CREATED);
        });

        static::updated(function ($model) {
            $model->logAudit(AuditLog::ACTION_UPDATED);
        });

        static::deleted(function ($model) {
            $model->logAudit(AuditLog::ACTION_DELETED);
        });
    }

    /**
     * Log an audit entry for this model
     */
    protected function logAudit(string $action): void
    {
        $user = Auth::user();
        $excludedFields = $this->getAuditExcludedFields();

        $oldValues = null;
        $newValues = null;
        $description = $this->getAuditDescription($action);

        if ($action === AuditLog::ACTION_CREATED) {
            $newValues = $this->filterAuditFields($this->getAttributes(), $excludedFields);
        } elseif ($action === AuditLog::ACTION_UPDATED) {
            $dirty = $this->getDirty();
            $original = $this->getOriginal();

            // Filter out excluded fields
            $changedFields = array_keys($dirty);
            $filteredFields = array_diff($changedFields, $excludedFields);

            if (empty($filteredFields)) {
                return; // No meaningful changes to log
            }

            $oldValues = [];
            $newValues = [];

            foreach ($filteredFields as $field) {
                $oldValues[$field] = $original[$field] ?? null;
                $newValues[$field] = $dirty[$field] ?? null;
            }
        } elseif ($action === AuditLog::ACTION_DELETED) {
            $oldValues = $this->filterAuditFields($this->getOriginal(), $excludedFields);
            $description = $this->getAuditDescription($action);
        }

        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'action'      => $action,
            'model_type'  => get_class($this),
            'model_id'    => $this->getKey(),
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Filter out excluded fields from audit data
     */
    protected function filterAuditFields(array $data, array $excludedFields): array
    {
        return array_diff_key($data, array_flip($excludedFields));
    }

    /**
     * Get description for audit log
     */
    protected function getAuditDescription(string $action): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getAuditIdentifier();

        return match ($action) {
            AuditLog::ACTION_CREATED => "{$modelName} '{$identifier}' foi criado",
            AuditLog::ACTION_UPDATED => "{$modelName} '{$identifier}' foi atualizado",
            AuditLog::ACTION_DELETED => "{$modelName} '{$identifier}' foi deletado",
            default => "{$modelName} '{$identifier}' - {$action}",
        };
    }

    /**
     * Get identifier for this model in audit logs
     */
    protected function getAuditIdentifier(): string
    {
        // Try common identifier fields
        if (isset($this->name) && !str_contains(strtolower($this->name), 'deleted')) {
            return $this->name;
        }

        if (isset($this->title)) {
            return $this->title;
        }

        if (isset($this->email) && !str_contains(strtolower($this->email), 'deleted')) {
            return $this->email;
        }

        if (isset($this->milestone_description)) {
            return substr($this->milestone_description, 0, 50);
        }

        return "ID: {$this->getKey()}";
    }
}
